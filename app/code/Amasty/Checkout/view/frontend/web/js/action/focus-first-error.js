define(
    ['jquery'],
    function ($) {
        'use strict';

        return function () {
            var errorField = $('.mage-error:visible:first');
            if (!errorField.length) {
                return;
            }
            if (errorField.prop('tagName').toLowerCase() === 'div') {
                if (errorField.attr('for')) {
                    errorField = $('#' + errorField.attr('for'));
                } else {
                    var input = errorField.prevAll(':input');
                    if (input.length) {
                        errorField = input;
                    }
                }
            }

            var offset =  errorField.offset().top - (window.innerHeight / 2);
            if (offset < 0) {
                offset = 0;
            }
            $(window).scrollTop(offset);

            errorField.focus();
        };
    }
);

