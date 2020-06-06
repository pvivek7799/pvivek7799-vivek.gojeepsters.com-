<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model;

/**
 * Login model
 */
class Login extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Login tome frame
     */
    const TIME_FRAME = 60;

    const XML_PATH_KEEP_GUEST_CART = 'mfloginascustomer/general/keep_guest_cart';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magefan_login_as_customer';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'loginascustomer_login';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_random;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param null|\Magento\Checkout\Model\Session $checkoutSession
     * @param null|\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Math\Random $random,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        $checkoutSession = null,
        $scopeConfig = null
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_dateTime = $dateTime;
        $this->_random = $random;
        $this->cart = $cart;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_checkoutSession = $checkoutSession ?: $objectManager->get(
            \Magento\Checkout\Model\Session::class
        );
        $this->scopeConfig = $scopeConfig ?: $objectManager->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\LoginAsCustomer\Model\ResourceModel\Login::class);
    }

    /**
     * Retrieve not used admin login
     * @param  string $secret
     * @return self
     */
    public function loadNotUsed($secret)
    {
        return $this->getCollection()
            ->addFieldToFilter('secret', $secret)
            ->addFieldToFilter('used', 0)
            ->addFieldToFilter('created_at', ['gt' => $this->getDateTimePoint()])
            ->setPageSize(1)
            ->getFirstItem();
    }

    /**
     * Delete not used credentials
     * @return void
     */
    public function deleteNotUsed()
    {
        $resource = $this->getResource();
        $resource->getConnection()->delete(
            $resource->getTable('magefan_login_as_customer'),
            [
                'created_at < ?' => $this->getDateTimePoint(),
                'used = ?' => 0,
            ]
        );
    }

    /**
     * Retrieve login datetime point
     * @return [type] [description]
     */
    protected function getDateTimePoint()
    {
        return date('Y-m-d H:i:s', $this->_dateTime->gmtTimestamp() - self::TIME_FRAME);
    }

    /**
     * Retrieve customer
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = $this->_customerFactory->create()
                ->load($this->getCustomerId());
        }
        return $this->_customer;
    }

    /**
     * Login Customer
     * @return false || \Magento\Customer\Model\Customer
     */
    public function authenticateCustomer()
    {
        if ($this->_customerSession->getId()) {
            /* Logout if logged in */
            $this->_customerSession->logout();
        } else {

            $quote = $this->cart->getQuote();

            $keepItems = $this->scopeConfig->getValue(
                self::XML_PATH_KEEP_GUEST_CART,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if (!$keepItems) {
                /* Remove items from guest cart */
                foreach ($quote->getAllVisibleItems() as $item) {
                    $this->cart->removeItem($item->getId());
                }
            }
            $this->cart->save();
        }

        $customer = $this->getCustomer();

        if (!$customer->getId()) {
            throw new \Exception(__("Customer are no longer exist."), 1);
        }

        if ($this->_customerSession->loginById($customer->getId())) {
            $this->_customerSession->regenerateId();
            $this->_customerSession->setLoggedAsCustomerAdmindId(
                $this->getAdminId()
            );
        } else {
            throw new \Exception(__("Cannot login customer."), 1);
        }

        /* Load Customer Quote */
        $this->_checkoutSession->loadCustomerQuote();

        $quote = $this->_checkoutSession->getQuote();
        $quote->setCustomerIsGuest(0);
        $quote->save();

        $this->setUsed(1)->save();

        return $customer;
    }

    /**
     * Generate new login credentials
     * @param  int $adminId
     * @return $this
     */
    public function generate($adminId)
    {
        return $this->setData([
            'customer_id' => $this->getCustomerId(),
            'admin_id' => $adminId,
            'secret' => $this->_random->getRandomString(64),
            'used' => 0,
            'created_at' => $this->_dateTime->gmtTimestamp(),
        ])->save();
    }
}
