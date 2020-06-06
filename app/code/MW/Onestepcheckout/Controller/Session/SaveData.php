<?php

/**
 * Mage-World
 *
 * @category    Mage-World
 * @package     MW
 * @author      Mage-world Developer
 *
 * @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Controller\Session;

/**
 * Class SaveData
 * @package MW\Onestepcheckout\Controller\Session
 */
class SaveData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * SaveCustomCheckoutData constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     */
    public function execute()
    {
        $dataObject = $this->dataObjectFactory->create([
            'data' => $this->jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
        $checkoutSession = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);
        $checkoutSession->setData('osc_comment', $dataObject->getData('osc_comment'));
        $checkoutSession->setData('osc_newsletter', $dataObject->getData('osc_newsletter'));

        if (!$this->scopeConfig->getValue('mw_ddate/group_general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $checkoutSession->setData('osc_delivery_date', $dataObject->getData('osc_delivery_date'));
            $checkoutSession->setData('osc_security_code', $dataObject->getData('osc_security_code'));
            $checkoutSession->setData('osc_delivery_time', $dataObject->getData('osc_delivery_time'));
        }
    }
}
