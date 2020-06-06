<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\Range;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\Range
 */
class Applier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $range = $filterPool->getFilter('range')->getValue();
        if (is_array($range)) {
            $collection->addRangeFilter($range);
        }
    }
}
