<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MageWorx\OptionBase\Model\Product\Attributes as ProductAttributes;

class AddProductAttributes implements ObserverInterface
{
    /**
     * @var ProductAttributes
     */
    protected $productAttributes;

    /**
     * @param ProductAttributes $productAttributes
     */
    public function __construct(
        ProductAttributes $productAttributes
    ) {
        $this->productAttributes = $productAttributes;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getData('product');
        if (!$product || !$product instanceof Product) {
            return $this;
        }

        $attributes = $this->productAttributes->getData();
        foreach ($attributes as $attribute) {
            $item = $attribute->getItemByProduct($product);
            foreach ($attribute->getKeys() as $attributeKey) {

                if ($attributeKey == 'absolute_price') {
                    $defaultValue = strval($attribute->getDefaultValue($attributeKey));
                } elseif ($attributeKey == 'sku_policy') {
                    $defaultValue = 'use_config';
                } else {
                    $defaultValue = '0';
                }
                $product[$attributeKey] = isset($item[$attributeKey]) ? $item[$attributeKey] : $defaultValue;
            }
        }

        return $this;
    }
}
