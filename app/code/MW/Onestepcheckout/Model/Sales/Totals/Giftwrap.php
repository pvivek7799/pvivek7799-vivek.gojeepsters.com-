<?php
namespace MW\Onestepcheckout\Model\Sales\Totals;

class Giftwrap extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Giftwrap constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Collect totals process.
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        parent::collect($quote, $shippingAssignment, $total);
        if ($address->getAddressType() == 'shipping') {
            $giftwrapAmount = $this->_checkoutSession->getData('onestepcheckout_giftwrap_amount');
            if ($giftwrapAmount) {
                $quote->setOnestepcheckoutGiftwrapAmount($giftwrapAmount);
                $quote->setOnestepcheckoutBaseGiftwrapAmount($giftwrapAmount);
                $total->setTotalAmount('osc_giftwrap', $giftwrapAmount);
                $total->setBaseTotalAmount('osc_giftwrap', $giftwrapAmount);

                $total->setGiftwrapAmount($giftwrapAmount);
                $total->setBaseGiftwrapAmount($giftwrapAmount);

                $total->setGrandTotal($total->getGrandTotal());
                $total->setBaseGrandTotal($total->getBaseGrandTotal());
            }
        }
        return $this;
    }
}
