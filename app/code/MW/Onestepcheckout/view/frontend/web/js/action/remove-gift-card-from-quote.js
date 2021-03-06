/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/url-builder',
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/error-processor',
        'Magento_GiftCardAccount/js/model/payment/gift-card-messages',
        'MW_Onestepcheckout/js/action/showLoader'
    ],
    function (
        $,
        urlBuilder,
        Request,
        customer,
        quote,
        getPaymentInformationAction,
        errorProcessor,
        messageList,
        showLoader
    ) {
        'use strict';

        return function (giftCardCode) {
            var serviceUrl;

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/carts/guest-carts/:cartId/giftCards/:giftCardCode', {
                    cartId: quote.getQuoteId(),
                    giftCardCode: giftCardCode
                });
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/giftCards/:giftCardCode', {
                    giftCardCode: giftCardCode
                });
            }

            showLoader.payment(true);
            showLoader.review(true);

            return Request.send(serviceUrl, "delete").done(function (response) {
                if (response) {
                    $.when(getPaymentInformationAction()).always(function () {
                        showLoader.payment(false);
                        showLoader.review(false);
                    });
                }
            }).fail(function (response) {
                errorProcessor.process(response, messageList);
                showLoader.payment(false);
                showLoader.review(false);
            });
        };
    }
);
