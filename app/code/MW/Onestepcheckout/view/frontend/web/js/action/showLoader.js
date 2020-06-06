/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/full-screen-loader',
    'mwJqueryPlugins',
    'mwJqueryCorner'
], function ($, ko, Component, shippingService, fullScreenLoader) {
    'use strict';

    window.OneStep = {
        Models: {},
        Collections: {},
        Views: {},
        Plugins: {},
        $: jQuery.noConflict()
    };

    window.withoutIE = false;

    if (navigator.appName.indexOf("Internet Explorer") != -1) {
        window.withoutIE = (
            navigator.appVersion.indexOf("MSIE 6") > -1 ||
            navigator.appVersion.indexOf("MSIE 7") > -1 ||
            navigator.appVersion.indexOf("MSIE 8") > -1 ||
            navigator.appVersion.indexOf("MSIE 9") > -1
        );
    }

    var Loader = {
        initialize:function(params){

        },
        renderParam: function(params){
            params.el           = (typeof params.el == 'undefined') ? '' : params.el;
            params.boxes        = (typeof params.boxes == 'undefined') ? '' : params.boxes;
            params.session_id   = (typeof params.el == 'undefined') ? '' : params.session_id;
            params.show_text    = (typeof params.show_text == 'undefined') ? false : params.show_text
            params.size         = (typeof params.size == 'undefined') ? 20 : params.size
            params.color        = (typeof params.color == 'undefined') ? onestepConfig.styleColor : params.color;
            return params;
        },
        insideBox: function(params){
            var params = this.renderParam(params);
            if(params.el == ""){
                console.log("No element to selector.");
                return false;
            }
            if(params.action == 'hide'){
                this.hide(params);
                return;
            }
            params.type = "inside";
            this.show(params);
        },
        show: function(params){
         fullScreenLoader.startLoader();
         $('body').removeClass('oscHideLoader');
//            var parent_box;
//            switch(params.type){
//                case 'inside':
//                    parent_box = params.el.closest(".mw-osc-block-content");
//                    parent_box.find(".loader").remove();
//                    parent_box.prepend("<div class='loader' id='loader_"+params.session_id+"'></div>");
//                    break;
//            }
//            var nodeLoader = parent_box.find("#loader_"+params.session_id);
//            if(nodeLoader.length > 0){
//                var cl = new CanvasLoader('loader_'+params.session_id);
//                cl.setColor('#'+params.color); // default is '#000000'
//                cl.setDiameter(params.size); // default is 40 (size)
//                cl.setDensity(55); // default is 40
//                cl.setRange(0.9); // default is 1.3
//                cl.setFPS(34); // default is 24
//                cl.show(); // Hidden by default
//                if(params.show_text){
//                    window.OneStep.$("#"+params.el).append("<div style='text-align: center; color: #757373;'>"+$.mage.__("Please wait..")+".</div>");
//                }
//            }
        },
        hide: function(params){
              fullScreenLoader.stopLoader();
//            if(params.boxes != ""){
//                var view = this;
//                window.OneStep.$("div[id*=loader]").each(function(k, v){
//                    window.OneStep.$(this).remove();
//                });
//            }else{
//                window.OneStep.$(params.el).remove();
//            }
        }
    };

    var ShowLoader = {
        shipping:ko.observable(false),
        payment: ko.observable(false),
        review: ko.observable(false),
        all: ko.observable(false),
        initialize: function () {
            var self = this;
            self.shipping.subscribe(function(){
                if(self.shipping() == true){
                    Loader.insideBox({
                        el:$("#checkout-shipping-method-load"),
                        session_id:"shipping"
                    });
                    $('body').addClass('oscHideLoader');
                }
                if(self.shipping() == false){
                    Loader.insideBox({
                        el:"#loader_shipping",
                        action:"hide"
                    });
                }
            });
            self.payment.subscribe(function(){
                if(self.payment() == true){
                    Loader.insideBox({
                        el:$("#checkout-step-payment"),
                        session_id:"payment"
                    });
                    $('body').addClass('oscHideLoader');
                }
                if(self.payment() == false){
                    Loader.insideBox({
                        el:"#loader_payment",
                        action:"hide"
                    });
                }
            });
            self.review.subscribe(function(){
                if(self.review() == true){
                    Loader.insideBox({
                        el:$("#checkout-review-load"),
                        session_id:"review"
                    });
                    $('body').addClass('oscHideLoader');
                }
                if(self.review() == false){
                    Loader.insideBox({
                        el:"#loader_review",
                        action:"hide"
                    });
                }
            });
            self.all.subscribe(function(){
                if(self.all() == true){
                    fullScreenLoader.startLoader();
                    $('body').removeClass('oscHideLoader');
                }else{
                    fullScreenLoader.stopLoader();
                }
            });

            self.loading = ko.pureComputed(function(){
                return (self.shipping() || self.payment() || self.review() || self.all())?true:false;
            });

            shippingService.isLoading.subscribe(function(){
                if(shippingService.isLoading() == true){
                    self.shipping(true);
                }
                if(shippingService.isLoading() == false){
                    self.shipping(false);
                }
            });

            return self;
        }
    };
    return ShowLoader.initialize();
});