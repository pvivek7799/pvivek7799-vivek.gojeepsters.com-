require(['jquery'], function ($) {

    $(document).ready(function () {
        $('.rd-navbar-fixed, .rd-navbar-static, .rd-navbar-fullwidth, .rd-navbar-sidebar').hide();
        $('a.close.skip-link-close.fa.fa-times').hide();
        $('[data-target-element="#search-dropdown"]').show();
        $('[data-target-element="#search-dropdown"]').click(function () {
            $('.page-header__content .navigation').hide();
            $(this).hide();
            $('.rd-navbar-static .rd-navbar-search .form-group').css({'visibility': 'visible', 'opacity': '1'});
            $('a.close.skip-link-close.fa.fa-times').show();
        });
        $('a.close.skip-link-close.fa.fa-times').click(function () {
            $('.rd-navbar-static .rd-navbar-search .form-group').css({'visibility': 'hidden', 'opacity': '0'});
            $(this).hide();
            $('[data-target-element="#search-dropdown"]').show();
            $('.page-header__content .navigation').show();
        });
        //code for checkout
        var stepTitle = setInterval(function () {
            if ($('.checkout-index-index').length > 0) {
                $('.checkout-index-index #shipping p.step-title').text('');
                // $('.checkout-index-index #shipping p.step-title').prepend('<div id="billing-number">1</div>');
                $('.checkout-index-index #shipping .step-title').prependTo('.checkout-index-index #shipping-new-address-form');
                $('.checkout-payment-method .payment-option-content').show();
                $('.opc-block-summary p.step-title').text('Review');
                $('#shipping-new-address-form p.step-title').text('Billing');
                // $('.checkout-index-index .opc-block-summary p.step-title title').prepend('<div id="billing-number"></div>');

                // clearInterval(stepTitle);
//				$('<div class="separator"><div></div><div></div></div>').insertBefore('#shipping-new-address-form');
            }


        }, 1000);

        if ($('body').hasClass('checkout-index-index')) {
            var discountCop = setInterval(function () {
                if ($('.payment-option._collapsible.opc-payment-additional.discount-code').length > 0) {
                    $('.payment-option._collapsible.opc-payment-additional.discount-code').insertAfter('#payment');
                    $('.checkout-payment-method .payment-option-content').show();
                    $('#shipping-new-address-form p.step-title').text('Billing');
                    $('.fieldset.fieldset.hidden-fields').show();

                    clearInterval(discountCop);
                }
            }, 1000);
            var formlog = setInterval(function () {
                if ($('.opc-wrapper .form-login').length > 0) {
                    $('<div class="separator"><div></div><div></div</div>').appendTo('.opc-wrapper .form-login');
                    $('<label class="amscheckout-label">To checkout as a guest add your email below</label>').prependTo('.opc-wrapper .form-login');
                    clearInterval(formlog);
                }
            }, 1000);
            var discCop = setInterval(function () {
                if ($('#payment').siblings('.discount-code').length > 0) {
                    $('.discount-code').show();
                    $('.action-toggle').trigger('click');
                    // $('#block-discount-heading').text('');
                    clearInterval(discCop);
                }
            }, 1000);

        }
		

        jQuery('.column.main > #layer-product-list .message.info.empty').insertBefore(jQuery('.amfinder-vertical'));
//        if (BASE_URL == 'https://tampa.gojeepsters.com/') {
//            jQuery('.header.content').css({"width": "40%", "float": "left"});
//            jQuery('<div class="head-cls" style="width: 20%; float: right; padding-top: 2em;"><span style="font-size: 16px; font-weight: 800;">Jeepsters Tampa Fl</span><br>6102 E Adamo Dr<br><span style="font-size: 16px;">Tampa Fl 33619</span><br><span style="font-size: 16px;font-weight: 800;color: #fe9b4b;">+1 813 605-2244</span><br><a href="mailto:jeepsterstampa@gmail.com" style="color: #000;">jeepsterstampa@gmail.com</a></div>').insertAfter(".header.content");
//        }
//        if (BASE_URL == "https://largo.gojeepsters.com/") {
           // jQuery('.header.content').css({"width": "40%", "float": "left"});
            //jQuery('<div class="header-right-cntnt"><div class="head-cls" style="width: 20%; float: right; padding-top: 2em;"><span style="font-size: 16px; font-weight: 800;">Jeepsters Largo Fl</span><br>6875 Ulmerton Rd<br><span style="font-size: 16px;">Largo Fl 33771</span><br><span style="font-size: 16px;font-weight: 800;color: #fe9b4b;">+1 727 538-0086</span><br><a href="mailto:largo@gojeepsters.com" style="color: #000;">largo@gojeepsters.com</a></div><div class="head-cls" style="width: auto; float: right; padding-top: 2em;    padding-right: 2em;"><span style="font-size: 16px; font-weight: 800;">Jeepsters Tampa Fl</span><br>6102 E Adamo Dr<br><span style="font-size: 16px;">Tampa Fl 33619</span><br><span style="font-size: 16px;font-weight: 800;color: #fe9b4b;">+1 813 605-2244</span><br><a href="mailto:tampa@gojeepsters.com" style="color: #000;">tampa@gojeepsters.com</a></div><div class="head-cls custom"><a href="https://gojeepsters.com/build-your-jeep"><img src="https://gojeepsters.com/pub/media/wysiwyg/gojeep.jpg"></a></div></div>').insertAfter(".header.content");
			//jQuery('.header.content').css('width','33%');
setTimeout(function(){
            jQuery('.authorization-link').prepend('<div class="logs-check"><span class="logs-in"> <a href="https://gojeepsters.com/free-shipping" style="text-decoration-line: underline" target="_blank">Free Shipping</a> &nbsp;<span class="small" style="font-size:14px;">on Orders Over $49*</span></span></div>');
            // jQuery('ul.header.links .welcome').css('width':'70%');
            // jQuery('div.logs-check').css('width':'70%');
},3000);
			
           if($(document).width()<768){
                console.log('tested');
                if(jQuery('body').hasClass('catalog-category-view')){
                    jQuery('.catalog-category-view .columns .sidebar-main').insertAfter('.columns .column.main');
                    // setInterval(function(){
                    jQuery('.amfinder-toggle').show();
                // },1000);
                }
            }


    });
        //var stepTitle1 = setInterval(function () {
          //  if ($('.ui-menu-item.search_window').length > 0) {
            //    $('.block.block-search').insertAfter('.ui-menu-item.search_window');
              //      clearInterval(stepTitle1);
               // }
            //}, 1000);
});
