<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionDependency\Model\Config as DependencyModel;

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

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optiondependency/use_title_id'],
                "path = 'mageworx_optiondependency/main/use_title_id'"
            );
        }

        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $this->processFields();
            $this->setup->getConnection()->beginTransaction();
            try {
                $this->convertDependencyOptionMageWorxIds();
                $this->convertDependencyOptionValueMageWorxIds();
                $this->setup->getConnection()->commit();
            } catch (\Exception $e) {
                $this->setup->getConnection()->rollback();
                throw($e);
            }
        }
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
     * Process fields due to removing mageworx_ids:
     * Copy old data to temporary field
     * Get option_id/option_type_id equivalent for mageworx_option_id/mageworx_option_type_id
     */
    protected function processFields()
    {
        $tableNames = [
            DependencyModel::TABLE_NAME,
            DependencyModel::OPTIONTEMPLATES_TABLE_NAME
        ];

        foreach ($tableNames as $tableName) {
            $this->moveMageWorxIdsToTemporaryFields($tableName);
            $this->modifyParentAndChildColumnDefinition($tableName);
        }
    }

    /**
     * @param string $tableName
     */
    protected function moveMageWorxIdsToTemporaryFields($tableName)
    {
        $data       = [
            DependencyModel::COLUMN_NAME_CHILD_MAGEWORX_OPTION_ID       =>
                new \Zend_Db_Expr(DependencyModel::COLUMN_NAME_CHILD_OPTION_ID),
            DependencyModel::COLUMN_NAME_CHILD_MAGEWORX_OPTION_TYPE_ID  =>
                new \Zend_Db_Expr(DependencyModel::COLUMN_NAME_CHILD_OPTION_TYPE_ID),
            DependencyModel::COLUMN_NAME_PARENT_MAGEWORX_OPTION_ID      =>
                new \Zend_Db_Expr(DependencyModel::COLUMN_NAME_PARENT_OPTION_ID),
            DependencyModel::COLUMN_NAME_PARENT_MAGEWORX_OPTION_TYPE_ID =>
                new \Zend_Db_Expr(DependencyModel::COLUMN_NAME_PARENT_OPTION_TYPE_ID),
            DependencyModel::COLUMN_NAME_IS_PROCESSED                   => 1
        ];
        $this->setup->getConnection()->update(
            $this->setup->getTable($tableName),
            $data,
            [DependencyModel::COLUMN_NAME_IS_PROCESSED . ' = ?' => '0']
        );
    }

    /**
     * Modify column definition of child/parent option/option_value fields to contain integer IDs
     *
     * @param string $tableName
     * @return void
     */
    protected function modifyParentAndChildColumnDefinition($tableName)
    {
        $fieldMap = [
            DependencyModel::COLUMN_NAME_CHILD_OPTION_ID,
            DependencyModel::COLUMN_NAME_CHILD_OPTION_TYPE_ID,
            DependencyModel::COLUMN_NAME_PARENT_OPTION_ID,
            DependencyModel::COLUMN_NAME_PARENT_OPTION_TYPE_ID
        ];

        foreach ($fieldMap as $field) {
            $table = $this->setup->getConnection()->describeTable($this->setup->getTable($tableName));

            if (!empty($table[$field]['DATA_TYPE']) && $table[$field]['DATA_TYPE'] === 'varchar') {
                $this->setup->getConnection()->modifyColumn(
                    $this->setup->getTable($tableName),
                    $field,
                    [
                        'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length'   => 10,
                        'nullable' => false,
                        'default'  => 0
                    ],
                    true
                );
                $indexType = \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX;
                $this->setup->getConnection()->addIndex(
                    $this->setup->getTable($tableName),
                    $this->setup->getIdxName($tableName, $field, $indexType),
                    $field,
                    $indexType
                );
            }
        }
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for dependency child/parent option
     */
    protected function convertDependencyOptionMageWorxIds()
    {
        $tableNames = [
            DependencyModel::TABLE_NAME                 => 'catalog_product_option',
            DependencyModel::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, 'mageworx_option_id')) {
                continue;
            }

            $fieldMap = [
                DependencyModel::COLUMN_NAME_CHILD_MAGEWORX_OPTION_ID  => DependencyModel::COLUMN_NAME_CHILD_OPTION_ID,
                DependencyModel::COLUMN_NAME_PARENT_MAGEWORX_OPTION_ID => DependencyModel::COLUMN_NAME_PARENT_OPTION_ID
            ];

            foreach ($fieldMap as $oldColumnName => $newColumnName) {
                $select = $this->setup
                    ->getConnection()
                    ->select()
                    ->joinLeft(
                        [
                            'cpo' => $this->setup->getTable($joinedTable)
                        ],
                        'cpo.mageworx_option_id = option_dependency.' . $oldColumnName,
                        [
                            $newColumnName => 'option_id'
                        ]
                    )
                    ->where(
                        "option_dependency." . $oldColumnName . " IS NOT NULL"
                    );

                $update = $this->setup
                    ->getConnection()
                    ->updateFromSelect(
                        $select,
                        ['option_dependency' => $this->setup->getTable($mainTable)]
                    );
                $this->setup->getConnection()->query($update);
            }
        }
    }

    /**
     * Update new option_type_id with mageworx_option_type_id equivalent for dependency child/parent option value
     */
    protected function convertDependencyOptionValueMageWorxIds()
    {
        $tableNames = [
            DependencyModel::TABLE_NAME                 => 'catalog_product_option_type_value',
            DependencyModel::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option_type_value'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, 'mageworx_option_type_id')) {
                continue;
            }

            $fieldMap = [
                DependencyModel::COLUMN_NAME_CHILD_MAGEWORX_OPTION_TYPE_ID  =>
                    DependencyModel::COLUMN_NAME_CHILD_OPTION_TYPE_ID,
                DependencyModel::COLUMN_NAME_PARENT_MAGEWORX_OPTION_TYPE_ID =>
                    DependencyModel::COLUMN_NAME_PARENT_OPTION_TYPE_ID
            ];

            foreach ($fieldMap as $oldColumnName => $newColumnName) {
                $select = $this->setup
                    ->getConnection()
                    ->select()
                    ->joinLeft(
                        [
                            'cpotv' => $this->setup->getTable($joinedTable)
                        ],
                        'cpotv.mageworx_option_type_id = option_value_dependency.' . $oldColumnName,
                        [
                            $newColumnName => 'option_type_id'
                        ]
                    )
                    ->where(
                        "option_value_dependency." . $oldColumnName . " IS NOT NULL"
                    );

                $update = $this->setup
                    ->getConnection()
                    ->updateFromSelect(
                        $select,
                        ['option_value_dependency' => $this->setup->getTable($mainTable)]
                    );
                $this->setup->getConnection()->query($update);
            }
        }
    }
}
