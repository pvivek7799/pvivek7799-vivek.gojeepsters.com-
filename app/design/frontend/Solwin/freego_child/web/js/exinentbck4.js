require(["jquery"], function ($) {


    jQuery(window).scroll(function ($) {
        if (jQuery(window).scrollTop() > jQuery('.navigation').offset().top && !(jQuery('header').hasClass('sticky'))) {
            jQuery('header').addClass('sticky');
        } else if (jQuery(window).scrollTop() == 0) {
            jQuery('header').removeClass('sticky');
        }
//        if (jQuery(this).scrollTop() > 100) {
//            jQuery('header').addClass("sticky");
//        } else {
//            jQuery('header').removeClass("sticky");
//        }
    });


    jQuery('.catalog-category-view .page-title-wrapper h1.page-title').insertBefore('#layer-product-list .toolbar.toolbar-products');
    $(document).ready(function () {
        jQuery('.form.form-cart').insertBefore('.cart-summary');
    });
    jQuery('.stock.available').prepend(jQuery('.product.attribute.sku'));


    $("#search_mini_form .actions button.action.search").click(function () {
        $('input#search').show();
        $('input#search').focus();
        $('.close.search-close').show();
    });

    $(".close.search-close").click(function () {
        $('#search_mini_form .field.search .control #search').val("");
        $('.close.search-close').hide();
        $('input#search').hide();
    });

    $(document).ready(function () {
        $('#search_mini_form .field.search .control #search').val("");
        setTimeout(function () {
            $('.action.search').attr("disabled", false);
            $('.action.search').removeAttr("disabled");
        }, 2500);
    });



    /*Code Start For Bundle Product Customization*/
    $(document).ready(function () {
        if (jQuery('body').hasClass('page-product-bundle')) {
            var html = "<div class='field main'><label class='label'><span>Select Vehicle</span></label><div class='control'><select class='customfitment'><option value='select'>Please Select...</option><option value='JK 4 door'>JK 4 door</option><option value='JK 2 door'>JK 2 door</option><option value='JL 4 door'>JL 4 door</option><option value='JL 2 door'>JL 2 door</option></select></div></div>"
            var inputhtml; //,fitments = [];
            jQuery(html).insertAfter('.back.customization');
            jQuery('.fieldset-bundle-options .field.option .label:first-child span').each(function () {
                var labeltext = jQuery(this).text();
                var fitment = labeltext.substr(labeltext.indexOf(' ') + 1);
                labeltext = labeltext.substr(0, labeltext.indexOf(' '));
//                if (fitments.indexOf(fitment) == -1) {
//                    fitments.push(fitment);
//                }
                jQuery(this).text(labeltext);
                jQuery(this).attr('value', fitment);
                inputhtml = '<input class="custompackages" type="checkbox" val="' + fitment + '"/>';
                jQuery(this).attr('value', fitment);
                jQuery(inputhtml).insertBefore(this);
                jQuery('.checkbox.product.bundle.option').hide();
            });
//            for (var i = 0; i < fitments.length; i++) {
//                $('.customfitment').append($("<option></option>").attr("value", fitments[i]).text(fitments[i]));
//            }
            jQuery('.fieldset-bundle-options .field.option').hide();
            jQuery('#bundle-slide').click();
            jQuery('.price-configured_price.price-box').insertBefore('.box-tocart');
            jQuery(document).on('change', '.customfitment', function () {
                jQuery('.fieldset-bundle-options .field.option').hide();
                var fitment = jQuery(this).val();
                jQuery('.fieldset-bundle-options .field.option').each(function () {
                    if (jQuery(this).find('.label:first-child span').attr('value').indexOf(fitment) != -1) {
                        jQuery(this).show();
                    }
                });
            });
            jQuery(document).on('click', '.custompackages', function () {
                if (jQuery(this).prop('checked') == true) {
                    jQuery('.custompackages').attr('checked', false);
                    jQuery(this).attr('checked', true);
                    jQuery('.checkbox.product.bundle.option').attr('checked', false);
                    jQuery(this).parents('.field.option').find('.checkbox.product.bundle.option').attr('checked', true);
                } else {
                    jQuery('.custompackages').attr('checked', false);
                    jQuery('.checkbox.product.bundle.option').attr('checked', false);
                }
                jQuery('.checkbox.product.bundle.option').trigger('change');
            });
        }
    });
    /*Code End For Bundle Product Customization*/
});