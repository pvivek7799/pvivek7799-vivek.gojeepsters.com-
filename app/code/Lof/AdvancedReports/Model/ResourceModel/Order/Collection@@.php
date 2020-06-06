<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Reports
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports orders collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Lof\AdvancedReports\Model\ResourceModel\Order;
class  Collection extends \Lof\AdvancedReports\Model\ResourceModel\AbstractReport\Ordercollection
{
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
    public function checkIsLive($range)
    {
        $this->_isLive = (bool)!Mage::getStoreConfig('sales/dashboard/use_aggregated_data');
        return $this;
    }

    /**
     * Retrieve is live flag for rep
     *
     * @return boolean
     */
    public function isLive()
    {
        return $this->_isLive;
    }

    /**
     * Prepare report summary
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->checkIsLive($range);
        if ($this->_isLive) {
            $this->_prepareSummaryLive($range, $customStart, $customEnd, $isFilter);
        } else {
            $this->_prepareSummaryAggregated($range, $customStart, $customEnd, $isFilter);
        }

        return $this;
    }

    /**
     * Get sales amount expression
     *
     * @return string
     */
    protected function _getSalesAmountExpression()
    {
        if (is_null($this->_salesAmountExpression)) {
            $adapter = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(array(
                'expression' => '%s - %s - %s - (%s - %s - %s)',
                'arguments' => array(
                    $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                )
            ));

            Mage::dispatchEvent('sales_prepare_amount_expression', array(
                'collection' => $this,
                'expression_object' => $expressionTransferObject,
            ));
            $this->_salesAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }

    /**
     * Prepare report summary from live data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _prepareSummaryLive($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->setMainTable('sales_order');
        $adapter = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $expression = $this->_getSalesAmountExpression();
        if ($isFilter == 0) {
            $this->getSelect()->columns(array(
                'revenue' => new \Zend_Db_Expr(
                    sprintf('SUM((%s) * %s)', $expression,
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                 )
            ));
        } else {
            $this->getSelect()->columns(array(
                'revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $expression))
            ));
        }

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range, 'created_at', $dateRange['from'], $dateRange['to']
        );

        $this->getSelect()
            ->columns(array(
                'quantity' => 'COUNT(main_table.entity_id)',
                'range' => $tzRangeOffsetExpression,
            ))
            ->where('main_table.state NOT IN (?)', array(
                \Magento\Sales\Model\Order::STATE_NEW,
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                \Magento\Sales\Model\Order::STATE_CLOSED,
                \Magento\Sales\Model\Order::STATE_CANCELED,
                \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
                \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
            )
            ->order('range', \Magento\Framework\DB\Select::SQL_ASC)
            ->group($tzRangeOffsetExpression);

        $this->addFieldToFilter('created_at', $dateRange);

        return $this;
    }

    /**
     * Prepare report summary from aggregated data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _prepareSummaryAggregated($range, $customStart, $customEnd)
    {
        $this->setMainTable('sales/order_aggregated_created');
        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $rangePeriod = $this->_getRangeExpressionForAttribute($range, 'main_table.period');

        $tableName = $this->getConnection()->quoteIdentifier('main_table.period');
        $rangePeriod2 = str_replace($tableName, "MIN($tableName)", $rangePeriod);

        $this->getSelect()->columns(array(
            'revenue'  => 'SUM(main_table.total_revenue_amount)',
            'quantity' => 'SUM(main_table.orders_count)',
            'range' => $rangePeriod2,
        ))
        ->order('range')
        ->group($rangePeriod);

        $this->getSelect()->where(
            $this->_getConditionSql('main_table.period', $this->getDateRange($range, $customStart, $customEnd))
        );

        $statuses =  $this->_objectManager->create('Magento\Sales\Model\Order\Config')
            ->getOrderStatusesForState(\Magento\Sales\Model\Order::STATE_CANCELED);
        if (empty($statuses)) {
            $statuses = array(0);
        }
        $this->addFieldToFilter('main_table.order_status', array('nin' => $statuses));

        return $this;
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return \Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range)
        {
            case '24h':
                $expression = $this->getConnection()->getConcatSql(array(
                    $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                    $this->getConnection()->quote('00')
                ));
                break;
            case '7d':
            case '1m':
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                break;
        }

        return $expression;
    }

    /**
     * Retrieve range expression adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @return string
     */
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }

    /**
     * Retrieve query for attribute with timezone conversion
     *
     * @param string $range
     * @param string $attribute
     * @param mixed $from
     * @param mixed $to
     * @return string
     */
    protected function _getTZRangeOffsetExpression($range, $attribute, $from = null, $to = null)
    {
        return str_replace(
            '{{attribute}}',
            Mage::getResourceModel('sales/report_order')
                    ->getStoreTZOffsetQuery($this->getMainTable(), $attribute, $from, $to),
            $this->_getRangeExpression($range)
        );
    }

    // /**
    //  * Retrieve range expression with timezone conversion adapted for attribute
    //  *
    //  * @param string $range
    //  * @param string $attribute
    //  * @param string $tzFrom
    //  * @param string $tzTo
    //  * @return string
    //  */
    // protected function _getTZRangeExpressionForAttribute($range, $attribute, $tzFrom = '+00:00', $tzTo = null)
    // {
    //     if (null == $tzTo) {
    //         $tzTo = Mage::app()->getLocale()->storeDate()->toString(Zend_Date::GMT_DIFF_SEP);
    //     }
    //     $adapter = $this->getConnection();
    //     $expression = $this->_getRangeExpression($range);
    //     $attribute  = $adapter->quoteIdentifier($attribute);
    //     $periodExpr = $adapter->getDateAddSql($attribute, $tzTo, Varien_Db_Adapter_Interface::INTERVAL_HOUR);

    //     return str_replace('{{attribute}}', $periodExpr, $expression);
    // }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param boolean $returnObjects
     * @return array
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd   = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range)
        {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

    /**
     * Add item count expression
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addItemCountExpr()
    {
        $this->getSelect()->columns(array('items_count' => 'total_item_count'), 'main_table');
        return $this;
    }

    /**
     * Calculate totals report
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function calculateTotals($isFilter = 0)
    {
        if ($this->isLive()) {
            $this->_calculateTotalsLive($isFilter);
        } else {
            $this->_calculateTotalsAggregated($isFilter);
        }

        return $this;
    }

    /**
     * Calculate totals live report
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _calculateTotalsLive($isFilter = 0)
    {

        $this->setMainTable('sales_order');
        $this->removeAllFieldsFromSelect();

        $adapter = $this->getConnection();

        $baseTaxInvoiced      = $adapter->getIfNullSql('main_table.base_tax_invoiced', 0);
        $baseTaxRefunded      = $adapter->getIfNullSql('main_table.base_tax_refunded', 0);
        $baseShippingInvoiced = $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0);
        $baseShippingRefunded = $adapter->getIfNullSql('main_table.base_shipping_refunded', 0);

        $revenueExp = $this->_getSalesAmountExpression();
        $taxExp = sprintf('%s - %s', $baseTaxInvoiced, $baseTaxRefunded);
        $shippingExp = sprintf('%s - %s', $baseShippingInvoiced, $baseShippingRefunded);

        if ($isFilter == 0) {
            $rateExp = $adapter->getIfNullSql('main_table.base_to_global_rate', 0);
            $this->getSelect()->columns(
                array(
                    'revenue'  => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $revenueExp, $rateExp)),
                    'tax'      => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $taxExp, $rateExp)),
                    'shipping' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $shippingExp, $rateExp))
                )
            );
        } else {
            $this->getSelect()->columns(
                array(
                    'revenue'  => new \Zend_Db_Expr(sprintf('SUM(%s)', $revenueExp)),
                    'tax'      => new \Zend_Db_Expr(sprintf('SUM(%s)', $taxExp)),
                    'shipping' => new \Zend_Db_Expr(sprintf('SUM(%s)', $shippingExp))
                )
            );
        }

        $this->getSelect()->columns(array(
            'quantity' => 'COUNT(main_table.entity_id)'
        ))
        ->where('main_table.state NOT IN (?)', array(
            \Magento\Sales\Model\Order::STATE_NEW,
            \Magento\Sales\Model\Order::STATE_PROCESSING,
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_CANCELED,
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
            \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
         );

        return $this;
    }

    /**
     * Calculate totals aggregated report
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _calculateTotalsAggregated($isFilter = 0)
    {
        $this->setMainTable('sales_order_aggregated_created');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()->columns(array(
            'revenue'  => 'SUM(main_table.total_revenue_amount)',
            'tax'      => 'SUM(main_table.total_tax_amount_actual)',
            'shipping' => 'SUM(main_table.total_shipping_amount_actual)',
            'quantity' => 'SUM(orders_count)',
        ));

        $statuses = $this->_objectManager->create('Magento\Sales\Model\Order\Config')
            ->getOrderStatusesForState(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }

        $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        return $this;
    }

    /**
     * Calculate lifitime sales
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function calculateSales($isFilter = 0)
    {
        $statuses = $this->_objectManager->create('Magento\Sales\Model\Order\Config')
            ->getOrderStatusesForState(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $adapter = $this->getConnection();

        if (Mage::getStoreConfig('sales/dashboard/use_aggregated_data')) {
            $this->setMainTable('sales/order_aggregated_created');
            $this->removeAllFieldsFromSelect();
            $averageExpr = $adapter->getCheckSql(
                'SUM(main_table.orders_count) > 0',
                'SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count)',
                0);
            $this->getSelect()->columns(array(
                'lifetime' => 'SUM(main_table.total_revenue_amount)',
                'average'  => $averageExpr
            ));

            if (!$isFilter) {
                $this->addFieldToFilter('store_id',
                    array('eq' => $this->_storeManger->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId())
                );
            }
            $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        } else {
            $this->setMainTable('sales/order');
            $this->removeAllFieldsFromSelect();

            $expr = $this->_getSalesAmountExpression();

            if ($isFilter == 0) {
                $expr = '(' . $expr . ') * main_table.base_to_global_rate';
            }

            $this->getSelect()
                ->columns(array(
                    'lifetime' => "SUM({$expr})",
                    'average'  => "AVG({$expr})"
                ))
                ->where('main_table.status NOT IN(?)', $statuses)
                ->where('main_table.state NOT IN(?)', array(
                    \Magento\Sales\Model\Order::STATE_NEW,
                    \Magento\Sales\Model\Order::STATE_PROCESSING,
                    \Magento\Sales\Model\Order::STATE_CLOSED,
                    \Magento\Sales\Model\Order::STATE_CANCELED,
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
                );
        }
        return $this;
    }

    /**
     * Set date range
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addFieldToFilter('created_at', array('from' => $from, 'to' => $to))
            ->addFieldToFilter('state', array('neq' => \Magento\Sales\Model\Order::STATE_CANCELED))
            ->getSelect()
                ->columns(array('orders' => 'COUNT(DISTINCT(main_table.entity_id))'))
                ->group('entity_id');

        $this->getSelect()->columns(array(
            'items' => 'SUM(main_table.total_qty_ordered)')
        );

        return $this;
    }

    /**
     * Set store filter collection
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function setStoreIds($storeIds)
    {
        $adapter = $this->getConnection();
        $baseSubtotalInvoiced = $adapter->getIfNullSql('main_table.base_subtotal_invoiced', 0);
        $baseDiscountRefunded = $adapter->getIfNullSql('main_table.base_discount_refunded', 0);
        $baseSubtotalRefunded = $adapter->getIfNullSql('main_table.base_subtotal_refunded', 0);
        $baseDiscountInvoiced = $adapter->getIfNullSql('main_table.base_discount_invoiced', 0);
        $baseTotalInvocedCost = $adapter->getIfNullSql('main_table.base_total_invoiced_cost', 0);
        if ($storeIds) {
            $this->getSelect()->columns(array(
                'subtotal'  => 'SUM(main_table.base_subtotal)',
                'tax'       => 'SUM(main_table.base_tax_amount)',
                'shipping'  => 'SUM(main_table.base_shipping_amount)',
                'discount'  => 'SUM(main_table.base_discount_amount)',
                'total'     => 'SUM(main_table.base_grand_total)',
                'invoiced'  => 'SUM(main_table.base_total_paid)',
                'refunded'  => 'SUM(main_table.base_total_refunded)',
                'profit'    => "SUM($baseSubtotalInvoiced) "
                                . "+ SUM({$baseDiscountRefunded}) - SUM({$baseSubtotalRefunded}) "
                                . "- SUM({$baseDiscountInvoiced}) - SUM({$baseTotalInvocedCost})"
            ));
        } else {
            $this->getSelect()->columns(array(
                'subtotal'  => 'SUM(main_table.base_subtotal * main_table.base_to_global_rate)',
                'tax'       => 'SUM(main_table.base_tax_amount * main_table.base_to_global_rate)',
                'shipping'  => 'SUM(main_table.base_shipping_amount * main_table.base_to_global_rate)',
                'discount'  => 'SUM(main_table.base_discount_amount * main_table.base_to_global_rate)',
                'total'     => 'SUM(main_table.base_grand_total * main_table.base_to_global_rate)',
                'invoiced'  => 'SUM(main_table.base_total_paid * main_table.base_to_global_rate)',
                'refunded'  => 'SUM(main_table.base_total_refunded * main_table.base_to_global_rate)',
                'profit'    => "SUM({$baseSubtotalInvoiced} *  main_table.base_to_global_rate) "
                                . "+ SUM({$baseDiscountRefunded} * main_table.base_to_global_rate) "
                                . "- SUM({$baseSubtotalRefunded} * main_table.base_to_global_rate) "
                                . "- SUM({$baseDiscountInvoiced} * main_table.base_to_global_rate) "
                                . "- SUM({$baseTotalInvocedCost} * main_table.base_to_global_rate)"
            ));
        }

        return $this;
    }

    /**
     * Add group By customer attribute
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function groupByCustomer()
    {
        $this->getSelect()
            ->where('main_table.customer_id IS NOT NULL')
            ->group('main_table.customer_id');

        /*
         * Allow Analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Join Customer Name (concat)
     *
     * @param string $alias
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function joinCustomerName($alias = 'name')
    {
        $fields  = array(
            'main_table.customer_firstname',
            'main_table.customer_middlename',
            'main_table.customer_lastname'
        );
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns(array($alias => $fieldConcat));
        return $this;
    }

    /**
     * Add Order count field to select
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addOrdersCount()
    {
        $this->addFieldToFilter('state', array('neq' => \Magento\Sales\Model\Order::STATE_CANCELED));
        $this->getSelect()
            ->columns(array('orders_count' => 'COUNT(main_table.entity_id)'));

        return $this;
    }

    /**
     * Add revenue
     *
     * @param boolean $convertCurrency
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addRevenueToSelect($convertCurrency = false)
    {
        if ($convertCurrency) {
            $this->getSelect()->columns(array(
                'revenue' => '(main_table.base_grand_total * main_table.base_to_global_rate)'
            ));
        } else {
            $this->getSelect()->columns(array(
                'revenue' => 'base_grand_total'
            ));
        }

        return $this;
    }

    /**
     * Add summary average totals
     *
     * @param int $storeId
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addSumAvgTotals($storeId = 0)
    {
        $adapter = $this->getConnection();
        $baseSubtotalRefunded = $adapter->getIfNullSql('main_table.base_subtotal_refunded', 0);
        $baseSubtotalCanceled = $adapter->getIfNullSql('main_table.base_subtotal_canceled', 0);
        $baseDiscountCanceled = $adapter->getIfNullSql('main_table.base_discount_canceled', 0);

        /**
         * calculate average and total amount
         */
        $expr = ($storeId == 0)
            ? "(main_table.base_subtotal -
            {$baseSubtotalRefunded} - {$baseSubtotalCanceled} - ABS(main_table.base_discount_amount) -
            {$baseDiscountCanceled}) * main_table.base_to_global_rate"
            : "main_table.base_subtotal - {$baseSubtotalCanceled} - {$baseSubtotalRefunded} -
            ABS(main_table.base_discount_amount) - {$baseDiscountCanceled}";

        $this->getSelect()
            ->columns(array('orders_avg_amount' => "AVG({$expr})"))
            ->columns(array('orders_sum_amount' => "SUM({$expr})"));

        return $this;
    }

    /**
     * Sort order by total amount
     *
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByTotalAmount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('orders_sum_amount ' . $dir);
        return $this;
    }

    /**
     * Order by orders count
     *
     * @param unknown_type $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByOrdersCount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('orders_count ' . $dir);
        return $this;
    }

    /**
     * Order by customer registration
     *
     * @param unknown_type $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByCustomerRegistration($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('customer_id', $dir);
        return $this;
    }

    /**
     * Sort order by order created_at date
     *
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByCreatedAt($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('created_at', $dir);
        return $this;
    }

    /**
     * Get select count sql
     *
     * @return unknown
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->columns("COUNT(DISTINCT main_table.entity_id)");

        return $countSelect;
    }

    /**
     * Initialize initial fields to select
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _initInitialFieldsToSelect()
    {
        // No fields should be initialized
        return $this;
    }

    /**
     * Add period filter by created_at attribute
     *
     * @param string $period
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $this->checkIsLive($period);

        if ($this->isLive()) {
            $fieldToFilter = 'created_at';
        } else {
            $fieldToFilter = 'period';
        }

        $this->addFieldToFilter($fieldToFilter, array(
            'from'  => $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to'    => $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));

        return $this;
    }

     /**
     * Apply stores filter to select object
     *
     * @param \Magento\Framework\DB\Select $select
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyStoresFilterToSelect(\Magento\Framework\DB\Select $select)
    {
        $nullCheck = false;
        $storeIds  = $this->_storesIds;

        if($storeIds) {
            if (!is_array($storeIds)) {
                $storeIds = array($storeIds);
            }

            $storeIds = array_unique($storeIds);

            if ($index = array_search(null, $storeIds)) {
                unset($storeIds[$index]);
                $nullCheck = true;
            }

            $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

            if ($nullCheck) {
                $select->where('main_table.store_id IN(?) OR main_table.store_id IS NULL', $storeIds);
            } else {
                $select->where('main_table.store_id IN(?)', $storeIds);
            }
        }
        return $this;
    }

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
        if($this->_to_date_filter && $this->_from_date_filter) {
            $locale = new Zend_Locale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE));

            $dateStart = new Zend_Date(null, null, $locale);
            $dateStart->setDate($this->_from_date_filter, $locale->getTranslation(null, 'date', $locale));
            $dateStart->setHour(0);
            $dateStart->setMinute(0);
            $dateStart->setSecond(0);

            $dateEnd = new Zend_Date(null, null, $locale);
            $dateEnd->setDate($this->_to_date_filter, $locale->getTranslation(null, 'date', $locale));
            $dateEnd->setHour(23);
            $dateEnd->setMinute(59);
            $dateEnd->setSecond(59);

            $dateStart->setTimezone('Etc/UTC');
            $dateEnd->setTimezone('Etc/UTC');

            $dateRange = array('from' => $dateStart->toString(Varien_Date::DATETIME_INTERNAL_FORMAT), 'to' => $dateEnd->toString(Varien_Date::DATETIME_INTERNAL_FORMAT), 'datetime' => true);

            $this->addFieldToFilter($this->getDateColumnFilter(), $dateRange);
        }
        return $this;
    }

    public function applyCustomFilter() {
        $this->_applyDateFilter();
        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();
        return $this;
    }

    public function prepareOrderDetailedCollection() {
        $hide_fields = array("avg_item_cost", "avg_order_amount");
        $this->setMainTableId('main_table.entity_id');
        $this->_aggregateByField('main_table.entity_id', $hide_fields);
        return $this;
    }

    public function prepareOrderItemDetailedCollection() {
        $hide_fields = array("avg_item_cost", "avg_order_amount");
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->_aggregateOrderItemsByField('', $hide_fields);
        return $this;
    }


    /**
     * Aggregate Orders data by custom field
     *
     * @throws Exception
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Order_Createdat
     */
    protected function _aggregateByField($aggregationField = "", $hide_fields = array(), $show_fields = array())
    {
        $adapter = $this->getResource()->getConnection();
        $adapter->beginTransaction();
        try {

            $subSelect = null;
            // Columns list
            $columns = array(
                // convert dates from UTC to current admin timezone
                'store_id'                       => 'main_table.store_id',
                'order_status'                   => 'main_table.status',
                'product_type'                   => 'oi.product_type',
                'total_cost_amount'              => new \Zend_Db_Expr('IFNULL(SUM(oi.total_cost_amount),0)'),
                'orders_count'                   => new \Zend_Db_Expr('COUNT(main_table.entity_id)'),
                'total_qty_ordered'              => new \Zend_Db_Expr('SUM(oi.total_qty_ordered)'),
                'total_qty_shipping'             => new \Zend_Db_Expr('SUM(oi.total_qty_shipping)'),
                'total_qty_refunded'             => new \Zend_Db_Expr('SUM(oi.total_qty_refunded)'),
                'total_subtotal_amount'          => new \Zend_Db_Expr('SUM(main_table.subtotal)'),
                'total_qty_invoiced'             => new \Zend_Db_Expr('SUM(oi.total_qty_invoiced)'),
                'total_grandtotal_amount'        => new \Zend_Db_Expr('SUM(main_table.grand_total)'),
                'avg_item_cost'                  => new \Zend_Db_Expr('AVG(oi.total_item_cost)'),
                'avg_order_amount'               => new \Zend_Db_Expr(
                sprintf('AVG((%s - %s - %s - (%s - %s - %s)) * %s)',
                    $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_income_amount'            => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_grand_total', 0),
                        $adapter->getIfNullSql('main_table.base_total_canceled',0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate',0)
                    )
                ),
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
                'total_profit_amount'            => new \Zend_Db_Expr(
                    sprintf('SUM(((%s - %s) - (%s - %s) - (%s - %s) - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_total_paid', 0),
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_total_invoiced_cost', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_invoiced_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_canceled_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_paid_amount'              => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_paid', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_refunded_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount'               => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_tax_amount', 0),
                        $adapter->getIfNullSql('main_table.base_tax_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount_actual'        => new \Zend_Db_Expr(
                    sprintf('SUM((%s -%s) * %s)',
                        $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_shipping_amount', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount_actual'   => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM((ABS(%s) - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_discount_amount', 0),
                        $adapter->getIfNullSql('main_table.base_discount_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount_actual'   => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_discount_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_discount_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                )
            );
            
            if($hide_fields) {
                foreach($hide_fields as $field){
                    if(isset($columns[$field])){
                        unset($columns[$field]);
                    }
                }
            }

            $selectOrderItem = $adapter->select();

            $qtyCanceledExpr = $adapter->getIfNullSql('qty_canceled', 0);
            $cols            = array(
                'order_id'           => 'order_id',
                'product_id'         => 'product_id',
                'product_type'       => 'product_type',
                'created_at'         => 'created_at',
                'sku'                => 'sku',
                'total_cost_amount'  => new \Zend_Db_Expr('SUM(base_cost)'),
                'total_qty_ordered'  => new \Zend_Db_Expr("SUM(qty_ordered - {$qtyCanceledExpr})"),
                'total_qty_invoiced' => new \Zend_Db_Expr('SUM(qty_invoiced)'),
                'total_qty_shipping' => new \Zend_Db_Expr('SUM(qty_shipped)'),
                'total_qty_refunded' => new \Zend_Db_Expr('SUM(qty_refunded)'),
                'total_item_cost'    => new \Zend_Db_Expr('SUM(row_total)'),
            );
            $selectOrderItem->from($this->getTable('sales/order_item'), $cols)
                ->where('parent_item_id IS NULL')
                ->group('order_id');

            
            $columns['store_id']       = new \Zend_Db_Expr($this->_storeManger->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()); 
           
            $this->getSelect()->columns($columns)
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = main_table.entity_id', array());

            if($aggregationField) {
                $this->getSelect()->group($aggregationField);
            }
            
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }


    /**
     * Aggregate Orders data by custom field
     *
     * @throws Exception
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Order_Createdat
     */
    protected function _aggregateOrderItemsByField($aggregationField = "", $hide_fields = array(), $show_fields = array())
    {
        $adapter = $this->getResource()->getConnection();
        $adapter->beginTransaction();
        try {

            $subSelect = null;
            $qtyCanceledExpr = $adapter->getIfNullSql('oi.qty_canceled', 0);
            // Columns list
            $columns = array(
                // convert dates from UTC to current admin timezone
                'oi.*'                           => 'oi.*',
                'increment_id'                   => 'main_table.increment_id',
                'status'                         => 'main_table.status',
                'created_at'                     => 'main_table.created_at',
                'store_id'                       => 'main_table.store_id',
                'order_status'                   => 'main_table.status',
                'real_tax_refunded'              => new \Zend_Db_Expr("IFNULL(oi.tax_refunded,0)"),
                'real_qty_shipped'               => new \Zend_Db_Expr("IFNULL(oi.qty_shipped,0)"),
                'real_qty_refunded'              => new \Zend_Db_Expr("IFNULL(oi.qty_refunded,0)"),
                'real_qty_ordered'               => new \Zend_Db_Expr("(oi.qty_ordered - {$qtyCanceledExpr})"),
                'subtotal'                       => new \Zend_Db_Expr("(oi.qty_ordered * oi.base_price)"),
                'total_revenue_amount'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL((%s - %s - %s - %s - %s),0) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0)
                    )
                ),
                'row_refunded_incl_tax'           => new \Zend_Db_Expr(
                    sprintf('(%s + %s)',
                        $adapter->getIfNullSql('oi.amount_refunded', 0),
                        $adapter->getIfNullSql('oi.tax_refunded', 0)
                    )
                ),
                'row_invoiced_incl_tax'           => new \Zend_Db_Expr(
                    sprintf('(%s + %s)',
                        $adapter->getIfNullSql('oi.row_invoiced', 0),
                        $adapter->getIfNullSql('oi.tax_invoiced', 0)
                    )
                ),
                'total_cost_amount'           => new \Zend_Db_Expr(
                    sprintf('(%s * %s)',
                        $adapter->getIfNullSql('oi.qty_ordered', 0),
                        $adapter->getIfNullSql('oi.base_cost', 0)
                    )
                ),
                'total_revenue_amount_excl_tax'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL((%s - %s - %s),0) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0)
                    )
                ),
                'total_profit_amount'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL(((%s - %s - %s - %s - %s) - (%s * %s)),0) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0),
                        $adapter->getIfNullSql('oi.qty_ordered', 0),
                        $adapter->getIfNullSql('oi.base_cost', 0)
                    )
                ),
                'total_margin'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL(ROUND((((%s - %s - %s -  %s - %s) - (%s * %s))/(%s - %s - %s -  %s - %s))*100), 100) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0),
                        $adapter->getIfNullSql('oi.qty_ordered', 0),
                        $adapter->getIfNullSql('oi.base_cost', 0),
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0)
                    )
                ),
            );
            
            if($hide_fields) {
                foreach($hide_fields as $field){
                    if(isset($columns[$field])){
                        unset($columns[$field]);
                    }
                }
            }

            $selectOrderItem = $adapter->select();

            $selectOrderItem->from($this->getTable('sales/order_item'), array("*"))
                ->where('parent_item_id IS NULL');

            
            $columns['store_id']       = new \Zend_Db_Expr($this->_storeManger->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId());
           
            $this->getSelect()->columns($columns)
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = main_table.entity_id', array());

            if($aggregationField) {
                $this->getSelect()->group($aggregationField);
            }
            
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }
}
