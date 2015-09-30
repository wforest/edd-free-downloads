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

    wp_enqueue_script( 'edd-free-downloads-mobile', EDD_FREE_DOWNLOADS_URL . 'assets/js/isMobile.js', array( 'jquery' ) );
    wp_enqueue_style( 'edd-free-downloads-modal', EDD_FREE_DOWNLOADS_URL . 'assets/js/jBox/Source/jBox.css' );
    wp_enqueue_script( 'edd-free-downloads-modal', EDD_FREE_DOWNLOADS_URL . 'assets/js/jBox/Source/jBox.min.js', array( 'jquery' ) );
    wp_enqueue_style( 'edd-free-downloads', EDD_FREE_DOWNLOADS_URL . 'assets/css/style.css', array(), EDD_FREE_DOWNLOADS_VER  );
    wp_enqueue_script( 'edd-free-downloads', EDD_FREE_DOWNLOADS_URL . 'assets/js/edd-free-downloads.js', array( 'edd-free-downloads-modal' ), EDD_FREE_DOWNLOADS_VER );
    wp_localize_script( 'edd-free-downloads', 'edd_free_downloads_vars', array(
        'close_button'      => $close_button,
        'user_registration' => ( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! class_exists( 'EDD_Auto_Register' ) ) ? 'true' : 'false',
        'require_name'      => edd_get_option( 'edd_free_downloads_require_name', false ) ? 'true' : 'false',
        'download_loading'  => __( 'Please Wait... ', 'edd-free-downloads' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'edd_free_downloads_scripts' );
