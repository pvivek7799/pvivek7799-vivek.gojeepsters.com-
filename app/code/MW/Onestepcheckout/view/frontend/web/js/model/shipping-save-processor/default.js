/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*global define,alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
        'MW_Onestepcheckout/js/model/billing-address-state'
    ],
    function (
        ko,
        quote,
        resourceUrlManager,
        Request,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        BillingAddressState
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload;
                if (!quote.billingAddress() || BillingAddressState.sameAsShipping() == true) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code
                    }
                };

                fullScreenLoader.startLoader();

                return Request.send(resourceUrlManager.getUrlForSetShippingInformation(quote), "post", payload).done(function (response) {
                    quote.setTotals(response.totals);
                    fullScreenLoader.stopLoader();
                }).fail(function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                });
            }
        };
    }
);
