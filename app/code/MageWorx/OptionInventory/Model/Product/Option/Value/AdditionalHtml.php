<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Model\Product\Option\Value;

use Magento\Catalog\Model\Product\Option;
use MageWorx\OptionInventory\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionInventory\Helper\Stock as StockHelper;

class AdditionalHtml
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StockHelper
     */
    protected $stockHelper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param StockHelper $stockHelper
     */
    public function __construct(
        Helper $helper,
        StockHelper $stockHelper,
        BaseHelper $baseHelper
    ) {
        $this->helper      = $helper;
        $this->baseHelper  = $baseHelper;
        $this->stockHelper = $stockHelper;
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

        $isDisabledOutOfStockOptions = $this->helper->isDisabledOutOfStockOptions();

        $xpath = new \DOMXPath($dom);
        $count = 1;
        foreach ($option->getValues() as $value) {
            $count++;
            if ($this->baseHelper->isCheckbox($option) || $this->baseHelper->isRadio($option)) {
                $element       = $xpath
                    ->query('//div/div[descendant::label[@for="options_' . $option->getId() . '_' . $count . '"]]')
                    ->item(0);
                $elementSelect = $element
                    ->getElementsByTagName('input')
                    ->item(0);
                $elementTitle  = $xpath
                    ->query('//label[@for="options_' . $option->getId() . '_' . $count . '"]')
                    ->item(0);
            } elseif ($this->baseHelper->isDropdown($option) || $this->baseHelper->isMultiselect($option)) {
                $element = $elementSelect = $elementTitle = $xpath
                    ->query('//option[@value="' . $value->getId() . '"]')
                    ->item(0);
            }

            $isOutOfStockOption = $this->stockHelper->isOutOfStockOption($value);
            if ($isOutOfStockOption) {
                if (!$isDisabledOutOfStockOptions) {
                    $this->stockHelper->hideOutOfStockOption($element);
                    continue;
                } else {
                    $this->stockHelper->disableOutOfStockOption($elementSelect);
                }
            }

            $stockMessage = $this->stockHelper->getStockMessage($value, $option->getProductId());
            if ($stockMessage) {
                $this->stockHelper->setStockMessage($dom, $elementTitle, $stockMessage);
            }
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
        return (!$dom || !$option);
    }
}
