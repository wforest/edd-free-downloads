/*global jQuery, document, window, edd_free_downloads_vars*/
jQuery(document).ready(function($) {
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
            newModal.close();
        });
    } else {
        new jBox('Modal', {
            attach: $('.edd-free-download'),
            content: $('#edd-free-downloads-modal'),
            width: 350,
            maxWidth: windowWidth * .80,
            maxHeight: windowHeight * .80,
            closeButton: edd_free_downloads_vars.close_button
        });
    }
    
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
