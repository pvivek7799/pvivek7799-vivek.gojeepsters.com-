<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Controller\Index;

/**
 * Class Giftwrap
 * @package MW\Onestepcheckout\Controller\Index
 */
class Giftwrap extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \MW\Onestepcheckout\Helper\Data
     */
    protected $oscHelper;

    /**
     * Giftwrap constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \MW\Onestepcheckout\Helper\Data $oscHelper
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \MW\Onestepcheckout\Helper\Data $oscHelper,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->oscHelper = $oscHelper;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $requestParams = $this->dataObjectFactory->create([
            'data' => $this->jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);

        $useGiftwrap = $requestParams->getData('use_giftwrap');
        if ($useGiftwrap) {
            $this->checkoutSession->setData('onestepcheckout_giftwrap', 1);
            $giftWrapAmount = $this->oscHelper->getOrderGiftWrapAmount();
            $this->checkoutSession->setData('onestepcheckout_giftwrap_amount', $giftWrapAmount);
        } else {
            $this->checkoutSession->unsetData('onestepcheckout_giftwrap');
            $this->checkoutSession->unsetData('onestepcheckout_giftwrap_amount');
        }
        $quote = $this->checkoutSession->getQuote();
        $this->totalsCollector->collectQuoteTotals($quote);
        $this->quoteRepository->save($quote);
        $this->getResponse()->setBody($this->checkoutSession->getData('onestepcheckout_giftwrap_amount'));
    }
}
