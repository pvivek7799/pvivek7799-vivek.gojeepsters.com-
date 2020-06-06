<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\Layout;

/**
 * Class Style
 * @package MW\Onestepcheckout\Block\Layout
 */
class Style extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MW\Onestepcheckout\Helper\Config
     */
    protected $_configHelper;

    /**
     * Style constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MW\Onestepcheckout\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MW\Onestepcheckout\Helper\Config $configHelper,
        array $data
    ) {
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|string
     */
    public function getHeadingTextColor()
    {
        return $this->_configHelper->getHeadingTextColor();
    }

    /**
     * @return mixed|string
     */
    public function getSummaryColor()
    {
        return $this->_configHelper->getSummaryColor();
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutBackgroundColor()
    {
        return $this->_configHelper->getCheckoutBackgroundColor();
    }

    /**
     * @return mixed|string
     */
    public function getStepNumberIconColor()
    {
        return $this->_configHelper->getStepNumberIconColor();
    }

    /**
     * @return mixed|string
     */
    public function getButtonColor()
    {
        return $this->_configHelper->getButtonColor();
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutFont()
    {
        return $this->_configHelper->getCheckoutFont();
    }
}
