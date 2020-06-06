<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as OptionFactory;
use MageWorx\OptionBase\Model\ResourceModel\DataSaver;
use MageWorx\OptionBase\Model\OptionSaver\Option as OptionDataCollector;
use MageWorx\OptionTemplates\Model\Group as GroupModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\OptionBase\Model\ResourceModel\Option as MageworxOptionResource;
use MageWorx\OptionBase\Model\ProductAttributes;
use MageWorx\OptionImportExport\Model\Config\Source\MigrationMode;

class OptionHandler
{
    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var OptionDataCollector
     */
    protected $optionDataCollector;

    /**
     * @var MageworxOptionResource
     */
    protected $mageworxOptionResource;

    /**
     * @var DataSaver
     */
    protected $dataSaver;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $optionData;

    /**
     * @var GroupModel
     */
    protected $groupModel;

    /**
     * @var array
     */
    protected $optionsToDelete;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CollectionUpdaterRegistry
     */
    protected $collectionUpdaterRegistry;

    /**
     * @var array
     */
    protected $productsWithOptions = [];

    /**
     * @var array
     */
    protected $productsWithRequiredOptions = [];

    /**
     * @var array
     */
    protected $linkField = [];

    /**
     * @var array
     */
    protected $currentIncrementIds = [];

    /**
     * @var array
     */
    protected $preparedOptions = [];

    /**
     * @var int
     */
    protected $sortOrderCounter;

    /**
     * @param OptionFactory $optionFactory
     * @param BaseHelper $baseHelper
     * @param OptionDataCollector $optionDataCollector
     * @param DataSaver $dataSaver
     * @param ManagerInterface $eventManager
     * @param ResourceConnection $resource
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param GroupModel $groupModel
     * @param MageworxOptionResource $mageworxOptionResource
     */
    public function __construct(
        OptionFactory $optionFactory,
        BaseHelper $baseHelper,
        OptionDataCollector $optionDataCollector,
        ManagerInterface $eventManager,
        ResourceConnection $resource,
        DataSaver $dataSaver,
        GroupModel $groupModel,
        ProductCollectionFactory $productCollectionFactory,
        MageworxOptionResource $mageworxOptionResource,
        CollectionUpdaterRegistry $collectionUpdaterRegistry
    ) {
        $this->optionFactory             = $optionFactory;
        $this->baseHelper                = $baseHelper;
        $this->optionDataCollector       = $optionDataCollector;
        $this->dataSaver                 = $dataSaver;
        $this->eventManager              = $eventManager;
        $this->resource                  = $resource;
        $this->groupModel                = $groupModel;
        $this->mageworxOptionResource    = $mageworxOptionResource;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->collectionUpdaterRegistry = $collectionUpdaterRegistry;
    }

    /**
     * Add product options
     *
     * @param array $data
     * @param array $productAttributesData
     * @param array $productSkuToGroupIdRelations
     * @param string $migrationMode
     */
    public function addProductOptions($data, $productAttributesData, $productSkuToGroupIdRelations, $migrationMode)
    {
        if ($migrationMode === MigrationMode::MIGRATION_MODE_DELETE_ALL_OPTIONS) {
            $this->mageworxOptionResource->removeCustomizableOptions(true);
        } elseif ($migrationMode === MigrationMode::MIGRATION_MODE_DELETE_OPTIONS_ON_INTERSECTING_PRODUCTS) {
            $this->mageworxOptionResource->removeCustomizableOptions(false, array_keys($data));
        }

        $this->linkField = $this->baseHelper->getLinkField(ProductInterface::class);
        $allProductSkus  = array_keys($data);

        $totalSkus = count($allProductSkus);
        $limit     = 50;

        for ($offset = 0; $offset < $totalSkus; $offset += $limit) {
            $skus = array_slice($allProductSkus, $offset, $limit);
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->getProductsCollection($skus);

            if (empty($collection->getItems())) {
                continue;
            }

            $this->processIncrementIds();
            $this->processProducts($collection, $data, $productAttributesData, $productSkuToGroupIdRelations);
            $this->mageworxOptionResource->setHasOptionsStatus($this->productsWithOptions);
            $this->mageworxOptionResource->setRequiredOptionsStatus($this->productsWithRequiredOptions);
        }
    }

    /**
     * Process product changes and collect default magento data from options
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param array $importData
     * @param array $productAttributesData
     * @param array $productSkuToGroupIdRelations
     */
    protected function processProducts($collection, $importData, $productAttributesData, $productSkuToGroupIdRelations)
    {
        $this->optionData              = [];
        $this->optionsToDelete         = [];
        $products                      = [];
        $preparedProductAttributesData = [];
        $productIdsToDelete            = [];

        foreach ($collection as $product) {
            $this->addOptions($product, $importData[$product->getSku()]);
            $products[] = $product;
            $this->prepareProductAttributes(
                $preparedProductAttributesData,
                $productIdsToDelete,
                $product,
                $productAttributesData
            );
        }
        $this->saveOptions($products, $productSkuToGroupIdRelations);
        $this->saveProductAttributes($preparedProductAttributesData, $productIdsToDelete);
    }

    /**
     * Collect data form options for multiple insert
     *
     * @param ProductInterface $product
     * @param array $options
     * @return void
     */
    protected function addOptions(&$product, $options)
    {
        $this->preparedOptions  = [];
        $this->sortOrderCounter = 1;

        $this->processOriginalOptions($product);
        $this->processImportedOptions($product, $options);

        $product->setOptions($this->preparedOptions);
        $product->setCanSaveCustomOptions(true);

        $this->optionDataCollector->collectOptionsBeforeInsert(
            $product,
            $this->optionData,
            $this->optionsToDelete
        );
    }

    /**
     * Prepare data form product attributes for multiple insert
     *
     * @param array $preparedProductAttributesData
     * @param array $productIdsToDelete
     * @param ProductInterface $product
     * @param array $productAttributesData
     * @return void
     */
    protected function prepareProductAttributes(
        &$preparedProductAttributesData,
        &$productIdsToDelete,
        $product,
        $productAttributesData
    ) {
        if (empty($productAttributesData[$product->getSku()])) {
            return;
        }
        $productIdsToDelete[]            = $product->getData($this->linkField);
        $preparedProductAttributesData[] = array_merge(
            $productAttributesData[$product->getSku()],
            ['product_id' => $product->getData($this->linkField)]
        );
    }

    /**
     * Save product attributes
     *
     * @param array $preparedProductAttributesData
     * @param array $productIdsToDelete
     * @return void
     */
    protected function saveProductAttributes($preparedProductAttributesData, $productIdsToDelete)
    {
        if (!$preparedProductAttributesData) {
            return;
        }

        $connection = $this->resource->getConnection();
        $tableName  = $this->resource->getTableName(ProductAttributes::TABLE_NAME);

        $connection->delete(
            $tableName,
            ['product_id IN (?)' => array_values($productIdsToDelete)]
        );
        $connection->insertMultiple(
            $tableName,
            $preparedProductAttributesData
        );
    }

    /**
     * Process original product options
     *
     * @param ProductInterface $product
     * @return void
     */
    protected function processOriginalOptions($product)
    {
        $customOptions = $product->getOptions();
        if (!$customOptions || !is_array($customOptions)) {
            return;
        }

        $orderedOriginalOptions = [];
        foreach ($customOptions as $customOption) {
            $sortOrder = $customOption->getData('sort_order');
            while (isset($orderedOriginalOptions[$sortOrder])) {
                $sortOrder++;
            }
            $orderedOriginalOptions[$sortOrder] = $customOption;
        }

        foreach ($orderedOriginalOptions as $customOption) {
            $valueObjects = $customOption->getValues();
            if ($valueObjects && is_array($valueObjects)) {
                $values = [];
                foreach ($valueObjects as $valueObject) {
                    $values[] = $valueObject->getData();
                }
                $customOption->setData('values', $values);
            }
            $customOption->setData('sort_order', $this->sortOrderCounter);
            $this->sortOrderCounter++;
            $this->preparedOptions[] = $customOption;
        }
    }

    /**
     * Process imported options
     *
     * @param ProductInterface $product
     * @param array $options
     * @return void
     */
    protected function processImportedOptions($product, $options)
    {
        if (!$options || !is_array($options)) {
            return;
        }

        $orderedImportedOptions = [];
        foreach ($options as $option) {
            if (!isset($option['sort_order'])) {
                $option['sort_order'] = 1;
            }
            $sortOrder = $option['sort_order'];
            while (isset($orderedImportedOptions[$sortOrder])) {
                $sortOrder++;
            }
            $orderedImportedOptions[$sortOrder] = $option;
        }

        foreach ($orderedImportedOptions as $option) {
            $option['id']                         = $this->currentIncrementIds['option'];
            $option['option_id']                  = $option['id'];
            $option['need_to_process_dependency'] = true;
            if (empty($option['group_option_id'])) {
                $option['group_option_id'] = null;
            }
            $this->currentIncrementIds['option'] += 1;

            $this->addIncrementIdsToValues($option);

            $customOption = $this->optionFactory->create(['data' => $option]);
            $values       = !empty($option['values']) ? $option['values'] : [];
            $customOption->setProductSku($product->getSku())
                         ->setData('sort_order', $this->sortOrderCounter)
                         ->setValues($values);
            $this->sortOrderCounter++;
            $this->preparedOptions[] = $customOption;
        }
    }

    /**
     * Add increment ids to values
     *
     * @param array $option
     * @return void
     */
    protected function addIncrementIdsToValues(&$option)
    {
        if (!empty($option['values'])) {
            foreach ($option['values'] as $valueKey => $value) {
                if (empty($value['group_option_value_id'])) {
                    $value['group_option_value_id'] = null;
                }

                $value['id']                        = $this->currentIncrementIds['value'];
                $value['option_type_id']            = $value['id'];
                $this->currentIncrementIds['value'] += 1;

                $value['need_to_process_dependency'] = true;
                $option['values'][$valueKey]         = $value;
            }
        }
    }

    /**
     * Try to collect current increment IDs for option and values and throw error if something wrong
     *
     * @throws LocalizedException
     * @return void
     */
    protected function processIncrementIds()
    {
        $this->collectCurrentIncrementIds();
        if (empty($this->currentIncrementIds['option']) || empty($this->currentIncrementIds['value'])) {
            throw new LocalizedException(__("Can't get current auto_increment ID"));
        }
    }

    /**
     * Collect current increment IDs for option and values
     *
     * @return void
     */
    protected function collectCurrentIncrementIds()
    {
        $this->currentIncrementIds = [];

        $optionTableStatus = $this->resource->getConnection()->showTableStatus(
            $this->resource->getTableName('catalog_product_option')
        );
        if (!empty($optionTableStatus['Auto_increment'])) {
            $this->currentIncrementIds['option'] = $optionTableStatus['Auto_increment'];
        }

        $valueTableStatus = $this->resource->getConnection()->showTableStatus(
            $this->resource->getTableName('catalog_product_option_type_value')
        );
        if (!empty($valueTableStatus['Auto_increment'])) {
            $this->currentIncrementIds['value'] = $valueTableStatus['Auto_increment'];
        }
    }

    /**
     * Get product collection using selected product SKUs
     *
     * @param array $skus
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    protected function getProductsCollection($skus)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $this->collectionUpdaterRegistry->setCurrentEntityType('product');
        $this->collectionUpdaterRegistry->setCurrentEntityIds([]);
        $this->collectionUpdaterRegistry->setOptionIds([]);
        $this->collectionUpdaterRegistry->setOptionValueIds([]);
        $this->baseHelper->resetOptionIdsCache();
        $collection->addStoreFilter(0)
                   ->setStoreId(0)
                   ->addFieldToFilter('sku', ['in' => $skus])
                   ->addOptionsToResult();
        return $collection;
    }

    /**
     * Save options by multiple insert and update needed products
     *
     * @param array $products
     * @param array $productSkuToGroupIdRelations
     * @return void
     */
    protected function saveOptions($products, $productSkuToGroupIdRelations)
    {
        if ($this->optionsToDelete) {
            $condition = 'option_id IN (' . implode(',', $this->optionsToDelete) . ')';
            $this->dataSaver->deleteData('catalog_product_option', $condition);
            $condition = 'option_id IN (' . implode(',', $this->optionsToDelete) . ')';
            $this->dataSaver->deleteData('catalog_product_option_type_value', $condition);
        }

        foreach ($this->optionData as $tableName => $dataItem) {
            $this->dataSaver->insertMultipleData($tableName, $dataItem);
        }

        $this->productsWithOptions = [];
        foreach ($products as $productItem) {
            $this->updateProductData($productItem);
            if (!isset($productSkuToGroupIdRelations[$productItem->getSku()])
                || !is_array($productSkuToGroupIdRelations[$productItem->getSku()])
            ) {
                continue;
            }
            foreach (array_values($productSkuToGroupIdRelations[$productItem->getSku()]) as $groupId) {
                $this->groupModel->addRelation($groupId, $productItem->getId());
            };
        }
    }

    /**
     * Transfer product based custom options attributes to the corresponding product
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function updateProductData($product)
    {
        $options = $product->getOptions();
        if ($options && is_array($options)) {
            foreach ($options as $option) {
                if ($option->getIsRequire()) {
                    $this->productsWithRequiredOptions[] = $product->getData($this->linkField);
                    $product->setRequiredOptions(1);
                }
            }
            $this->productsWithOptions[] = $product->getData($this->linkField);
            $product->setHasOptions(1);
        }

        $product->setIsAfterTemplateSave(true);

        $this->eventManager->dispatch(
            'mageworx_attributes_save_trigger',
            ['product' => $product, 'is_after_template' => false]
        );
    }

    /**
     * Check if products exist in magento
     *
     * @param array $data
     * @return bool
     */
    public function isProductsExist($data)
    {
        return $this->mageworxOptionResource->isProductsExist(array_keys($data));
    }

    /**
     * Check if custom options exist in magento
     *
     * @return bool
     */
    public function isCustomOptionsExist()
    {
        return $this->mageworxOptionResource->isCustomOptionsExist();
    }

    /**
     * Check if custom options in magento intersects with data
     *
     * @param array $data
     * @return bool
     */
    public function hasIntersectingProducts($data)
    {
        return $this->mageworxOptionResource->hasIntersectingProducts(array_keys($data));
    }
}
