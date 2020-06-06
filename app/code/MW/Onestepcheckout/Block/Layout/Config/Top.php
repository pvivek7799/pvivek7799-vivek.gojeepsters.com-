<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\Layout\Config;

/**
 * Class Top
 * @package MW\Onestepcheckout\Block\Layout\Config
 */
class Top extends \Magento\Framework\View\Element\Template
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

    public function toHtml()
    {
        $topBlockId = $this->_configHelper->getOneStepConfig('top_bottom_block/top_block');
        if ($topBlockId) {
            return $this->getLayout()->createBlock(\Magento\Cms\Block\Block::class)->setBlockId($topBlockId)->toHtml();
        }
        return '';
    }
}
