<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets;

/**
 * Class ChartWidget
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets
 */
class ChartWidget extends AbstractWidget
{
    /**
     * Retrieve metric options
     *
     * @return array
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
