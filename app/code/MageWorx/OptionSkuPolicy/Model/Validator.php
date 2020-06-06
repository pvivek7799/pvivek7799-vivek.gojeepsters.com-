<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSkuPolicy\Model;

use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use MageWorx\OptionSkuPolicy\Helper\Data as Helper;
use MageWorx\OptionBase\Api\ValidatorInterface;

class Validator implements ValidatorInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Run validation process for add to cart action
     *
     * @param DefaultType $subject
     * @param array $values
     * @return bool
     */
    public function canValidateAddToCart($subject, $values)
    {
        if (!$this->helper->isEnabledSkuPolicy()) {
            return true;
        }
        $skuPolicy = $this->getSkuPolicy($subject->getProduct(), $subject->getOption());

        if (($skuPolicy == Helper::SKU_POLICY_INDEPENDENT || $skuPolicy == Helper::SKU_POLICY_GROUPED) && $values) {
            return false;
        }

        return true;

    }

    /**
     * Run validation process for cart and checkout
     *
     * @param ProductInterface $product
     * @param CustomOptionInterface $option
     * @return bool
     */
    public function canValidateCartCheckout($product, $option)
    {
        if (!$this->helper->isEnabledSkuPolicy()) {
            return true;
        }
        $skuPolicy = $this->getSkuPolicy($product, $option);

        if ($skuPolicy == Helper::SKU_POLICY_INDEPENDENT || $skuPolicy == Helper::SKU_POLICY_GROUPED) {
            return false;
        }

        return true;
    }

    /**
     * Get SKU policy for validation
     *
     * @param ProductInterface $product
     * @param CustomOptionInterface $option
     * @return string
     */
    protected function getSkuPolicy($product, $option)
    {
        $skuPolicy = $option->getSkuPolicy();
        if ($option->getSkuPolicy() == Helper::SKU_POLICY_USE_CONFIG) {
            if ($product->getSkuPolicy() == Helper::SKU_POLICY_USE_CONFIG) {
                $skuPolicy = $this->helper->getDefaultSkuPolicy();
            } else {
                $skuPolicy = $product->getSkuPolicy();
            }
        }

        return $skuPolicy;
    }
}
