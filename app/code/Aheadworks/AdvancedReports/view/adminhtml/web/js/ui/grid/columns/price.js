/**
* Copyright 2019 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Catalog/js/price-utils',
    'Aheadworks_AdvancedReports/js/ui/grid/columns/number'
], function (priceUtils, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            imports: {
                priceFormat: '${ $.provider }:data.priceFormat'
            }
        },

        /**
         * Initializes observable properties
         *
         * @returns {Widget} Chainable
         */
        initObservable: function () {
            this._super()
                .track([
                    'priceFormat'
                ]);

            return this;
        },

        /**
         * Meant to preprocess data associated with a current columns' field
         *
         * @param {Object} row
         * @param {String} index
         * @returns {String}
         */
        getLabel: function (row, index) {
            var price = this._super(row, index);

            return priceUtils.formatPrice(price, this.priceFormat);
        }
    });
});
