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
        'Magento_GiftMessage/js/model/url-builder',
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/error-processor',
        'mage/url',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, urlBuilder, Request, messageList, errorProcessor, url, quote) {
        'use strict';

        return function (giftMessage, remove) {
            var serviceUrl;
            var deferred = $.Deferred();
            url.setBaseUrl(giftMessage.getConfigValue('baseUrl'));

            if (giftMessage.getConfigValue('isCustomerLoggedIn')) {
                serviceUrl = urlBuilder.createUrl('/carts/mine/gift-message', {});

                if (giftMessage.itemId != 'orderLevel') {
                    serviceUrl = urlBuilder.createUrl('/carts/mine/gift-message/:itemId', {
                        itemId: giftMessage.itemId
                    });
                }
            } else {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/gift-message', {
                    cartId: quote.getQuoteId()
                });

                if (giftMessage.itemId != 'orderLevel') {
                    serviceUrl = urlBuilder.createUrl(
                        '/guest-carts/:cartId/gift-message/:itemId',
                        {
                            cartId: quote.getQuoteId(), itemId: giftMessage.itemId
                        }
                    );
                }
            }
            messageList.clear();

            var params = {
                gift_message: giftMessage.getSubmitParams(remove)
            };
            return Request.send(serviceUrl, "post", params);
        };
    }
);
