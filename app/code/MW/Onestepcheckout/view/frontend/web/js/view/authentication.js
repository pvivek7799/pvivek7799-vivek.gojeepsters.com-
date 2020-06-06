/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/authentication',
    'mage/translate'
], function ($, ko, Authentication, __) {
    'use strict';

    return Authentication.extend({
        defaults: {
            template: 'MW_Onestepcheckout/authentication'
        },
        isShowLoginLink: ko.observable(window.checkoutConfig.show_login_link),
        isLogin: ko.observable(window.checkoutConfig.is_login),
        loginLinkTitle: ko.computed(function(){
            if (window.checkoutConfig.login_link_title) {
                return window.checkoutConfig.login_link_title;
            } else {
                return __('Click here to login or create a new account');
            }
        }),
    });
});
