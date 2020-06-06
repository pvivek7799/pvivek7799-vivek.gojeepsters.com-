<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Model;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionVisibility\Helper\Data as Helper;

class InstallSchema implements \MageWorx\OptionBase\Api\InstallSchemaInterface
{
    const PRODUCT_OPTION  = 'catalog_product_option';
    const IS_ALL_GROUPS   = 'is_all_groups';
    const IS_ALL_WEBSITES = 'is_all_websites';

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param BaseHelper $baseHelper
     * @param Helper $helper
     */
    public function __construct(
        BaseHelper $baseHelper,
        Helper $helper
    ) {
        $this->baseHelper = $baseHelper;
        $this->helper     = $helper;
    }

    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return 'mageworx_optionvisibility';
    }

    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $customerGroupIdType = $this->baseHelper->checkModuleVersion(
            '100.0.0',
            '101.0.0',
            '>=',
            '<',
            'Magento_Customer'
        )
            ? Table::TYPE_SMALLINT
            : Table::TYPE_INTEGER;


        $dataArray = [
            /* Table 'mageworx_optionvisibility_option_customer_group' */
            [
                'table_name' => OptionCustomerGroup::TABLE_NAME,
                'field_name' => OptionCustomerGroup::COLUMN_NAME_VISIBILITY_CUSTOMER_GROUP_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'comment'  => 'Option Visibility Customer Group Id',
                ]
            ],
            [
                'table_name' => OptionCustomerGroup::TABLE_NAME,
                'field_name' => OptionCustomerGroup::COLUMN_NAME_MAGEWORX_OPTION_ID,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'MageWorx Option ID',
                ]
            ],
            [
                'table_name' => OptionCustomerGroup::TABLE_NAME,
                'field_name' => OptionCustomerGroup::COLUMN_NAME_OPTION_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Option ID',
                ]
            ],
            [
                'table_name' => OptionCustomerGroup::TABLE_NAME,
                'field_name' => OptionCustomerGroup::COLUMN_NAME_GROUP_ID,
                'params'     => [
                    'type'     => $customerGroupIdType,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Customer Group ID',
                ]
            ],

            /* Table 'mageworx_optionvisibility_option_store_view' */
            [
                'table_name' => OptionStoreView::TABLE_NAME,
                'field_name' => OptionStoreView::COLUMN_NAME_VISIBILITY_STORE_VIEW_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'comment'  => 'Option Visibility Customer Store View Id',
                ]
            ],
            [
                'table_name' => OptionStoreView::TABLE_NAME,
                'field_name' => OptionStoreView::COLUMN_NAME_MAGEWORX_OPTION_ID,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'MageWorx Option ID',
                ]
            ],
            [
                'table_name' => OptionStoreView::TABLE_NAME,
                'field_name' => OptionStoreView::COLUMN_NAME_OPTION_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Option ID',
                ]
            ],
            [
                'table_name' => OptionStoreView::TABLE_NAME,
                'field_name' => OptionStoreView::COLUMN_NAME_STORE_ID,
                'params'     => [
                    'type'     => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Store ID',
                ]
            ],

            /*  'catalog_product_option' */
            [
                'table_name' => self::PRODUCT_OPTION,
                'field_name' => self::IS_ALL_GROUPS,
                'params'     => [
                    'type'     => Table::TYPE_BOOLEAN,
                    'length'   => null,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '1',
                    'comment'  => 'ALL Customer Group ID (added by MageWorx Option Visibility)',
                ]
            ],
            [
                'table_name' => self::PRODUCT_OPTION,
                'field_name' => self::IS_ALL_WEBSITES,
                'params'     => [
                    'type'     => Table::TYPE_BOOLEAN,
                    'length'   => null,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '1',
                    'comment'  => 'ALL Store ID (added by MageWorx Option Visibility)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_DISABLED,
                'params'     => [
                    'type'     => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Disabled (added by MageWorx Option Visibility)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_DISABLED_BY_VALUES,
                'params'     => [
                    'type'     => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Disabled by values (added by MageWorx Option Visibility)',
                ]
            ],
            /* 'catalog_product_option_type_value' */
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_DISABLED,
                'params'     => [
                    'type'     => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Disabled (added by MageWorx Option Visibility)',
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
        $dataArray = [
            [
                'table_name' => OptionCustomerGroup::TABLE_NAME,
                'field_name' => OptionCustomerGroup::COLUMN_NAME_OPTION_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_INDEX,
                'options'    => []
            ],
            [
                'table_name' => OptionStoreView::TABLE_NAME,
                'field_name' => OptionStoreView::COLUMN_NAME_OPTION_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_INDEX,
                'options'    => []
            ],
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
                'table_name'            => OptionStoreView::TABLE_NAME,
                'column_name'           => OptionStoreView::COLUMN_NAME_STORE_ID,
                'reference_table_name'  => 'store',
                'reference_column_name' => 'store_id',
                'on_delete'             => Table::ACTION_CASCADE
            ],
            [
                'table_name'            => OptionCustomerGroup::TABLE_NAME,
                'column_name'           => OptionCustomerGroup::COLUMN_NAME_GROUP_ID,
                'reference_table_name'  => 'customer_group',
                'reference_column_name' => 'customer_group_id',
                'on_delete'             => Table::ACTION_CASCADE
            ]
        ];

        return $dataArray;
    }
}