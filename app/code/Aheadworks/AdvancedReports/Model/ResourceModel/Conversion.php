<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel;

/**
 * Class Conversion
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel
 */
class Conversion extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Status of view rows in index
     *
     * @var string
     */
    const VIEWED_STATUS = 'viewed';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_arep_conversion', 'id');
    }
}
