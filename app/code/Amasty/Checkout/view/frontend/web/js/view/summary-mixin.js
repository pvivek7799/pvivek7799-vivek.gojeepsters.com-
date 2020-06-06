define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                summaryLabel: 'Order Summary'
            },
            getNameSummary: function () {
                return this.summaryLabel;
            }
        });
    }
});
