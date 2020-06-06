<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Closure;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Model\ProductFactory;

class StockRegistryProvider{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    protected $stockRegistry;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
    }

    public function aroundGetStockItem(
        \Magento\CatalogInventory\Model\StockRegistryProvider $subject,
        Closure $proceed,
        $productId,
        $scopeId = null
    )
    {
        $stockItem = $proceed($productId, $scopeId);
        if($scopeId != $this->stockConfiguration->getDefaultScopeId()){
            if(!$stockItem->getId()){
                $defaultStockItem = $subject->getStockItem($productId, $this->stockConfiguration->getDefaultScopeId());
                if($defaultStockItem->getId()){
                    $stockItem->setData($defaultStockItem->getData());
                }
                $stock = $this->stockRegistry->getStock($scopeId);
                if($stock->getId()){
                    $stockItem
                        ->unsetData($stockItem->getIdFieldName())
                        ->setStockId($stock->getId())
                        ->setWebsiteId($stock->getWebsiteId())
                        ->setUseDefaultValues(1);
                }
            }else{
                if($stockItem->getUseDefaultValues()){
                    $stockItemId = $stockItem->getId();
                    $stockItemStockId = $stockItem->getStockId();
                    $stockItemWebsiteId = $stockItem->getWebsiteId();
                    $defaultStockItem = $subject->getStockItem($productId, $this->stockConfiguration->getDefaultScopeId());
                    if($defaultStockItem->getId()){
                        $stockItem->setData($defaultStockItem->getData());
                        $stockItem->setId($stockItemId)
                            ->setStockId($stockItemStockId)
                            ->setWebsiteId($stockItemWebsiteId)
                            ->setUseDefaultValues(1);
                    }
                }
            }
        }
        $stockItem->setDataChanges(false);
        return $stockItem;
    }


    public function aroundGetStockStatus(
        \Magento\CatalogInventory\Model\StockRegistryProvider $subject,
        Closure $proceed,
        $productId,
        $scopeId = null
    )
    {
        $stockStatus = $proceed($productId, $scopeId);
        if($scopeId != $this->stockConfiguration->getDefaultScopeId()){
            if(!$stockStatus->getProductId()){
                $defaultStockStatus = $subject->getStockStatus($productId, $this->stockConfiguration->getDefaultScopeId());
                if($defaultStockStatus->getProductId()){
                    $stockStatus->setData($defaultStockStatus->getData());
                }
                $stock = $this->stockRegistry->getStock($scopeId);
                if($stock->getId()){
                    $stockStatus
                        ->setStockId($stock->getId())
                        ->setWebsiteId($stock->getWebsiteId())
                    ;
                }
            }
        }
        return $stockStatus;
    }
}