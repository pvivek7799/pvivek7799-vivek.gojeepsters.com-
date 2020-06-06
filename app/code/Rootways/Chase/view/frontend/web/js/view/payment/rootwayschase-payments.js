define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
            
        rendererList.push(
            {
                type: 'rootways_chase_option',
                component: 'Rootways_Chase/js/view/payment/method-renderer/chase-method'
            }
        );
            
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
