<?php

/**
 * Mage-World
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Model\Sales\Totals\Invoice;

class Giftwrap extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect Weee amounts for the invoice
     * @param  \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $store = $invoice->getStore();
        $order = $invoice->getOrder();

        $giftwrapAmount     = $order->getGiftwrapAmount();
        $baseGiftwrapAmount = $order->getBaseGiftwrapAmount();
        $totalInvoiceQty = 0;
        $totalOrderedQty = $order->getTotalQtyOrdered();
        $totalIsVirtualPro = 0;

        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getIsVirtual()) {
                $totalIsVirtualPro += $item->getQtyOrdered();
            }
        }

        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        foreach ($invoice->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItem() || $item->getOrderItem()->getIsVirtual()) {
                continue;
            }
            $totalInvoiceQty += $item->getQty();
        }

        $giftwrapInvoiceAmount = ($giftwrapAmount*$totalInvoiceQty)/($totalOrderedQty-$totalIsVirtualPro);
        if ($giftwrapInvoiceAmount) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $giftwrapInvoiceAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $giftwrapInvoiceAmount);
            $invoice->setGiftwrapAmount($giftwrapInvoiceAmount);
            $invoice->setBaseGiftwrapAmount($giftwrapInvoiceAmount);
        }
        return $this;
    }
}
