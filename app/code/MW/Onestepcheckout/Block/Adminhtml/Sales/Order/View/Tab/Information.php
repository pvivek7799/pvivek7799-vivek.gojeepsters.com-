<?php

/**
 * Mage-World
 *
 * @category    Mage-World
 * @package     MW
 * @author      Mage-world Developer
 *
 * @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Block\Adminhtml\Sales\Order\View\Tab;

/**
 * Class Information
 * @package MW\Onestepcheckout\Block\Adminhtml\Sales\Order\View\Tab
 */
class Information extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {

        $order = $this->getOrder();

        $delivery = [];
        if ($order->getData('mw_deliverydate_date')) {
            $delivery['mw_deliverydate_date'] = $order->getData('mw_deliverydate_date');
            $mwDeliverydateTime = $order->getData('mw_deliverydate_time');
            if ($mwDeliverydateTime) {
                $delivery['mw_deliverydate_time'] = $mwDeliverydateTime;
            } else {
                $delivery['mw_deliverydate_time'] = '';
            }

            $mwDeliverydateSecuritycode = $order->getData('mw_deliverydate_securitycode');
            if ($mwDeliverydateSecuritycode) {
                $delivery['mw_deliverydate_securitycode'] = $mwDeliverydateSecuritycode;
            } else {
                $delivery['mw_deliverydate_securitycode'] = '';
            }
        }
        return $delivery;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        $orderId = $this->getOrderId();
        $comment = $this->_objectManager->create(\Magento\Sales\Model\Order::class)
            ->load($orderId)->getMwCustomercommentInfo();

        return $comment;
    }

    /**
     * @param null $orderId
     *
     * @return bool
     */
    public function getLastItem($orderId = null)
    {
        if (!$orderId) {
            $order_id = $this->getOrderId();
        } else {
            $order_id = $orderId;
        }

        $order = $this->_loadOrder($order_id);
        $itemCollection = $order->getItemsCollection();
        $lastItem = $itemCollection->setPageSize(1)->setCurPage($itemCollection->getLastPageNumber())->getLastItem();

        if ($lastItem->getParentItemId()) {
            $lastId = $lastItem->getParentItemId();
        } else {
            $lastId = $lastItem->getId();
        }
        if ($lastId != $this->getParentBlock()->getItem()->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @param $order_id
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _loadOrder($order_id)
    {
        if ($order_id) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class);

            return $order->load($order_id);
        } elseif ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->_objectManager->create(\Magento\Sales\Model\Order\Invoice::class);

            return $invoice->load($invoiceId)->getOrder();
        } elseif ($shipmentId = $this->getRequest()->getParam('shipment_id')) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->_objectManager->create(\Magento\Sales\Model\Order\Shipment::class);

            return $shipment->load($shipmentId)->getOrder();
        } else {
            /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
            $creditmemo = $this->_objectManager->create(\Magento\Sales\Model\Order\Creditmemo::class);

            return $creditmemo->load($this->getRequest()->getParam('creditmemo_id'))->getOrder();
        }
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('mw_ddate/group_general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return '';
        }
        return parent::_toHtml();
    }
}
