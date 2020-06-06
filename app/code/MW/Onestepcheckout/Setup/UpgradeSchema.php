<?php
namespace MW\Onestepcheckout\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $connection->addColumn(
                $setup->getTable('sales_order'),
                'mw_deliverydate_securitycode',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Delivery Security Code'
                ]
            );
        }

        $installer->endSetup();
    }
}
