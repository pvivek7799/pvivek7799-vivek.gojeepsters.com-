<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Source\ProductAttributes;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Operators
 *
 * @package Aheadworks\AdvancedReports\Model\Source\ProductAttributes
 */
class Operators implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return []
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'AND', 'label' => __('AND')],
            ['value' => 'OR', 'label' => __('OR')]
        ];
    }
}
