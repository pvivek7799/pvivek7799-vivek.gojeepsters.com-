/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'Magento_Reward/js/view/payment/reward'
    ],
    function (Reward) {
        'use strict';
        return Reward.extend({
            defaults: {
                template: 'MW_Onestepcheckout/payment/reward'
            },
            initialize: function () {
                this._super();
            }
        });
    }
);
