<?php
namespace Mexbs\MultiInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Model\Stock;

class WebsiteSaveAfter implements ObserverInterface{

    protected $stock;

    public function __construct(
        Stock $stock
    )
    {
        $this->stock = $stock;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $website = $observer->getEvent()->getWebsite();
		

        $stock = $this->stock->load($website->getId(), 'website_id');
        if(!$stock->getId()){
            $stock->setWebsiteId($website->getId())
                ->setStockName($website->getName())
                ->save();
        }
    }
}