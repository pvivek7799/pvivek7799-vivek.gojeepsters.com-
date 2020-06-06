<?php
namespace Lof\LayeredNavigation\Block\Product;
class Custom extends \Magento\Framework\View\Element\Template
{
	protected $_categoryFactory;
	protected $_productCollectionFactory;
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory)
	{
		$this->_categoryFactory = $categoryFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		parent::__construct($context);
	}

	protected function _prepareLayout()
{
    parent::_prepareLayout();
    


    if ($this->getNews()) {
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'lof.layerednavigation.pager'
        )->setAvailableLimit(array(9=>9,15=>15,30=>30))->setShowPerPage(true)->setCollection(
            $this->getNews()
        );
        $this->setChild('pager', $pager);
        $this->getNews()->load();
    }
    return $this;
}
public function getPagerHtml()
{
    return $this->getChildHtml('pager');
}
public function getNews()
    {
      //get values of current page
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
		
    //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 9;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$currentCategoryId = $objectManager->create('Magento\Catalog\Model\Layer\Resolver')->get()->getCurrentCategory()->getId();
		//$categoryData = $objectmanager->get('\Magento\Catalog\Model\CategoryFactory')->create()->load($currentCategoryId);
		$categoryId = $currentCategoryId;
		$category = $this->_categoryFactory->create()->load($categoryId);
		$collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addCategoryFilter($category);
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		//$collection->setOrder('price','ASC');
		$listOrder='';
		if($this->getRequest()->getParam('product_list_order')&& !($this->getRequest()->getParam('product_list_dir'))){
			$listOrder = $this->getRequest()->getParam('product_list_order');
			$collection->setOrder($listOrder,'ASC');
		}elseif(!$this->getRequest()->getParam('product_list_order')){
			$collection->setOrder('price','ASC');
		}else{
			$listOrder = $this->getRequest()->getParam('product_list_order');
			$listOrderDir = $this->getRequest()->getParam('product_list_dir');
			$collection->setOrder($listOrder,$listOrderDir);
		}
		
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }
}