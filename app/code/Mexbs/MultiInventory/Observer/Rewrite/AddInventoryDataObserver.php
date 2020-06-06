<?php
namespace Mexbs\MultiInventory\Observer\Rewrite;

use Magento\Framework\Event\Observer as EventObserver;

class AddInventoryDataObserver extends \Magento\CatalogInventory\Observer\AddInventoryDataObserver
{
    protected $multiStockHelper;

    public function __construct(
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper
    )
    {
        $this->multiStockHelper = $multiStockHelper;
        parent::__construct($stockHelper);
    }

    /**
     * Add stock information to product
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $websiteId = null;
            if($product->getStore() && $product->getStore()->getWebsiteId()){
                $websiteId = $product->getStore()->getWebsiteId();
            }
            $this->multiStockHelper->assignStatusToProductForWebsite($product, null, $websiteId);
        }
    }
}