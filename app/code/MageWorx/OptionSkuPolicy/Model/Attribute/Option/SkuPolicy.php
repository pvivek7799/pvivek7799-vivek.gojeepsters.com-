<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSkuPolicy\Model\Attribute\Option;

use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionSkuPolicy\Helper\Data as Helper;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class SkuPolicy extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var mixed
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_SKU_POLICY;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        $map = [
            '0' => Helper::SKU_POLICY_USE_CONFIG,
            '1' => Helper::SKU_POLICY_STANDARD,
            '2' => Helper::SKU_POLICY_INDEPENDENT,
            '3' => Helper::SKU_POLICY_GROUPED,
            '4' => Helper::SKU_POLICY_REPLACEMENT,
        ];
        if (!isset($data['sku_policy']) || !isset($map[$data['sku_policy']])) {
            return Helper::SKU_POLICY_USE_CONFIG;
        }
        return $map[$data['sku_policy']];
    }
}
