<?php
namespace Magecomp\Customprice\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_CUSTOM_IS_ENABLED = 'customprice/module_options/enable';
    const CONFIG_CUSTOM_PRODUCT_OPTION = 'customprice/module_options/list_mode';
    const CONFIG_CUSTOM_MINMUN_PRODUCT_PRICE = 'customprice/module_options/minimumprice';
    const CONFIG_CUSTOM_FRONTEND_LABEL = 'customprice/module_options/frontend_label';


    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_CUSTOM_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }
    public function isProductOption()
    {
        if($this->isModuleEnabled()){
            return $this->scopeConfig->getValue(self::CONFIG_CUSTOM_PRODUCT_OPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function Frontendlabel()
    {
        if($this->isModuleEnabled()) {
            return $this->scopeConfig->getValue(self::CONFIG_CUSTOM_FRONTEND_LABEL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        }
    }
    public function getCustomPrice($_product)
    {
        if(!$this->isModuleEnabled()){
            return 0;
        }
        if($this->isProductOption()== '0'){
            return $this->scopeConfig->getValue(self::CONFIG_CUSTOM_MINMUN_PRODUCT_PRICE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        if($_product->getCustompriceEnabledisable()){
            return $_product->getCustompriceProductwise();
        }
        return 0;
   }
}