<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter;

/**
 * Class FilterPool
 *
 * @package Aheadworks\AdvancedReports\Model\Filter
 */
class FilterPool
{
    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @param array $filters
     */
    public function __construct(
        array $filters = []
    ) {
        $this->filters = $filters;
    }

    /**
     * Retrieve filter by filter name
     *
     * @param string $filterName
     * @return FilterInterface|null
     */
    public function getFilter($filterName)
    {
        if (isset($this->filters[$filterName])) {
            return $this->filters[$filterName];
        }

        return null;
    }
}
