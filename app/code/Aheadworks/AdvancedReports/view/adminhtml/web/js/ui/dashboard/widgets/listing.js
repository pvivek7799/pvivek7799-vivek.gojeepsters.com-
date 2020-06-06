/**
* Copyright 2019 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'uiCollection'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Aheadworks_AdvancedReports/ui/dashboard/widgets/listing'
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable
         */
        initObservable: function () {
            this._super()
                .track({
                    rows: []
                });

            return this;
        }
    });
});
