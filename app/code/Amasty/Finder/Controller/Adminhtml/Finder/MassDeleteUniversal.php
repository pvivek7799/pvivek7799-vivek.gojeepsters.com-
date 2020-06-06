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

class MassDeleteUniversal extends \Amasty\Finder\Controller\Adminhtml\Finder
{
    use \Amasty\Finder\MyTrait\FinderController;

    public function execute()
    {
        $finder = $this->_initFinder();
        $finderId = $finder->getId();
        $ids = $this->getRequest()->getParam('universal_ids');
        if ($ids) {
            try {
                $this->universalRepository->deleteByIds($ids);
               
                $this->messageManager->addSuccess(__('You deleted the universal product(s).'));
                $this->_redirect(
                    'amasty_finder/finder/edit',
                    ['id' => $finderId, '_fragment' => 'amasty_finder_finder_edit_tabs_universal_section_content']
                );
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete the universal product(s) right now. Please review the log and try again.')
                );
                $this->logInterface->critical($e);
                $this->_redirect(
                    'amasty_finder/finder/edit',
                    ['id' => $finderId, '_fragment' => 'amasty_finder_finder_edit_tabs_universal_section_content']
                );
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a the universal product(s) to delete.'));
        $this->_redirect(
            'amasty_finder/finder/edit',
            ['id' => $finderId, '_fragment' => 'amasty_finder_finder_edit_tabs_universal_section_content']
        );
    }
}
