<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters;

use Aheadworks\AdvancedReports\Model\Filter\FilterPool;

/**
 * Interface FilterApplierInterface
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters
 */
interface FilterApplierInterface
{
    /**
     * Apply default filter
     *
     * @param $collection
     * @param FilterPool $filterPool
     * @return void
     */
    public function apply($collection, $filterPool);
}
