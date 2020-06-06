<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
 
namespace MW\Onestepcheckout\Observer\Payment\Cart;

use Magento\Framework\Event\ObserverInterface;

class AddCustomItem implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
 
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
         $this->_checkoutSession = $checkoutSession;
    }
 
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $quote = $this->_checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();
        $paypalMehodList = [
            'payflowpro',
            'payflow_link',
            'payflow_advanced',
            'braintree_paypal',
            'paypal_express_bml',
            'payflow_express_bml',
            'payflow_express',
            'paypal_express'
        ];
        if ($quote->getOnestepcheckoutGiftwrapAmount() &&
            ($paymentMethod == null || in_array($paymentMethod, $paypalMehodList))) {
            if (method_exists($cart, 'addCustomItem')) {
                $name = __("Gift Wrap");
                $cart->addCustomItem($name, 1, $quote->getOnestepcheckoutGiftwrapAmount(), 'osc_giftwrap');
            }
        }
        return $this;
    }
}
