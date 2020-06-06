<?php
namespace Magecomp\Extrafee\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Catalog\Model\ProductCategoryList;

class ExtrafeeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magecomp\Extrafee\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magecomp\Extrafee\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
		ProductCategoryList $productCategory,

        \Psr\Log\LoggerInterface $logger

    )
    {
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
		$this->productCategory = $productCategory;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $ExtrafeeConfig = [];
        $enabled = $this->dataHelper->isModuleEnabled();
        $minimumOrderAmount = $this->dataHelper->getMinimumOrderAmount();
        $ExtrafeeConfig['fee_label'] = $this->dataHelper->getFeeLabel();
        $quote = $this->checkoutSession->getQuote();
        $subtotal = $quote->getSubtotal();
		$items = $quote->getAllItems();
		$fee =array();
		foreach($items as $item) {
			 $product_id = $item->getProductId();
			 ///$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
			//$Ids = $product->getCategoryIds();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
			$id = $product ->getTireTax();
			$categoryIds = $this->productCategory->getCategoryIds($product_id);
			if (in_array(5, $categoryIds) ||($id == 187)){
				$fee[] = $item->getQty() *2;				
			}
			
		  }
		 
        $ExtrafeeConfig['custom_fee_amount'] = array_sum($fee) ;//$name; //$this->dataHelper->getExtrafee();
        $ExtrafeeConfig['show_hide_Extrafee_block'] = ($enabled && ($minimumOrderAmount <= $subtotal) && $quote->getFee()) ? true : false;
        $ExtrafeeConfig['show_hide_Extrafee_shipblock'] = ($enabled && ($minimumOrderAmount <= $subtotal)) ? true : false;
        return $ExtrafeeConfig;
    }
}
