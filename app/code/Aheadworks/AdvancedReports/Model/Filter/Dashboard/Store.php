<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter\Dashboard;

/**
 * Class Store
 *
 * @package Aheadworks\AdvancedReports\Model\Filter\Dashboard
 */
class Store extends \Aheadworks\AdvancedReports\Model\Filter\Store
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $reportScope = $this->request->getParam('report_scope');
        if (!empty($reportScope)) {
            return $reportScope;
        }

        return $this->getDefaultValue();
    }
}
