<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\ProductConversion;

use Magento\Framework\DataObject;
use Aheadworks\AdvancedReports\Model\ResourceModel\Conversion as ResourceConversion;
use Aheadworks\AdvancedReports\Model\ResourceModel\ProductConversion as ResourceProductConversion;

/**
 * Class Collection
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\ProductConversion
 */
class Collection extends \Aheadworks\AdvancedReports\Model\ResourceModel\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(DataObject::class, ResourceProductConversion::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()], [])
            ->group('product_id');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->columns($this->getColumns());
        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns($addRate = false)
    {
        $ordersCount = 'SUM(main_table.orders_count)';
        $viewsCount = 'SUM(main_table.views_count)';
        return [
            'period' => 'period',
            'product_id' => 'main_table.product_id',
            'product_name' => 'main_table.product_name',
            'views_count' => 'COALESCE(SUM(main_table.views_count), 0)',
            'orders_count' => 'COALESCE(SUM(main_table.orders_count), 0)',
            'conversion_rate' => 'IF(' . $viewsCount . ' > 0, ' .
                'IF(' . $viewsCount . ' < ' . $ordersCount . ', 100, ' .
                $ordersCount . ' / ' . $viewsCount . ' * 100), 0)',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function addExcludeRefundedItemsFilter()
    {
        $this->getSelect()
            ->where('(is_refunded = 0 OR is_refunded IS NULL)');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrderStatuses()
    {
        $orderStatuses = parent::getOrderStatuses();
        $orderStatuses[] = ResourceConversion::VIEWED_STATUS;
        return $orderStatuses;
    }
}
