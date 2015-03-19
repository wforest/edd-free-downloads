/*global jQuery, document, edd_free_downloads_vars*/
/*jslint newcap: true*/
jQuery(document).ready(function ($) {
    'use strict';

    var newModal, formURL;

    if (isMobile.any) {
        $('.edd-free-download').click(function (e) {
            e.preventDefault();

            window.location.href = $(this).attr('href');
        });

        $('.edd-free-download-cancel').click(function () {
            parent.history.back();
            return false;
        });
    } else {
        newModal = new jBox('Modal', {
            attach: $('.edd-free-download'),
            content: $('#edd-free-downloads-modal'),
            width: 350,
            closeButton: edd_free_downloads_vars.close_button,
        });

        $('.edd-free-download').click(function (e) {
            e.preventDefault();

            var download_id = $(this).closest('form').attr('id').replace('edd_purchase_', '');
            $('input[name="edd_free_download_id"]').val(download_id);
        });

        // Select email field on click
        $('.edd-free-download').click(function (e) {
            $('input[name="edd_free_download_email"]').focus();
            $('input[name="edd_free_download_email"]').select();
        });
    }

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
