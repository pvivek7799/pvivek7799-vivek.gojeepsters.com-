<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionFeatures\Model\OptionDescription;
use MageWorx\OptionFeatures\Model\OptionTypeDescription;
use MageWorx\OptionFeatures\Model\Image;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefault;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \MageWorx\OptionBase\Model\Installer
     */
    protected $optionBaseInstaller;

    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * UpgradeSchema constructor.
     *
     * @param \MageWorx\OptionBase\Model\Installer $optionBaseInstaller
     */
    public function __construct(
        \MageWorx\OptionBase\Model\Installer $optionBaseInstaller
    ) {
        $this->optionBaseInstaller = $optionBaseInstaller;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;

        $this->optionBaseInstaller->install();

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_weight'],
                "path = 'mageworx_optionfeatures/main/use_weight'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_absolute_weight'],
                "path = 'mageworx_optionfeatures/main/use_absolute_weight'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_cost'],
                "path = 'mageworx_optionfeatures/main/use_cost'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_absolute_cost'],
                "path = 'mageworx_optionfeatures/main/use_absolute_cost'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_absolute_price'],
                "path = 'mageworx_optionfeatures/main/use_absolute_price'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_one_time'],
                "path = 'mageworx_optionfeatures/main/use_one_time'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_qty_input'],
                "path = 'mageworx_optionfeatures/main/use_qty_input'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_description'],
                "path = 'mageworx_optionfeatures/main/use_description'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_option_description'],
                "path = 'mageworx_optionfeatures/main/use_option_description'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_is_default'],
                "path = 'mageworx_optionfeatures/main/use_is_default'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/base_image_thumbnail_size'],
                "path = 'mageworx_optionfeatures/main/base_image_thumbnail_size'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/tooltip_image_thumbnail_size'],
                "path = 'mageworx_optionfeatures/main/tooltip_image_thumbnail_size'"
            );
        }
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionvisibility/use_is_disabled'],
                "path = 'mageworx_apo/optionfeatures/use_is_disabled'"
            );
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->setup->getConnection()->beginTransaction();
            try {
                $this->convertOptionDescriptionMageWorxIds();
                $this->convertOptionTypeDescriptionMageWorxIds();
                $this->convertOptionTypeImageMageWorxIds();
                $this->convertOptionTypeIsDefaultMageWorxIds();
                $this->setup->getConnection()->commit();
            } catch (\Exception $e) {
                $this->setup->getConnection()->rollback();
                throw($e);
            }
            $this->addUniqueIndexes();
        }
    }

    /**
     * Add unique indexes after data is processed
     *
     * @return void
     */
    protected function addUniqueIndexes()
    {
        $connection = $this->setup->getConnection();

        $map = [
            $this->setup->getTable(OptionTypeDescription::TABLE_NAME)                 => [
                OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID,
                OptionTypeDescription::COLUMN_NAME_STORE_ID
            ],
            $this->setup->getTable(OptionTypeDescription::OPTIONTEMPLATES_TABLE_NAME) => [
                OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID,
                OptionTypeDescription::COLUMN_NAME_STORE_ID
            ],
            $this->setup->getTable(OptionDescription::TABLE_NAME)                     => [
                OptionDescription::COLUMN_NAME_OPTION_ID,
                OptionDescription::COLUMN_NAME_STORE_ID
            ],
            $this->setup->getTable(OptionDescription::OPTIONTEMPLATES_TABLE_NAME)     => [
                OptionDescription::COLUMN_NAME_OPTION_ID,
                OptionDescription::COLUMN_NAME_STORE_ID
            ],
            $this->setup->getTable(OptionTypeIsDefault::TABLE_NAME)                   => [
                OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID,
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID
            ],
            $this->setup->getTable(OptionTypeIsDefault::OPTIONTEMPLATES_TABLE_NAME)   => [
                OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID,
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID
            ]
        ];

        foreach ($map as $tableName => $fieldName) {
            if ($connection->isTableExists($tableName) && !$this->isIndexExist($fieldName, $tableName)) {
                $indexType = \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE;
                $connection->addIndex(
                    $tableName,
                    $this->setup->getIdxName($tableName, $fieldName, $indexType),
                    $fieldName,
                    $indexType
                );
            }
        }
    }

    /**
     * Check if index already exist
     *
     * @param array $fieldName
     * @param string $tableName
     * @return bool $skipFlag
     */
    protected function isIndexExist($fieldName, $tableName)
    {
        $indexList = $this->setup->getConnection()->getIndexList($tableName);
        $indexType = \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE;

        $skipFlag = 0;
        foreach ($indexList as $index) {
            if ($index['KEY_NAME'] ==
                $this->setup->getIdxName(
                    $tableName,
                    $fieldName,
                    $indexType
                )
            ) {
                $skipFlag = 1;
                break;
            }
        }
        return $skipFlag;
    }

    /**
     * Out if column doesn't exist
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function out($table, $column)
    {
        return !$this->setup->getConnection()->tableColumnExists($this->setup->getTable($table), $column);
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for option description
     */
    protected function convertOptionDescriptionMageWorxIds()
    {
        $tableNames = [
            OptionDescription::TABLE_NAME                 => 'catalog_product_option',
            OptionDescription::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpo' => $this->setup->getTable($joinedTable)
                    ],
                    'cpo.' . OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID
                    . ' = option_description.' . OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID,
                    [
                        OptionDescription::COLUMN_NAME_OPTION_ID => OptionDescription::COLUMN_NAME_OPTION_ID
                    ]
                )
                ->where(
                    "option_description." . OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    ['option_description' => $this->setup->getTable($mainTable)]
                );
            $this->setup->getConnection()->query($update);
        }
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for option type description
     */
    protected function convertOptionTypeDescriptionMageWorxIds()
    {
        $tableNames = [
            OptionTypeDescription::TABLE_NAME                 => 'catalog_product_option_type_value',
            OptionTypeDescription::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option_type_value'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpotv' => $this->setup->getTable($joinedTable)
                    ],
                    'cpotv.' . OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID
                    . ' = option_type_description.' . OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                    [
                        OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID => OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID
                    ]
                )
                ->where(
                    "option_type_description." . OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    [
                        'option_type_description' => $this->setup->getTable($mainTable)
                    ]
                );

            $this->setup->getConnection()->query($update);
        }
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for option type image
     */
    protected function convertOptionTypeImageMageWorxIds()
    {
        $tableNames = [
            Image::TABLE_NAME                 => 'catalog_product_option_type_value',
            Image::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option_type_value'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, Image::COLUMN_MAGEWORX_OPTION_TYPE_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpotv' => $this->setup->getTable($joinedTable)
                    ],
                    'cpotv.' . Image::COLUMN_MAGEWORX_OPTION_TYPE_ID
                    . ' = option_type_image.' . Image::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                    [
                        Image::COLUMN_OPTION_TYPE_ID => Image::COLUMN_OPTION_TYPE_ID
                    ]
                )
                ->where(
                    "option_type_image." . Image::COLUMN_MAGEWORX_OPTION_TYPE_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    ['option_type_image' => $this->setup->getTable($mainTable)]
                );
            $this->setup->getConnection()->query($update);
        }
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for option type isDefault
     */
    protected function convertOptionTypeIsDefaultMageWorxIds()
    {
        $tableNames = [
            OptionTypeIsDefault::TABLE_NAME                 => 'catalog_product_option_type_value',
            OptionTypeIsDefault::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option_type_value'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpotv' => $this->setup->getTable($joinedTable)
                    ],
                    'cpotv.' . OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID
                    . ' = option_type_is_default.' . OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                    [
                        OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID => OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID
                    ]
                )
                ->where(
                    "option_type_is_default." . OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    ['option_type_is_default' => $this->setup->getTable($mainTable)]
                );
            $this->setup->getConnection()->query($update);
        }
    }
}
