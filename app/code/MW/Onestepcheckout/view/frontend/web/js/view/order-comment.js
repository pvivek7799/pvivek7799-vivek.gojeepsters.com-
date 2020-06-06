/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
/*global define*/
define(
    [
        'ko',
        'uiComponent'
    ],
    function(ko, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'MW_Onestepcheckout/order-comment'
            },

            isShowComment: ko.observable(window.checkoutConfig.show_comment)

        });
    }
);
