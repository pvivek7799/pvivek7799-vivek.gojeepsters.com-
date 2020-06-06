<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\GroupBy;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\GroupBy
 */
class Applier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $periodFilter = $filterPool->getFilter('period');

        $groupBy = $filterPool->getFilter('group_by')->getValue();
        $periodFrom = $periodFilter->getPeriodFrom();
        $periodTo = $periodFilter->getPeriodTo();
        $compareFrom = $periodFilter->getCompareFrom();
        $compareTo = $periodFilter->getCompareTo();

        $collection->addGroupByFilter($groupBy, $periodFrom, $periodTo, $compareFrom, $compareTo);
    }
}
