<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\Indexer\Statistics;

/**
 * Class SalesOverview
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\Indexer\Statistics
 */
class SalesOverview extends AbstractResource
{
    /**
     * @var string
     */
    protected $period;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_arep_sales_overview', 'id');
    }

    /**
     * {@inheritdoc}
     */
    protected function process()
    {
        $columns = $this->getColumns();
        $columns['payment_method'] = 'order_payment.method';

        $select = $this->getConnection()->select()
            ->from(['main_table' => $this->getTable('sales_order')], [])
            ->join(
                ['order_payment' => $this->getTable('sales_order_payment')],
                'order_payment.parent_id = main_table.entity_id',
                []
            )
            ->columns($columns)
            ->group($this->getGroupByFields(['order_payment.method']));
        $select = $this->addFilterByCreatedAt($select, 'main_table');

        $this->safeInsertFromSelect($select, $this->getIdxTable(), array_keys($columns));
    }

    /**
     * Retrieve base columns for table
     *
     * @return array
     */
    protected function getColumns()
    {
        $this->period = $this->getPeriod('main_table.created_at');
        $columns = [
            'period' => $this->period,
            'store_id' => 'main_table.store_id',
            'order_status' => 'main_table.status',
            'customer_group_id' => 'main_table.customer_group_id',
            'orders_count' => 'COUNT(main_table.entity_id)',
            'order_items_count' => 'SUM(COALESCE(main_table.total_qty_ordered, 0))',
            'subtotal' => 'SUM(COALESCE(main_table.base_subtotal, 0.0))',
            'tax' => 'SUM(COALESCE(main_table.base_tax_amount, 0.0))',
            'shipping' => 'SUM(COALESCE(main_table.base_shipping_amount, 0.0))',
            'discount' => 'ABS(SUM(COALESCE(main_table.base_discount_amount, 0.0)))',
            'other_discount' => $this->getOtherDiscountsExpression(),
            'total' => 'SUM(COALESCE(main_table.base_subtotal, 0.0) 
                        + COALESCE(main_table.base_discount_amount, 0.0) 
                        + COALESCE(main_table.base_tax_amount, 0.0) 
                        + COALESCE(main_table.base_shipping_amount, 0.0))',
            'invoiced' => 'SUM(COALESCE(main_table.base_total_invoiced, 0.0))',
            'refunded' => 'SUM(COALESCE(main_table.base_total_refunded, 0.0))',
            'to_global_rate' => 'main_table.base_to_global_rate'
        ];

        return $columns;
    }

    /**
     * Retrieve base group by for table
     *
     * @param array $additionalFields
     * @return array
     */
    protected function getGroupByFields($additionalFields)
    {
        return array_merge(
            [
                'main_table.status',
                $this->period,
                'main_table.store_id',
                'main_table.base_to_global_rate',
                'main_table.customer_group_id'
            ],
            $additionalFields
        );
    }
}
