/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_GiftCardAccount/js/view/payment/gift-card-account',
        'MW_Onestepcheckout/js/action/set-gift-card-information'
    ],
    function (
        $,
        ko,
        GiftCardAccount,
        setGiftCardAction
    ) {
        "use strict";
        return GiftCardAccount.extend({
            defaults: {
                template: 'MW_Onestepcheckout/payment/gift-card-account',
                giftCartCode: ''
            },
            initialize: function () {
                this._super();
            },
            setGiftCard: function () {
                if (this.validate()) {
                    setGiftCardAction([this.giftCartCode()])
                }
            }
        })
    }
);
