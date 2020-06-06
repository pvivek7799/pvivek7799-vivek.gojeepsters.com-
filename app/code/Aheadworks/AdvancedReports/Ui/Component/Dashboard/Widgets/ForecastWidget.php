<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets;

/**
 * Class ForecastWidget
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets
 */
class ForecastWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function getMetricOptions()
    {
        return $this->getForecastSalesReportMetrics();
    }
}
