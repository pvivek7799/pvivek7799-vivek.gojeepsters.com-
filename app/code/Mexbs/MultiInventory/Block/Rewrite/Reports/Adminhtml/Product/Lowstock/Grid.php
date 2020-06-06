<?php
namespace Mexbs\MultiInventory\Block\Rewrite\Reports\Adminhtml\Product\Lowstock;

class Grid extends \Magento\Reports\Block\Adminhtml\Product\Lowstock\Grid{
    protected $stockConfiguration;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Product\Lowstock\CollectionFactory $lowstocksFactory,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        array $data = []
    ) {
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct(
            $context,
            $backendHelper,
            $lowstocksFactory,
            $data
        );
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $website = $this->getRequest()->getParam('website');
        $group = $this->getRequest()->getParam('group');
        $store = $this->getRequest()->getParam('store');

        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();

        if ($website) {
            $storeIds = $this->_storeManager->getWebsite($website)->getStoreIds();
            $storeId = array_pop($storeIds);

            $websiteId = $website->getId();
        } elseif ($group) {
            $storeIds = $this->_storeManager->getGroup($group)->getStoreIds();
            $storeId = array_pop($storeIds);

            $websiteId = $this->_storeManager->getGroup($group)->getWebsiteId();
        } elseif ($store && ($store != 'null')) {
            $storeId = (int)$store;

            $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        } else {
            $storeId = '';

            $websiteId = '';
        }


        /** @var $collection \Magento\Reports\Model\ResourceModel\Product\Lowstock\Collection  */
        $collection = $this->_lowstocksFactory->create()->addAttributeToSelect(
            '*'
        )->setStoreId(
            $storeId
        )->filterByIsQtyProductTypes();

        $collection->getSelect()
        ->joinLeft(
            ['default_stock_item' => $collection->getConnection()->getTableName('cataloginventory_stock_item')],
            sprintf("e.entity_id=default_stock_item.product_id AND default_stock_item.website_id=%s",$defaultScopeId),
            ['qty' => new \Zend_Db_Expr('IF(website_stock_item.item_id IS NULL, default_stock_item.qty,'.
                '(IF(website_stock_item.use_default_values = "0", website_stock_item.qty, default_stock_item.qty)))')]

        )->joinLeft(
            ['website_stock_item' => $collection->getConnection()->getTableName('cataloginventory_stock_item')],
                sprintf("default_stock_item.product_id=website_stock_item.product_id".
                    (is_numeric($websiteId) ? " AND website_stock_item.website_id = %s" : " AND website_stock_item.website_id <> %s"),
                    (is_numeric($websiteId) ? $websiteId : $defaultScopeId)
                ),
            []
       )->where(
                new \Zend_Db_Expr(
                    sprintf("IF(".
                                "website_stock_item.item_id IS NULL,".
                                "(IF(default_stock_item.use_config_manage_stock = 1, %s, default_stock_item.manage_stock) = 1) AND (default_stock_item.qty < IF(default_stock_item.use_config_notify_stock_qty = 1, %s, default_stock_item.notify_stock_qty)),".
                                "(IF(".
                                    "website_stock_item.use_default_values = '0', ".
                                    "(IF(website_stock_item.use_config_manage_stock = 1, %s, website_stock_item.manage_stock) = 1) AND (website_stock_item.qty < IF(website_stock_item.use_config_notify_stock_qty = 1, %s, website_stock_item.notify_stock_qty)),".
                                    "(IF(default_stock_item.use_config_manage_stock = 1, %s, default_stock_item.manage_stock) = 1) AND (default_stock_item.qty < IF(default_stock_item.use_config_notify_stock_qty = 1, %s, default_stock_item.notify_stock_qty))".
                                    ")".
                                ")".
                             ")",
                 (int)$this->stockConfiguration->getManageStock($defaultScopeId),
                 (int)$this->stockConfiguration->getNotifyStockQty($defaultScopeId),
                 (int)$this->stockConfiguration->getManageStock($defaultScopeId),
                 (int)$this->stockConfiguration->getNotifyStockQty($defaultScopeId),
                 (int)$this->stockConfiguration->getManageStock($defaultScopeId),
                 (int)$this->stockConfiguration->getNotifyStockQty($defaultScopeId)
               )
           )
       );
       if(!is_numeric($storeId)){
           $collection->getSelect()->group("e.entity_id");
       }
       $collection->setOrder(
            'qty',
            \Magento\Framework\Data\Collection::SORT_ORDER_ASC
       );

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }
}