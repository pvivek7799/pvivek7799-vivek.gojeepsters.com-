<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Closure;

class DefaultStockqty{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    public function aroundGetStockQtyLeft(
        \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty $subject,
        Closure $proceed
    )
    {
        $result = $proceed();
        if($subject->getProduct()->getStore()){
            $stockItem = $this->stockRegistry->getStockItem($subject->getProduct()->getId(), $subject->getProduct()->getStore()->getWebsiteId());
            $minStockQty = $stockItem->getMinQty();
            $result = $subject->getStockQty() - $minStockQty;
        }
        return $result;
    }
}