<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\Period;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\Period
 */
class Applier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $periodFilter = $filterPool->getFilter('period');

        $periodFrom = $periodFilter->getPeriodFrom();
        $periodTo = $periodFilter->getPeriodTo();
        $compareFrom = $periodFilter->getCompareFrom();
        $compareTo = $periodFilter->getCompareTo();

        $collection->addPeriodFilter($periodFrom, $periodTo, $compareFrom, $compareTo);
    }
}
