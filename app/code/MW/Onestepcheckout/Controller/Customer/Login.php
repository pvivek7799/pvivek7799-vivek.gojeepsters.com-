<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Controller\Customer;

/**
 * Class Login
 * @package MW\Onestepcheckout\Controller\Customer
 */
class Login extends \Magento\Framework\App\Action\Action
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
     * Login constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \MW\Onestepcheckout\Api\ResultServiceInterface $resultService
     * @param \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MW\Onestepcheckout\Api\ResultServiceInterface $resultService,
        \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService
    ) {
        parent::__construct($context);
        $this->resultService = $resultService;
        $this->customerService = $customerService;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $accountData = null;
        $httpBadRequestCode = 400;

        $resultRaw = $this->resultService->createRaw();
        try {
            $paramsData = $this->_getParamDataObject();
            $username = $paramsData->getData('username');
            $password = $paramsData->getData('password');
            $accountData['username'] = $username;
            $accountData['password'] = $password;
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$accountData || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = $this->customerService->login($username, $password);
        $resultJson = $this->resultService->createJson();
        return $resultJson->setData($response);
    }

    /**
     * @return mixed
     */
    protected function _getParamDataObject()
    {
        return $this->dataObjectFactory->create([
            'data' => $this->jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
    }
}
