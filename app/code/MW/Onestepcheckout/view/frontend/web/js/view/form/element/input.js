/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'MW_Onestepcheckout/js/action/validate-shipping-info',
    'MW_Onestepcheckout/js/action/save-shipping-address'
], function ($, abstract,ValidateShippingInfo,SaveAddressBeforePlaceOrder) {
    'use strict';

    return abstract.extend({
        saveShippingAddress: function(){
            if(ValidateShippingInfo()){
                SaveAddressBeforePlaceOrder();
            }
        }
    });
});
