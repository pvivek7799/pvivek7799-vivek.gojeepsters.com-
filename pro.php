<?php
use Magento\Framework\App\Bootstrap;
 
require __DIR__ . '/app/bootstrap.php';
 
$params = $_SERVER;
 
$bootstrap = Bootstrap::create(BP, $params);
 
$objectManager = $bootstrap->getObjectManager();
  
 $appState = $objectManager->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');
 $categoryId = 312; // YOUR CATEGORY ID
$categoryFactory = $objectManager->create('Magento\Catalog\Model\CategoryFactory')->create();
$productFac = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
//$categoryId = 'yourcategoryid';
$category = $categoryFactory->load($categoryId);
$collection = $productFac->addAttributeToSelect('*');
$collection->addCategoriesFilter(['in' => 312]);
$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
//$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);               
        $i=1;                     
 foreach ($collection as $product) {
				echo '<pre>';print_r( $product->getData());
        }
die('end');
?>