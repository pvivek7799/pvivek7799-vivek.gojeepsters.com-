<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Helper;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class Data
 * @package MW\Onestepcheckout\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var Config
     */
    protected $_configHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Config $configHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MW\Onestepcheckout\Helper\Config $configHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        parent::__construct($context);
        $this->_subscriberFactory = $subscriberFactory;
        $this->_configHelper = $configHelper;
        $this->_objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @param $email
     */
    public function addSubscriber($email)
    {
        if ($email) {
            $subscriberModel = $this->_subscriberFactory->create()->loadByEmail($email);
            if ($subscriberModel->getId() === null) {
                try {
                    $this->_subscriberFactory->create()->subscribe($email);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->notice($e->getMessage());
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->notice($e->getMessage());
                }
            } elseif ($subscriberModel->getData('subscriber_status') != 1) {
                $subscriberModel->setData('subscriber_status', 1);
                try {
                    $subscriberModel->save();
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->notice($e->getMessage());
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function hasGiftwrap()
    {
        return $this->_checkoutSession->getData('onestepcheckout_giftwrap');
    }

    /**
     * @return bool
     */
    public function isContainDownloadableProduct()
    {
        if ($this->scopeConfig->isSetFlag('catalog/downloadable/disable_guest_checkout')) {
            $quote = $this->getOnepage()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                if (($product = $item->getProduct())
                    && $product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getGiftWrapAmount()
    {
        return $this->_configHelper->getGiftWrapAmount();
    }

    /**
     * @return float|int|mixed
     */
    public function getOrderGiftWrapAmount()
    {
        $amount = $this->getGiftWrapAmount();
        $giftWrapAmount = 0;
        $items = $this->getQuote()->getAllVisibleItems();
        if ($this->getGiftwrapType() == 1) {
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $giftWrapAmount += $amount * ($item->getQty());
            }
        } else {
            $giftWrapAmount = $amount;
        }
        $giftWrapAmount = $this->_priceCurrency->convert($giftWrapAmount);

        return $giftWrapAmount;
    }

    /**
     * @return mixed
     */
    public function getGiftWrapType()
    {
        return $this->_configHelper->getGiftWrapType();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Calculate color brightness
     * @param  string $hex
     * @param  string $percent
     * @return string
     */
    public function colorBrightness($hex, $percent)
    {
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }

        // HEX TO RGB
        $rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        for ($i = 0; $i < 3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent*2);
                $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }

        // RBG to Hex
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }

        return $hash.$hex;
    }
}
