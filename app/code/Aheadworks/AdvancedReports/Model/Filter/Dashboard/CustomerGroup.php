<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter\Dashboard;

use Magento\Customer\Model\GroupManagement;

/**
 * Class CustomerGroup
 *
 * @package Aheadworks\AdvancedReports\Model\Filter\Dashboard
 */
class CustomerGroup extends \Aheadworks\AdvancedReports\Model\Filter\CustomerGroup
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $customerGroupId = $this->request->getParam(self::REQUEST_PARAM);
        if (null !== $customerGroupId && '' !== $customerGroupId) {
            return $customerGroupId;
        }

        return $this->getDefaultValue();
    }
}
