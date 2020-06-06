<?php
namespace Mexbs\MultiInventory\Model\Rewrite\ResourceModel\Stock;

class Status extends \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
{
    protected $multiStockHelper;



    public function __construct(
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Config $eavConfig,
        $connectionName = null
    ){
        $this->multiStockHelper = $multiStockHelper;
        parent::__construct(
            $context,
            $storeManager,
            $websiteFactory,
            $eavConfig,
            $connectionName
        );
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();

        $joinCondition = 'e.entity_id = stock_status_index.product_id';

        $method = $isFilterInStock ? 'join' : 'joinLeft';
        $collection->getSelect()->$method(
            ['stock_status_index' => $this->getMainTable()],
            $joinCondition,
            ['is_salable' => 'stock_status']
        );

        if ($isFilterInStock) {
            $collection->getSelect()->where(
                'stock_status_index.stock_status = ?',
                \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
            );
        }

        $activeStockStatusItemsStr = $this->multiStockHelper->getActiveStockStatusItemsStr($currentWebsiteId);

        if(!empty($activeStockStatusItemsStr)){
            $collection->getSelect()->where(
                '(stock_status_index.product_id,stock_status_index.website_id) IN ('.$activeStockStatusItemsStr.')'
            );
        }

        return $collection;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();

        $joinCondition = 'e.entity_id = stock_status_index.product_id';

        $collection->getSelect()->join(
            ['stock_status_index' => $this->getMainTable()],
            $joinCondition,
            []
        )->where(
            'stock_status_index.stock_status=?',
            \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
        );

        $activeStockStatusItemsStr = $this->multiStockHelper->getActiveStockStatusItemsStr($currentWebsiteId);
        if(!empty($activeStockStatusItemsStr)){
            $collection->getSelect()->where(
                '(stock_status_index.product_id,stock_status_index.website_id) IN ('.$activeStockStatusItemsStr.')'
            );
        }

        return $this;
    }


    /**
     * Add stock status to prepare index select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Status
     */
    public function addStockStatusToSelect(\Magento\Framework\DB\Select $select, \Magento\Store\Model\Website $website)
    {
        $select->joinLeft(
            ['stock_status' => $this->getMainTable()],
            'e.entity_id = stock_status.product_id',
            ['is_salable' => 'stock_status.stock_status']
        );

        $activeStockStatusItemsStr = $this->multiStockHelper->getActiveStockStatusItemsStr($website->getWebsiteId());

        if(!empty($activeStockStatusItemsStr)){
            $select->where(
                '(stock_status.product_id,stock_status.website_id) IN ('.$activeStockStatusItemsStr.')'
            );
        }

        return $this;
    }
}