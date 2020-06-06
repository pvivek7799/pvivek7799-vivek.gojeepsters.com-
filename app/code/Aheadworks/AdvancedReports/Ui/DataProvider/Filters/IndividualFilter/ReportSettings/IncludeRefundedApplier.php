<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\ReportSettings;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class IncludeRefundedApplier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\ReportSettings
 */
class IncludeRefundedApplier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $reportSettingsFilter = $filterPool->getFilter('report_settings');

        $includeRefunded = $reportSettingsFilter->getReportSettingParam('include_refunded_items');
        if ((int)$includeRefunded != 1) {
            $collection->addExcludeRefundedItemsFilter();
        }
    }
}
