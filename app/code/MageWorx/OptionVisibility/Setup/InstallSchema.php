<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package MageWorx\OptionVisibility\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \MageWorx\OptionBase\Model\Installer
     */
    protected $optionBaseInstaller;

    /**
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
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->optionBaseInstaller->install();

        $installer->endSetup();
    }
}