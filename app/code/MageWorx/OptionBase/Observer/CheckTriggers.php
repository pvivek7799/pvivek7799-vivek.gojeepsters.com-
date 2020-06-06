<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class CheckTriggers implements ObserverInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @param BaseHelper $baseHelper
     * @param MessageManager $messageManager
     */
    public function __construct(
        BaseHelper $baseHelper,
        MessageManager $messageManager
    ) {
        $this->baseHelper = $baseHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->baseHelper->checkTriggers()) {
            $this->messageManager->addWarningMessage($this->baseHelper->getMissingTriggersMessage());
        }
    }
}
