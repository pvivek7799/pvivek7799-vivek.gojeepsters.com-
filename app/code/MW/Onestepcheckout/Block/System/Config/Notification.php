<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\System\Config;

/**
 * class FieldPosition
 *
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class Notification extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'notification_block',
            \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\Notification::class
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
        return $this->getChildHtml('notification_block');
    }
}
