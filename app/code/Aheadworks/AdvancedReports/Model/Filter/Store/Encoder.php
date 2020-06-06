<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter\Store;

/**
 * Class Encoder
 *
 * @package Aheadworks\AdvancedReports\Model\Filter\Store
 */
class Encoder
{
    /**
     * @var string
     */
    const DELIMITER = '_';

    /**
     * Encode scope values
     *
     * @param string $scope
     * @param int $value
     * @return string
     */
    public function encode($scope, $value)
    {
        return implode(self::DELIMITER, [$scope, $value]);
    }

    /**
     * Decode scope values
     *
     * @param string $params
     * @return array
     */
    public function decode($params)
    {
        return explode(self::DELIMITER, $params);
    }
}
