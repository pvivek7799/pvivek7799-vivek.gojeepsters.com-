<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\TriggerFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const MAGEWORX_OPTION_ID                      = 'mageworx_option_id';
    const MAGEWORX_OPTION_TYPE_ID                 = 'mageworx_option_type_id';
    const CATALOG_PRODUCT_OPTION_TABLE            = 'catalog_product_option';
    const CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE = 'catalog_product_option_type_value';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity'),
                'mageworx_is_require',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'MageWorx Is Required',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            if ($installer->getConnection()->tableColumnExists(
                $setup->getTable(static::CATALOG_PRODUCT_OPTION_TABLE),
                static::MAGEWORX_OPTION_ID
            )) {
                $triggerName = 'insert_' . static::MAGEWORX_OPTION_ID;
                $setup->getConnection()->dropTrigger($triggerName);
            }

            if ($installer->getConnection()->tableColumnExists(
                $setup->getTable(static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE),
                static::MAGEWORX_OPTION_TYPE_ID
            )) {
                $triggerName = 'insert_' . static::MAGEWORX_OPTION_TYPE_ID;
                $setup->getConnection()->dropTrigger($triggerName);
            }
        }

        $installer->endSetup();
    }
}
