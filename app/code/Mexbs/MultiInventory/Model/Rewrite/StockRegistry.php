<?php
namespace Mexbs\MultiInventory\Model\Rewrite;

class StockRegistry extends \Magento\CatalogInventory\Model\StockRegistry
{

    /**
     * @param int $scopeIdToGet
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        return $this->stockRegistryProvider->getStock($scopeIdToGet);
    }

    /**
     * @param int $productId
     * @param int $scopeIdToGet
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        return $this->stockRegistryProvider->getStockItem($productId, $scopeIdToGet);
    }

    /**
     * @param string $productSku
     * @param int $scopeIdToGet
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        $productId = $this->resolveProductId($productSku);
        return $this->stockRegistryProvider->getStockItem($productId, $scopeIdToGet);
    }

    /**
     * @param int $productId
     * @param int $scopeIdToGet
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        return $this->stockRegistryProvider->getStockStatus($productId, $scopeIdToGet);
    }

    /**
     * @param string $productSku
     * @param int $scopeIdToGet
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        $productId = $this->resolveProductId($productSku);
        return $this->getStockStatus($productId, $scopeIdToGet);
    }

    /**
     * Retrieve Product stock status
     * @param int $productId
     * @param int $scopeIdToGet
     * @return int
     */
    public function getProductStockStatus($productId, $scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        $stockStatus = $this->getStockStatus($productId, $scopeIdToGet);
        return $stockStatus->getStockStatus();
    }

    /**
     * @param string $productSku
     * @param null $scopeIdToGet
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $scopeIdToGet = null)
    {
        if (!$scopeIdToGet) {
            $scopeIdToGet = $this->stockConfiguration->getDefaultScopeId();
        }
        $productId = $this->resolveProductId($productSku);
        return $this->getProductStockStatus($productId, $scopeIdToGet);
    }

}