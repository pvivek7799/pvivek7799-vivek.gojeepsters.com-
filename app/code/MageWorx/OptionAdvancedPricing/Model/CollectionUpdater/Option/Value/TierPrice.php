<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model\CollectionUpdater\Option\Value;

use MageWorx\OptionBase\Model\Product\Option\AbstractUpdater;
use MageWorx\OptionAdvancedPricing\Model\TierPrice as TierPriceModel;

class TierPrice extends AbstractUpdater
{
    /**
     * {@inheritdoc}
     */
    public function getFromConditions(array $conditions)
    {
        $alias = $this->getTableAlias();
        $table = $this->getTable($conditions);
        return [$alias => $table];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName(TierPriceModel::OPTIONTEMPLATES_TABLE_NAME);
        }
        return $this->resource->getTableName(TierPriceModel::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        return 'main_table.' . TierPriceModel::COLUMN_OPTION_TYPE_ID . ' = '
            . $this->getTableAlias() . '.' . TierPriceModel::FIELD_OPTION_TYPE_ID_ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return [TierPriceModel::KEY_TIER_PRICE => $this->getTableAlias() . '.' . TierPriceModel::KEY_TIER_PRICE];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return $this->resource->getConnection()->getTableName('option_value_tier_price');
    }

    /**
     * Get table for from conditions
     *
     * @param array $conditions
     * @return \Zend_Db_Expr
     */
    private function getTable($conditions)
    {
        $entityType = $conditions['entity_type'];
        $tableName  = $this->getTableName($entityType);

        $this->resource->getConnection()->query('SET SESSION group_concat_max_len = 100000;');

        $selectExpr = "SELECT " . TierPriceModel::COLUMN_OPTION_TYPE_ID . " as "
            . TierPriceModel::FIELD_OPTION_TYPE_ID_ALIAS . ","
            . " CONCAT('[',"
            . " GROUP_CONCAT(CONCAT("
            . "'{\"price\"',':\"',IFNULL(price,''),'\",',"
            . "'\"customer_group_id\"',':\"',customer_group_id,'\",',"
            . "'\"price_type\"',':\"',price_type,'\",',"
            . "'\"date_from\"',':\"',IFNULL(date_from,''),'\",',"
            . "'\"date_to\"',':\"',IFNULL(date_to,''),'\",',"
            . "'\"qty\"',':\"',qty,'\"}'"
            . ")),"
            . "']')"
            . " AS tier_price FROM " . $tableName;

        if ($conditions && (!empty($conditions['option_id']) || !empty($conditions['value_id']))) {
            $optionTypeIds = $this->helper->findOptionTypeIdByConditions($conditions);

            if (is_array($optionTypeIds) && count($optionTypeIds) > 0) {
                $selectExpr .= " WHERE option_type_id IN(" . implode(',', $optionTypeIds) . ")";
            }
        }
        $selectExpr .= " GROUP BY option_type_id";

        return new \Zend_Db_Expr('(' . $selectExpr . ')');
    }
}
