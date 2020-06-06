/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define(
    [
        'MW_Onestepcheckout/js/model/core/request',
        'Magento_Reward/js/view/cart/reward',
        'Magento_Checkout/js/action/get-payment-information',
        'MW_Onestepcheckout/js/action/showLoader'
    ],
    function (Request, Reward, getPaymentInformation, showLoader) {
        'use strict';

        return Reward.extend({
            defaults: {
                template: 'MW_Onestepcheckout/summary/reward'
            },
            removeRewardFromQuote: function () {
                var url = this.rewardPointsRemoveUrl;
                var params = {};
                showLoader.payment(true);
                showLoader.review(true);
                Request.send(url, "post", params).always(function (result) {
                    getPaymentInformation().done(function () {
                        showLoader.payment(false);
                        showLoader.review(false);
                    });
                });
            }
        });
    }
);
