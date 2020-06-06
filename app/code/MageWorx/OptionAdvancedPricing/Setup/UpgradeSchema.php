<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionAdvancedPricing\Model\TierPrice;
use MageWorx\OptionAdvancedPricing\Model\SpecialPrice;

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
                $this->convertOptionTypeSpecialPriceMageWorxIds();
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
     * Update new option_id with mageworx_option_id equivalent for option type special price
     */
    protected function convertOptionTypeSpecialPriceMageWorxIds()
    {
        $tableNames = [
            SpecialPrice::TABLE_NAME                 => 'catalog_product_option_type_value',
            SpecialPrice::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option_type_value'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, SpecialPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpotv' => $this->setup->getTable($joinedTable)
                    ],
                    'cpotv.' . SpecialPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID
                    . ' = option_type_special_price.' . SpecialPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                    [
                        SpecialPrice::COLUMN_OPTION_TYPE_ID => SpecialPrice::COLUMN_OPTION_TYPE_ID
                    ]
                )
                ->where(
                    "option_type_special_price." . SpecialPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    [
                        'option_type_special_price' => $this->setup->getTable($mainTable)
                    ]
                );
            $this->setup->getConnection()->query($update);
        }
    }

    /**
     * Update new option_id with mageworx_option_id equivalent for option type tier price
     */
    protected function convertOptionTypeTierPriceMageWorxIds()
    {
        $tableNames = [
            TierPrice::TABLE_NAME                 => 'catalog_product_option_type_value',
            TierPrice::OPTIONTEMPLATES_TABLE_NAME => 'mageworx_optiontemplates_group_option_type_value'
        ];

        foreach ($tableNames as $mainTable => $joinedTable) {

            if ($this->out($joinedTable, TierPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID)) {
                continue;
            }

            $select = $this->setup
                ->getConnection()
                ->select()
                ->joinLeft(
                    [
                        'cpotv' => $this->setup->getTable($joinedTable)
                    ],
                    'cpotv.' . TierPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID
                    . ' = option_type_tier_price.' . TierPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                    [
                        TierPrice::COLUMN_OPTION_TYPE_ID => TierPrice::COLUMN_OPTION_TYPE_ID
                    ]
                )
                ->where(
                    "option_type_tier_price." . TierPrice::COLUMN_MAGEWORX_OPTION_TYPE_ID . " IS NOT NULL"
                );

            $update = $this->setup
                ->getConnection()
                ->updateFromSelect(
                    $select,
                    [
                        'option_type_tier_price' => $this->setup->getTable($mainTable)
                    ]
                );
            $this->setup->getConnection()->query($update);
        }
    }
}
