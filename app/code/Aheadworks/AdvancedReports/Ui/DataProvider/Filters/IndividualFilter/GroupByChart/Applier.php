<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\GroupByChart;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\GroupByChart
 */
class Applier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $groupBy = $filterPool->getFilter('group_by')->getValue();

        $collection->addGroupByFilterForChart($groupBy);
    }
}
