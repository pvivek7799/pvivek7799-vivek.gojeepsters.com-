<?php

namespace MW\Onestepcheckout\Observer\Sales\Order;

class UpdateGiftwrapCreditmemo implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fullActionName = $this->request->getFullActionName();
        if ($fullActionName == 'sales_order_creditmemo_save') {
            $creditmemo = $observer->getCreditmemo();
            $order = $creditmemo->getOrder();
            $order->setGiftwrapAmountRefunded($order->getGiftwrapAmountRefunded() + $creditmemo->getGiftwrapAmount());
            $order->setBaseGiftwrapAmountRefunded(
                $order->getBaseGiftwrapAmountRefunded() + $creditmemo->getBaseGiftwrapAmount()
            );
            $order->save();
        }

        return $this;
    }
}
