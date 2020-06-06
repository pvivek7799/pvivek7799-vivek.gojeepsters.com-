<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Controller\Adminhtml\Statistics;

use Magento\Backend\App\Action\Context;
use Aheadworks\AdvancedReports\Model\Indexer\Statistics\Processor as StatisticsProcessor;

/**
 * Class Schedule
 *
 * @package Aheadworks\AdvancedReports\Controller\Adminhtml\Statistics
 */
class Schedule extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_AdvancedReports::reports_statistics';

    /**
     * @var StatisticsProcessor
     */
    private $statisticsProcessor;

    /**
     * @param Context $context
     * @param StatisticsProcessor $statisticsProcessor
     */
    public function __construct(
        Context $context,
        StatisticsProcessor $statisticsProcessor
    ) {
        parent::__construct($context);
        $this->statisticsProcessor = $statisticsProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->statisticsProcessor->markIndexerAsInvalid();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
}
