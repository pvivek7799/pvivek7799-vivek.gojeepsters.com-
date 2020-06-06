<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\CustomerSales;

use Aheadworks\AdvancedReports\Model\ResourceModel\CustomerSales\Range as RangeResource;

/**
 * Class Range
 * @package Aheadworks\AdvancedReports\Model\CustomerSales
 */
class Range extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(RangeResource::class);
    }
}
