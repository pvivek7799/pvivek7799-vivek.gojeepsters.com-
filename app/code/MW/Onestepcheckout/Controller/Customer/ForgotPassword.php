<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Controller\Customer;

/**
 * Class ForgotPassword
 * @package MW\Onestepcheckout\Controller\Customer
 */
class ForgotPassword extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \MW\Onestepcheckout\Api\ResultServiceInterface
     */
    protected $resultService;

    /**
     * @var \MW\Onestepcheckout\Api\CustomerServiceInterface
     */
    protected $customerService;

    /**
     * ForgotPassword constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \MW\Onestepcheckout\Api\ResultServiceInterface $resultService
     * @param \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MW\Onestepcheckout\Api\ResultServiceInterface $resultService,
        \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultService = $resultService;
        $this->customerService = $customerService;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this;
        }
        $resultJson = $this->resultService->createJson();
        $paramsData = $this->dataObjectFactory->create([
            'data' => $this->jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
        $email = $paramsData->getData('email');
        $result = $this->customerService->forgotPassword($email);
        return $resultJson->setData($result);
    }
}
