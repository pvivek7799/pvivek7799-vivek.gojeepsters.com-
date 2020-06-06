<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements \MageWorx\OptionBase\Api\InstallSchemaInterface
{
    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return 'mageworx_optionadvancedpricing';
    }

    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            /* Table 'mageworx_optionadvancedpricing_option_type_special_price' */
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_OPTION_TYPE_SPECIAL_PRICE_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'comment'  => 'Option Type Special Price ID',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'MageWorx Option Type ID',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_OPTION_TYPE_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Option Type ID',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_CUSTOMER_GROUP_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Customer Group ID',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_PRICE,
                'params'     => [
                    'type'     => Table::TYPE_DECIMAL,
                    'length'   => '12,4',
                    'nullable' => false,
                    'default'  => '0.0000',
                    'comment'  => 'Special Price',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_PRICE_TYPE,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => false,
                    'default'  => 'fixed',
                    'comment'  => 'Special Price Type (fixed, percentage_discount)',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_COMMENT,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => false,
                    'default'  => '',
                    'comment'  => 'Special Price Comment',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_DATE_FROM,
                'params'     => [
                    'type'    => Table::TYPE_DATE,
                    'comment' => 'Special Price Date From',
                ]
            ],
            [
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_DATE_TO,
                'params'     => [
                    'type'    => Table::TYPE_DATE,
                    'comment' => 'Special Price Date To',
                ]
            ],
            /* Table 'mageworx_optionadvancedpricing_option_type_tier_price' */
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_OPTION_TYPE_TIER_PRICE_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'comment'  => 'Option Type Tier Price ID',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'MageWorx Option Type ID',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_OPTION_TYPE_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Option Type ID',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_CUSTOMER_GROUP_ID,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Customer Group ID',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_QTY,
                'params'     => [
                    'type'     => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'Tier Price Qty',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_PRICE,
                'params'     => [
                    'type'     => Table::TYPE_DECIMAL,
                    'length'   => '12,4',
                    'nullable' => false,
                    'default'  => '0.0000',
                    'comment'  => 'Tier Price',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_PRICE_TYPE,
                'params'     => [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 40,
                    'nullable' => false,
                    'default'  => 'fixed',
                    'comment'  => 'Tier Price Type (fixed, percentage_discount)',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_DATE_FROM,
                'params'     => [
                    'type'    => Table::TYPE_DATE,
                    'comment' => 'Tier Price Date From',
                ]
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_DATE_TO,
                'params'     => [
                    'type'    => Table::TYPE_DATE,
                    'comment' => 'Tier Price Date To',
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
                'table_name' => SpecialPrice::TABLE_NAME,
                'field_name' => SpecialPrice::COLUMN_OPTION_TYPE_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_INDEX,
                'options'    => []
            ],
            [
                'table_name' => TierPrice::TABLE_NAME,
                'field_name' => TierPrice::COLUMN_OPTION_TYPE_ID,
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
        return [];
    }
}
