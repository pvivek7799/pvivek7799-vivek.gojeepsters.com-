<?php

namespace Mexbs\MultiInventory\Helper\Rewrite;

use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\Catalog\Model\Product;

class Stock extends \Magento\CatalogInventory\Helper\Stock
{
    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @param StockRegistryProviderInterface $stockRegistryProvider
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StatusFactory $stockStatusFactory,
        StockRegistryProviderInterface $stockRegistryProvider
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        parent::__construct(
            $storeManager,
            $scopeConfig,
            $stockStatusFactory,
            $stockRegistryProvider
        );
    }

    /**
     * Add stock status information to products
     *
     * @param AbstractCollection $productCollection
     * @deprecated Use Stock::addIsInStockFilterToCollection instead
     * @return void
     */
    public function addStockStatusToProducts(AbstractCollection $productCollection)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, $websiteId);
            $status = $stockStatus->getStockStatus();
            $product->setIsSalable($status);
        }
    }
}