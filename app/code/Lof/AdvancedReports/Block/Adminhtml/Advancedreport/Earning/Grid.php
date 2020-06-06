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

namespace Lof\AdvancedReports\Block\Adminhtml\Advancedreport\Earning;

/**
 * Adminhtml sales report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Lof\AdvancedReports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {  
        parent::_construct();
        $this->setCountTotals(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\AdvancedReports\Model\ResourceModel\Earning\Collection';
    }
 
    protected function _prepareColumns()
    {   
        $this->addColumn('period',
            [
            'header'        => __('Period'),
            'index'         => 'period',
            'width'         => 100,
            'is_export'     => $this->_isExport,
            'filter_data'   => $this->getFilterData(),
            'period_type'   => $this->getPeriodType(), 
            'renderer'      => 'Lof\AdvancedReports\Block\Adminhtml\Grid\Column\Renderer\Date',
            'totals_label'  => __('Total'),
            'html_decorators' => array('nobr'),
            ]
        );   
        $this->addColumn('orders_count',
            [
            'header'    => __('Sales Count'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'total'     => 'sum'
            ]
        );

        $this->addColumn('total_item_count', 
            [
            'header'    => __('Products Ordered'),
            'index'     => 'total_item_count',
            'type'      => 'number',
            'total'     => 'sum'
            ]
        );

        $this->addColumn('total_qty_ordered',
            [
            'header'    => __('Sales Items'),
            'index'     => 'total_qty_ordered',
            'type'      => 'number',
            'total'     => 'sum'
            ]
        ); 
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('total_revenue_amount', 
            [
            'header'            =>  __('Revenue'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_revenue_amount',
            'total'             => 'sum',
            'rate'              => $rate
            ]
        );
 
        $this->addExportType('*/*/exportEarningCsv', __('CSV'));
        $this->addExportType('*/*/exportEarningExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
     protected function _prepareCollection()
    {

        $filterData = $this->getFilterData();  
        $storeIds = $this->_getStoreIds();
  
        $resourceCollection = $this->_objectManager->create($this->getResourceCollectionName())
            ->prepareReportCollection()
            ->setDateColumnFilter($this->_columnDate)
            ->addYearFilter($filterData->getData('filter_year', null))
            ->addMonthFilter($filterData->getData('filter_month', null))
            ->addDayFilter($filterData->getData('filter_day', null))
            ->addStoreFilter($storeIds)
            ->setAggregatedColumns($this->_getAggregatedColumns());

        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);

        $resourceCollection->getSelect()
                            ->group('period')
                            ->order(new \Zend_Db_Expr($this->getColumnOrder()." ".$this->getColumnDir()));

        $resourceCollection->applyCustomFilter(); 
// echo ($resourceCollection->getSelect());

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            $this->_prepareTotals('orders_count,total_item_count,total_qty_ordered,total_revenue_amount'); //Add this Line with all the columns you want to have in totals bar
            return parent::_prepareCollection();
        }

        if ($filterData->getData('show_empty_rows', false)) {
            $this->_reportsData->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('year', null),
                $filterData->getData('month', null),
                $filterData->getData('day', null)
            );
        }

        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        }

        if (!$this->getTotals()) {
            $totalsCollection = $this->_objectManager->create($this->getResourceCollectionName())
                ->prepareReportCollection()
                ->setDateColumnFilter($this->_columnDate)
                ->addYearFilter($filterData->getData('year', null))
                ->addMonthFilter($filterData->getData('month', null))
                ->addDayFilter($filterData->getData('day', null))
                ->addStoreFilter($storeIds)
                ->setAggregatedColumns($this->_getAggregatedColumns())
                ->isTotals(true);

            $this->_addOrderStatusFilter($totalsCollection, $filterData);
            $this->_addCustomFilter($totalsCollection, $filterData);

            $totalsCollection->getSelect()
                            ->group('period')
                            ->order(new \Zend_Db_Expr($this->getColumnOrder()." ".$this->getColumnDir()));

            $totalsCollection->applyCustomFilter();
            
            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }


        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);
        // echo $resourceCollection->getSelect();
        // echo '<pre>';
        // print_r($resourceCollection->getData());
        // echo '</pre>';
        if(!$this->_registry->registry('report_collection')) {
            $this->_registry->register('report_collection', $resourceCollection);
        } 

 
        $this->_prepareTotals('orders_count,total_item_count,total_qty_ordered,total_revenue_amount'); //Add this Line with all the columns you want to have in totals bar
        return parent::_prepareCollection();
    }
}
