<?php
namespace Mexbs\MultiInventory\Model\Rewrite;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock as ResourceStock;
use Magento\CatalogInventory\Model\StockRegistryStorage;

class StockManagement extends \Magento\CatalogInventory\Model\StockManagement
{
    /**
     * @var QtyCounterInterface
     */
    private $qtyCounter;
    private $stockRegistryStorage;

    /**
     * @param ResourceStock $stockResource
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockState $stockState
     * @param StockConfigurationInterface $stockConfiguration
     * @param ProductRepositoryInterface $productRepository
     * @param QtyCounterInterface $qtyCounter
     */
    public function __construct(
        ResourceStock $stockResource,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockState $stockState,
        StockConfigurationInterface $stockConfiguration,
        ProductRepositoryInterface $productRepository,
        QtyCounterInterface $qtyCounter,
        StockRegistryStorage $stockRegistryStorage
    ) {
        $this->qtyCounter = $qtyCounter;
        $this->stockRegistryStorage = $stockRegistryStorage;
        parent::__construct(
            $stockResource,
            $stockRegistryProvider,
            $stockState,
            $stockConfiguration,
            $productRepository,
            $qtyCounter
        );
    }

    /**
     * Subtract product qtys from stock.
     * Return array of items that require full save
     *
     * @param string[] $items
     * @param int $websiteId
     * @return StockItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function registerProductsSale($itemsDataToRegister, $websiteIdToRegisterIn = null)
    {
        if (!$websiteIdToRegisterIn) {
            $websiteIdToRegisterIn = $this->stockConfiguration->getDefaultScopeId();
        }
        $this->getResource()->beginTransaction();
        $lockedItems = $this->getResource()->lockProductsStock(array_keys($itemsDataToRegister), $websiteIdToRegisterIn);
        $fullSaveItems = $registeredItems = [];
        foreach ($lockedItems as $lockedItemRecord) {
            $productIdToRegister = $lockedItemRecord['product_id'];
            $this->stockRegistryStorage->removeStockItem($productIdToRegister, $websiteIdToRegisterIn);
            /** @var StockItemInterface $stockItemToRegister */
            $orderedQty = $itemsDataToRegister[$productIdToRegister];
            $stockItemToRegister = $this->stockRegistryProvider->getStockItem($productIdToRegister, $websiteIdToRegisterIn);

            $stockItemToRegister->setQty($lockedItemRecord['qty']);

            $canSubtractQtyFromItem = $stockItemToRegister->getProductId() && $this->canSubtractQty($stockItemToRegister);
            if (!$canSubtractQtyFromItem || !$this->stockConfiguration->isQty($lockedItemRecord['type_id'])) {
                continue;
            }
            if (!$stockItemToRegister->hasAdminArea()
                && !$this->stockState->checkQty($productIdToRegister, $orderedQty, $stockItemToRegister->getWebsiteId())
            ) {
                $this->getResource()->commit();
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Not all of your products are available in the requested quantity.')
                );
            }
            if ($this->canSubtractQty($stockItemToRegister)) {
                $stockItemToRegister->setQty($stockItemToRegister->getQty() - $orderedQty);
            }
            $registeredItems[$productIdToRegister] = $orderedQty;
            if (!$this->stockState->verifyStock($productIdToRegister, $stockItemToRegister->getWebsiteId())
                || $this->stockState->verifyNotification(
                    $productIdToRegister,
                    $stockItemToRegister->getWebsiteId()
                )
                || ((float)$stockItemToRegister->getQty() < $stockItemToRegister->getNotifyStockQty())
            ) {
                $fullSaveItems[] = $stockItemToRegister;
            }
        }
        $this->qtyCounter->correctItemsQty($registeredItems, $websiteIdToRegisterIn, '-');
        $this->getResource()->commit();
        return $fullSaveItems;
    }

    /**
     * @param string[] $items
     * @param int $websiteId
     * @return bool
     */
    public function revertProductsSale($items, $websiteIdToRevertIn = null)
    {
        if (!$websiteIdToRevertIn) {
            $websiteIdToRevertIn = $this->stockConfiguration->getDefaultScopeId();
        }
        $this->qtyCounter->correctItemsQty($items, $websiteIdToRevertIn, '+');
        return true;
    }

    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @return bool
     */
    public function backItemQty($productIdToBack, $qty, $websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        }
        $stockItemToBack = $this->stockRegistryProvider->getStockItem($productIdToBack, $websiteId);
        if ($stockItemToBack->getProductId() && $this->stockConfiguration->isQty($this->getProductType($productIdToBack))) {
            if ($this->canSubtractQty($stockItemToBack)) {
                $stockItemToBack->setQty($stockItemToBack->getQty() + $qty);
            }
            if ($this->stockConfiguration->getCanBackInStock($stockItemToBack->getStoreId()) && $stockItemToBack->getQty()
                > $stockItemToBack->getMinQty()
            ) {
                $stockItemToBack->setIsInStock(true);
                $stockItemToBack->setStockStatusChangedAutomaticallyFlag(true);
            }
            $stockItemToBack->save();
        }
        return true;
    }
}