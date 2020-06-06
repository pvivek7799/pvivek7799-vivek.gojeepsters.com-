<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Controller\Customer;

use Magento\Customer\Api\Data\CustomerInterface;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Register
 * @package MW\Onestepcheckout\Controller\Customer
 */
class Register extends \Magento\Framework\App\Action\Action
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
     * @var \MW\Onestepcheckout\Api\CustomerServiceInterface
     */
    protected $customerService;

    /**
     * @var \MW\Onestepcheckout\Api\ResultServiceInterface
     */
    protected $resultService;

    /**
     * @var \MW\Onestepcheckout\Model\Data\Customer\RegisterInterfaceFactory
     */
    protected $registerRequestFactory;

    /**
     * Register constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \MW\Onestepcheckout\Api\ResultServiceInterface $resultService
     * @param \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService
     * @param \MW\Onestepcheckout\Model\Data\Customer\RegisterInterfaceFactory $registerRequestFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MW\Onestepcheckout\Api\ResultServiceInterface $resultService,
        \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService,
        \MW\Onestepcheckout\Model\Data\Customer\RegisterInterfaceFactory $registerRequestFactory
    ) {
        parent::__construct($context);
        $this->resultService = $resultService;
        $this->customerService = $customerService;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->jsonHelper = $jsonHelper;
        $this->registerRequestFactory = $registerRequestFactory;
    }
    /**
     * @return $this
     */
    public function execute()
    {
        $registerRequest = $this->registerRequestFactory->create();
        $resultJson = $this->resultService->createJson();
        $wantSubscribe = $this->getRequest()->getParam('is_subscribed', false);
        $wantSubscribe = ($wantSubscribe)?true:false;
        $paramsData = $this->_getParamDataObject();
        $registerRequest->setPassword($paramsData->getData('password'));
        $registerRequest->setConfirmation($paramsData->getData('password_confirmation'));
        $registerRequest->setFirstname($paramsData->getData('firstname'));
        $registerRequest->setLastname($paramsData->getData('lastname'));
        $registerRequest->setEmail($paramsData->getData('email'));
        $registerRequest->setWantSubscribe($wantSubscribe);
        $result = $this->customerService->register($registerRequest);
        return $resultJson->setData($result);
    }

    /**
     * @return mixed
     */
    protected function _getParamDataObject()
    {
        return $this->_dataObjectFactory->create([
            'data' => $this->_jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
    }
}
