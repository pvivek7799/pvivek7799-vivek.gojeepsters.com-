<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Controller\Cart;

/**
 * Class Update
 * @package MW\Onestepcheckout\Controller\Cart
 */

class Update extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Sidebar
     */
    protected $sidebar;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultService;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \MW\Onestepcheckout\Helper\Data
     */
    protected $oscHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \MW\Onestepcheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * Update constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \MW\Onestepcheckout\Api\ResultServiceInterface $resultService
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     * @param \MW\Onestepcheckout\Helper\Data $oscHelper
     * @param \MW\Onestepcheckout\Helper\Config $configHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MW\Onestepcheckout\Api\ResultServiceInterface $resultService,
        \Magento\Checkout\Model\Sidebar $sidebar,
        \MW\Onestepcheckout\Helper\Data $oscHelper,
        \MW\Onestepcheckout\Helper\Config $configHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Checkout\Model\Cart $cart
    ) {
        parent::__construct($context);
        $this->resultService = $resultService;
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->sidebar = $sidebar;
        $this->oscHelper = $oscHelper;
        $this->configHelper = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->totalsCollector = $totalsCollector;
        $this->cart = $cart;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute()
    {
        $updateData = $this->dataObjectFactory->create([
            'data' => $this->jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
        
        $type = $updateData->getData('type');
        $result = [];
        $result['error'] = '';
        $itemId = $updateData->getData('item_id');
        $newQty = $updateData->getData('qty');
        $item = $this->cart->getQuote()->getItemById($itemId);
        $oldQty = $item->getQty();
        try {
            if ($type == 'update') {
                $this->sidebar->checkQuoteItem($itemId);
                $this->sidebar->updateQuoteItem($itemId, $newQty);
            } else {
                $this->sidebar->removeQuoteItem($itemId);
            }
        } catch (\Exception $e) {
            $this->sidebar->updateQuoteItem($itemId, $oldQty);
            $result['error'] = $e->getMessage();
        }

        if ($this->configHelper->isEnableGiftWrap()) {
            $giftWrapAmount = $this->oscHelper->getOrderGiftWrapAmount();
            $this->checkoutSession->setData('onestepcheckout_giftwrap_amount', $giftWrapAmount);
            $result['giftwrap_amount'] = $giftWrapAmount;
            $this->totalsCollector->collectQuoteTotals($this->cart->getQuote());
            $this->cart->getQuote()->save();
        }

        if ($this->cart->getSummaryQty() == 0) {
            $this->checkoutSession->setOnestepcheckoutGiftwrapAmount(null);
            $this->checkoutSession->setOnestepcheckoutBaseGiftwrapAmount(null);
            $this->checkoutSession->setOnestepcheckoutGiftwrap(null);
            $result['cart_empty'] = true;
        }
        $result['is_virtual'] = ($this->cart->getQuote()->isVirtual())?true:false;
        $resultJson = $this->resultService->createJson();
        return $resultJson->setData($result);
    }
}
