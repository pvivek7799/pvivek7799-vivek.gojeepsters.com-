define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                toggleUseDefault: '${ $.provider }:data.product.stock_data.use_default_is_in_stock'
            },
            exports: {
                disabled: '${ $.provider }:data.product.stock_data.use_default_is_in_stock'
            }
        },
        toggleUseDefault: function (state) {
            this.isUseDefault(state);
            this.disabled(state);
        }
    });
});
