<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\System\Config;

/**
 *
 *
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class FieldStyle extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'style_block',
            \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\Style::class
        );

        return parent::_prepareLayout();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getChildHtml('style_block');
    }
}
