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

namespace MW\Onestepcheckout\Block\Checkout;

/**
 * Class LayoutProcessor
 * @package MW\Onestepcheckout\Block\Checkout
 */
class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    /**
     * @var \MW\Onestepcheckout\Helper\Config
     */
    protected $_helperConfig;

    /**
     * @var \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\Position
     */
    protected $positionConfig;

    /**
     * LayoutProcessor constructor.
     * @param \MW\Onestepcheckout\Helper\Config $helperConfig
     * @param \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\Position $positionConfig
     */
    public function __construct(
        \MW\Onestepcheckout\Helper\Config $helperConfig,
        \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\Position $positionConfig
    ) {
        $this->_helperConfig = $helperConfig;
        $this->positionConfig = $positionConfig;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->_helperConfig->isEnabledOneStep()) {
//            if(isset($jsLayout['components']['checkout']['children']['authentication'])) {
//                unset($jsLayout['components']['checkout']['children']['authentication']);
//            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount'])) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount']);
            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
                $childs = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $this->processShippingInput($childs);
            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'])) {
                $childs = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] = $this->processBillingInput($childs);
            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['giftCardAccount'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['giftCardAccount']['component'] = "MW_Onestepcheckout/js/view/payment/gift-card-account";
            }
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['giftCardAccount'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['giftCardAccount']['component'] = "MW_Onestepcheckout/js/view/summary/gift-card-account";
            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['storeCredit'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['storeCredit']['component'] = "MW_Onestepcheckout/js/view/payment/customer-balance";
            }
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['customerbalance'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['customerbalance']['component'] = "MW_Onestepcheckout/js/view/summary/customer-balance";
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['customerbalance']['config']['template'] = "MW_Onestepcheckout/summary/customer-balance";
            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['reward'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['reward']['component'] = "MW_Onestepcheckout/js/view/payment/reward";
            }
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['before_grandtotal']['children']['reward'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['before_grandtotal']['children']['reward']['component'] = "MW_Onestepcheckout/js/view/summary/reward";
            }

            /* Change template summary*/
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['component'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['component'] = "MW_Onestepcheckout/js/view/summary";
            }

            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['component'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['component'] = "MW_Onestepcheckout/js/view/summary/totals";
            }

            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['config']['template'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['config']['template'] = "MW_Onestepcheckout/summary/totals";
            }

            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['config']['template'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['config']['template'] = "MW_Onestepcheckout/summary/totals";
            }

            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['component'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['component'] = "MW_Onestepcheckout/js/view/summary/cart-items";
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['displayArea'] = "item-review";
            }

            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']['component'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']['component'] = 'MW_Onestepcheckout/js/view/summary/item/details';
            }

            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']['children']['thumbnail']['component'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']['children']['thumbnail']['component'] = 'MW_Onestepcheckout/js/view/summary/item/details/thumbnail';
            }

            /* End change summary*/

            if (!$this->_helperConfig->getOneStepConfig('display_configuration/show_comment')) {
                if (isset($jsLayout['components']['checkout']['children']['order-comment'])) {
                    unset($jsLayout['components']['checkout']['children']['order-comment']);
                }
            }

            if (!$this->_helperConfig->getOneStepConfig('delivery_information/delivery_time_date')) {
                if (isset($jsLayout['components']['checkout']['children']['delivery-date'])) {
                    unset($jsLayout['components']['checkout']['children']['delivery-date']);
                }
            }

            if (!$this->_helperConfig->getOneStepConfig('display_configuration/show_discount')) {
                if (isset($jsLayout['components']['checkout']['children']['discount'])) {
                    unset($jsLayout['components']['checkout']['children']['discount']);
                }
            }

            if (!$this->_helperConfig->isEnableGiftWrap()) {
                if (isset($jsLayout['components']['checkout']['children']['gift-wrap'])) {
                    unset($jsLayout['components']['checkout']['children']['gift-wrap']);
                }
            }

            if (!$this->_helperConfig->canShowNewsletter()) {
                if (isset($jsLayout['components']['checkout']['children']['sign-up-newsletter'])) {
                    unset($jsLayout['components']['checkout']['children']['sign-up-newsletter']);
                }
            }

            if (!$this->_helperConfig->enableGiftMessage()) {
                if (isset($jsLayout['components']['checkout']['children']['giftOptionsCart'])) {
                    unset($jsLayout['components']['checkout']['children']['giftOptionsCart']);
                }
            }

            if ($this->_helperConfig->isEnableLoginWithAmazon()) {
                $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress'];
                $paymentConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment'];
                $amazonHelper = \Magento\Framework\App\ObjectManager::getInstance()->create(\Amazon\Core\Helper\Data::class);
                if ($amazonHelper->isPwaEnabled()) {
                    $shippingConfig['component'] = 'MW_Onestepcheckout/js/integrate/amazonpay/view/shipping';
                    $shippingConfig['children']['customer-email']['component'] = 'Amazon_Payment/js/view/form/element/email';
                    $shippingConfig['children']['address-list']['component'] = 'Amazon_Payment/js/view/shipping-address/list';
                    $shippingConfig['children']['shipping-address-fieldset']['children']
                    ['inline-form-manipulator']['component'] = 'Amazon_Payment/js/view/shipping-address/inline-form';

                    $paymentConfig['children']['payments-list']['component'] = 'Amazon_Payment/js/view/payment/list';
                } else {
                    unset($shippingConfig['children']['customer-email']['children']['amazon-button-region']);
                    unset($shippingConfig['children']['before-form']['children']['amazon-widget-address']);

                    unset($paymentConfig['children']['renders']['children']['amazon_payment']);
                    unset($paymentConfig['children']['beforeMethods']['children']['amazon-sandbox-simulator']);
                    unset($paymentConfig['children']['payments-list']['children']['amazon_payment-form']);
                }
            }
        }

        return $jsLayout;
    }

    /**
     * @param $childs
     * @return mixed
     */
    public function processShippingInput($childs)
    {
        if (count($childs) > 0) {
            foreach ($childs as $key => $child) {
                if (!in_array($key, $this->positionConfig->getUsedField())) {
                    unset($childs[$key]);
                    continue;
                }

                if (isset($child['config']['template']) && $child['config']['template'] == 'ui/group/group' && isset($child['children'])) {
                    $childs[$key]['component'] = "MW_Onestepcheckout/js/view/form/components/group";
                    if (isset($childs[$key]['children'])) {
                        $children = $childs[$key]['children'];
                        $newChildren = array();
                        foreach ($children as $item) {
                            $item['config']['component'] = "MW_Onestepcheckout/js/view/form/element/input";
                            $item['config']['elementTmpl'] = "MW_Onestepcheckout/form/element/shipping-input";
                            $newChildren[] = $item;
                        }
                        $childs[$key]['children'] = $newChildren;
                    }
                }
                if (isset($child['config']) && isset($child['config']['elementTmpl']) && $child['config']['elementTmpl'] == "ui/form/element/input") {
                    if ($key != 'postcode') {
                        $childs[$key]['config']['component'] = "MW_Onestepcheckout/js/view/form/element/input";
                    } else {
                        $childs[$key]['config']['component'] = "MW_Onestepcheckout/js/view/form/element/post-code";
                    }
                    $childs[$key]['config']['elementTmpl'] = "MW_Onestepcheckout/form/element/shipping-input";
                }
                if (isset($child['config']) && isset($child['config']['template']) && $child['config']['template'] == "ui/form/field") {
                    $childs[$key]['config']['template'] = "MW_Onestepcheckout/js/form/components/field";
                    $childs[$key]['config']['template'] = "MW_Onestepcheckout/form/field";
                }
                $sortOrder = $this->_helperConfig->getFieldSortOrder($key);
                if ($sortOrder !== false) {
                    $childs[$key]['sortOrder'] = strval($sortOrder);
                }
            }
        }
        return $childs;
    }

    /**
     * @param $payments
     * @return mixed
     */
    public function processBillingInput($payments)
    {
        if (count($payments) > 0) {
            foreach ($payments as $paymentCode => $paymentComponent) {
                if (isset($paymentComponent['component']) && $paymentComponent['component'] != "Magento_Checkout/js/view/billing-address") {
                    continue;
                }
                $paymentComponent['component'] = "MW_Onestepcheckout/js/view/billing-address";
                if (isset($paymentComponent['children']['form-fields']['children'])) {
                    $childs = $paymentComponent['children']['form-fields']['children'];
                    foreach ($childs as $key => $child) {
			if (!in_array($key, $this->positionConfig->getUsedField())) {
                    	    unset($childs[$key]);
                    	    continue;
                	}

                        if (isset($child['config']['template']) && $child['config']['template'] == 'ui/group/group' && isset($child['children'])) {
                            $childs[$key]['component'] = "MW_Onestepcheckout/js/view/form/components/group";
                            if (isset($childs[$key]['children'])) {
                                $children = $childs[$key]['children'];
                                $newChildren = array();
                                foreach ($children as $item) {
                                    $item['config']['elementTmpl'] = "MW_Onestepcheckout/form/element/input";
                                    $newChildren[] = $item;
                                }
                                $childs[$key]['children'] = $newChildren;
                            }
                        }
                        if (isset($child['config']) && isset($child['config']['elementTmpl']) && $child['config']['elementTmpl'] == "ui/form/element/input") {
                            $childs[$key]['config']['elementTmpl'] = "MW_Onestepcheckout/form/element/input";
                        }
                        if (isset($child['config']) && isset($child['config']['template']) && $child['config']['template'] == "ui/form/field") {
                            $childs[$key]['config']['template'] = "MW_Onestepcheckout/form/field";
                        }
                        $sortOrder = $this->_helperConfig->getFieldSortOrder($key);
                        if ($sortOrder !== false) {
                            $childs[$key]['sortOrder'] = $sortOrder;
                        }
                    }
                    $paymentComponent['children']['form-fields']['children'] = $childs;
                    $payments[$paymentCode] = $paymentComponent;
                }
            }
        }
        return $payments;
    }
}
