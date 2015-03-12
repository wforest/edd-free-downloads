/*global jQuery, document, window, edd_free_downloads_vars, jBox*/
/*jslint newcap: true*/
jQuery(document).ready(function ($) {
    'use strict';

    var windowWidth, windowHeight, newModal;

    windowWidth = $(window).width();
    windowHeight = $(window).height();

    if ((windowWidth < 800 && windowHeight < 600) || (windowWidth < 600 && windowHeight < 800)) {
        $('.edd-free-download-cancel').css('display', 'block');

        newModal = new jBox('Modal', {
            attach: $('.edd-free-download'),
            content: $('#edd-free-downloads-modal'),
            width: windowWidth,
            height: windowHeight,
            fade: false,
            animation: false,
            reposition: true,
            addClass: 'edd-free-downloads-mobile'
        });

        $('.edd-free-download-cancel').click(function () {
            $('#edd-free-downloads-modal').find('input[type=text]').val('');

            newModal.close();
        });
    } else {
        newModal = new jBox('Modal', {
            attach: $('.edd-free-download'),
            content: $('#edd-free-downloads-modal'),
            width: 350,
            maxWidth: windowWidth * 0.80,
            maxHeight: windowHeight * 0.80,
            closeButton: edd_free_downloads_vars.close_button
        });
    }

    $('.edd-free-download').click(function (e) {
        e.preventDefault();

        var download_id = $(this).closest('form').attr('id').replace('edd_purchase_', '');
        $('input[name="edd_free_download_id"]').val(download_id);
    });

    $('.edd-free-download-submit').click(function () {
        var email, regex, has_error = false;

        email = $('input[name="edd_free_download_email"]');
        regex = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/;

        if (email.val() === '') {
            $('.edd-free-download-errors').css('display', 'block');
            $('#edd-free-download-error-email-required').css('display', 'block');

            has_error = true;
        }

        if (!regex.test(email.val())) {
            $('.edd-free-download-errors').css('display', 'block');
            $('#edd-free-download-error-email-invalid').css('display', 'block');

            has_error = true;
        }

        if (has_error === false) {
            $('#edd_free_download_form').submit();
        }
    });
});
