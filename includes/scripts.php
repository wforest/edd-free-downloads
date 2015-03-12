<?php
/**
 * Scripts
 *
 * @package     EDD\FreeDownloads\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Load scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_free_downloads_scripts() {
    $close_button   = edd_get_option( 'edd_free_downloads_close_button', false );
    $close_button   = ( $close_button ? 'box' : 'overlay' );

    wp_enqueue_style( 'edd-free-downloads-modal', EDD_FREE_DOWNLOADS_URL . 'assets/js/jBox/Source/jBox.css' );
    wp_enqueue_script( 'edd-free-downloads-modal', EDD_FREE_DOWNLOADS_URL . 'assets/js/jBox/Source/jBox.min.js', array( 'jquery' ) );
    wp_enqueue_style( 'edd-free-downloads', EDD_FREE_DOWNLOADS_URL . 'assets/css/style.css', array(), EDD_FREE_DOWNLOADS_VER  );
    wp_enqueue_script( 'edd-free-downloads', EDD_FREE_DOWNLOADS_URL . 'assets/js/edd-free-downloads.js', array( 'edd-free-downloads-modal' ), EDD_FREE_DOWNLOADS_VER );
    wp_localize_script( 'edd-free-downloads', 'edd_free_downloads_vars', array(
        'close_button'      => $close_button
    ) );
}
add_action( 'wp_enqueue_scripts', 'edd_free_downloads_scripts' );


/**
 * Modal window
 *
 * @since       1.0.0
 * @return      void
 */
function edd_free_downloads_modal() {
    if( is_user_logged_in() ) {
        $user = new WP_User( get_current_user_id() );
    }

    $email = isset( $user ) ? $user->user_email : '';
    $fname = isset( $user ) ? $user->user_firstname : '';
    $lname = isset( $user ) ? $user->user_lastname : '';

    $color = edd_get_option( 'checkout_color', 'blue' );
    $color = ( $color == 'inherit' ) ? '' : $color;
    $label = edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) );

    $modal  = '<div id="edd-free-downloads-modal">';
    $modal .= '<form id="edd_free_download_form" method="post">';

    // Email is always required
    $modal .= '<p>';
    $modal .= '<label for="edd_free_download_email" class="edd-free-downloads-label">' . __( 'Email Address', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
    $modal .= '<input type="text" name="edd_free_download_email" id="edd_free_download_email" placeholder="' . __( 'Email Address', 'edd-free-downloads' ) . '" value="' . $email . '" />';
    $modal .= '</p>';

    if( edd_get_option( 'edd_free_downloads_get_name', false ) ) {
        $modal .= '<p>';
        $modal .= '<label for="edd_free_download_fname" class="edd-free-downloads-label">' . __( 'First Name', 'edd-free-downloads' ) . '</label>';
        $modal .= '<input type="text" name="edd_free_download_fname" id="edd_free_download_fname" placeholder="' . __( 'First Name', 'edd-free-downloads' ) . '" value="' . $fname . '" />';
        $modal .= '</p>';
        
        $modal .= '<p>';
        $modal .= '<label for="edd_free_download_lname" class="edd-free-downloads-label">' . __( 'Last Name', 'edd-free-downloads' ) . '</label>';
        $modal .= '<input type="text" name="edd_free_download_lname" id="edd_free_download_lname" placeholder="' . __( 'Last Name', 'edd-free-downloads' ) . '" value="' . $lname . '" />';
        $modal .= '</p>';
    }

    $modal .= '<div class="edd-free-download-errors">';
    $modal .= '<p id="edd-free-download-error-email-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a valid email address', 'edd-free-downloads' ) . '</p>';
    $modal .= '<p id="edd-free-download-error-email-invalid"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Invalid email', 'edd-free-downloads' ) . '</p>';
    $modal .= '</div>';

    $modal .= '<input type="hidden" name="edd_action" value="free_download_process" />';
    $modal .= '<input type="hidden" name="edd_free_download_id" />';
    $modal .= '<input type="button" name="edd_free_download_submit" class="edd-free-download-submit edd-submit button ' . $color . '" value="' . $label . '" />';
    $modal .= '<input type="button" name="edd_free_download_cancel" class="edd-free-download-cancel edd-submit button ' . $color . '" value="' . __( 'Cancel', 'edd-free-downloads' ) . '" />';

    $modal .= '</form>';
    $modal .= '</div>';

    echo $modal;
}
add_action( 'wp_footer', 'edd_free_downloads_modal' );
