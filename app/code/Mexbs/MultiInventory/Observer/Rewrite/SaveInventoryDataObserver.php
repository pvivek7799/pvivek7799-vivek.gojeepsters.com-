<?php
namespace Mexbs\MultiInventory\Observer\Rewrite;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\CatalogInventory\Model\StockItemValidator;
use Magento\Framework\App\ObjectManager;

class SaveInventoryDataObserver extends \Magento\CatalogInventory\Observer\SaveInventoryDataObserver
{
    private $multiStockHelper;
    private $stockConfiguration;
    protected $storeManager;
    private $stockRegistry;
    private $stockItemValidator;

    private $paramListToCheck = [
        'use_config_min_qty' => [
            'item' => 'stock_data/min_qty',
            'config' => 'stock_data/use_config_min_qty',
        ],
        'use_config_min_sale_qty' => [
            'item' => 'stock_data/min_sale_qty',
            'config' => 'stock_data/use_config_min_sale_qty',
        ],
        'use_config_max_sale_qty' => [
            'item' => 'stock_data/max_sale_qty',
            'config' => 'stock_data/use_config_max_sale_qty',
        ],
        'use_config_backorders' => [
            'item' => 'stock_data/backorders',
            'config' => 'stock_data/use_config_backorders',
        ],
        'use_config_notify_stock_qty' => [
            'item' => 'stock_data/notify_stock_qty',
            'config' => 'stock_data/use_config_notify_stock_qty',
        ],
        'use_config_enable_qty_inc' => [
            'item' => 'stock_data/enable_qty_increments',
            'config' => 'stock_data/use_config_enable_qty_inc',
        ],
        'use_config_qty_increments' => [
            'item' => 'stock_data/qty_increments',
            'config' => 'stock_data/use_config_qty_increments',
        ],
    ];

    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper,
        StoreManagerInterface $storeManager,
        StockItemValidator $stockItemValidator = null
    ) {
        $this->multiStockHelper = $multiStockHelper;
        $this->storeManager = $storeManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemValidator = $stockItemValidator ?: ObjectManager::getInstance()->get(StockItemValidator::class);
        parent::__construct(
            $stockConfiguration,
            $stockRegistry,
            $stockItemValidator
        );
    }

    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $stockItemData = $product->getStockData();
        $stockItemData['product_id'] = $product->getId();

        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();

        if (!isset($stockItemData['website_id'])) {
            if($product->getCopyFromView() && $product->getStoreId()){
                $stockItemData['website_id'] = $this->storeManager->getStore($product->getStoreId())->getWebsiteId();
            }else{
                $stockItemData['website_id'] = $defaultScopeId;
            }
        }
        $stockItemData['stock_id'] = $this->stockRegistry->getStock($stockItemData['website_id'])->getStockId();

        foreach ($this->paramListToCheck as $dataKey => $configPath) {
            if (null !== $product->getData($configPath['item']) && null === $product->getData($configPath['config'])) {
                $stockItemData[$dataKey] = false;
            }
        }

        $originalQty = $product->getData('stock_data/original_inventory_qty');
        if (strlen($originalQty) > 0) {
            $stockItemData['qty_correction'] = (isset($stockItemData['qty']) ? $stockItemData['qty'] : 0)
                - $originalQty;
        }

        $stockItem = $this->stockRegistry->getStockItem($stockItemData['product_id'], $stockItemData['website_id']);
        if($product->getCopyFromView()){
            $stockItemOnLoad = null;
            if($product->getExtensionAttributes() && $product->getExtensionAttributes()->getStockItem()){
                $stockItemOnLoad = $product->getExtensionAttributes()->getStockItem();
            }
            if($stockItemOnLoad && $stockItemOnLoad->getData()){
                $stockItemId = $stockItem->getId();
                $stockItemWebsiteId = $stockItem->getWebsiteId();
                $stockItemStockId = $stockItem->getStockId();

                $stockItem->setData($stockItemOnLoad->getData());

                if($stockItemOnLoad->getWebsiteId() == $defaultScopeId){
                    $stockItem->setUseDefaultValues(1);
                }

                $stockItem->setId($stockItemId);
                $stockItem->setWebsiteId($stockItemWebsiteId);
                $stockItem->setStockId($stockItemStockId);
            }
        }else{
            if(($stockItemData['website_id'] != $defaultScopeId)
                && isset($stockItemData['use_default_values'])
                && $stockItemData['use_default_values']){
                $defaultStockItem = $this->stockRegistry->getStockItem($stockItemData['product_id'], $defaultScopeId);

                $stockItemId = $stockItem->getId();
                $stockItemWebsiteId = $stockItem->getWebsiteId();
                $stockItemStockId = $stockItem->getStockId();

                $stockItem->setData($defaultStockItem->getData());

                $stockItem->setId($stockItemId)
                    ->setWebsiteId($stockItemWebsiteId)
                    ->setStockId($stockItemStockId)
                    ->setUseDefaultValues(1);
            }else{
                $stockItem->addData($stockItemData);
            }
        }

        $this->stockItemValidator->validate($product, $stockItem);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
    }
}