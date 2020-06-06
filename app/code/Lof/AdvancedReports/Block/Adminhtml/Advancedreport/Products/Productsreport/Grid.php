<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_AdvancedReports
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\AdvancedReports\Block\Adminhtml\Advancedreport\Products\Productsreport;

class Grid extends \Lof\AdvancedReports\Block\Adminhtml\Grid\AbstractGrid
{

    protected $_columnDate = 'main_table.created_at';
    protected $_columnGroupBy = '';
    protected $_defaultSort = 'period';
    protected $_defaultDir = 'ASC';
    protected $_resource_grid_collection = null;
    protected $_scopeconfig;
    public function _construct()
    {  
        parent::_construct(); 
        $this->setCountTotals(true);
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setId('productsreportGrid');
        $this->setUseAjax(false);  
    }
 /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\AdvancedReports\Model\ResourceModel\Products\Collection';
    }
    protected function _prepareColumns()
    {      
$this->addColumn('name', array(
            'header'    =>  __('Product Name'),
            'width'     => '215px',
            'index'     => 'name',
            'type'      => 'text',
            'filter'    => false,
            'filter_data'   => $this->getFilterData(),
            'renderer'      => 'Lof\AdvancedReports\Block\Adminhtml\Grid\Column\Renderer\Productname',
            'totals_label'  => __('Total'),
            'html_decorators' => array('nobr')
        ));
        $this->addColumn('sku', array(
            'header'    => __('Product SKU'),
            'type'      => 'text',
            'filter'    => false,
            'index'     =>'sku'
        ));

        $this->addColumn('product_type', array(
            'header'    => __('Type'),
            'filter'    => false,
            'index'     => 'product_type',
            'visibility_filter' => array('show_actual_columns')
        ));

        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('price', array(
            'header'    => __('Price'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'price',
            'type'      => 'currency',
            'rate'          => $rate,
            'total'     => 'sum',
        ));

        $this->addColumn('total_qty', array(
            'header'    => __('Quantity'),
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'total_qty',
            'type'      => 'number',
            'total'     => 'sum',
        ));

        $this->addColumn('orders_count', array(
            'header'    => __('Unique Purchases'),
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'orders_count',
            'type'      => 'number',
            'total'     => 'sum',
        ));

        $this->addColumn('total_revenue_amount', array(
            'header'    => __('Revenue'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'total_revenue_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
        ));

        $this->addColumn('total_qty_invoiced', array(
            'header'    => __('Invoiced'),
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'total_qty_invoiced',
            'type'      => 'number',
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_qty_refunded', array(
            'header'    => __('QTY Refunded'),
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'total_qty_refunded',
            'type'      => 'number',
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_refunded_amount', array(
            'header'    => __('Refunded'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'total_refunded_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_tax_amount', array(
            'header'    => __('Tax'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'total_tax_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
        ));

        $this->addColumn('total_discount_amount', array(
            'header'    => __('Discount'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'total_discount_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        )); 

        $this->addColumn('avg_price', array(
            'header'    => __('Avg. Price'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'avg_price',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('avg_qty', array(
            'header'    => __('Avg. QTY'),
            'align'     => 'right',
            'filter'    => false,
            'index'     => 'avg_qty',
            'type'      => 'number',
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('avg_revenue', array(
            'header'    => __('Avg. Revenue'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'avg_revenue',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('avg_discount_amount', array(
            'header'    => __('Avg. Discount'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'avg_discount_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('avg_tax_amount', array(
            'header'    => __('Avg. Tax'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'avg_tax_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('avg_refunded_amount', array(
            'header'    => __('Avg. Refunded'),
            'align'     => 'right',
            'filter'    => false,
            'currency_code' => $currencyCode,
            'index'     => 'avg_refunded_amount',
            'type'      => 'currency',
            'rate'      => $rate,
            'total'     => 'sum',
            'visibility_filter' => array('show_actual_columns')
        ));
 
        $this->addExportType('*/*/exportProductsReportCsv', __('CSV'));
        $this->addExportType('*/*/exportProductsReportExcel', __('Excel XML'));
        // }
       
        // $this->initExportActions();

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {

        $filterData = $this->getFilterData();
        $report_type = $this->getReportType(); 
        $limit = $filterData->getData("limit", null);
        if(!$limit) {
            $limit = $this->_defaultLimit;
        } 
        $report_field = $filterData->getData("report_field", null);
        $report_field = $report_field?$report_field: "main_table.created_at";
        $this->setCulumnDate($report_field);
        $this->setDefaultSort("orders_count");
        $this->setDefaultDir("DESC");
        
        $storeIds = $this->_getStoreIds();  
        $resourceCollection = $this->_objectManager->create($this->getResourceCollectionName())
            ->prepareProductsCollection()
            ->setMainTableId("product_id")
            ->setPeriodType($this->getPeriodType())
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($filterData->getData('filter_from', null))
            ->addDateToFilter($filterData->getData('filter_to', null))
            ->addProductNameFilter($filterData->getData('name', null))
            ->addProductSkuFilter($filterData->getData('sku', null))
            ->addStoreFilter($storeIds)
            ->setAggregatedColumns($this->_getAggregatedColumns());
 
            
        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);
        $resourceCollection->getSelect()
                            ->group('product_id')
                            ->order(new \Zend_Db_Expr($this->getColumnOrder()." ".$this->getColumnDir()));
        $resourceCollection->applyCustomFilter(); 
 
        $resourceCollection->setPageSize((int) $this->getParam($this->getVarNameLimit(), $limit));
        $resourceCollection->setCurPage((int) $this->getParam($this->getVarNamePage(), $this->_defaultPage));

        $order_filter = $this->getParam($this->getVarNameFilter(), null);
   
        if ($filterData->getData('show_empty_rows', false)) {
            $this->_reportsData->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('year', null),
                $filterData->getData('month', null),
                $filterData->getData('day', null)
            );
        } 
        $this->setCollection($resourceCollection); 
        if(!$this->_registry->registry('report_collection')) {
            $this->_registry->register('report_collection', $resourceCollection);
        } 
        
        $this->_prepareTotals('price,total_qty,total_canceled,total_qty_invoiced,total_qty_refunded,total_refunded_amount,total_tax_amount,total_discount_amount,total_revenue_amount,orders_count,avg_price,avg_qty,avg_revenue,avg_refunded,avg_discount_amount,avg_tax_amount,avg_refunded_amount'); //Add this Line with all the columns you want to have in totals bar

        return parent::_prepareCollection();
    } 

}