<?php
namespace Magecomp\Customprice\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'customprice_enabledisable',
			[
				'group' =>"Custom Price",
				'frontend' => '',
				'label' => 'Custom Price',
				'input' => 'select',
				'class' => '',
				'source' => 'Magecomp\Customprice\Model\Config\Product\Dropdowntype',
				'global' => true,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'apply_to' => '',
				'visible_on_front' => false,
				'is_used_in_grid' => true,
				'is_visible_in_grid' => false,
				'is_filterable_in_grid' => false,
			]
		);
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'customprice_productwise',
			[
				'type' => 'text',
				'label' => 'Minimum Custom Price',
				'input' => 'text',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'sort_order' => 10,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'group' =>"Custom Price",
			]
		);
	}
	
}