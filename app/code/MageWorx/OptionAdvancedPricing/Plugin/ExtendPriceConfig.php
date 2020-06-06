<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Plugin;

use MageWorx\OptionAdvancedPricing\Helper\Data as Helper;
use MageWorx\OptionAdvancedPricing\Model\SpecialPrice as SpecialPriceModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Json\DecoderInterface;

class ExtendPriceConfig extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SpecialPriceModel
     */
    protected $specialPriceModel;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param Helper $helper
     * @param SpecialPriceModel $specialPriceModel
     * @param PriceCurrencyInterface $priceCurrency
     * @param DecoderInterface $jsonDecoder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        Helper $helper,
        SpecialPriceModel $specialPriceModel,
        PriceCurrencyInterface $priceCurrency,
        DecoderInterface $jsonDecoder,
        array $data = []
    ) {
        $this->helper            = $helper;
        $this->specialPriceModel = $specialPriceModel;
        $this->priceCurrency     = $priceCurrency;
        $this->jsonDecoder       = $jsonDecoder;
        parent::__construct($context, $pricingHelper, $catalogData, $jsonEncoder, $option, $registry, $arrayUtils);
    }

    /**
     * Extend price config with suitable special price on frontend
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundGetJsonConfig($subject, $proceed)
    {
        if (!$this->helper->isSpecialPriceEnabled() || !$subject->hasOptions()) {
            return $proceed();
        }

        $defaultConfig  = $this->jsonDecoder->decode($proceed());
        $extendedConfig = $defaultConfig;

        foreach ($subject->getOptions() as $option) {
            /* @var $option \Magento\Catalog\Model\Product\Option */
            $values = $option->getValues();
            if (!empty($values)) {
                foreach ($values as $valueId => $value) {
                    $config       = [];
                    $specialPrice = $this->specialPriceModel->getActualSpecialPrice($value);
                    if ($specialPrice === null) {
                        continue;
                    }
                    $config['title']                            = $value->getTitle();
                    $config['prices']['oldPrice']['amount']     =
                        $defaultConfig[$option->getId()][$valueId]['prices']['oldPrice']['amount'];
                    $config['valuePrice']                       = $this->priceCurrency->format(
                        $config['prices']['oldPrice']['amount'],
                        false
                    );
                    $config['prices']['basePrice']['amount']    = $specialPrice;
                    $config['prices']['finalPrice']['amount']   = $specialPrice;
                    $config['special_price_display_node']       = $this->helper->getSpecialPriceDisplayNode(
                        $this->priceCurrency->format($specialPrice, false),
                        $this->priceCurrency->format($config['prices']['oldPrice']['amount'], false),
                        $this->specialPriceModel->getSpecialPriceItem()
                    );
                    $extendedConfig[$option->getId()][$valueId] = array_merge(
                        $defaultConfig[$option->getId()][$valueId],
                        $config
                    );
                }
            } else {
                $extendedConfig[$option->getId()] = $defaultConfig[$option->getId()];
            }
        }

        return $this->_jsonEncoder->encode($extendedConfig);
    }
}
