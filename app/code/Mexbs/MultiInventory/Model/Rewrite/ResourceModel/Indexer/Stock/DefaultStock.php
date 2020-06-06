<?php
namespace Mexbs\MultiInventory\Model\Rewrite\ResourceModel\Indexer\Stock;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

class DefaultStock extends \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
{
    private $queryProcessorComposite;
    protected $multiStockHelper;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mexbs\MultiInventory\Helper\Data $multiStockHelper,
        $connectionName = null
    ) {
        $this->multiStockHelper = $multiStockHelper;
        parent::__construct(
            $context,
            $tableStrategy,
            $eavConfig,
            $scopeConfig,
            $connectionName
        );
    }

    /**
     * @param AdapterInterface $connection
     * @param bool $isAggregate
     * @return mixed
     */
    protected function getStatusExpression(AdapterInterface $connection, $isAggregate = false)
    {
        $isInStockExpression = $isAggregate ? 'MAX(cisi.is_in_stock)' : 'cisi.is_in_stock';
        $defaultIsInStockExpression = $isAggregate ? 'MAX(cisid.is_in_stock)' : 'cisid.is_in_stock';

        if ($this->_isManageStock()) {
            $statusExpr = new \Zend_Db_Expr(
                "IF(cisi.use_default_values=1 AND (cisid.item_id IS NOT NULL),".
                sprintf("(IF(cisid.use_config_manage_stock = 0 AND cisid.manage_stock = 0,1,%s)),",$defaultIsInStockExpression).
                sprintf("(IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,1,%s)))",$isInStockExpression)
            );
        } else {
            $statusExpr = new \Zend_Db_Expr(
                "IF(cisi.use_default_values=1 AND (cisid.item_id IS NOT NULL),".
                sprintf("(IF(cisid.use_config_manage_stock = 0 AND cisid.manage_stock = 1,%s,1)),",$defaultIsInStockExpression).
                sprintf("(IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,%s,1)))",$isInStockExpression)
            );
        }
        return $statusExpr;
    }

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $defaultWebsiteId = $this->getStockConfiguration()->getDefaultScopeId();
        $allWebsiteIds = $this->multiStockHelper->getAllWebsiteIds();
        $allWebsiteIds[] = $defaultWebsiteId;

        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $connection = $this->getConnection();
        $qtyExpr = new \Zend_Db_Expr
        (
            "IF(cisi.use_default_values=1 AND (cisid.item_id IS NOT NULL),".
            "(IF(cisid.qty>0, cisid.qty, 0)),".
            "(IF(cisi.qty>0, cisi.qty, 0)))"
        );

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );
        $select->join(
            ['cis' => $this->getTable('cataloginventory_stock')],
            '',
            ['website_id', 'stock_id']
        )->joinInner(
                ['cisi' => $this->getTable('cataloginventory_stock_item')],
                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                []
        )->joinLeft(
            ['cisid' => $this->getTable('cataloginventory_stock_item')],
            'cisid.product_id = cisi.product_id',
            []
        )
        ->joinInner(
            ['mcpei' => $this->getTable('catalog_product_entity_int')],
            'e.' . $linkField . ' = mcpei.' . $linkField
            . ' AND mcpei.attribute_id = ' . $this->_getAttribute('status')->getId()
            . ' AND mcpei.value = ' . ProductStatus::STATUS_ENABLED,
            []
        )->columns(
            ['qty' => $qtyExpr]
        )->where(
            'cis.website_id IN (?)'.
            sprintf(" AND cisid.website_id = %s", $defaultWebsiteId),
            $allWebsiteIds
        )->where('e.type_id = ?', $this->getTypeId())
        ->group(['e.entity_id', 'cis.website_id', 'cis.stock_id']);

        $select->columns(['status' => $this->getStatusExpression($connection, true)]);
        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * @return QueryProcessorComposite
     */
    private function getQueryProcessorComposite()
    {
        if (null === $this->queryProcessorComposite) {
            $this->queryProcessorComposite = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorComposite');
        }
        return $this->queryProcessorComposite;
    }


    private function deleteOldRecords(array $ids)
    {
        if (count($ids) !== 0) {
            $this->getConnection()->delete($this->getMainTable(), ['product_id in (?)' => $ids]);
        }
    }

    /**
     * Update Stock status index by product ids
     *
     * @param array|int $entityIds
     * @return $this
     */
    protected function _updateIndex($entityIds)
    {
        $dbConnection = $this->getConnection();
        $updateSelect = $this->_getStockStatusSelect($entityIds, true);
        $updateSelect = $this->getQueryProcessorComposite()->processQuery($updateSelect, $entityIds, true);
        $updateQuery = $dbConnection->query($updateSelect);

        $index = 0;
        $rowsData = [];
        while ($invetnoryRow = $updateQuery->fetch(\PDO::FETCH_ASSOC)) {
            $index++;
            $rowsData[] = [
                'product_id' => (int)$invetnoryRow['entity_id'],
                'website_id' => (int)$invetnoryRow['website_id'],
                'stock_id' => (int)$invetnoryRow['stock_id'],
                'qty' => (double)$invetnoryRow['qty'],
                'stock_status' => (int)$invetnoryRow['status'],
            ];
            if ($index % 1000 == 0) {
                $this->_updateIndexTable($rowsData);
                $rowsData = [];
            }
        }

        $this->deleteOldRecords($entityIds);
        $this->_updateIndexTable($rowsData);

        return $this;
    }
}