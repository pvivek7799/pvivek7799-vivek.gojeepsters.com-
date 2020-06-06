<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Listing;

use Aheadworks\AdvancedReports\Ui\Component\OptionsContainer;

/**
 * Class GroupBy
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Listing
 */
class GroupBy extends OptionsContainer
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $groupByFilter = $this->context->getDataProvider()->getDefaultFilterPool()->getFilter('group_by');
        $config = $this->getData('config');

        $config['currentValue'] = $groupByFilter->getValue();

        $this->setData('config', $config);
    }
}
