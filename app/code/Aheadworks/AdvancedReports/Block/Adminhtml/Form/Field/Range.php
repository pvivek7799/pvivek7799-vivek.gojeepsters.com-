<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Block\Adminhtml\Form\Field;

/**
 * Class Range
 * @package Aheadworks\AdvancedReports\Block\Adminhtml\Form\Field
 */
class Range extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('range_from', [
            'label' => __('From'),
            'class' => 'required-entry validate-number validate-zero-or-greater',
        ]);
        $this->addColumn('range_to', [
            'label' => __('To'),
            'class' => 'validate-number validate-greater-than-zero',
        ]);
        $this->_addAfter = false;
        $this->setHtmlId('range');
    }
}
