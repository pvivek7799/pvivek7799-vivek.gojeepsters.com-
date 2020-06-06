define([
    'Magento_CatalogInventory/js/components/qty-validator-changer',
    'uiRegistry'
], function (Qty, registry) {
    'use strict';

    return Qty.extend({
        defaults: {
            imports: {
                toggleUseDefault: '${ $.provider }:data.product.stock_data.use_default_qty'
            },
            exports: {
                disabled: '${ $.provider }:data.product.stock_data.use_default_qty'
            }
        },
        toggleUseDefault: function (state) {
            var matrix = this.source.get('data.configurable-matrix');

            if(!!matrix){
                this.isUseDefault(state);
                this.disabled(state);
            }else{
                this.disabled(true);
            }
        }
    });
});
