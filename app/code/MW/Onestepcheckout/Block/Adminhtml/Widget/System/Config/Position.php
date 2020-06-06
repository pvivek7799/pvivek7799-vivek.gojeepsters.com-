<?php

/**
 * Mage-World
 *
 * @category    Mage-World
 * @package     MW
 * @author      Mage-world Developer
 *
 * @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config;

/**
 * Class Position
 * @package MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config
 */
class Position extends \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\ConfigAbstract
{
    /**
     * @var string
     */
    protected $_template = 'MW_Onestepcheckout::system/config/position.phtml';

    /**
     * @return bool
     */
    public function isHasPrefixName()
    {
        $prefixName = $this->_scopeConfig->getValue('customer/address/prefix_options');
        if ($prefixName) {
            return $prefixName;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHasMiddleName()
    {
        $middleName = $this->_scopeConfig->getValue('customer/address/middlename_show');
        if ($middleName) {
            return $middleName;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHasSuffixName()
    {
        $suffix = $this->_scopeConfig->getValue('customer/address/suffix_show');
        if ($suffix) {
            return $suffix;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHasVatId()
    {
        $taxVat = $this->_scopeConfig->getValue('customer/create_account/vat_frontend_visibility');
        if ($taxVat) {
            return $taxVat;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHasGender()
    {
        $gender = $this->_scopeConfig->getValue('customer/address/gender_show');
        if ($gender) {
            return $gender;
        } else {
            return false;
        }
    }

    public function isHasCompany()
    {
        $value = $this->_scopeConfig->getValue('customer/address/company_show');
        if ($value) {
            return $value;
        } else {
            if ($value == '') {
                $value = 'opt';
                return $value;
            }
            return false;
        }
    }

    public function isHasTelephone()
    {
        $value = $this->_scopeConfig->getValue('customer/address/telephone_show');
        if ($value) {
            return $value;
        } else {
            if ($value == '') {
                $value = 'req';
                return $value;
            }
            return false;
        }
    }

    public function isHasFax()
    {
        $value = $this->_scopeConfig->getValue('customer/address/fax_show');
        if ($value) {
            return $value;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        $fieldOptions = [];
//        $fieldOptions['0'] =  __('Null');
        $fieldOptions['firstname'] =  ['label' => __('First Name'), 'required' => 1];
        $fieldOptions['lastname'] =  ['label' => __('Last Name'), 'required' => 1];
        $fieldOptions['street'] =  ['label' => __('Address'), 'required' => 1];
        $fieldOptions['country_id'] = ['label' => __('Country'), 'required' => 1];
        $fieldOptions['region_id'] =  ['label' => __('State/Province'), 'required' => 1];
        $fieldOptions['city'] =  ['label' => __('City'), 'required' => 1];
        $fieldOptions['postcode'] =  ['label' => __('Zip/Postal Code'), 'required' => 1];

        if ($this->isHasCompany()) {
            $fieldOptions['company'] =  ['label' => __('Company'), 'required' => ($this->isHasCompany()=='req')?1:0];
        }

        if ($this->isHasTelephone()) {
            $fieldOptions['telephone'] =  ['label' => __('Telephone'), 'required' => ($this->isHasTelephone()=='req')?1:0];
        }

        if ($this->isHasSuffixName()) {
            $fieldOptions['suffix'] =  ['label' => __('Suffix Name'), 'required' => ($this->isHasSuffixName()=='req')?1:0];
        }

        if ($this->isHasMiddleName()) {
            $fieldOptions['middlename'] =  ['label' => __('Middle Name'), 'required' => ($this->isHasMiddleName()=='req')?1:0];
        }

        if ($this->isHasPrefixName()) {
            $fieldOptions['prefix'] =  ['label' => __('Prefix Name'), 'required' => ($this->isHasPrefixName()=='req')?1:0];
        }

        if ($this->isHasVatId()) {
            $fieldOptions['vat_id'] =  ['label' => __('Tax/VAT number'), 'required' => ($this->isHasVatId()=='req')?1:0];
        }

//        if ($this->isHasGender()) {
//            $fieldOptions['gender'] =  ['label' => __('Gender'), 'required' => ($this->isHasGender()=='req')?1:0];
//        }

//        if ($this->isHasFax()) {
//            $fieldOptions['gender'] =  ['label' => __('Fax'), 'required' => ($this->isHasFax()=='req')?1:0];
//        }

        return $fieldOptions;
    }

    /**
     * @return array
     */
    public function getUsedField()
    {
        $usedField = [];
        for ($i = 0; $i < 20; $i++) {
            $field = $this->getDefaultField($i, $this->getScope(), $this->getScopeId());
            $usedField[$i] = $field;
        }
        return $usedField;
    }

    /**
     * @return array
     */
    public function getUsedFieldOptions()
    {
        $usedOptions = [];
        $useFields = $this->getUsedField();
        $allFields = $this->getFieldOptions();
        foreach ($allFields as $index => $field) {
            if (in_array($index, $useFields)) {
                $usedOptions[$index] = $field;
            }
        }

        return $usedOptions;
    }

    /**]
     * @return array
     */
    public function getAvaiableFieldOptions()
    {
        $avaiableOptions = [];
        $useFields = $this->getUsedField();
        $allFields = $this->getFieldOptions();
        foreach ($allFields as $index => $field) {
            if (!in_array($index, $useFields)) {
                $avaiableOptions[$index] = $field;
            }
        }

        return $avaiableOptions;
    }

    /**
     * @param $number
     *
     * @return mixed
     */
    public function getDefaultField($number, $scope, $scopeId)
    {
        return $this->_scopeConfig
            ->getValue('mw_onestepcheckout/field_position_management/row_' . $number, $scope, $scopeId);
    }

    /**
     * @param $number
     * @param $scope
     * @param $scopeId
     *
     * @return mixed
     */
    public function getFieldEnableBackEnd($number, $scope, $scopeId)
    {
        $configCollection = $this->_dataConfigCollectionFactory->create()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->addFieldToFilter('path', 'mw_onestepcheckout/field_position_management/row_' . $number);

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
    public function getElementHtmlId($number)
    {
        return 'onestepcheckout_field_position_management_row_' . $number;
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getElementHtmlName($number)
    {
        return 'groups[field_position_management][fields][row_' . $number . '][value]';
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getCheckBoxElementHtmlId($number)
    {
        return 'onestepcheckout_field_position_management_row_' . $number . '_inherit';
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getCheckBoxElementHtmlName($number)
    {
        return 'groups[field_position_management][fields][row_' . $number . '][inherit]';
    }
}
