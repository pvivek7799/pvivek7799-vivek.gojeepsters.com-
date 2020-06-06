/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'MW_Onestepcheckout/js/action/set-coupon-code',
        'MW_Onestepcheckout/js/action/cancel-coupon',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction) {
        'use strict';
        var totals = quote.getTotals();
        var couponCode = ko.observable(null);
        if (totals()) {
            couponCode(totals()['coupon_code']);
        }
        var isApplied = ko.observable(couponCode() != null);
        var isLoading = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'MW_Onestepcheckout/payment/discount'
            },
            couponCode: couponCode,

            isShowDiscount: ko.observable(window.checkoutConfig.show_discount),
            /**
             * Applied flag
             */
            isApplied: isApplied,
            isLoading: isLoading,
            /**
             * Coupon code application procedure
             */
            apply: function() {
                if (this.validate()) {
                    isLoading(true);
                    setCouponCodeAction(couponCode(), isApplied, isLoading);
                }
            },
            /**
             * Cancel using coupon
             */
            cancel: function() {
                if (this.validate()) {
                    isLoading(true);
                    couponCode('');
                    cancelCouponAction(isApplied, isLoading);
                }
            },


            /**
             * Coupon form validation
             *
             * @returns {boolean}
             */
            validate: function() {
                var form = '#discount-form';
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);
