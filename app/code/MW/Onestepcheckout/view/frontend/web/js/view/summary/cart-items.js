/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/totals',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
    ],
    function ($, ko, totals, Component, stepNavigator, quote) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
            },
            defaults: {
                template: 'MW_Onestepcheckout/summary/cart-items'
            },
            totals: totals.totals(),
            getItems: totals.getItems(),
            getItemsQty: function() {
                return parseFloat(this.totals.items_qty);
            },

            isItemsBlockExpanded: function () {
                return quote.isVirtual() || stepNavigator.isProcessed('shipping');
            }

        });
    }
);
