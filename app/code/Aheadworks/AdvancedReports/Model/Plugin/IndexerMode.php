<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Plugin;

use Aheadworks\AdvancedReports\Model\Indexer\Statistics\Processor as AdvancedReportsIndexer;
use Magento\Framework\Mview\View\StateInterface;

/**
 * Class IndexerMode
 *
 * @package Aheadworks\AdvancedReports\Model\Plugin
 */
class IndexerMode
{
    /**
     * Disable UPDATE ON SAVE mode
     *
     * @param \Magento\Indexer\Model\Mview\View\State\Interceptor $mode
     * @return \Magento\Indexer\Model\Mview\View\State\Interceptor
     */
    public function afterSetMode(
        \Magento\Indexer\Model\Mview\View\State\Interceptor $mode
    ) {
        if ($mode->getViewId() == AdvancedReportsIndexer::INDEXER_ID
            && StateInterface::MODE_DISABLED == $mode->getMode()
        ) {
            $mode->setMode(StateInterface::MODE_ENABLED);
        }
        return $mode;
    }
}
