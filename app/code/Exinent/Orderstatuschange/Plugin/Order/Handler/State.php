<?php

namespace Exinent\Orderstatuschange\Plugin\Order\Handler;

use Magento\Sales\Model\Order;

/**
 * Class State
 */
class State
{
    /**
     * Check order status and adjust the status before save for check money orders
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\State $subject
     * @param $result
     * @param Order $order
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheck(\Magento\Sales\Model\ResourceModel\Order\Handler\State $subject, $result,  Order $order)
    {

        if($order->getEntityType() == 'order' && $order->getPayment()->getMethod()){
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus('processing');

        }
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/orderstatuschange.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('OrderData:'.print_r($order->debug(),true));

        return $result;
    }
}