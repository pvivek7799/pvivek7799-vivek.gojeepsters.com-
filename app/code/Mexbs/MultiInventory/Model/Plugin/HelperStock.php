<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Closure;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
class HelperStock{

    protected $stockRegistryProvider;
    protected $multiStockHelper;
    protected $storeManager;

    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->multiStockHelper = $multiStockHelper;
        $this->storeManager = $storeManager;
    }

    public function aroundAddInStockFilterToCollection(
        \Magento\CatalogInventory\Helper\Stock $subject,
        Closure $proceed,
        $collection
    ){
        $proceed($collection);

        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        $activeItemIds = $this->multiStockHelper->getActiveStockItemIds($currentWebsiteId);
        if(!empty($activeItemIds)){
            $collection->getSelect()
                ->where('item_id IN ('.join(',',$activeItemIds).')');
        }
    }
}