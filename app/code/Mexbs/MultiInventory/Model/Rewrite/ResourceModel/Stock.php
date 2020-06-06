<?php
namespace Mexbs\MultiInventory\Model\Rewrite\ResourceModel;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stock extends \Magento\CatalogInventory\Model\ResourceModel\Stock
{
    protected $multiStockHelper;

    public function __construct(
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        StockConfigurationInterface $stockConfiguration,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->multiStockHelper = $multiStockHelper;
        parent::__construct(
            $context,
            $scopeConfig,
            $dateTime,
            $stockConfiguration,
            $storeManager,
            $connectionName
        );
    }

    /**
     * Set items out of stock basing on their quantities and config settings
     *
     * @param string|int $websiteId
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function updateSetOutOfStock($websiteIdToUpdate = null)
    {
        if(!$websiteIdToUpdate){
            $websiteIdToUpdate = $this->stockConfiguration->getDefaultScopeId();
        }

        $this->_initConfig();
        $dbConnection = $this->getConnection();
        $bindValues = ['is_in_stock' => 0, 'stock_status_changed_auto' => 1];

        $updateSelect = $dbConnection->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $isConfigManageStock = $this->_isConfigManageStock;
        $isConfigBackorders = $this->_isConfigBackorders;
        $configMinQty = $this->_configMinQty;

        $updateWhere = sprintf(
            'website_id = %1$d' .
            ' AND ' . ' is_in_stock = 1' .
            ' AND ' . ' ((use_config_manage_stock = 1 AND 1 = %2$d) OR ' . ' (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ' . ' ((use_config_backorders = 1 AND %3$d = %4$d) OR ' . ' (use_config_backorders = 0 AND backorders = %3$d))' .
            ' AND ' . ' ((use_config_min_qty = 1 AND qty <= %5$d) OR ' . ' (use_config_min_qty = 0 AND qty <= min_qty))' .
            ' AND ' . ' product_id IN (%6$s)',
            $websiteIdToUpdate,
            $isConfigManageStock,
            \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO,
            $isConfigBackorders,
            $configMinQty,
            $updateSelect->assemble()
        );

        $dbConnection->update($this->getTable('cataloginventory_stock_item'), $bindValues, $updateWhere);
    }

    /**
     * Set items in stock basing on their quantities and config settings
     *
     * @param int|string $websiteId
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function updateSetInStock($websiteIdToUpdate)
    {
        if(!$websiteIdToUpdate){
            $websiteIdToUpdate = $this->stockConfiguration->getDefaultScopeId();
        }

        $this->_initConfig();
        $dbConnection = $this->getConnection();
        $bindValues = ['is_in_stock' => 1];

        $configTypeIds = $this->_configTypeIds;
        $updateSelect = $dbConnection->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $configTypeIds);

        $isConfigManageStock = $this->_isConfigManageStock;
        $configMinQty = $this->_configMinQty;

        $updateWhere = sprintf(
            'website_id = %1$d' .
            ' AND ' . ' is_in_stock = 0' .
            ' AND ' . ' stock_status_changed_auto = 1' .
            ' AND ' . ' ((use_config_manage_stock = 1 AND 1 = %2$d) OR ' . ' (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ' . ' ((use_config_min_qty = 1 AND qty > %3$d) OR ' . ' (use_config_min_qty = 0 AND qty > min_qty))' .
            ' AND ' . ' product_id IN (%4$s)',
            $websiteIdToUpdate,
            $isConfigManageStock,
            $configMinQty,
            $updateSelect->assemble()
        );

        $dbConnection->update($this->getTable('cataloginventory_stock_item'), $bindValues, $updateWhere);
    }

    /**
     * Update items low stock date basing on their quantities and config settings
     *
     * @param int|string $websiteId
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function updateLowStockDate($websiteIdToUpdate)
    {
        if(!$websiteIdToUpdate){
            $websiteIdToUpdate = $this->stockConfiguration->getDefaultScopeId();
        }
        $this->_initConfig();

        $dbConnection = $this->getConnection();

        $configNotifyStockQty = $this->_configNotifyStockQty;
        $updateCondition = $dbConnection->quoteInto(
                '(use_config_notify_stock_qty = 1 AND ' . ' qty < ?)',
                $configNotifyStockQty
            ) . ' OR (use_config_notify_stock_qty = 0 AND ' . ' qty < notify_stock_qty)';
        $currentDbTime = $dbConnection->quoteInto('?', $this->dateTime->gmtDate());
        $updateConditionalDate = $dbConnection->getCheckSql($updateCondition, $currentDbTime, 'NULL');

        $bindValue = ['low_stock_date' => new \Zend_Db_Expr($updateConditionalDate)];

        $configTypeIds = $this->_configTypeIds;
        $updateSelect = $dbConnection->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $configTypeIds);

        $isConfigManageStock = $this->_isConfigManageStock;
        $updateWhere = sprintf(
            'website_id = %1$d' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR ' . ' (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND product_id IN (%3$s)',
            $websiteIdToUpdate,
            $isConfigManageStock,
            $updateSelect->assemble()
        );

        $dbConnection->update($this->getTable('cataloginventory_stock_item'), $bindValue, $updateWhere);
    }

    /**
     * Lock Stock Item records
     *
     * @param int[] $productIds
     * @param int $websiteId
     * @return array
     */
    public function lockProductsStock(array $productIdsToLock, $websiteId)
    {
        if (empty($productIdsToLock)) {
            return [];
        }
        $itemTable = $this->getTable('cataloginventory_stock_item');

        $activeItemIds = $this->multiStockHelper->getActiveStockItemIds($websiteId);
        $defaultWebsiteId = $this->stockConfiguration->getDefaultScopeId();

        $select = $this->getConnection()->select()->from(['si' => $itemTable])
            ->where('website_id IN (?)', [$websiteId, $defaultWebsiteId])
            ->where('product_id IN(?)', $productIdsToLock);

        if(!empty($activeItemIds)){
            $select = $select->where('item_id IN(?)', $activeItemIds);
        }
        $select = $select->forUpdate(true);

        $productTable = $this->getTable('catalog_product_entity');
        $selectProducts = $this->getConnection()->select()->from(['p' => $productTable], [])
            ->where('entity_id IN (?)', $productIdsToLock)
            ->columns(
                [
                    'product_id' => 'entity_id',
                    'type_id' => 'type_id',
                ]
            );
        $items = [];

        foreach ($this->getConnection()->query($select)->fetchAll() as $si) {
            $items[$si['product_id']] = $si;
        }
        foreach ($this->getConnection()->fetchAll($selectProducts) as $p) {
            $items[$p['product_id']]['type_id'] = $p['type_id'];
        }

        return $items;
    }

    public function correctItemsQty(array $items, $websiteId, $operator)
    {
        if (empty($items)) {
            return $this;
        }

        $dbConnection = $this->getConnection();
        $conditions = [];
        foreach ($items as $productId => $qty) {
            $case = $dbConnection->quoteInto('?', $productId);
            $result = $dbConnection->quoteInto("qty{$operator}?", $qty);
            $conditions[$case] = $result;
        }

        $bindValue = $dbConnection->getCaseSql('product_id', $conditions, 'qty');

        $defaultWebsiteId = $this->stockConfiguration->getDefaultScopeId();
        $where = ['product_id IN (?)' => array_keys($items), 'website_id IN (?)' => [$defaultWebsiteId, $websiteId]];

        $activeItemIds = $this->multiStockHelper->getActiveStockItemIds($websiteId);
        if(!empty($activeItemIds)){
            $where['item_id IN(?)'] = $activeItemIds;
        }

        $dbConnection->beginTransaction();
        $dbConnection->update($this->getTable('cataloginventory_stock_item'), ['qty' => $bindValue], $where);
        $dbConnection->commit();
    }
}