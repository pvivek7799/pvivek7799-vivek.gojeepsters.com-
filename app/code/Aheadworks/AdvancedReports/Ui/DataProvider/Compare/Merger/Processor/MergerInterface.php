<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor;

/**
 * Interface MergerInterface
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor
 */
interface MergerInterface
{
    /**
     * Merge data
     *
     * @param array $rows
     * @param array $compareRows
     * @param array $dataSourceData
     * @return array
     */
    public function merge($rows, $compareRows, $dataSourceData);
}
