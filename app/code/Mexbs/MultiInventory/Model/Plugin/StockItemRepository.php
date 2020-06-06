<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class StockItemRepository{
    private $stockConfiguration;
    private $stockRegistry;

    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
    }

    public function beforeSave(
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $subject,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
    ){
        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
        if (($stockItem->getWebsiteId() != $defaultScopeId)
            && $stockItem->getUseDefaultValues()){
            $defaultStockItem = $this->stockRegistry->getStockItem($stockItem->getProductId(), $defaultScopeId);
            if($defaultStockItem && $defaultStockItem->getId()){
                $defaultStockItemId = $defaultStockItem->getId();
                $defaultStockItemWebsiteId = $defaultStockItem->getWebsiteId();
                $defaultStockItemStockId = $defaultStockItem->getStockId();

                $defaultStockItem->setData($stockItem->getData());

                $defaultStockItem->setId($defaultStockItemId);
                $defaultStockItem->setWebsiteId($defaultStockItemWebsiteId);
                $defaultStockItem->setStockId($defaultStockItemStockId);

                $subject->save($defaultStockItem);
            }
        }
        return [$stockItem];
    }
}