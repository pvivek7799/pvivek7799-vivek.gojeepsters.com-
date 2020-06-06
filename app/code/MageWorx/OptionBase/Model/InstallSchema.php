<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class InstallSchema implements \MageWorx\OptionBase\Api\InstallSchemaInterface
{
    CONST TABLE_NAME_CATALOG_PRODUCT_ENTITY = 'catalog_product_entity';

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        BaseHelper $baseHelper
    ) {
        $this->baseHelper = $baseHelper;
    }

    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return 'mageworx_optionbase';
    }

    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            /* Table 'mageworx_optionbase_product_attributes' */
            [
                'table_name'           => ProductAttributes::TABLE_NAME,
                'field_name'           => ProductAttributes::COLUMN_ENTITY_ID,
                'params'               => [
                    'type'     => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'comment'  => 'ID',
                ],
                'skip_add_to_template' => true
            ],
            [
                'table_name'           => ProductAttributes::TABLE_NAME,
                'field_name'           => ProductAttributes::COLUMN_PRODUCT_ID,
                'params'               => [
                    'type'     => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Product ID',
                ],
                'skip_add_to_template' => true
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
        $dataArray = [
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => ProductAttributes::COLUMN_PRODUCT_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options'    => []
            ]
        ];

        return $dataArray;
    }

    /**
     * Retrieve module foreign keys data array
     *
     * @return array
     */
    public function getForeignKeys()
    {
        $dataArray = [
            [
                'table_name'            => ProductAttributes::TABLE_NAME,
                'column_name'           => ProductAttributes::COLUMN_PRODUCT_ID,
                'reference_table_name'  => static::TABLE_NAME_CATALOG_PRODUCT_ENTITY,
                'reference_column_name' => $this->baseHelper->getLinkField(),
                'on_delete'             => Table::ACTION_CASCADE
            ]
        ];

        return $dataArray;
    }
}
