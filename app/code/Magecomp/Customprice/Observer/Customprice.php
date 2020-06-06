<?php
namespace Magecomp\Customprice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magecomp\Customprice\Helper\Data ;
class Customprice implements ObserverInterface
{
    protected $_request;
    protected $helper;
    public function __construct(
        RequestInterface $request,
        Data $data
    )
    {
        $this->_request = $request;
        $this->helper =$data;
    }
    public function execute(\Magento\Framework\Event\Observer $observer) {
        if(!$this->helper->isModuleEnabled()) {
            return;
        }
            $cprice = $this->_request->getPost('cprice');
            $item = $observer->getEvent()->getData('quote_item');
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $price = $cprice; //set your price here
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
        }
}