<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\Conversion;

use Magento\Framework\DataObject;
use Aheadworks\AdvancedReports\Model\ResourceModel\Conversion as ResourceConversion;
use Aheadworks\AdvancedReports\Model\ResourceModel\AbstractPeriodBasedCollection;

/**
 * Class Collection
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\Conversion
 */
class Collection extends AbstractPeriodBasedCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(DataObject::class, ResourceConversion::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()], []);

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
    protected function getOrderStatuses()
    {
        $orderStatuses = parent::getOrderStatuses();
        $orderStatuses[] = ResourceConversion::VIEWED_STATUS;
        return $orderStatuses;
    }
}
