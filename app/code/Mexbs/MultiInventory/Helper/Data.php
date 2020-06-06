<?php
namespace Mexbs\MultiInventory\Helper;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;

class Data
{
    protected $stockConfiguration;
    protected $storeManager;
    protected $_resource;
    protected $_websiteFactory;
    protected $_allWebsiteIds;
    protected $stockItemCriteriaFactory;
    protected $stockItemRepository;
    protected $stockRegistryProvider;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_resource = $resource;
        $this->stockConfiguration = $stockConfiguration;
        $this->storeManager = $storeManager;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
    }

    public function getAllWebsiteIds(){
        if(!$this->_allWebsiteIds){
            $this->_allWebsiteIds = [];
            $websiteCollection = $this->_websiteFactory->create()->getResourceCollection();
            foreach($websiteCollection as $website){
                $this->_allWebsiteIds[] = $website->getId();
            }
        }
        return $this->_allWebsiteIds;
    }


    public function getActiveStockItemIds($websiteIdInUse = null){
        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
        if($websiteIdInUse === null){
            $websiteIdInUse = $defaultScopeId;
        }
        /**
         * @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection
         */
        $connection = $this->_resource->getConnection();

        if($websiteIdInUse == $defaultScopeId){
            $select = $connection->select()
                ->from(
                    ['stock_item' => $this->_resource->getTableName('cataloginventory_stock_item')],
                    ['item_id']
                )->where(
                    $connection->quoteInto(
                        '(website_id = ?)',
                        $defaultScopeId)
                );
        }else{
            $select = $connection->select()
                ->from(
                    ['default_stock_item' => $this->_resource->getTableName('cataloginventory_stock_item')],
                    ['item_id' => new \Zend_Db_Expr('IF(website_stock_item.item_id IS NULL, default_stock_item.item_id,'.
                     '(IF(website_stock_item.use_default_values = "0", website_stock_item.item_id, default_stock_item.item_id)))')]
                )->joinLeft(
                    ['website_stock_item' => $this->_resource->getTableName('cataloginventory_stock_item')],
                    sprintf("default_stock_item.product_id=website_stock_item.product_id".
                    " AND website_stock_item.website_id = %s",
                        $connection->quote($websiteIdInUse)
                    ),
                    []
                )->where(
                sprintf(
                    "default_stock_item.website_id = %s",
                    $connection->quote($defaultScopeId)
                )
                );
        }

        return $connection->fetchCol($select);
    }

    public function getActiveStockStatusItems($websiteIdInUse = null){
        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
        if($websiteIdInUse === null){
            $websiteIdInUse = $defaultScopeId;
        }
        /**
         * @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection
         */
        $connection = $this->_resource->getConnection();

        if($websiteIdInUse == $defaultScopeId){
            $select = $connection->select()
                ->from(
                    ['stock_status' => $this->_resource->getTableName('cataloginventory_stock_item')],
                    ['product_id','website_id']
                )->where(
                    $connection->quoteInto(
                        '(website_id = ?)',
                        $defaultScopeId)
                );
        }else{
            $select = $connection->select()
                ->from(
                    ['stock_status' => $this->_resource->getTableName('cataloginventory_stock_status')],
                    [
                        'product_id' => 'default_stock_item.product_id',
                        'website_id' => new \Zend_Db_Expr('IF(website_stock_item.item_id IS NULL, default_stock_item.website_id,'.
                        '(IF(website_stock_item.use_default_values = "0", website_stock_item.website_id, default_stock_item.website_id)))')
                    ]
                )
                ->joinLeft(
                    ['default_stock_item' => $this->_resource->getTableName('cataloginventory_stock_item')],
                    'stock_status.product_id=default_stock_item.product_id AND '.
                    'stock_status.website_id=default_stock_item.website_id',
                    []
                )->joinLeft(
                    ['website_stock_item' => $this->_resource->getTableName('cataloginventory_stock_item')],
                    sprintf(
                        "default_stock_item.product_id=website_stock_item.product_id".
                        " AND website_stock_item.website_id = %s",
                        $connection->quote($websiteIdInUse)
                    ),
                    []
                )->where(
                    sprintf(
                        "default_stock_item.website_id = %s",
                        $connection->quote($defaultScopeId)
                    )
                );
        }
        return $connection->fetchAll($select);
    }

    public function getActiveStockStatusItemsStr($websiteIdInUse){
        $activeStockStatusItems = $this->getActiveStockStatusItems($websiteIdInUse);
        $activeStockStatusItemsStr = "";
        if(!empty($activeStockStatusItems)){
            $activeStockStatusItemsStr = "";
            $index = 1;
            foreach($activeStockStatusItems as $activeStockStatusItem){
                $activeStockStatusItemsStr .= "(".$activeStockStatusItem['product_id'].",".$activeStockStatusItem['website_id'].")";
                if($index < count($activeStockStatusItems)){
                    $activeStockStatusItemsStr .= ",";
                }
                $index++;
            }
        }
        return $activeStockStatusItemsStr;

    }

    private function getStockRegistryProvider()
    {
        if ($this->stockRegistryProvider === null) {
            $this->stockRegistryProvider = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface');
        }
        return $this->stockRegistryProvider;
    }

    public function assignStatusToProductForWebsite(\Magento\Catalog\Model\Product $product, $status = null, $websiteId = null)
    {
        if ($status === null) {
            if($websiteId === null){
                $websiteId = $this->stockConfiguration->getDefaultScopeId();
            }
            $stockStatus = $this->getStockRegistryProvider()->getStockStatus($product->getId(), $websiteId);
            $status = $stockStatus->getStockStatus();
        }
        $product->setIsSalable($status);
    }
}