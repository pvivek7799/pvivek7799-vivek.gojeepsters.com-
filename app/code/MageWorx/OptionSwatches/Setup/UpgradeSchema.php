<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSwatches\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \MageWorx\OptionBase\Model\Installer
     */
    protected $optionBaseInstaller;

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
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $query = $setup->getConnection()
                  ->select()
                  ->from($setup->getTable('core_config_data'))
                  ->reset(\Zend_Db_Select::COLUMNS)
                  ->columns(['value'])
                  ->where("path = 'mageworx_apo/optionfeatures/swatch_size'");
            $swatchSize = $setup->getConnection()->fetchOne($query);
            if (!$swatchSize) {
                return;
            }
            $setup->getConnection()->insert(
                $setup->getTable('core_config_data'),
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'mageworx_apo/optionswatches/swatch_height',
                    'value' => $swatchSize,
                ]
            );
            $setup->getConnection()->insert(
                $setup->getTable('core_config_data'),
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'mageworx_apo/optionswatches/swatch_width',
                    'value' => $swatchSize,
                ]
            );
            $setup->getConnection()->delete(
                $setup->getTable('core_config_data'),
                "path = 'mageworx_apo/optionfeatures/swatch_size'"
            );
        }

        $this->optionBaseInstaller->install();
    }
}
