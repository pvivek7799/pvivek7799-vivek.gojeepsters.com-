<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Model;

use MageWorx\OptionBase\Api\ValidatorInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\CustomerVisibility as VisibilityHelper;
use MageWorx\OptionVisibility\Helper\Data as Helper;

class Validator implements ValidatorInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var VisibilityHelper
     */
    protected $visibilityHelper;

    /**
     * @param BaseHelper $baseHelper
     * @param VisibilityHelper $visibilityHelper
     */
    public function __construct(
        BaseHelper $baseHelper,
        VisibilityHelper $visibilityHelper
    ) {
        $this->baseHelper       = $baseHelper;
        $this->visibilityHelper = $visibilityHelper;
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
        $option = $subject->getOption();


        return $this->process($subject->getOption());
    }

    /**
     * Run validation process for cart and checkout
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Catalog\Api\Data\CustomOptionInterface $option
     * @return bool
     */
    public function canValidateCartCheckout($product, $option)
    {
        return $this->process($option);
    }

    /**
     * @param $option
     * @return bool
     */
    protected function process($option)
    {
        if (!empty($option[Helper::KEY_DISABLED]) || !empty($option[Helper::KEY_DISABLED_BY_VALUES])) {
            return false;
        }

        $values = $option->getValues() ?: [];

        foreach ($values as $value) {
            if (!empty($value[Helper::KEY_DISABLED])) {
                return false;
            }
        }

        return true;
    }
}