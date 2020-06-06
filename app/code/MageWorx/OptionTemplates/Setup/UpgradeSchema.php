<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionTemplates\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionTemplates\Helper\Data as Helper;
use MageWorx\OptionBase\Model\Installer;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const MAGEWORX_OPTION_ID                              = 'mageworx_option_id';
    const MAGEWORX_OPTION_TYPE_ID                         = 'mageworx_option_type_id';
    const MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE     = 'mageworx_optiontemplates_group_option';
    const MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE = 'mageworx_optiontemplates_group_option_type_value';

    /**
     * @var Installer
     */
    protected $optionBaseInstaller;

    public function __construct(
        Installer $optionBaseInstaller
    ) {
        $this->optionBaseInstaller = $optionBaseInstaller;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->optionBaseInstaller->install();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optiontemplates/hide_all_options'],
                "path = 'mageworx_optiontemplates/main/hide_all_options'"
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $setup->getConnection()->update(
                $setup->getTable(Helper::TABLE_NAME_RELATION),
                [Helper::COLUMN_NAME_IS_CHANGED => true],
                "1"
            );
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            if ($installer->getConnection()->tableColumnExists(
                $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE),
                static::MAGEWORX_OPTION_ID
            )) {
                $triggerName = 'insert_template_'.static::MAGEWORX_OPTION_ID;
                $setup->getConnection()->dropTrigger($triggerName);
            }

            if ($installer->getConnection()->tableColumnExists(
                $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE),
                static::MAGEWORX_OPTION_TYPE_ID
            )) {
                $triggerName = 'insert_template_'.static::MAGEWORX_OPTION_TYPE_ID;
                $setup->getConnection()->dropTrigger($triggerName);
            }
        }

        $installer->endSetup();
    }
}
