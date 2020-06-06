<?php
namespace Magecomp\Extrafee\Model\Quote\Total;
use Magento\Catalog\Model\ProductCategoryList;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    protected $helperData;
	protected $_priceCurrency;

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;

    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator,
								\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
								ProductCategoryList $productCategory,

                                \Magecomp\Extrafee\Helper\Data $helperData)
    {
        $this->quoteValidator = $quoteValidator;
		$this->_priceCurrency = $priceCurrency;
		$this->productCategory = $productCategory;
        $this->helperData = $helperData;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $enabled = $this->helperData->isModuleEnabled();
        $minimumOrderAmount = $this->helperData->getMinimumOrderAmount();
        $subtotal = $total->getTotalAmount('subtotal');
      
	   if ($enabled && $minimumOrderAmount <= $subtotal) {
			$fee =array();
            $items = $quote->getAllItems();
			foreach($items as $item) {
			 $product_id = $item->getProductId();
			 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
			$id = $product ->getTireTax();
			 $categoryIds = $this->productCategory->getCategoryIds($product_id);
			if (in_array(5, $categoryIds) ||($id == 187)){
				$qty = $item->getQty();
				$fee[] = $qty *1; 
			}

		  }


			$fee = array_sum($fee);
			$total->setTotalAmount('fee', $fee);
            $total->setBaseTotalAmount('fee', $fee);
            $total->setFee($fee);
            $quote->setFee($fee);
            
			
			$productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
			$version = (float)$productMetadata->getVersion(); 
			
			if($version > 2.1)
			{
				//$total->setGrandTotal($total->getGrandTotal() + $fee);
			}
			else
			{
				$total->setGrandTotal($total->getGrandTotal() + $fee);
			}
			
		}
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {

        $enabled = $this->helperData->isModuleEnabled();
        $minimumOrderAmount = $this->helperData->getMinimumOrderAmount();
        $subtotal = $quote->getSubtotal();
        $fee = $quote->getFee();

        $result = [];
        if ($enabled && ($minimumOrderAmount <= $subtotal) && $fee) {
            $result = [
                'code' => 'fee',
                'title' => $this->helperData->getFeeLabel(),
                'value' => $fee
            ];
        }
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Extra Fee');
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);

    }
}
