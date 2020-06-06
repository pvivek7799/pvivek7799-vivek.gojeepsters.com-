<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter;

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class CustomerGroup
 *
 * @package Aheadworks\AdvancedReports\Model\Filter
 */
class CustomerGroup implements FilterInterface
{
    /**
     * @var string
     */
    const SESSION_KEY = 'aw_rep_customer_group_key';

    /**
     * @var string
     */
    const REQUEST_PARAM = 'customer_group_id';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var string
     */
    private $customerGroup;

    /**
     * @param RequestInterface $request
     * @param SessionManagerInterface $session
     */
    public function __construct(
        RequestInterface $request,
        SessionManagerInterface $session
    ) {
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if ($this->customerGroup == null) {
            $customerGroupId = $this->request->getParam(self::REQUEST_PARAM);
            if ($customerGroupId != null) {
                $this->customerGroup = $customerGroupId;
            }
            if ($this->customerGroup != null) {
                $this->session->setData(self::SESSION_KEY, $this->customerGroup);
                return $this->customerGroup;
            }
            $keyFromSession = $this->session->getData(self::SESSION_KEY);
            if ($keyFromSession != null) {
                $this->customerGroup = $keyFromSession;
                return $this->customerGroup;
            }
            $this->customerGroup = $this->getDefaultValue();
        }
        return $this->customerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return GroupManagement::CUST_GROUP_ALL;
    }
}
