<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Indexer\Statistics;

use Magento\Framework\Indexer\StateInterface;

/**
 * Class Processor
 *
 * @package Aheadworks\AdvancedReports\Model\Indexer\Statistics
 */
class Processor extends \Magento\Framework\Indexer\AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'aw_arep_statistics';

    /**
     * Is reindex scheduled
     *
     * @return bool
     */
    public function isReindexScheduled()
    {
        /** @var StateInterface $state */
        $state = $this->getIndexer()->getState();
        if ($state->getStatus() == StateInterface::STATUS_INVALID) {
            return true;
        }
        return false;
    }
}
