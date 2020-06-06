<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Toolbar;

use Aheadworks\AdvancedReports\Ui\Component\OptionsContainer;

/**
 * Class CustomerGroup
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Toolbar
 */
class CustomerGroup extends OptionsContainer
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $customerGroupFilter = $this->context->getDataProvider()->getDefaultFilterPool()->getFilter('customer_group');
        $config = $this->getData('config');

        $config['currentValue'] = $customerGroupFilter->getValue();

        $this->setData('config', $config);
    }
}
