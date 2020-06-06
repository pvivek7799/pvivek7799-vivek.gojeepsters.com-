<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Controller\Customer;

/**
 * Class Logout
 * @package MW\Onestepcheckout\Controller\Customer
 */
class Logout extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \MW\Onestepcheckout\Api\CustomerServiceInterface
     */
    protected $customerService;

    /**
     * Logout constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MW\Onestepcheckout\Api\CustomerServiceInterface $customerService
    ) {
        parent::__construct($context);
        $this->customerService = $customerService;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->customerService->logout();
    }
}
