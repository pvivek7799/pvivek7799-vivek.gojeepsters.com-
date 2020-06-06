<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\CustomerGroupId;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;
use Magento\Customer\Model\GroupManagement;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\CustomerGroupId
 */
class Applier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $customerGroupId = $filterPool->getFilter('customer_group')->getValue();
        if ($customerGroupId != GroupManagement::CUST_GROUP_ALL) {
            $collection->addCustomerGroupFilter($customerGroupId);
        }
    }
}
