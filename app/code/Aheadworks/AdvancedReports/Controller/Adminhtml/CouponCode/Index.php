<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Controller\Adminhtml\CouponCode;

use Aheadworks\AdvancedReports\Ui\Component\Listing\Breadcrumbs;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Aheadworks\AdvancedReports\Controller\Adminhtml\CouponCode
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_AdvancedReports::reports_couponcode';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $title = __('Sales by Coupon Code');
        $this->_session->setData(Breadcrumbs::BREADCRUMBS_CONTROLLER_TITLE, $title);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_AdvancedReports::reports_couponcode');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
