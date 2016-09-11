/*global jQuery, document*/
/*jslint newcap: true*/
jQuery(document).ready(function ($) {
    'use strict';

    var EDD_Free_Downloads_Settings;

    /**
     * Settings
     */
    EDD_Free_Downloads_Settings = {
        init : function () {
            this.general();
        },

        general : function () {
            $('input[name="edd_settings[edd_free_downloads_get_name]"]').change(function () {
                if ($(this).prop('checked')) {
                    $('input[name="edd_settings[edd_free_downloads_require_name]"]').closest('tr').fadeIn('fast').css('display', 'table-row');
                } else {
                    $('input[name="edd_settings[edd_free_downloads_require_name]"]').closest('tr').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });
                }
            }).change();

            $('input[name="edd_settings[edd_free_downloads_show_notes]"]').change(function () {
                if ($(this).prop('checked')) {
                    $('input[name="edd_settings[edd_free_downloads_notes_title]"]').closest('tr').fadeIn('fast').css('display', 'table-row');
                    $('textarea[name="edd_settings[edd_free_downloads_notes]"]').closest('tr').fadeIn('fast').css('display', 'table-row');
                } else {
                    $('input[name="edd_settings[edd_free_downloads_notes_title]"]').closest('tr').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });
                    $('textarea[name="edd_settings[edd_free_downloads_notes]"]').closest('tr').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });
                }
            }).change();

            $('select[name="edd_settings[edd_free_downloads_on_complete]"]').change(function () {
                var selectedItem = $('select[name="edd_settings[edd_free_downloads_on_complete]"] option:selected');
                var directDownload = $('input[name="edd_settings[edd_free_downloads_direct_download]"]').is(':checked');

                if (selectedItem.val() === 'redirect' ) {
                    $('input[name="edd_settings[edd_free_downloads_redirect]"]').closest('tr').fadeIn('fast').css('display', 'table-row');

                    if (! directDownload) {
                        $('.edd-free-downloads-zip-status-available').closest('tr').fadeOut('fast', function () {
                            $(this).css('display', 'none');
                        });
                    }
                } else if(selectedItem.val() === 'default') {
                    $('input[name="edd_settings[edd_free_downloads_redirect]"]').closest('tr').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });

                    if (! directDownload) {
                        $('.edd-free-downloads-zip-status-available').closest('tr').fadeOut('fast', function () {
                            $(this).css('display', 'none');
                        });
                    }
                } else {
                    $('input[name="edd_settings[edd_free_downloads_redirect]"]').closest('tr').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });

                    if (! directDownload) {
                        $('.edd-free-downloads-zip-status-available').closest('tr').fadeIn('fast').css('display', 'table-row');
                    }
                }
            }).change();

            $('input[name="edd_settings[edd_free_downloads_direct_download]"]').change(function () {
                var onComplete = $('select[name="edd_settings[edd_free_downloads_on_complete]"] option:selected');

                if ($(this).prop('checked')) {
                    $('input[name="edd_settings[edd_free_downloads_direct_download_label]"]').closest('tr').fadeIn('fast').css('display', 'table-row');

                    if (onComplete.val() !== 'auto-download' ) {
                        $('.edd-free-downloads-zip-status-available').closest('tr').fadeIn('fast').css('display', 'table-row');
                    }
                } else {
                    $('input[name="edd_settings[edd_free_downloads_direct_download_label]"]').closest('tr').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });

                    if (onComplete.val() !== 'auto-download' ) {
                        $('.edd-free-downloads-zip-status-available').closest('tr').fadeOut('fast', function () {
                            $(this).css('display', 'none');
                        });
                    }
                }
            }).change();
        }
    };
    EDD_Free_Downloads_Settings.init();
});
