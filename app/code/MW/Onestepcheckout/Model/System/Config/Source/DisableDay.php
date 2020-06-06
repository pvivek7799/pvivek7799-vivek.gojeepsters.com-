<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Model\System\Config\Source;

/**
 * Class DisableDay
 * @package MW\Onestepcheckout\Model\System\Config\Source
 */
class DisableDay implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Monday'),
                'value' => 1,
            ],
            [
                'label' => __('Tuesday'),
                'value' => 2,
            ],
            [
                'label' => __('Wednesday'),
                'value' => 3,
            ],
            [
                'label' => __('Thursday'),
                'value' => 4,
            ],
            [
                'label' => __('Friday'),
                'value' => 5,
            ],
            [
                'label' => __('Saturday'),
                'value' => 6,
            ],
            [
                'label' => __('Sunday'),
                'value' => 0,
            ]
        ];
    }
}
