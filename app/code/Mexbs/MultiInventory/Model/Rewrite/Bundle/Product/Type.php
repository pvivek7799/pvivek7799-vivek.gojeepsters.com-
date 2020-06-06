<?php
namespace Mexbs\MultiInventory\Model\Rewrite\Bundle\Product;

class Type extends \Magento\Bundle\Model\Product\Type{
    public function isSalable($productToCheck)
    {
        if (!\Magento\Catalog\Model\Product\Type\AbstractType::isSalable($productToCheck)) {
            return false;
        }

        if ($productToCheck->hasData('all_items_salable')) {
            return $productToCheck->getData('all_items_salable');
        }

        $productOptionCollection = $this->getOptionsCollection($productToCheck);

        if (!count($productOptionCollection->getItems())) {
            return false;
        }

        $requiredOptionIds = [];

        foreach ($productOptionCollection->getItems() as $productOption) {
            if ($productOption->getRequired()) {
                $requiredOptionIds[$productOption->getId()] = 0;
            }
        }

        $productSelectionCollection = $this->getSelectionsCollection($productOptionCollection->getAllIds(), $productToCheck);

        if (!count($productSelectionCollection->getItems())) {
            return false;
        }
        $salableSelectionCount = 0;

        foreach ($productSelectionCollection as $productSelection) {
            /* @var $productSelection \Magento\Catalog\Model\Product */
            if ($productSelection->isSalable()) {
                $productSelectionEnoughQty = $this->_stockRegistry->getStockItem($productSelection->getId(), $productSelection->getStore()->getWebsiteId())
                    ->getManageStock()
                    ? $productSelection->getSelectionQty() <= $this->_stockState->getStockQty($productSelection->getId(), $productSelection->getStore()->getWebsiteId())
                    : $productSelection->isInStock();

                if (!$productSelection->hasSelectionQty() || $productSelection->getSelectionCanChangeQty() || $productSelectionEnoughQty) {
                    $requiredOptionIds[$productSelection->getOptionId()] = 1;
                    $salableSelectionCount++;
                }
            }
        }
        $isSalable = array_sum($requiredOptionIds) == count($requiredOptionIds) && $salableSelectionCount;
        $productToCheck->setData('all_items_salable', $isSalable);

        return $isSalable;
    }
}