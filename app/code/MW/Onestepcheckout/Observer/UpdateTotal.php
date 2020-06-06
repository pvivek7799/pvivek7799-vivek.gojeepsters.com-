<?php

/**
 * Mage-World
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderPlaceAfter
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class UpdateTotal implements ObserverInterface
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
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        $quote = $observer->getQuote();
//        $total = $observer->getTotal();
//        $giftwrapAmount = $this->_checkoutSession->getData('onestepcheckout_giftwrap_amount');
//        if($giftwrapAmount){
//            $quote->setOnestepcheckoutGiftwrapAmount($giftwrapAmount);
//            $quote->setOnestepcheckoutBaseGiftwrapAmount($giftwrapAmount);
//            $total->setTotalAmount('osc_giftwrap', $giftwrapAmount);
//            $total->setBaseTotalAmount('osc_giftwrap', $giftwrapAmount);
//
//            $total->setGiftwrapAmount($giftwrapAmount);
//            $total->setBaseGiftwrapAmount($giftwrapAmount);
//
//            $total->setGrandTotal($total->getGrandTotal() + $giftwrapAmount);
//            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $giftwrapAmount);
//        }

        return $this;
    }
}
