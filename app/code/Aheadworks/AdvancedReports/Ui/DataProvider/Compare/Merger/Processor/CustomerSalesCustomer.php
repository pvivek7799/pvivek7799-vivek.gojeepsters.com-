<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor;

/**
 * Class CustomerSalesCustomer
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor
 */
class CustomerSalesCustomer extends Field
{
    /**
     * {@inheritdoc}
     */
    protected function isEqualsValues($rowValue, $compareRowValue)
    {
        if (empty($rowValue['customer_id']) || empty($compareRowValue['customer_id'])) {
            return false;
        }
        if ((!empty($rowValue['customer_id']) && !empty($compareRowValue['customer_id'])
                && $rowValue['customer_id'] == $compareRowValue['customer_id'])
            || $rowValue['customer_email'] == $compareRowValue['customer_email']) {
            return true;
        }

        return false;
    }
}
