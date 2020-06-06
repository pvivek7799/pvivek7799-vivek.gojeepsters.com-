<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Finder\Controller\Adminhtml\Finder;

use Magento\Framework\App\ResponseInterface;

class Delete extends \Amasty\Finder\Controller\Adminhtml\Finder
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $finderId = $this->getRequest()->getParam('id');
        if ($finderId) {
            try {
                $finder = $this->finderRepository->getById($finderId);
                $this->finderRepository->delete($finder);
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('amasty_finder/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logInterface->critical($e);
                $this->_redirect('amasty_finder/*/edit', ['id' => (int) $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('amasty_finder/*/');
    }
}
