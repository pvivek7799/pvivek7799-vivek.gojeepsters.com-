<?php

namespace Lof\LayeredNavigation\Plugins\Model\Layer;

class FilterList
{
    const CONFIG_ENABLED_XML_PATH   = 'layered_navigation/general/stockFilter';
    const CONFIG_POSITION_XML_PATH  = 'bottom';
    const STOCK_FILTER_CLASS        = 'Lof\LayeredNavigation\Model\Layer\Filter\Stock';
    const XML_PATH_ENABLE_STATUS = 'layered_navigation/general/enable';
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_layer;
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    protected $_stockResource;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_stockResource = $stockResource;
        $this->_scopeConfig = $scopeConfig;
        $this->_eventManager = $eventManager;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $moduleEnabled = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_STATUS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $outOfStockEnabled = $this->_scopeConfig->isSetFlag(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $extensionEnabled = $this->_scopeConfig->isSetFlag(
            self::CONFIG_ENABLED_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $outOfStockEnabled && $extensionEnabled && $moduleEnabled;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList\Interceptor $filterList
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array
     */
    public function beforeGetFilters(
        \Magento\Catalog\Model\Layer\FilterList\Interceptor $filterList,
        \Magento\Catalog\Model\Layer $layer
    ) {
        $this->_layer = $layer;
        if ($this->isEnabled()) {
            $collection = $layer->getProductCollection();
            $websiteId = $this->_storeManager->getStore($collection->getStoreId())->getWebsiteId();
            $this->_addStockStatusToSelect($collection->getSelect(), $websiteId);
        }
        return array($layer);
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList\Interceptor $filterList
     * @param array $filters
     * @return array
     */
    public function afterGetFilters(
        \Magento\Catalog\Model\Layer\FilterList\Interceptor $filterList,
        array $filters
    ) {

        if ($this->isEnabled()) {
            $position = $this->getFilterPosition();
            $stockFilter = $this->getStockFilter();
            switch ($position) {
                case \Lof\LayeredNavigation\Model\Config\Source\Position::POSITION_BOTTOM:
                    $filters[] = $this->getStockFilter();
                    break;
                case \Lof\LayeredNavigation\Model\Config\Source\Position::POSITION_TOP:
                    array_unshift($filters, $stockFilter);
                    break;
                case \Lof\LayeredNavigation\Model\Config\Source\Position::POSITION_AFTER_CATEGORY:
                    $processed = [];
                    $stockFilterAdded = false;
                    foreach ($filters as $key => $value) {
                        $processed[] = $value;
                        if ($value instanceof \Magento\Catalog\Model\Layer\Filter\Category || $value instanceof \Magento\CatalogSearch\Model\Layer\Filter\Category) {
                            $processed[] = $stockFilter;
                            $stockFilterAdded = true;
                        }
                    }
                    $filters = $processed;
                    if (!$stockFilterAdded) {
                        array_unshift($filters, $stockFilter);
                    }
                    break;
            }

        }
        return $filters;
    }

    /**
     * @return \Lof\LayeredNavigation\Model\Layer\Filter\Stock
     */
    public function getStockFilter()
    {
        $filter = $this->_objectManager->create(
            $this->getStockFilterClass(),
            ['layer' => $this->_layer]
        );
        return $filter;
    }

    /**
     * @return string
     */
    public function getStockFilterClass()
    {
        return self::STOCK_FILTER_CLASS;
    }

    /**
     * @param \Zend_Db_Select $select
     * @param $websiteId
     * @return $this
     */
    protected function _addStockStatusToSelect(\Zend_Db_Select $select, $websiteId)
    {
        $from = $select->getPart(\Zend_Db_Select::FROM);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        if (!isset($from['stock_status_idx'])) {


            $joinCondition = $this->_stockResource->getConnection()->quoteInto(
                'e.entity_id = stock_status_idx.product_id AND stock_status_idx.stock_id = ?',
                \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID
            );
            $select->join(
                [
                    'stock_status_idx' => $this->_stockResource->getMainTable()
                ],
                $joinCondition,
                []
            );
        }

        return $this;
    }
    public function getFilterPosition()
    {
        return self::CONFIG_POSITION_XML_PATH;
    }
}
