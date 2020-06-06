<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Model\CollectionUpdater\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Model\Product\Option\AbstractUpdater;
use MageWorx\OptionVisibility\Model\OptionCustomerGroup as CustomerGroupModel;
use MageWorx\OptionVisibility\Helper\Data as VisibilityHelper;
use MageWorx\OptionBase\Helper\CustomerVisibility as CustomerHelper;

class CustomerGroup extends AbstractUpdater
{
    const ALIAS_TABLE_CUSTOMER_GROUP = 'option_customer_group';

    /**
     * @var VisibilityHelper
     */
    protected $visibilityHelper;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var bool
     */
    protected $isVisibilityFilterRequired;

    /**
     * @var bool
     */
    protected $isVisibilityCustomerGroup;

    /**
     * CustomerGroup constructor.
     *
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param VisibilityHelper $visibilityHelper
     * @param CustomerHelper $customerHelper
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        SystemHelper $systemHelper,
        VisibilityHelper $visibilityHelper,
        CustomerHelper $customerHelper
    ) {
        $this->visibilityHelper           = $visibilityHelper;
        $this->customerHelper             = $customerHelper;
        $this->isVisibilityFilterRequired = $this->customerHelper->isVisibilityFilterRequired();
        $this->isVisibilityCustomerGroup  = $this->visibilityHelper->isVisibilityCustomerGroupEnabled();

        parent::__construct($resource, $helper, $systemHelper);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $conditions
     * @return array
     */
    public function getFromConditions(array $conditions)
    {
        if ($this->isVisibilityFilterRequired && $this->isVisibilityCustomerGroup) {
            $table = $this->getTableForVisibilityFilter($conditions);
        } else {
            $table = $this->getTable($conditions);
        }
        $alias = $this->getTableAlias();

        return [$alias => $table];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $entityType
     * @return string
     */
    public function getTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName(CustomerGroupModel::OPTIONTEMPLATES_TABLE_NAME);
        }

        return $this->resource->getTableName(CustomerGroupModel::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getOnConditionsAsString()
    {
        if ($this->isVisibilityFilterRequired && $this->isVisibilityCustomerGroup) {
            $customerGroupId = $this->customerHelper->getCurrentCustomerGroupId();
            $conditions      = 'main_table.' . CustomerGroupModel::COLUMN_NAME_OPTION_ID
                . ' = ' . $this->getTableAlias() . '.' . CustomerGroupModel::FIELD_OPTION_ID_ALIAS
                . " AND " . $this->getTableAlias() . "." . CustomerGroupModel::COLUMN_NAME_GROUP_ID
                . " = '" . $customerGroupId . "'";
            return $conditions;
        }

        return 'main_table.' . CustomerGroupModel::COLUMN_NAME_OPTION_ID . ' = '
            . $this->getTableAlias() . '.' . CustomerGroupModel::FIELD_OPTION_ID_ALIAS;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getColumns()
    {
        if ($this->isVisibilityFilterRequired && $this->isVisibilityCustomerGroup) {
            $customerGroupExpr = $this->resource->getConnection()->getCheckSql(
                'main_table.is_all_groups = 1',
                '1',
                'IF(' . self::ALIAS_TABLE_CUSTOMER_GROUP . '.'
                . CustomerGroupModel::COLUMN_NAME_GROUP_ID . ' IS NULL,0,1)'
            );

            return [
                'visibility_by_customer_group_id' => $customerGroupExpr
            ];
        }

        return [
            CustomerGroupModel::KEY_CUSTOMER_GROUP => $this->getTableAlias(
                ) . '.' . CustomerGroupModel::KEY_CUSTOMER_GROUP
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTableAlias()
    {
        return $this->resource->getConnection()->getTableName(self::ALIAS_TABLE_CUSTOMER_GROUP);
    }

    /**
     * @param array $conditions
     * @return \Zend_Db_Expr
     */
    protected function getTable($conditions)
    {
        $entityType = $conditions['entity_type'];
        $tableName  = $this->getTableName($entityType);

        $this->resource->getConnection()->query('SET SESSION group_concat_max_len = 100000;');

        $selectExpr = "SELECT " . CustomerGroupModel::COLUMN_NAME_OPTION_ID . " as "
            . CustomerGroupModel::FIELD_OPTION_ID_ALIAS . ","
            . " CONCAT('[',"
            . " GROUP_CONCAT(CONCAT("
            . "'{\"customer_group_id\"',':\"',IFNULL(customer_group_id,''),'\"}'"
            . ")),"
            . "']')"
            . " AS customer_group FROM " . $tableName;

        if (!empty($conditions['option_id']) || !empty($conditions['value_id'])) {
            $optionIds = $this->helper->findOptionIdByConditions($conditions);

            if ($optionIds) {
                $selectExpr .= " WHERE option_id IN(" . implode(',', $optionIds) . ")";
            }
        }
        $selectExpr .= " GROUP BY option_id";

        return new \Zend_Db_Expr('(' . $selectExpr . ')');
    }

    /**
     * @param array $conditions
     * @return \Zend_Db_Expr
     */
    protected function getTableForVisibilityFilter($conditions)
    {
        $entityType = $conditions['entity_type'];
        $tableName  = $this->getTableName($entityType);

        $selectExpr = "SELECT " . CustomerGroupModel::COLUMN_NAME_OPTION_ID . " as "
            . CustomerGroupModel::FIELD_OPTION_ID_ALIAS . ", "
            . CustomerGroupModel::COLUMN_NAME_GROUP_ID
            . " FROM " . $tableName;

        return new \Zend_Db_Expr('(' . $selectExpr . ')');
    }
}