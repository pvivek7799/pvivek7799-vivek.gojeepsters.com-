<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Model\System\Config\Source;

/**
 * Class Payment
 *
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class Payment implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $_modelTypeOnepage;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelperData;

    /**
     * Payment constructor.
     *
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Payment\Helper\Data         $paymentHelperData
     */
    public function __construct(
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Payment\Helper\Data $paymentHelperData
    ) {
        $this->_modelTypeOnepage = $onePage;
        $this->_paymentHelperData = $paymentHelperData;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('-- Please select --'),
                'value' => '',
            ],
        ];

        $quote = $this->_modelTypeOnepage->getQuote();
        $store = $quote ? $quote->getStoreId() : null;
        $methods = $this->_paymentHelperData->getStoreMethods($store, $quote);
        foreach ($methods as $key => $method) {
            $options[] = [
                'label' => $method->getTitle(),
                'value' => $method->getCode(),
            ];
        }

        return $options;
    }
}
