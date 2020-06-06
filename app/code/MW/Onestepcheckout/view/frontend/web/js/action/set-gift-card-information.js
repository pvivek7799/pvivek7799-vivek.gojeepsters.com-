/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_GiftCardAccount/js/model/payment/gift-card-messages',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'MW_Onestepcheckout/js/action/showLoader'
    ],
    function (
        $,
        quote,
        urlBuilder,
        Request,
        messageList,
        errorProcessor,
        customer,
        getPaymentInformationAction,
        totals,
        showLoader
    ) {
        'use strict';

        return function (giftCardCode) {
            var serviceUrl,
                payload,
                message = 'Gift Card ' + giftCardCode + ' was added.';
            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/carts/guest-carts/:cartId/giftCards', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    giftCardAccountData: {gift_cards: giftCardCode}
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/giftCards', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    giftCardAccountData: {gift_cards: giftCardCode}
                };
            }
            messageList.clear();
            showLoader.payment(true);
            showLoader.review(true);
            Request.send(serviceUrl, "post", payload).done(function (response) {
                var deferred = $.Deferred();
                if (response) {
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        totals.isLoading(false);
                    });
                    messageList.addSuccessMessage({'message': message});
                }
            }).fail(function (response) {
                totals.isLoading(false);
                errorProcessor.process(response, messageList);
            }).always(function() {
                showLoader.payment(false);
                showLoader.review(false);
            });
        };
    }
);
