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
namespace Lof\AdvancedReports\Model\ResourceModel\Products;
class  Collection extends \Lof\AdvancedReports\Model\ResourceModel\AbstractReport\Ordercollection
{
 protected $_date_column_filter = "main_table.created_at";
 protected $_period_type = "";
 protected $_product_name_filter = "";
 protected $_product_sku_filter = "";
 protected $_product_id_filter = "";
     /**
     * Is live
     *
     * @var boolean
     */
     protected $_isLive   = false;
     
     
     /**
     * Sales amount expression
     *
     * @var string
     */
     protected $_salesAmountExpression;
     
     /**
     * Check range for live mode
     *
     * @param unknown_type $range
     * @return Mage_Reports_Model_Resource_Order_Collection
     */


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
    public function addDateFromFilter($from = null)
    {
        $this->_from_date_filter = $from;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addDateToFilter($to = null)
    {
        $this->_to_date_filter = $to;
        return $this;
    }

    public function setPeriodType($period_type = "") {
        $this->_period_type = $period_type;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addProductIdFilter($product_id = 0)
    {
        $this->_product_id_filter = $product_id;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addProductNameFilter($product_name = "")
    {
        $this->_product_name_filter = $product_name;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addProductSkuFilter($product_sku = "")
    {
        $this->_product_sku_filter = $product_sku;
        return $this;
    }


    protected function _applyDateFilter()
    {
        $select_datefield = array();
        if($this->_period_type) {
            switch( $this->_period_type) {
                case "year":
                $select_datefield = array(
                    'period'  => 'YEAR('.$this->getDateColumnFilter().')'
                );
                break;
                case "quarter":
                $select_datefield = array(
                    'period'  => 'CONCAT(QUARTER('.$this->getDateColumnFilter().'),"/",YEAR('.$this->getDateColumnFilter().'))'
                );
                break;
                case "week":
                $select_datefield = array(
                    'period'  => 'CONCAT(YEAR('.$this->getDateColumnFilter().'),"", WEEK('.$this->getDateColumnFilter().'))'
                );
                break;
                case "day":
                $select_datefield = array(
                    'period'  => 'DATE('.$this->getDateColumnFilter().')'
                );
                break;
                case "hour":
                $select_datefield = array(
                    'period'  => "DATE_FORMAT(".$this->getDateColumnFilter().", '%H:00')"
                );
                break;
                case "weekday":
                $select_datefield = array(
                    'period'  => 'WEEKDAY('.$this->getDateColumnFilter().')'
                );
                break;
                case "month":
                default:
                $select_datefield = array(
                    'period'  => 'CONCAT(MONTH('.$this->getDateColumnFilter().'),"/",YEAR('.$this->getDateColumnFilter().'))',
                    'period_sort'  => 'CONCAT(MONTH('.$this->getDateColumnFilter().'),"",YEAR('.$this->getDateColumnFilter().'))'
                );
                break;
            }
        }
        if($select_datefield) {
            $this->getSelect()->columns($select_datefield);
        }


        // sql theo filter date 
        if($this->_to_date_filter && $this->_from_date_filter) {  

            // kiem tra lai doan convert ngay thang nay ! 

            $dateStart = $this->_localeDate->convertConfigTimeToUtc($this->_from_date_filter,'Y-m-d 00:00:00');
            $endStart = $this->_localeDate->convertConfigTimeToUtc($this->_to_date_filter, 'Y-m-d 23:59:59'); 
            $dateRange = array('from' => $dateStart, 'to' => $endStart , 'datetime' => true);

            $this->addFieldToFilter($this->getDateColumnFilter(), $dateRange);
        }


        return $this;
    }

    public function applyCustomFilter() { 
        $this->_applyDateFilter();
        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();
        $this->applyProductFilter();
        return $this;
    }

    public function applyProductIdFilter() { 
        if($this->_product_id_filter) {
            $this->getSelect()->where('main_table.product_id IN(?)', $this->_product_id_filter);  
        }
        return $this;
    }

    public function applyOrderStatusFilter(){ 
        $this->_applyDateFilter();
        $this->_applyOrderStatusFilter();
        return $this;
    }

    public function applyProductFilter() {
        if($this->_product_name_filter) {   
            $this->addFieldToFilter(
                array('main_table.name'),
                array( 
                    array('like' => '%'.$this->_product_name_filter.'%')
                )             
            ); 
        }
        if($this->_product_sku_filter) {
            $this->addFieldToFilter("main_table.sku", $this->_product_sku_filter);    
        } 
        return $this;
    }

    public function prepareProductReportCollection() {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->prepareProductsCollection();
        $this->getSelect()->group('period');
        return $this;
    }

    public function prepareProductSoldTogetherCollection(){ 
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->_prepareProductSoldTogetherCollection(); 
        return $this;
    }


    public function prepareProductsCollection() {
        $adapter = $this->getResource()->getConnection(); 

        $this->setMainTable('sales_order_item');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $this->getSelect()->columns(array(
            'name'                      => 'main_table.name',
            'sku'                       => 'main_table.sku',
            'product_options'           => 'main_table.product_options',
            'product_id'                => 'main_table.product_id',
            'price'                     => 'main_table.price',
            'product_type'              => 'main_table.product_type',
            'total_canceled'            => 'IFNULL(SUM(main_table.qty_canceled),0)',
            'total_qty'                 => 'IFNULL(SUM(main_table.qty_ordered),0)',
            'total_qty_invoiced'        => 'IFNULL(SUM(main_table.qty_invoiced),0)',
            'total_qty_refunded'        => 'IFNULL(SUM(main_table.qty_refunded),0)',
            'total_refunded_amount'     => 'IFNULL(SUM(main_table.amount_refunded),0)',
            'total_tax_amount'          => 'IFNULL(SUM(main_table.tax_amount),0)',
            'total_discount_amount'     => 'IFNULL(SUM(main_table.discount_amount),0)',
            'total_base_amount'         => 'IFNULL(SUM(main_table.base_row_total),0)',
            'total_revenue_amount'      => new \Zend_Db_Expr(
                sprintf('SUM(%s - %s - %s - %s)',
                    $adapter->getIfNullSql('main_table.base_row_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_amount_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0)
                )
            ),
            'orders_count'              => 'COUNT(o.entity_id)',
            'avg_price'                 => 'AVG(main_table.price)',
            'avg_qty'                   => 'AVG(main_table.qty_ordered)',
            'avg_revenue'               => 'AVG(main_table.row_total)',
            'avg_refunded'              => 'AVG(main_table.qty_refunded)',
            'avg_discount_amount'       => 'AVG(main_table.discount_amount)',
            'avg_tax_amount'            => 'AVG(main_table.tax_amount)',
            'avg_refunded_amount'       => 'AVG(main_table.amount_refunded)',
        ));
        $this->join(array('o'=>'sales_order'),'main_table.order_id=o.entity_id', array()); 
        $this->getSelect()->where('main_table.parent_item_id IS NULL OR main_table.parent_item_id = "" OR main_table.parent_item_id = "0"')
        ->where('o.state NOT IN (?)', array(
            \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
            \Magento\Sales\Model\Order::STATE_NEW
        ));

        $this->_status_field = 'o.status'; 
        return $this;
    }


    public function prepareProductsConfigCollection() {
        $adapter = $this->getResource()->getConnection(); 

        $this->setMainTable('sales_order_item');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $this->getSelect()->columns(array(
            'name'                      => 'main_table.name',
            'sku'                       => 'main_table.sku',
            'product_options'           => 'main_table.product_options',
            'product_id'                => 'main_table.product_id',
            'price'                     => 'main_table.price',
            'product_type'              => 'main_table.product_type',
            'total_canceled'            => 'IFNULL(SUM(main_table.qty_canceled),0)',
            'total_qty'                 => 'IFNULL(SUM(main_table.qty_ordered),0)',
            'total_qty_invoiced'        => 'IFNULL(SUM(main_table.qty_invoiced),0)',
            'total_qty_refunded'        => 'IFNULL(SUM(main_table.qty_refunded),0)',
            'total_refunded_amount'     => 'IFNULL(SUM(main_table.amount_refunded),0)',
            'total_tax_amount'          => 'IFNULL(SUM(main_table.tax_amount),0)',
            'total_discount_amount'     => 'IFNULL(SUM(main_table.discount_amount),0)',
            'total_base_amount'         => 'IFNULL(SUM(main_table.base_row_total),0)',
            'total_revenue_amount'      => new \Zend_Db_Expr(
                sprintf('SUM(%s - %s - %s - %s)',
                    $adapter->getIfNullSql('main_table.base_row_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_amount_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0)
                )
            ),
            'orders_count'              => 'COUNT(o.entity_id)',
            'avg_price'                 => 'AVG(main_table.price)',
            'avg_qty'                   => 'AVG(main_table.qty_ordered)',
            'avg_revenue'               => 'AVG(main_table.row_total)',
            'avg_refunded'              => 'AVG(main_table.qty_refunded)',
            'avg_discount_amount'       => 'AVG(main_table.discount_amount)',
            'avg_tax_amount'            => 'AVG(main_table.tax_amount)',
            'avg_refunded_amount'       => 'AVG(main_table.amount_refunded)',
        ));
        $this->join(array('o'=>'sales_order'),'main_table.order_id=o.entity_id', array()); 
        $this->getSelect()->where('main_table.parent_item_id IS NOT NULL OR main_table.parent_item_id <> "" OR main_table.parent_item_id <> "0"' )
        ->where('o.state NOT IN (?)', array(
            \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
            \Magento\Sales\Model\Order::STATE_NEW
        ));

        $this->_status_field = 'o.status'; 
        return $this;
    }



    public function _prepareProductSoldTogetherCollection(){ 
        $adapter = $this->getResource()->getConnection(); 
        
        $this->setMainTable('sales_order_item');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $this->getSelect()->columns(array(
            'original_sku'                      => 'main_table.sku',
            'original_name'                     => 'main_table.name',
            'bought_with_sku'                   => 'c.sku',
            'bought_with_name'                  => 'c.name',
            'times_bought_together'             => 'COUNT(*)',
            'total_product_id'      => new \Zend_Db_Expr(
                sprintf('SUM(%s + %s)',
                    $adapter->getIfNullSql('main_table.product_id', 0),
                    $adapter->getIfNullSql('c.product_id', 0)
                )
            ),

        ));
        $this->join(array('o'=>'sales_order'),'main_table.order_id=o.entity_id', array());  
        $this->join(array('b'=>'sales_order_item'),'main_table.sku=b.sku', array());
        $this->join(array('c'=>'sales_order_item'),'b.order_id=c.order_id', array());
        $this->getSelect()->where('main_table.order_id = b.order_id AND main_table.sku <> c.sku AND main_table.product_type <> "configurable" AND c.product_type <> "configurable" AND b.product_type <> "configurable"');
        $this->getSelect()->group('original_sku')->group('bought_with_sku');     

        $this->_status_field = 'o.status'; 
        return $this; 
    }

    public function prepareInventoryCollection() {
        $adapter = $this->getResource()->getConnection(); 

        $this->setMainTable('sales_order_item');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $this->getSelect()->columns(array( 
            'product_id'                => 'main_table.product_id',  
            'total_qty'                 => 'IFNULL(SUM(main_table.qty_ordered),0)', 
            'total_revenue_amount'      => new \Zend_Db_Expr(
                sprintf('SUM(%s - %s - %s - %s)',
                    $adapter->getIfNullSql('main_table.base_row_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_amount_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0)
                )
            ),
            'total_tax_amount'          => 'IFNULL(SUM(main_table.tax_amount),0)'
        ));
        $this->join(array('o'=>'sales_order'),'main_table.order_id=o.entity_id', array()); 
        $this->getSelect()->where('main_table.parent_item_id IS NULL OR main_table.parent_item_id = "" OR main_table.parent_item_id = "0"');

        $this->_status_field = 'o.status';                                                                              
        return $this;
    }
    
    public function getSummary() {
        $adapter = $this->getResource()->getConnection(); 
        $this->setMainTable('sales_order');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns([ 
            'orders_count'  => 'COUNT(main_table.entity_id)',
            'total_revenue_amount' => 'SUM(main_table.total_paid)',
            'total_item_count' => 'SUM(main_table.total_item_count)',  
            'total_qty_ordered' => 'SUM(main_table.total_qty_ordered)'
        ]); 
        $data = $adapter->fetchRow($this->getSelect());
        
        return $data;   
    } 

    public function getAvailableQty(){
        $adapter = $this->getResource()->getConnection(); 
        $this->setMainTable('cataloginventory_stock_item');
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns([  
            'available_qty' => 'SUM(main_table.qty)' 
        ]); 
        $data = $adapter->fetchRow($this->getSelect());
        
        return $data;   
    }
    

}
