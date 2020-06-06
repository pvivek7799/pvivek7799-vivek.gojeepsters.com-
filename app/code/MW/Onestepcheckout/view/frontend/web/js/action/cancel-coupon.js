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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'MW_Onestepcheckout/js/action/reload-shipping-method'
    ],
    function ($, quote, urlManager, errorProcessor, messageContainer, Request, getPaymentInformationAction, totals, __, reloadShippingMethod) {
        'use strict';

        return function (isApplied, isLoading) {
            var quoteId = quote.getQuoteId(),
                url = urlManager.getCancelCouponUrl(quoteId),
                message = __('Your coupon was successfully removed.');
            messageContainer.clear();

            return Request.send(url, "delete").done(function () {
                var deferred = $.Deferred();
                getPaymentInformationAction(deferred);
                reloadShippingMethod();
                deferred.done(function () {
                    isApplied(false);
                });
                messageContainer.addSuccessMessage({
                    'message': message
                });
            }).fail(function (response) {
                totals.isLoading(false);
                errorProcessor.process(response, messageContainer);
            }).always(function () {
                isLoading(false);
            });
        };
    }
);
