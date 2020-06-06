<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderPlaceAfter
 *
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \MW\Onestepcheckout\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $_sender;

    /**
     * OrderPlaceAfter constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $sender
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \MW\Onestepcheckout\Helper\Data $helper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $sender,
        \Magento\Payment\Helper\Data $paymentHelper,
        \MW\Onestepcheckout\Helper\Data $helper
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_transportBuilder = $transportBuilder;
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->_paymentHelper = $paymentHelper;
        $this->_sender = $sender;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $comment = $this->_checkoutSession->getData('osc_comment', true);
        if ($comment) {
            $order->setMwCustomercommentInfo($comment);
            $order->setCustomerNote($comment);
            $order->addStatusHistoryComment($comment);
        }
        $isSubscriber =  $this->_checkoutSession->getData('osc_newsletter', true);
        if ($isSubscriber) {
            if ($order->getShippingAddress()) {
                $sendEmail = $order->getShippingAddress()->getEmail();
            } elseif ($order->getBillingAddress()) {
                $sendEmail = $order->getBillingAddress()->getEmail();
            } else {
                $sendEmail = '';
            }
            if ($sendEmail) {
                $this->_helper->addSubscriber($sendEmail);
            }
        }

        $oscDeliveryDate = $this->_checkoutSession->getData('osc_delivery_date', true);
        if ($oscDeliveryDate) {
            $order->setMwDeliverydateDate($oscDeliveryDate);
        }

        $oscDeliveryTime = $this->_checkoutSession->getData('osc_delivery_time', true);
        if ($oscDeliveryTime) {
            $order->setMwDeliverydateTime($oscDeliveryTime);
        }

        $oscSecurityCode = $this->_checkoutSession->getData('osc_security_code', true);
        if ($oscSecurityCode) {
            $order->setMwDeliverydateSecuritycode($oscSecurityCode);
        }
        $this->_checkoutSession->setOnestepcheckoutGiftwrapAmount(null);
        $this->_checkoutSession->setOnestepcheckoutBaseGiftwrapAmount(null);
        $this->_checkoutSession->setOnestepcheckoutGiftwrap(null);
    }
}
