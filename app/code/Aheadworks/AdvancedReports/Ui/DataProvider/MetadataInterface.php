<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider;

/**
 * Interface MetadataInterface
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider
 */
interface MetadataInterface
{
    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Get default filter appliers
     *
     * @return array
     */
    public function getDefaultFilterAppliers();

    /**
     * Get individual filter appliers
     *
     * @return array
     */
    public function getIndividualFilterAppliers();

    /**
     * Get compare rows merger
     *
     * @return string
     */
    public function getCompareRowsMerger();

    /**
     * Get compare rows merger config
     *
     * @return array
     */
    public function getCompareRowsMergerConfig();

    /**
     * Get compare chart rows merger
     *
     * @return string
     */
    public function getCompareChartsMerger();

    /**
     * Get compare chart rows merger config
     *
     * @return array
     */
    public function getCompareChartsMergerConfig();

    /**
     * Get compare totals merger
     *
     * @return string
     */
    public function getCompareTotalsMerger();
}
