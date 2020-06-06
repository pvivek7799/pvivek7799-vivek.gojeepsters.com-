/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*jshint browser:true jquery:true*/
/*global alert*/
/**
 * Customer balance summary block info
 */
define(
    [
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_CustomerBalance/js/view/cart/summary/customer-balance',
        'Magento_Checkout/js/action/get-payment-information',
        'MW_Onestepcheckout/js/action/showLoader'
    ],
    function (Request, CustomerBalance, getPaymentInformation, showLoader) {
        'use strict';
        return CustomerBalance.extend({
            removeBalanceFromQuote: function () {
                var url = this.getRemoveUrl();
                var params = {};
                showLoader.payment(true);
                showLoader.review(true);
                Request.send(url, "post", params).always(function() {
                    getPaymentInformation().done(function () {
                        showLoader.payment(false);
                        showLoader.review(false);
                    });
                });
            }
        });
    }
);
