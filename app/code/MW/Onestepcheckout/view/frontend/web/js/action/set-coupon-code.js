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
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'MW_Onestepcheckout/js/model/core/request',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'MW_Onestepcheckout/js/action/reload-shipping-method'
    ],
    function (
        ko,
        $,
        quote,
        urlManager,
        errorProcessor,
        messageContainer,
        Request,
        $t,
        getPaymentInformationAction,
        totals,
        reloadShippingMethod
    ) {
        'use strict';
        return function (couponCode, isApplied, isLoading) {
            var quoteId = quote.getQuoteId();
            var url = urlManager.getApplyCouponUrl(couponCode, quoteId);
            var message = $t('Your coupon was successfully applied.');
            return Request.send(url, "put", {}).done(function (response) {
                if (response) {
                    isLoading(false);
                    isApplied(true);
                    getPaymentInformationAction();
                    reloadShippingMethod();
                    messageContainer.addSuccessMessage({'message': message});
                }
            }).fail(function (response) {
                isLoading(false);
                totals.isLoading(false);
                errorProcessor.process(response, messageContainer);
            });
        };
    }
);
