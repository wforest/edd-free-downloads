/*global jQuery, document, edd_free_downloads_vars*/
jQuery(document).ready(function($) {
    new jBox('Modal', {
        attach: $('.edd-free-download'),
        content: $('#edd-free-downloads-modal'),
        closeButton: edd_free_downloads_vars.close_button
    });

    $('.edd-free-download').click(function (e) {
        e.preventDefault();

        var download_id = $(this).closest('form').attr('id').replace('edd_purchase_', '');
        $('input[name="edd_free_download_id"]').val(download_id);
    });

    $('.edd-free-download-submit').click(function (e) {
        var email, regex, has_error = false;

        email = $('input[name="edd_free_download_email"]');
        regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

        if (email.val() === '') {
            $('.edd-free-download-errors').css('display', 'block');
            $('#edd-free-download-error-email-required').css('display', 'block');
       
            has_error = true;
        }

        if (! regex.test(email.val())) {
            $('.edd-free-download-errors').css('display', 'block');
            $('#edd-free-download-error-email-invalid').css('display', 'block');
        
            has_error = true;
        }

        if (has_error === true) {
            e.preventDefault();
        }
    });
});
