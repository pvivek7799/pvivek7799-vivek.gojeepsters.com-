<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Log;

use Aheadworks\AdvancedReports\Model\ResourceModel\Log\ProductView as ProductViewLogResource;

/**
 * Class ProductView
 * @package Aheadworks\AdvancedReports\Model\Log
 */
class ProductView extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ProductViewLogResource::class);
    }
}
