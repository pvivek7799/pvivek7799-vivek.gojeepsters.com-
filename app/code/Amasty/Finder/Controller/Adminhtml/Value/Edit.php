<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Finder\Controller\Adminhtml\Value;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Amasty\Finder\Controller\Adminhtml\Value
{
    /**
     * Dispatch request
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_initModel();

        $newId = $this->getRequest()->getParam('id');
        $setterId = $this->model->newSetterId($newId);
        $model = $this->valueRepository->getValueModel();

        if ($setterId) {
            try {
                $model = $this->valueRepository->getById($setterId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('Record does not exist'));
                $this->_redirect('amasty_finder/finder/edit', ['id' => $this->model->getId()]);
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->setFinder($this->model);
        $this->coreRegistry->register('current_amasty_finder_value', $model);
        $this->_initAction();

        if ($model->getId()) {
            $title = __('Edit Product');
        } else {
            $title = __("Add new Product");
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);

        $this->_view->renderLayout();
    }
}
