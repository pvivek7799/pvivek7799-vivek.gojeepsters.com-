<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;

class CustomerGroup extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param HttpContext $httpContext
     * @param Context $context
     * @param Session $customerSession
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        HttpContext $httpContext,
        Context $context,
        Session $customerSession,
        GroupManagementInterface $groupManagement
    ) {
        $this->httpContext     = $httpContext;
        $this->customerSession = $customerSession;
        $this->groupManagement = $groupManagement;
        parent::__construct($context);
    }

    /**
     * Get current customer group ID
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCustomerGroupId()
    {
        $groupId = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
        if ($groupId !== null) {
            return $groupId;
        }

        $groupId = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($groupId !== null) {
            return $groupId;
        }

        if ($this->customerSession->isLoggedIn()) {
            $groupId = $this->customerSession->getCustomer()->getGroupId();
        }
        if ($groupId !== null) {
            return $groupId;
        }

        return $this->groupManagement->getNotLoggedInGroup()->getId();
    }

    /**
     * Get Customer Group ID for ALL group
     *
     * @return int
     */
    public function getAllCustomersGroupId()
    {
        return GroupInterface::CUST_GROUP_ALL;
    }
}