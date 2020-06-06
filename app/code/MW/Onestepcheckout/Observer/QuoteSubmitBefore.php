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
 * Class FieldSet
 * @package MW\Onestepcheckout\Observer
 */
class QuoteSubmitBefore implements ObserverInterface
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $order->setGiftwrapAmount($quote->getOnestepcheckoutGiftwrapAmount())
            ->setBaseGiftwrapAmount($quote->getOnestepcheckoutBaseGiftwrapAmount());

        return $this;
    }
}
