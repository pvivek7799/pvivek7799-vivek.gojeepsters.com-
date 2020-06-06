<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Mapper;

/**
 * Class Base
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Mapper
 */
class Base
{
    /**
     * Map row
     *
     * @param array $rowValues
     * @param array $numberColumns
     * @param bool $isCompare
     * @return array
     * @throws \Exception
     */
    public function mapRow($rowValues, $numberColumns, $isCompare)
    {
        $mappedRow = [];
        foreach ($rowValues as $index => $value) {
            $indexPrefix = $isCompare ? 'c_' : '';
            $mappedRow[$indexPrefix . $index] = $value;

            // add empty fields
            $indexPrefix = $isCompare ? '' : 'c_';
            $default = in_array($index, $numberColumns) ? 0 : $rowValues[$index];
            $mappedRow[$indexPrefix . $index] = $default;
        }

        return $mappedRow;
    }
}
