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
        'uiComponent',
        'ko',
        'MW_Onestepcheckout/js/model/core/request',
        'MW_Onestepcheckout/js/model/core/url-builder',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function($, Component, ko, Request, UrlBuilder, fullScreenLoader) {
        'use strict';

        return Component.extend({
            oneStepTitle: ko.observable(window.checkoutConfig.checkout_title),
            oneStepDescription: ko.observable(window.checkoutConfig.checkout_description),
            isLogin: ko.observable(window.checkoutConfig.is_login),
            isShowLoginLink: ko.observable(window.checkoutConfig.show_login_link),

            defaults: {
                template: 'MW_Onestepcheckout/before-form'
            },

            showLoginForm: function () {
                $('#onestepcheckout-login-popup').show();
                $('#control_overlay').show();
            },


            logout: function () {
                var params = {};
                $('body').removeClass('oscHideLoader');
                fullScreenLoader.startLoader();
                var url = UrlBuilder.createUrl("onestepcheckout/customer/logout", {}, false);
                Request.send(url, "post", params).always(function (result) {
                    window.location.reload();
                });
            }


        });
    }
);
