<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\DatesGrouping;

use Aheadworks\AdvancedReports\Model\ResourceModel\DatesGrouping\AbstractResource;

/**
 * Class Day
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\DatesGrouping
 */
class Day extends AbstractResource
{
    /**
     * @var string
     */
    const KEY = 'day';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_arep_days', 'date');
    }

    /**
     * {@inheritdoc}
     */
    public function updateTable()
    {
        $maxDayDateStr = $this->getConnection()->fetchOne('SELECT MAX(date) FROM ' . $this->getMainTable());
        $fromDate = $this->getFromDate($maxDayDateStr);
        $toDate = $this->getToDate();

        $intervals = [];
        while ($fromDate < $toDate) {
            // If main table is empty
            if (!$maxDayDateStr) {
                $intervals[] = ['date' => $fromDate->format('Y-m-d')];
                $fromDate->modify('+1 day');
            } else {
                $fromDate->modify('+1 day');
                $intervals[] = ['date' => $fromDate->format('Y-m-d')];
            }
        }
        $this->addPeriodToTable($this->getMainTable(), $intervals);
    }

    /**
     * Retrieve min date from table
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinDate()
    {
        return $this->getConnection()->fetchOne('SELECT MIN(date) FROM ' . $this->getMainTable());
    }

    /**
     * {@inheritdoc}
     */
    public function getPeriods($from, $intervalsCount)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable() . ".date >= (?)", $from->format('Y-m-d'))
            ->order($this->getMainTable() . ".date ASC")
            ->limit($intervalsCount)
        ;
        $data = $this->getConnection()->fetchAll($select);

        return $data;
    }
}
