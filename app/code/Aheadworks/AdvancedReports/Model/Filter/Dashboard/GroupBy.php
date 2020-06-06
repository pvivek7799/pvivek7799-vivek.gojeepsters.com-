<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter\Dashboard;

use Aheadworks\AdvancedReports\Model\Source\Groupby as GroupbySource;

/**
 * Class GroupBy
 *
 * @package Aheadworks\AdvancedReports\Model\Filter\Dashboard
 */
class GroupBy extends \Aheadworks\AdvancedReports\Model\Filter\GroupBy
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return GroupbySource::TYPE_DAY;
    }
}
