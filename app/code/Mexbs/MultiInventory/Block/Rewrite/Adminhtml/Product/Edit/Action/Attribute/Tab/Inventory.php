<?php
namespace Mexbs\MultiInventory\Block\Rewrite\Adminhtml\Product\Edit\Action\Attribute\Tab;

class Inventory extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory{

    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Model\Source\Backorders $backorders,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        array $data = []
    ){
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $backorders,
            $stockConfiguration,
            $data
        );
    }

    public function getCurrentWebsiteId(){
        $storeId = $this->getStoreId();
        if(!$storeId){
            return $this->stockConfiguration->getDefaultScopeId();
        }else{
            return $this->storeManager->getStore($storeId)->getWebsiteId();
        }
    }

    public function getDefaultScopeId(){
        return $this->stockConfiguration->getDefaultScopeId();
    }
}