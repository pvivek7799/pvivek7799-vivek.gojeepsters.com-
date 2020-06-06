/*
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */

define(
    [
        'ko'
    ],
    function (
        ko
    ) {
        'use strict';
        return {
            errorValidationMessage:ko.observable(false),
            validating: ko.observable(false)
        }
    }
);
