/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*global define,alert*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'MW_Onestepcheckout/js/model/shipping-save-processor'
    ],
    function (quote, shippingSaveProcessor) {
        'use strict';
        return function () {
            return shippingSaveProcessor.saveShippingInformation(quote.shippingAddress().getType());
        }
    }
);
