<?php

namespace MW\Onestepcheckout\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_logger;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_countryCollection;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;

    /**
     * @param \Magento\Framework\Logger\Monolog $logger
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Config\Model\ResourceModel\Config $config
     */
    public function __construct(
        \Magento\Framework\Logger\Monolog $logger,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Config\Model\ResourceModel\Config $config
    ) {
        $this->_logger = $logger;
        $this->_countryCollection = $countryCollection;
        $this->_config = $config;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Delivery information on sales_order table
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'mw_customercomment_info',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Customer Comment'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'mw_deliverydate_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Delivery Date'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'mw_deliverydate_time',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Delivery Time'
            ]
        );

        // Gift Wrap
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Giftwrap Amount'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'giftwrap_amount_invoiced',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Giftwrap Amount Invoiced'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'giftwrap_amount_refunded',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Giftwrap Amount Refunded'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'base_giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Giftwrap Amount'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'base_giftwrap_amount_invoiced',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Giftwrap Amount Invoiced'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_order'),
            'base_giftwrap_amount_refunded',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Giftwrap Amount Refunded'
            ]
        );

        $connection->addColumn(
            $setup->getTable('quote_address'),
            'giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Giftwrap Amount'
            ]
        );
        $connection->addColumn(
            $setup->getTable('quote_address'),
            'base_giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Giftwrap Amount'
            ]
        );

        $connection->addColumn(
            $setup->getTable('sales_invoice'),
            'giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Giftwrap Amount'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_invoice'),
            'base_giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Giftwrap Amount'
            ]
        );

        $connection->addColumn(
            $setup->getTable('sales_creditmemo'),
            'giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Giftwrap Amount'
            ]
        );
        $connection->addColumn(
            $setup->getTable('sales_creditmemo'),
            'base_giftwrap_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Giftwrap Amount'
            ]
        );

        $setup->endSetup();
    }
}
