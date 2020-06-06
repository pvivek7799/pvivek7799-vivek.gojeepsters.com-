<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets;

/**
 * Class NumericWidget
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets
 */
class NumericWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function getMetricOptions()
    {
        return array_merge(
            $this->getSalesReportMetrics(),
            $this->getTrafficAndConversionsReportMetrics(),
            $this->getAbandonedCartsReportMetrics()
        );
    }
}
