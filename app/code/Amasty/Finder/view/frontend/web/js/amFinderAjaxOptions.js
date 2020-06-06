define([
    'jquery',
], function ($) {
    $.widget('mage.amFinderAjaxOptions', {

        _create: function () {
            this.callAjax();
        },

        callAjax: function () {
            var self = this;
            $.getJSON(
                this.options.ajaxUrl,
                {product_id : this.options.productId},
                function (response) {
                    if (!response.options) {
                        $('#tab-label-amfinder-product-attributes').hide();
                    }
                    self.element.append(response.html);
                }
            );
        }
    });

    return $.mage.amFinderAjaxOptions;
});
