<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSkuPolicy\Model;

use Magento\Framework\DB\Ddl\Table;
use MageWorx\OptionSkuPolicy\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\ProductAttributes;

class InstallSchema implements \MageWorx\OptionBase\Api\InstallSchemaInterface
{
    const CATALOG_PRODUCT_OPTION_TABLE_NAME                     = 'catalog_product_option';
    const MAGEWORX_OPTIONFEATURES_PRODUCT_ATTRIBUTES_TABLE_NAME = ProductAttributes::TABLE_NAME;

    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return '';
    }

    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            [
                'table_name' => self::MAGEWORX_OPTIONFEATURES_PRODUCT_ATTRIBUTES_TABLE_NAME,
                'field_name' => Helper::KEY_SKU_POLICY,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 20,
                    'nullable' => false,
                    'default'  => Helper::SKU_POLICY_USE_CONFIG,
                    'comment'  => 'SKU Policy (added by MageWorx Option Sku Policy)',
                ]
            ],
            [
                'table_name' => self::CATALOG_PRODUCT_OPTION_TABLE_NAME,
                'field_name' => Helper::KEY_SKU_POLICY,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 20,
                    'nullable' => false,
                    'default'  => Helper::SKU_POLICY_USE_CONFIG,
                    'comment'  => 'SKU Policy (added by MageWorx Option Sku Policy)',
                ]
            ]
        ];

        return $dataArray;
    }

    /**
     * Retrieve module indexes data array
     *
     * @return array
     */
    public function getIndexes()
    {
        return [];
    }

    /**
     * Retrieve module foreign keys data array
     *
     * @return array
     */
    public function getForeignKeys()
    {
        return [];
    }
}
