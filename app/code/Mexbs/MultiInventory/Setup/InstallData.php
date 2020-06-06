<?php
namespace Mexbs\MultiInventory\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    protected $_websiteFactory;

    /**
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory
    ) {
        $this->_websiteFactory = $websiteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $select = $setup->getConnection()->select()->from(
            $setup->getTable('cataloginventory_stock'),
            ['stock_id','website_id']
        );
        $stockRows = $setup->getConnection()->fetchPairs($select);

        $websiteCollection = $this->_websiteFactory->create()->getResourceCollection();
        foreach($websiteCollection as $website){
            if(!in_array($website->getId(),$stockRows)){
                $setup->getConnection()
                ->insertForce(
                    $setup->getTable('cataloginventory_stock'),
                    ['stock_id' => null, 'stock_name' => $website->getName(), 'website_id' => $website->getId()]
                );
            }
        }
    }
}