<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model\Product\Option\Value;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionAdvancedPricing\Helper\Data as Helper;
use MageWorx\OptionAdvancedPricing\Model\TierPrice as TierPriceModel;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;

class AdditionalHtml
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var Option
     */
    protected $option;

    /**
     * @var TierPriceModel
     */
    protected $tierPriceModel;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param TierPriceModel $tierPriceModel
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        Helper $helper,
        BaseHelper $baseHelper,
        TierPriceModel $tierPriceModel,
        PricingHelper $pricingHelper
    ) {
        $this->helper         = $helper;
        $this->baseHelper     = $baseHelper;
        $this->pricingHelper  = $pricingHelper;
        $this->tierPriceModel = $tierPriceModel;
    }

    /**
     * @param \DOMDocument $dom
     * @param Option $option
     * @return void
     */
    public function getAdditionalHtml($dom, $option)
    {
        if ($this->out($dom, $option)) {
            return;
        }

        $this->dom    = $dom;
        $this->option = $option;

        if ($this->baseHelper->isCheckbox($this->option) || $this->baseHelper->isRadio($this->option)) {
            $this->addHtmlToMultiSelectionOption();
        } elseif ($this->baseHelper->isDropdown($this->option) || $this->baseHelper->isMultiselect($this->option)) {
            $this->addHtmlToSingleSelectionOption();
        }

        libxml_clear_errors();

        return;
    }

    /**
     * @param \DOMDocument $dom
     * @param Option $option
     * @return bool
     */
    protected function out($dom, $option)
    {
        if (!$this->helper->isTierPriceEnabled()
            || !$this->helper->isDisplayTierPriceTableNeeded()
            || !$dom
            || !$option
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param \DOMElement $node
     * @return string
     */
    protected function getInnerHtml(\DOMElement $node)
    {
        $innerHTML = '';
        $children  = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

    /**
     * Get tier price table html
     *
     * @param ProductCustomOptionValuesInterface|Option\Value $value
     * @return string
     */
    protected function getTierPriceHtml($value)
    {
        $tierPrices = $this->tierPriceModel->getSuitableTierPrices($value);
        if (!$tierPrices) {
            return '';
        }
        $index = 1;
        $html  = '<ul id="value_' . $value->getOptionTypeId()
            . '_tier_price" class="prices-tier items" style="display: none">';
        foreach ($tierPrices as $tierPriceItem) {
            $index++;
            $html .= '<li class="item">';
            $html .= __(
                'Buy %1 for %2 each and <strong class="benefit">save<span class="percent tier-%3">&nbsp;%4</span>%</strong>',
                $tierPriceItem['qty'],
                htmlentities($this->pricingHelper->currency($tierPriceItem['price'], true, false)),
                $index,
                $tierPriceItem['percent']
            );
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Get qty input html for checkbox, radio
     *
     * @return void
     */
    protected function addHtmlToMultiSelectionOption()
    {
        if (empty($this->option->getValues())) {
            return;
        }
        $count = 1;
        foreach ($this->option->getValues() as $value) {
            $count++;
            $html = $this->getTierPriceHtml($value);
            if (!$html) {
                continue;
            }

            $tpl = new \DOMDocument('1.0', 'UTF-8');
            $tpl->loadHtml($html);

            $xpath    = new \DOMXPath($this->dom);
            $idString = 'options_' . $this->option->getId() . '_' . $count;
            $input    = $xpath->query("//*[@id='$idString']")->item(0);

            $input->setAttribute('style', 'vertical-align: middle');
            $input->parentNode->appendChild($this->dom->importNode($tpl->documentElement, true));
        }
    }

    /**
     * Get qty input html for dropdown, swatch
     *
     * @return void
     */
    protected function addHtmlToSingleSelectionOption()
    {
        if (empty($this->option->getValues())) {
            return;
        }
        foreach ($this->option->getValues() as $value) {
            $html = $this->getTierPriceHtml($value);
            if (!$html) {
                continue;
            }
            $body = $this->dom->documentElement->firstChild;
            $tpl  = new \DOMDocument();
            $tpl->loadHtml($html);
            $body->appendChild($this->dom->importNode($tpl->documentElement, true));
        }
    }
}
