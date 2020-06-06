<?php
namespace Magecomp\Customprice\Model\Config\Source;

class Listmode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Global')],
            ['value' => 1, 'label' => __('Product Wise')]
        ];
    }
}