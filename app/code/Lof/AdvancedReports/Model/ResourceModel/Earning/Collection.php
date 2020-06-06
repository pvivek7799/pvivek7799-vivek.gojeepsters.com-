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
namespace Lof\AdvancedReports\Model\ResourceModel\Earning;

class Collection extends \Lof\AdvancedReports\Model\ResourceModel\AbstractReport\Ordercollection
{

    protected $_date_column_filter = "main_table.created_at";

    public function setDateColumnFilter($column_name = '') {
        if($column_name) {
            $this->_date_column_filter = $column_name;
        }
        return $this;
    }

    public function getDateColumnFilter() {
        return $this->_date_column_filter;
    }


    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addYearFilter($year)
    {
        $this->_year_filter = $year;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addMonthFilter($month)
    {
        $this->_month_filter = $month;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addDayFilter($day)
    {
        $this->_day_filter = $day;
        return $this;
    }

    protected function _applyDateFilter()
    {
        $select_datefield = array();
        if($this->_year_filter) {
            $select_datefield = array(
                'period'  => 'MONTH('.$this->getDateColumnFilter().')',
                $this->getDateColumnFilter()
                );

            $this->getSelect()->where('YEAR('.$this->getDateColumnFilter().") = ?", $this->_year_filter);

        } else {
            $select_datefield = array(
                'period'  => 'YEAR('.$this->getDateColumnFilter().')',
                $this->getDateColumnFilter()
                );
        }

        if($this->_month_filter) {
            $select_datefield = array(
                'period'  => 'DAY('.$this->getDateColumnFilter().')',
                $this->getDateColumnFilter()
            );

            $this->getSelect()->where('MONTH('.$this->getDateColumnFilter().") = ?", $this->_month_filter);
        }

        if($this->_day_filter) {
            $select_datefield = array(
                'period'  => 'HOUR('.$this->getDateColumnFilter().')',
                $this->getDateColumnFilter()
            ); 
            $this->getSelect()->where('DAY('.$this->getDateColumnFilter().") = ?", $this->_day_filter);
        }

        if($select_datefield) {
            $this->getSelect()->columns($select_datefield);
        }
        return $this;
    }

    public function prepareReportCollection() {
        $adapter = $this->getResource()->getConnection();
        // $adapter->beginTransaction();
        
        $this->setMainTable('sales_order');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns([
            'orders_count'                   => 'COUNT(main_table.entity_id)',
            'total_revenue_amount1'           => 'SUM(main_table.total_paid)',
            'total_revenue_amount'           => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s - %s - (%s - %s - %s)) * %s)',
                        $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
            'total_item_count'                => 'SUM(main_table.total_item_count)',
            'total_qty_ordered'               => 'SUM(main_table.total_qty_ordered)'
        ]);
        return $this;
    }

    public function prepareBestsellersCollection() {
        $this->setMainTable('sales_order');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns([
            'qty_ordered' => 'SUM(order_item.qty_ordered)',
            'orders_count'  => 'COUNT(main_table.entity_id)',
            'total_revenue_amount' => 'SUM(main_table.total_paid)',
            'total_item_count' => 'SUM(main_table.total_item_count)',
            'total_qty_ordered' => 'SUM(main_table.total_qty_ordered)',
            'base_row_total' => 'SUM(order_item.base_row_total)',
            'product_id'     => 'order_item.product_id',
            'product_name'   => 'MAX(order_item.name)',
            'product_price'  => 'MAX(order_item.price)'
        ]);
        return $this;
    }

    public function prepareCountryReport() {
        $this->setMainTable('sales_order');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns([
            'orders_count'  => 'COUNT(main_table.entity_id)',
            'total_revenue_amount' => 'SUM(main_table.total_paid)',
            'total_item_count' => 'SUM(main_table.total_item_count)',
            'total_qty_ordered' => 'SUM(main_table.total_qty_ordered)'
        ]);
        return $this;
    }

    public function applyCustomFilter() {
        $this->_applyDateFilter();
        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();
        return $this;
    }
}
