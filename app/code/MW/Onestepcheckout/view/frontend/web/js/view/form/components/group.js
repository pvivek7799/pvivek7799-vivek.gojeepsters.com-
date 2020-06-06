/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define([
    'Magento_Ui/js/form/components/group'
], function (Group) {
    'use strict';

    return Group.extend({
        defaults: {
            fieldTemplate: 'MW_Onestepcheckout/form/field'
        }
    });
});
