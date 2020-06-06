<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Cron;

use Aheadworks\AdvancedReports\Model\Indexer\Statistics\Processor as StatisticsProcessor;

/**
 * Class Reindex
 *
 * @package Aheadworks\AdvancedReports\Cron
 */
class Reindex
{
    /**
     * @var StatisticsProcessor
     */
    private $statisticsProcessor;

    /**
     * @param StatisticsProcessor $statisticsProcessor
     */
    public function __construct(
        StatisticsProcessor $statisticsProcessor
    ) {
        $this->statisticsProcessor = $statisticsProcessor;
    }

    /**
     * Reindex all indexes
     *
     * @return $this
     */
    public function execute()
    {
        $this->statisticsProcessor->reindexAll();
        return $this;
    }
}
