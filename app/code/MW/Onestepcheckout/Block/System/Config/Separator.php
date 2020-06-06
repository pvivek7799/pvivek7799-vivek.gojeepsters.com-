<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\System\Config;

/**
 * class Separator
 *
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class Separator extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $htmlId = $element->getHtmlId();
        $html = '<tr id="row_' . $htmlId . '">'
            . '<td class="label" colspan="3">';

        $marginTop = '30px';
        $customStyle = 'text-align:left;';

        $html .= '<div style="margin-top: ' . $marginTop
            . '; font-weight: bold; border-bottom: 1px solid #dfdfdf;'
            . $customStyle . '">';
        $html .= $element->getLabel();
        $html .= '</div></td></tr>';

        return $html;
    }
}
