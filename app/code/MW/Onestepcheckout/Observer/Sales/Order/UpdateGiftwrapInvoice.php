<?php

namespace MW\Onestepcheckout\Observer\Sales\Order;

class UpdateGiftwrapInvoice implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getInvoice();
        $order = $invoice->getOrder();
        $order->setGiftwrapAmountInvoiced($order->getGiftwrapAmountInvoiced() + $invoice->getGiftwrapAmount());
        $order->setBaseGiftwrapAmountInvoiced(
            $order->getBaseGiftwrapAmountInvoiced() + $invoice->getBaseGiftwrapAmount()
        );
        $order->save();

        return $this;
    }
}
