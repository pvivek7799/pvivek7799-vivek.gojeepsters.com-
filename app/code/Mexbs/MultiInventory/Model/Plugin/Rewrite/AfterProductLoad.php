<?php
namespace Mexbs\MultiInventory\Model\Plugin\Rewrite;

use Magento\CatalogInventory\Api\StockConfigurationInterface;

class AfterProductLoad extends \Magento\CatalogInventory\Model\Plugin\AfterProductLoad
{
    protected $stockConfiguration;
    private $stockRegistry;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($stockRegistry);
    }

    /**
     * Add stock item information to the product's extension attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterLoad(\Magento\Catalog\Model\Product $product)
    {
        $productExtension = $product->getExtensionAttributes();

        if($product->getStoreId()){
            $scopeId = $product->getStore()->getWebsiteId();
        }else{
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }

        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $scopeId);
        $productExtension->setStockItem($stockItem);

        $product->setExtensionAttributes($productExtension);
        return $product;
    }
}