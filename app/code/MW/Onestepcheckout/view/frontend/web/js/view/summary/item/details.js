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
        'MW_Onestepcheckout/js/model/core/request',
        'MW_Onestepcheckout/js/model/core/url-builder',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'MW_Onestepcheckout/js/action/reload-shipping-method',
        'Magento_Checkout/js/action/get-payment-information',
        'MW_Onestepcheckout/js/model/gift-wrap',
        'Magento_Ui/js/modal/confirm',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function (
        $,
        Component,
        Request,
        UrlBuilder,
        customerData,
        getTotalsAction,
        totals,
        quote,
        reloadShippingMethod,
        getPaymentInformation,
        giftWrapModel,
        confirm,
        alertPopup,
        __
    ) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MW_Onestepcheckout/summary/item/details'
            },
            isEditProductQty:window.window.checkoutConfig.edit_product_qty,
            addQty: function (data) {
                this.updateQty(data.item_id, 'update', data.qty + 1);
            },

            minusQty: function (data) {
                this.updateQty(data.item_id, 'update', data.qty - 1);
            },

            updateNewQty: function (data) {
                this.updateQty(data.item_id, 'update', data.qty);
            },

            deleteItem: function (data) {
                var self = this;
                confirm({
                    content: __('Do you want to remove the item from cart?'),
                    actions: {
                        confirm: function () {
                            self.updateQty(data.item_id, 'delete', '');
                        },
                        always: function (event) {
                            event.stopImmediatePropagation();
                        }
                    }
                });

            },

            updateQty: function (itemId, type, qty) {
                var params = {
                    item_id: itemId,
                    qty: qty,
                    type: type
                };
                var self = this;
                var url = UrlBuilder.createUrl("onestepcheckout/cart/update", {}, false);
                var updateRequest = Request.send(url, "post", params);
                updateRequest.done(function(response){
                    var miniCart = $('[data-block="minicart"]');
                    miniCart.trigger('contentLoading');
                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                    miniCart.trigger('contentUpdated');
                }).always(function (response) {
                    if (response.error) {
                        alertPopup({
                            content: __(response.error),
                            autoOpen: true,
                            clickableOverlay: true,
                            focus: "",
                            actions: {
                                always: function(){

                                }
                            }
                        });
                    }

                    if(response.cart_empty || response.is_virtual){
                        window.location.reload();
                    }else{
                        if (response.giftwrap_amount && !response.error) {
                            giftWrapModel.setGiftWrapAmount(response.giftwrap_amount);
                        }
                        reloadShippingMethod();
                        getPaymentInformation();
                    }
                });
            }
        });
    }
);
