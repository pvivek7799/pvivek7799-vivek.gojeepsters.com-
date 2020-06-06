define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.amastyDraggableFieldArray', {
        options: {
            rowsContainer: '[data-role="row-container"]',
            orderInput: '[data-role="sort-order"]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var rowsContainer = this.element.find(this.options.rowsContainer),
                useWebsiteCheckbox = rowsContainer.parents('td').siblings('.use-default').find('input[type="checkbox"]');

            rowsContainer.sortable({
                tolerance: 'pointer',
                axis: 'y',
                update: function () {
                    rowsContainer.find(this.options.orderInput).each(function (index, element) {
                        $(element).val(index);
                    });
                }.bind(this)
            });

            if(useWebsiteCheckbox.length) {
                useWebsiteCheckbox.on('change', this.toggleSortable.bind(this));
                this.toggleSortable('change', useWebsiteCheckbox);
            }
        },

        toggleSortable: function (event, input) {
            var checkbox = (input) ? input : $(event.target),
                sortableElement = checkbox.parents('td').siblings('.value').find(this.options.rowsContainer),
                inherit = $('#block_management_inherit');

            sortableElement.sortable({
                disabled: checkbox.prop('checked')
            });

            inherit.val(+checkbox.prop('checked'));
            sortableElement.find('input').prop('disabled', checkbox.prop('checked'))
        }
    });

    return $.mage.amastyDraggableFieldArray;
});
