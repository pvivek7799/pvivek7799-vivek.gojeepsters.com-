/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mageworx.optionAdvancedPricing', {
        options: {
            optionConfig: {}
        },

        firstRun: function firstRun(optionConfig, productConfig, base, self) {
            base.setOptionValueTitle();
        },

        update: function update(option, optionConfig, productConfig, base) {
            var $option = $(option),
                values = $option.val();

            $('option', $option).each(function (i, e) {
                var tierPrice = $('#value_' + e.value + '_tier_price');
                if (tierPrice.length > 0) {
                    tierPrice.hide();
                }
            });

            if (!values) {
                return;
            }

            if (!Array.isArray(values)) {
                values = [values];
            }

            $(values).each(function (i, e) {
                var tierPrice = $('#value_' + e + '_tier_price');
                if (tierPrice.length > 0) {
                    if ($option.is(':checked') || $('option:selected', $option).val()) {
                        tierPrice.show();
                    } else {
                        tierPrice.hide();
                    }
                }
            });

        }
    });

    return $.mageworx.optionAdvancedPricing;

});