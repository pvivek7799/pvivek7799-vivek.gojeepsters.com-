<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Cron;

use Aheadworks\AdvancedReports\Model\DatesGroupingManagement;

/**
 * Class UpdatePeriodTables
 *
 * @package Aheadworks\AdvancedReports\Cron
 */
class UpdatePeriodTables
{
    /**
     * @var DatesGroupingManagement
     */
    private $datesGroupingManagement;

    /**
     * @param DatesGroupingManagement $datesGroupingManagement
     */
    public function __construct(
        DatesGroupingManagement $datesGroupingManagement
    ) {
        $this->datesGroupingManagement = $datesGroupingManagement;
    }

    /**
     * Update dates grouping tables
     *
     * @return $this
     */
    public function execute()
    {
        $this->datesGroupingManagement->updateTables();
        return $this;
    }
}
