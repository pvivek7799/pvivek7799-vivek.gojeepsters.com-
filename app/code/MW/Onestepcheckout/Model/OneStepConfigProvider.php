<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model;

/**
 * Class OneStepConfigProvider
 * @package MW\Onestepcheckout\Model
 */
class OneStepConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \MW\Onestepcheckout\Helper\Config
     */
    protected $_configHelper;
    /**
     * @var \MW\Onestepcheckout\Helper\Data
     */
    protected $_oscHelper;

    /**
     * OneStepConfigProvider constructor.
     * @param \MW\Onestepcheckout\Helper\Config $configHelper
     * @param \MW\Onestepcheckout\Helper\Data $oscHelper
     */
    public function __construct(
        \MW\Onestepcheckout\Helper\Config $configHelper,
        \MW\Onestepcheckout\Helper\Data $oscHelper
    ) {
        $this->_configHelper = $configHelper;
        $this->_oscHelper = $oscHelper;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        $output['suggest_address'] = (boolean) $this->_configHelper->getOneStepConfig('general/suggest_address');
        $output['google_api_key'] = $this->_configHelper->getOneStepConfig('general/google_api_key');
        $output['validate_google_api_key'] = $this->_configHelper->validateGoogleApiKey();
        $output['geoip'] = $this->_configHelper->getGeoIpInformation();
        $output['checkout_description'] = $this->_configHelper->getOneStepConfig('general/checkout_description');
        $output['checkout_title'] = $this->_configHelper->getOneStepConfig('general/checkout_title');
        $output['edit_product_qty'] = (boolean)$this->_configHelper->getOneStepConfig('general/edit_product_qty');
        $output['reload_apply_coupon'] = (boolean)$this->_configHelper->getOneStepConfig('general/reload_apply_coupon');
        $output['is_login'] = (boolean) $this->_configHelper->isLogin();
        $output['login_link_title'] = $this->_configHelper->getOneStepConfig('general/login_link_title');
        $output['enable_giftwrap'] = (boolean) $this->_configHelper->getOneStepConfig('giftwrap_information/enable_giftwrap');
        $output['giftwrap_amount'] = $this->_oscHelper->getOrderGiftWrapAmount();
        $output['has_giftwrap'] = (boolean) $this->_oscHelper->hasGiftwrap();
        $output['giftwrap_type'] = $this->_configHelper->getOneStepConfig('giftwrap_information/giftwrap_type');
        $output['enable_items_image'] =(boolean) $this->_configHelper->getOneStepConfig('display_configuration/enable_items_image');
        $output['show_discount'] = (boolean) $this->_configHelper->getOneStepConfig('display_configuration/show_discount');
        $output['show_comment'] = (boolean) $this->_configHelper->getOneStepConfig('display_configuration/show_comment');
//        $output['show_create_account'] = (boolean) $this->_configHelper->getOneStepConfig('display_configuration/show_create_account');
        $output['show_newsletter'] = (boolean) $this->_configHelper->canShowNewsletter();
        $output['newsletter_default_checked'] = (boolean) $this->_configHelper->getOneStepConfig('display_configuration/newsletter_default_checked');
        $output['delivery_time_date'] = (boolean) $this->_configHelper->getOneStepConfig('delivery_information/delivery_time_date');
        $output['delivery_hour'] = $this->_configHelper->getHourConfig();
        $output['is_enable_security_code'] = (boolean) $this->_configHelper->getOneStepConfig('delivery_information/is_enable_security_code');
        $output['default_shipping'] = $this->_configHelper->getDefaultShippingMethod();
        $output['default_payment'] = $this->_configHelper->getDefaultPaymentMethod();
        $output['disable_day'] = $this->_configHelper->getOneStepConfig('delivery_information/disable_day');
        $output['one_step_checkout_is_actived'] = (boolean) $this->_configHelper->getOneStepConfig('general/active');
        $output['top_block'] =$this->_configHelper->getOneStepConfig('top_bottom_block/top_block');
        $output['bottom_block'] =$this->_configHelper->getOneStepConfig('top_bottom_block/bottom_block');
        $output['show_header_footer'] =(boolean)$this->_configHelper->getOneStepConfig('style_management/show_header_footer');
//        $output['checkout_text_font'] =$this->_configHelper->getOneStepConfig('style_management/checkout_text_font');
        $output['show_shipping_address'] = true;
        $output['show_login_link'] = true;
        return $output;
    }
}
