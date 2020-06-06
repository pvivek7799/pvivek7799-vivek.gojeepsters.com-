<?php
namespace Mexbs\MultiInventory\Model\Rewrite\Quote\Item\QuantityValidator\Initializer;

class Option extends \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option
{
    /**
     * Init stock item
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return \Magento\CatalogInventory\Model\Stock\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockItem(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        $stockItem = $this->stockRegistry->getStockItem(
            $option->getProduct()->getId(),
            $quoteItem->getStore()->getWebsiteId()
        );
        if (!$stockItem->getProductId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The stock item for Product in option is not valid.')
            );
        }
        /**
         * define that stock item is child for composite product
         */
        $stockItem->setIsChildItem(true);
        /**
         * don't check qty increments value for option product
         */
        $stockItem->setSuppressCheckQtyIncrements(true);

        return $stockItem;
    }
}