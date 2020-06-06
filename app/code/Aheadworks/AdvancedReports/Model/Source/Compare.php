<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Compare
 *
 * @package Aheadworks\AdvancedReports\Model\Source
 */
class Compare implements OptionSourceInterface
{
    /**#@+
     * Constants defined for the source model
     */
    const TYPE_PREVIOUS_PERIOD  = 'previous_period';
    const TYPE_PREVIOUS_YEAR = 'previous_year';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_PREVIOUS_PERIOD, 'label' => __('Previous period')],
            ['value' => self::TYPE_PREVIOUS_YEAR, 'label' => __('Previous year')],
        ];
    }
}
