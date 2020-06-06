<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionVisibility\Model\OptionCustomerGroup;
use MageWorx\OptionVisibility\Model\OptionStoreView;

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

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->setup->getConnection()->beginTransaction();
            try {
                $this->convertOptionCustomerGroupMageWorxIds();
                $this->convertOptionTypeTierPriceMageWorxIds();
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
     * Update new option_id with mageworx_option_id equivalent for option customer group
     */
    protected function convertOptionCustomerGroupMageWorxIds()
    {
        $tableNames = [
            OptionCustomerGroup::TABLE_NAME                 => 'catalog_product_option',
            OptionCustomerGroup::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, OptionCustomerGroup::COLUMN_NAME_MAGEWORX_OPTION_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpo' => $this->setup->getTable($joinedTable)
                    ],
                    'cpo.' . OptionCustomerGroup::COLUMN_NAME_MAGEWORX_OPTION_ID
                    . ' = option_customer_group.' . OptionCustomerGroup::COLUMN_NAME_MAGEWORX_OPTION_ID,
                    [
                        OptionCustomerGroup::COLUMN_NAME_OPTION_ID => OptionCustomerGroup::COLUMN_NAME_OPTION_ID
                    ]
                )
                ->where(
                    "option_customer_group." . OptionCustomerGroup::COLUMN_NAME_MAGEWORX_OPTION_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    [
                        'option_customer_group' => $this->setup->getTable($mainTable)
                    ]
                );
            $this->setup->getConnection()->query($update);
        }
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for option store view
     */
    protected function convertOptionTypeTierPriceMageWorxIds()
    {
        $tableNames = [
            OptionStoreView::TABLE_NAME                 => 'catalog_product_option',
            OptionStoreView::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, OptionStoreView::COLUMN_NAME_MAGEWORX_OPTION_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpo' => $this->setup->getTable($joinedTable)
                    ],
                    'cpo.' . OptionStoreView::COLUMN_NAME_MAGEWORX_OPTION_ID
                    . ' = option_store_view.' . OptionStoreView::COLUMN_NAME_MAGEWORX_OPTION_ID,
                    [
                        OptionStoreView::COLUMN_NAME_OPTION_ID => OptionStoreView::COLUMN_NAME_OPTION_ID
                    ]
                )
                ->where(
                    "option_store_view." . OptionStoreView::COLUMN_NAME_MAGEWORX_OPTION_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    [
                        'option_store_view' => $this->setup->getTable($mainTable)
                    ]
                );
            $this->setup->getConnection()->query($update);
        }
    }
}
