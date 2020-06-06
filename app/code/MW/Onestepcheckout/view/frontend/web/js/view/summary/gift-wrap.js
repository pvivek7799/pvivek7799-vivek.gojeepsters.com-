/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'MW_Onestepcheckout/js/model/gift-wrap'
    ],
    function ($, ko, Component, giftWrap) {
        return Component.extend({
            getPureValue: ko.observable(window.checkoutConfig.giftwrap_amount),

            initialize: function () {
                this._super();
                var self = this;
                this.isGiftWrapDisplay = ko.computed(function () {
                    return (giftWrap.getIsWrap());
                });
            },

            defaults: {
                template: 'MW_Onestepcheckout/summary/gift-wrap'
            },


            getValue: function () {
                return this.getFormattedPrice(giftWrap.getGiftWrapAmount());
            }

        });
    }
);
