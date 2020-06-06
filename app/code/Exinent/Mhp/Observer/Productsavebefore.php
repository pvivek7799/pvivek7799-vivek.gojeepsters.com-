<?php

    namespace Exinent\Mhp\Observer;

    use Magento\Framework\Event\ObserverInterface;

    class Productsavebefore implements ObserverInterface
    {

        public function execute(\Magento\Framework\Event\Observer $observer)
        {
            	$_product = $observer->getProduct();  // you will get product object
           	$_getMyAttr = $_product->getResource()->getAttribute('map');
		$attrValue = $_getMyAttr->getFrontend()->getValue($_product);
		$_product->setPrice($attrValue); 
		return $this;

        }
    }
?>
