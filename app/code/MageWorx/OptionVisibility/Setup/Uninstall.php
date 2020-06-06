<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    const TABLE_CUSTOMER_GROUP = 'mageworx_optionvisibility_option_customer_group';
    const TABLE_STORE_VIEW     = 'mageworx_optionvisibility_option_store_view';

    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $connection = $setup->getConnection();

        $connection->dropTable($connection->getTableName(self::TABLE_CUSTOMER_GROUP));
        $connection->dropTable($connection->getTableName(self::TABLE_STORE_VIEW));

        $setup->endSetup();
    }
}
