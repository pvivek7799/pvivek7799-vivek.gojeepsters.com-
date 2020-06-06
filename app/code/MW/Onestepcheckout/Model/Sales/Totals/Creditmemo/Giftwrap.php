<?php
/**
 * Mage-World
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Model\Sales\Totals\Creditmemo;

class Giftwrap extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Collect credit memo subtotal
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $store = $creditmemo->getStore();
        $order = $creditmemo->getOrder();

        $giftwrapAmount     = $order->getGiftwrapAmount();
        $baseGiftwrapAmount = $order->getBaseGiftwrapAmount();
        $totalCreditmemoQty = 0;
        $totalOrderedQty = $order->getTotalQtyOrdered();
        $totalIsVirtualPro = 0;

        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getIsVirtual()) {
                $totalIsVirtualPro += $item->getQtyOrdered();
            }
        }

        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItem() || $item->getOrderItem()->getIsVirtual()) {
                continue;
            }
            $totalCreditmemoQty += $item->getQty();
        }

        $giftwrapCreditmemoAmount = ($giftwrapAmount*$totalCreditmemoQty)/($totalOrderedQty-$totalIsVirtualPro);
        if ($giftwrapCreditmemoAmount) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $giftwrapCreditmemoAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $giftwrapCreditmemoAmount);
            $creditmemo->setGiftwrapAmount($giftwrapCreditmemoAmount);
            $creditmemo->setBaseGiftwrapAmount($giftwrapCreditmemoAmount);
        }
        return $this;
    }
}
