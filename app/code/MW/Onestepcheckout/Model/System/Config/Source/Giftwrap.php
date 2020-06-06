<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Model\System\Config\Source;

/**
 * Class Giftwrap
 * @package MW\Onestepcheckout\Model\System\Config\Source
 */
class Giftwrap implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            0 => __('Per Order'),
            1 => __('Per Item'),
        ];
    }
}
