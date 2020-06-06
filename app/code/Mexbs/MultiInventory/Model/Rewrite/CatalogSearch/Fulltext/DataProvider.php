<?php
namespace Mexbs\MultiInventory\Model\Rewrite\CatalogSearch\Fulltext;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;

class DataProvider extends \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
{
    protected $fullTextDataProvider;
    protected $stockConfiguration;
    protected $miHelper;
    protected $storeManager;
    protected $stockResource;
    protected $scopeConfig;
    protected $engine;
    protected $connection;
    protected $catalogProductType;
    protected $attributeOptions = [];
    protected $separator = ' | ';
    protected $productEmulators = [];
    protected $productTypes = [];

    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider $fullTextDataProvider,
        StockConfigurationInterface $stockConfiguration,
        \Mexbs\MultiInventory\Helper\Data $miHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Model\ResourceModel\Stock $stockResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider,
        ResourceConnection $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        int $antiGapMultiplier = 5
    ) {
        $this->fullTextDataProvider = $fullTextDataProvider;
        $this->stockConfiguration = $stockConfiguration;
        $this->miHelper = $miHelper;
        $this->storeManager = $storeManager;
        $this->stockResource = $stockResource;
        $this->scopeConfig = $scopeConfig;
        $this->catalogProductType = $catalogProductType;
        $this->engine = $engineProvider->get();
        $this->connection = $resource->getConnection();

        parent::__construct(
            $resource,
            $catalogProductType,
            $eavConfig,
            $prodAttributeCollectionFactory,
            $engineProvider,
            $eventManager,
            $storeManager,
            $metadataPool,
            $antiGapMultiplier
        );
    }


    private function filterOutOfStockProducts($indexData, $storeId)
    {
        if (!$this->stockConfiguration->isShowOutOfStock($storeId)) {

            /**
             * @var \Magento\Store\Model\Store $store
             */
            $store = $this->storeManager->getStore($storeId);
            if($store->getId()){
                $websiteId = $store->getWebsite()->getId();
                $defaultWebsiteId = $this->stockConfiguration->getDefaultScopeId();

                $activeItemIds = $this->miHelper->getActiveStockItemIds($websiteId);

                $itemTable = $this->stockResource->getTable('cataloginventory_stock_item');
                $select = $this->stockResource->getConnection()->select()->from(['si' => $itemTable], 'product_id')
                    ->where('website_id IN (?)', [$websiteId, $defaultWebsiteId])
                    ->where('product_id IN(?)', array_keys($indexData));

                $isConfigManageStock = $this->scopeConfig->getValue(
                    \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                if(!empty($activeItemIds)){
                    $select = $select->where('item_id IN(?)', $activeItemIds)
                        ->where(sprintf("((use_config_manage_stock = 1 AND 1 = %s) OR (use_config_manage_stock = 0 AND manage_stock = 1 AND is_in_stock = 1))", $isConfigManageStock));
                }

                $inStockProductRows = $this->connection->query($select)->fetchAll();
                $inStockProductIds =
                    array_map(
                        function ($productRow) {
                            return [$productRow['product_id'] => $productRow['product_id']];
                        },
                        $inStockProductRows
                    );
                $indexData = array_intersect_key($indexData,$inStockProductIds);
            }
        }
        return $indexData;
    }

    private function filterAttributeValue($value)
    {
        return preg_replace('/\s+/iu', ' ', trim(strip_tags($value)));
    }

    private function getAttributeOptionValue($attributeId, $valueIds, $storeId)
    {
        $optionKey = $attributeId . '-' . $storeId;
        $attributeValueIds = explode(',', $valueIds);
        $attributeOptionValue = '';
        if (!array_key_exists($optionKey, $this->attributeOptions)
        ) {
            $attribute = $this->getSearchableAttribute($attributeId);
            if ($this->engine->allowAdvancedIndex()
                && $attribute->getIsSearchable()
                && $attribute->usesSource()
            ) {
                $attribute->setStoreId($storeId);
                $options = $attribute->getSource()->toOptionArray();
                $this->attributeOptions[$optionKey] = array_column($options, 'label', 'value');
                $this->attributeOptions[$optionKey] = array_map(function ($value) {
                    return $this->filterAttributeValue($value);
                }, $this->attributeOptions[$optionKey]);
            } else {
                $this->attributeOptions[$optionKey] = null;
            }
        }
        foreach ($attributeValueIds as $attrValueId) {
            if (isset($this->attributeOptions[$optionKey][$attrValueId])) {
                $attributeOptionValue .= $this->attributeOptions[$optionKey][$attrValueId] . ' ';
            }
        }
        return empty($attributeOptionValue) ? null : trim($attributeOptionValue);
    }

    private function getAttributeValue($attributeId, $valueIds, $storeId)
    {
        $attribute = $this->getSearchableAttribute($attributeId);
        $value = $this->engine->processAttributeValue($attribute, $valueIds);
        if (false !== $value) {
            $optionValue = $this->getAttributeOptionValue($attributeId, $valueIds, $storeId);
            if (null === $optionValue) {
                $value = $this->filterAttributeValue($value);
            } else {
                $value = implode($this->separator, array_filter([$value, $optionValue]));
            }
        }

        return $value;
    }

    private function getProductEmulator($typeId)
    {
        if (!isset($this->productEmulators[$typeId])) {
            $productEmulator = new \Magento\Framework\DataObject();
            $productEmulator->setTypeId($typeId);
            $this->productEmulators[$typeId] = $productEmulator;
        }
        return $this->productEmulators[$typeId];
    }

    private function getProductTypeInstance($typeId)
    {
        if (!isset($this->productTypes[$typeId])) {
            $productEmulator = $this->getProductEmulator($typeId);

            $this->productTypes[$typeId] = $this->catalogProductType->factory($productEmulator);
        }
        return $this->productTypes[$typeId];
    }

    public function prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = [];

        $indexData = $this->filterOutOfStockProducts($indexData, $storeId);

        foreach ($this->getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                if ('store_id' === $attributeCode) {
                    continue;
                }

                $value = $this->getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    if (isset($index[$attribute->getId()])) {
                        if (!is_array($index[$attribute->getId()])) {
                            $index[$attribute->getId()] = [$index[$attribute->getId()]];
                        }
                        $index[$attribute->getId()][] = $value;
                    } else {
                        $index[$attribute->getId()] = $value;
                    }
                }
            }
        }
        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValues) {
                $value = $this->getAttributeValue($attributeId, $attributeValues, $storeId);
                if (!empty($value)) {
                    if (isset($index[$attributeId])) {
                        $index[$attributeId][$entityId] = $value;
                    } else {
                        $index[$attributeId] = [$entityId => $value];
                    }
                }
            }
        }

        $product = $this->getProductEmulator(
            $productData['type_id']
            )->setId(
                $productData['entity_id']
            )->setStoreId(
                $storeId
            );
        $typeInstance = $this->getProductTypeInstance($productData['type_id']);
        $data = $typeInstance->getSearchableData($product);
        if ($data) {
            $index['options'] = $data;
        }

        return $this->engine->prepareEntityIndex($index, $this->separator);
    }


}