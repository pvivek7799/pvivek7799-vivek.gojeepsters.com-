/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */

/**
 * Customer store credit(balance) application
 */
/*global define,alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'MW_Onestepcheckout/js/action/showLoader'
    ],
    function (
        $,
        ko,
        quote,
        urlBuilder,
        errorProcessor,
        Request,
        messageList,
        __,
        getPaymentInformationAction,
        totals,
        showLoader
    ) {
        'use strict';
        return function () {
            var message = __('Your store credit was successfully applied');
            messageList.clear();
            showLoader.payment(true);
            showLoader.review(true);
            return Request.send(urlBuilder.createUrl('/carts/mine/balance/apply', {}), "post").done(function (response) {
                if (response) {
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        totals.isLoading(false);
                    });
                    messageList.addSuccessMessage({'message': message});
                }
            }).fail(function (response) {
                totals.isLoading(false);
                errorProcessor.process(response);
            }).always(function() {
                showLoader.payment(false);
                showLoader.review(false);
            });
        };
    }
);
