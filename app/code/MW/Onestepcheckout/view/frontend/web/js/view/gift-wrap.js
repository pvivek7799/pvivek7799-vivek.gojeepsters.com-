/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*global define*/
define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'MW_Onestepcheckout/js/model/core/request',
        'MW_Onestepcheckout/js/model/core/url-builder',
        'Magento_Catalog/js/price-utils',
        'MW_Onestepcheckout/js/view/summary/gift-wrap',
        'MW_Onestepcheckout/js/action/reload-shipping-method',
        'Magento_Checkout/js/action/get-payment-information',
        'MW_Onestepcheckout/js/model/gift-wrap'
    ],
    function($, Component, ko, Request, UrlBuilder, priceUtils, giftWrap, reloadShippingMethod, getPaymentInformation, giftWrapModel) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                this.giftWrapAmountPrice = ko.computed(function () {
                    var priceFormat = window.checkoutConfig.priceFormat;
                    return priceUtils.formatPrice(self.giftWrapValue(), priceFormat)
                });
            },

            isGiftWrap: ko.observable(window.checkoutConfig.enable_giftwrap),

            giftWrapValue: ko.computed(function () {
                return giftWrapModel.getGiftWrapAmount();
            }),
       
            defaults: {
                template: 'MW_Onestepcheckout/gift-wrap'
            },

            formatPrice: function(amount) {
                amount = parseFloat(amount);
                var priceFormat = window.checkoutConfig.priceFormat;
                return priceUtils.formatPrice(amount, priceFormat)
            },

            setGiftWrapValue: function (amount) {
                this.giftWrapValue(amount);
            },

            afterRenderGiftWrap: function () {
                var params = {
                    use_giftwrap: this.isChecked()
                };
                var self = this;
                var url = UrlBuilder.createUrl("onestepcheckout/index/giftwrap", {}, false);
                Request.send(url, "post", params).done(function (result) {
                    window.checkoutConfig.giftwrap_amount = result;
                    reloadShippingMethod();
                    getPaymentInformation().done(function () {
                        if (self.isChecked()) {
                            giftWrapModel.setGiftWrapAmount(result);
                            giftWrapModel.setIsWrap(true);
                        } else {
                            giftWrapModel.setIsWrap(false);
                        }
                    });
                });
                return true;
            },

            addGiftWrap: function () {
                var params = {
                    use_giftwrap: !this.isChecked()
                };
                var self = this;
                var url = UrlBuilder.createUrl("onestepcheckout/index/giftwrap", {}, false);
                Request.send(url, "post", params).done(function (result) {
                    window.checkoutConfig.giftwrap_amount = result;
                    reloadShippingMethod();
                    getPaymentInformation().done(function () {
                        if (self.isChecked()) {
                            giftWrapModel.setGiftWrapAmount(result);
                            giftWrapModel.setIsWrap(true);
                        } else {
                            giftWrapModel.setIsWrap(false);
                        }
                    });
                });
                return true;
            },

            isChecked: ko.observable(window.checkoutConfig.has_giftwrap)
            
        });
    }
);
