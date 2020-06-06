/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'MW_Onestepcheckout/js/model/core/request'
    ],
    function ($, resourceUrlManager, quote, Request) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address.
             * @param {Object} address
             */
            getRates: function (address,request) {
                var serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote),
                    payload = JSON.stringify({
                            address: {
                                'street': address.street,
                                'city': address.city,
                                'region_id': address.regionId,
                                'region': address.region,
                                'country_id': address.countryId,
                                'postcode': address.postcode,
                                'email': address.email,
                                'customer_id': address.customerId,
                                'firstname': address.firstname,
                                'lastname': address.lastname,
                                'middlename': address.middlename,
                                'prefix': address.prefix,
                                'suffix': address.suffix,
                                'vat_id': address.vatId,
                                'company': address.company,
                                'telephone': address.telephone,
                                'fax': address.fax,
                                'custom_attributes': address.customAttributes,
                                'save_in_address_book': address.saveInAddressBook
                            }
                        }
                    );
                request = (request)?request:$.Deferred();
                Request.send(serviceUrl, "post", payload).done(function (response) {
                    request.resolve(response);
                }).fail(function (response) {
                    request.reject(response);
                });
                return request;
            }
        };
    }
);
