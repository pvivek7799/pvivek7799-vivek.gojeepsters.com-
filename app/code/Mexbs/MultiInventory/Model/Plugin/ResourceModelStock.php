<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Closure;

class ResourceModelStock{
    protected $multiStockHelper;
    protected $storeManager;

    public function __construct(
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->multiStockHelper = $multiStockHelper;
        $this->storeManager = $storeManager;
    }

    public function aroundAddLowStockFilter(
        \Magento\CatalogInventory\Model\ResourceModel\Stock $subject,
        Closure $proceed,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $fields
    )
    {
        $result = $proceed($collection, $fields);
        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        $activeItemIds = $this->multiStockHelper->getActiveStockItemIds($currentWebsiteId);

        if(!empty($activeItemIds)){
            $collection->getSelect()
                ->where('invtr.item_id IN ('.join(',',$activeItemIds).')');
        }

        return $result;
    }
}