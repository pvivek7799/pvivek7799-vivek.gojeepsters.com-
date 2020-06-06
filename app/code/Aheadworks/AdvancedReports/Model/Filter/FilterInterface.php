<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter;

/**
 * Interface FilterInterface
 *
 * @package Aheadworks\AdvancedReports\Model\Filter
 */
interface FilterInterface
{
    /**
     * Retrieve filter value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Retrieve default filter value
     *
     * @return mixed
     */
    public function getDefaultValue();
}
