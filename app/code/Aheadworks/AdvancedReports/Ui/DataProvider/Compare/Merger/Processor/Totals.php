<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor;

/**
 * Class Totals
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor
 */
class Totals implements MergerInterface
{
    /**
     * {@inheritdoc}
     */
    public function merge($rows, $compareRows, $dataSourceData)
    {
        $rows = $this->mergeArray($rows, $compareRows);

        return $rows;
    }

    /**
     * Merge data from two arrays
     *
     * @param array $rows
     * @param array $compareRows
     * @return array
     */
    private function mergeArray($rows, $compareRows)
    {
        foreach ($compareRows as $compareRowIndex => $compareRowValue) {
            foreach ($compareRowValue as $index => $value) {
                $rows[$compareRowIndex]['c_' . $index] = $value;
            }
        }

        return $rows;
    }
}
