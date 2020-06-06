<?php
namespace Mexbs\MultiInventory\Model\Rewrite;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Exception\LocalizedException;

class StockItemValidator extends \Magento\CatalogInventory\Model\StockItemValidator
{
    private $stockConfiguration;
    private $stockRegistry;

    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;

        parent::__construct(
            $stockConfiguration,
            $stockRegistry
        );
    }

    public function validate(ProductInterface $product, StockItemInterface $stockItem)
    {
        $stockItemId = $stockItem->getItemId();
        if ($stockItemId !== null && (!is_numeric($stockItemId) || $stockItemId <= 0)) {
            throw new LocalizedException(
                __('Invalid stock item id: %1. Should be null or numeric value greater than 0', $stockItemId)
            );
        }
    }
}