<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel;

/**
 * Class CustomerSales
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel
 */
class CustomerSales extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_arep_customer_sales', 'id');
    }
}
