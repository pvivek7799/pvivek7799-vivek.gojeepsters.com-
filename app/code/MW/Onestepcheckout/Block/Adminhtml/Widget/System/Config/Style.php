<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config;

/**
 * Class Style
 * @package MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config
 */
class Style extends \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\ConfigAbstract
{
    /**
     * @var string
     */
    protected $_template = 'MW_Onestepcheckout::system/config/style.phtml';

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return [
            'custom'   => __('Custom'),
        ];
    }

    /**
     * @param $number
     *
     * @return mixed
     */
    public function getDefaultField($path, $scope, $scopeId)
    {
        return $this->_scopeConfig->getValue($path, $scope, $scopeId);
    }

    /**
     * @param $number
     * @param $scope
     * @param $scopeId
     *
     * @return mixed
     */
    public function getFieldEnableBackEnd($path, $scope, $scopeId)
    {
        $configCollection = $this->_dataConfigCollectionFactory->create()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->addFieldToFilter('path', $path);
        if (count($configCollection)) {
            return $configCollection->getFirstItem()->getData('value');
        } else {
            return null;
        }
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getElementHtmlId($field)
    {
        return 'onestepcheckout_style_management_' . $field;
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getElementHtmlName($configName)
    {
        return 'groups[style_management][fields][' . $configName . '][value]';
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getCheckBoxElementHtmlId($configName)
    {
        return 'onestepcheckout_style_management_' . $configName . '_inherit';
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getCheckBoxElementHtmlName($configName)
    {
        return 'onestepcheckout_style_management_' . $configName . '][inherit]';
    }

    /**
     * @param $color
     *
     * @return string
     */
    public function getImageColor($color)
    {
        return $this->getViewFileUrl('MW_Onestepcheckout::images/style/' . $color . '.png');
    }
}
