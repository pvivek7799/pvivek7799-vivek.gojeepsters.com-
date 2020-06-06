<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Block;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Config extends Template
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $data = [];

        return $this->jsonEncoder->encode($data);
    }
}
