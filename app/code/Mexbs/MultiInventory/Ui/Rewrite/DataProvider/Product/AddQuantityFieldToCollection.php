<?php
namespace Mexbs\MultiInventory\Ui\Rewrite\DataProvider\Product;
use Magento\Framework\Data\Collection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class AddQuantityFieldToCollection extends \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;
    protected $resource;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->storeManager = $storeManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->resource = $resource;
    }
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
        $defaultStockId = $this->stockRegistry->getStock($defaultScopeId)->getStockId();

        $collection->getSelect()->joinLeft(
            ['default_qty' => $this->resource->getTableName('cataloginventory_stock_item')],
            '(default_qty.product_id=e.entity_id) AND (default_qty.stock_id='.$defaultStockId.')',
            []
        );

        $currentStoreId = ($collection->getStoreId() ? $collection->getStoreId() : \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $currentWebsiteId = $this->storeManager->getStore($currentStoreId)->getWebsiteId();
        $scopeStockId = $this->stockRegistry->getStock($currentWebsiteId)->getStockId();

        if($scopeStockId != $defaultStockId){
            $collection->getSelect()->joinLeft(
                ['website_qty' => $this->resource->getTableName('cataloginventory_stock_item')],
                '(website_qty.product_id=e.entity_id) AND (website_qty.stock_id='.$scopeStockId.')',
                []
            );
            $collection->getSelect()->columns(
                ['qty' => 'IF(website_qty.item_id IS NULL, default_qty.qty,'.
                '(IF(website_qty.use_default_values = "0", website_qty.qty, default_qty.qty)))']
            );
        }else{
            $collection->getSelect()->columns(
                ['qty' => 'default_qty.qty']
            );
        }
    }
}