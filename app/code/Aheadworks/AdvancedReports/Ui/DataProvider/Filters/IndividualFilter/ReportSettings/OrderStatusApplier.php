<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\ReportSettings;

use Aheadworks\AdvancedReports\Model\Config;
use Aheadworks\AdvancedReports\Model\Filter\FilterInterface;
use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class OrderStatusApplier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\ReportSettings
 */
class OrderStatusApplier implements FilterApplierInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $filter = $filterPool->getFilter('report_settings');
        $collection->setOrderStatuses($this->getOrderStatuses($filter));
    }

    /**
     * Retrieve order statuses
     *
     * @param FilterInterface $filter
     * @return array
     */
    private function getOrderStatuses($filter)
    {
        $reportSettingOrderStatuses = $filter->getReportSettingParam('report_settings_order_status');
        if (!empty($reportSettingOrderStatuses)) {
            return $reportSettingOrderStatuses;
        }

        return explode(',', $this->config->getOrderStatus());
    }
}
