<?php
/**
 * Template Actions
 *
 * @package     EDD\FreeDownloads\Template\Actions
 * @since       2.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Listen for edd-free-download queries and handle accordingly
 *
 * @since       1.0.1
 * @return      void
 */
function edd_free_downloads_display_inline() {
	echo '<div id="edd-free-downloads-modal" class="edd-free-downloads-hidden">';

	ob_start();
	edd_get_template_part( 'download', 'modal' );
	echo ob_get_clean();

	echo '</div>';
}
add_action( 'wp_footer', 'edd_free_downloads_display_inline' );


/**
 * Listen for edd-free-download queries and handle accordingly
 *
 * @since       1.1.0
 * @return      void
 */
function edd_free_downloads_display_redirect() {
	global $wp_query;

	// Check for edd-free-download variable
	if( ! isset( $wp_query->query_vars['edd-free-download'] ) ) {
		return;
	}

	// Make sure we have a download InvalidArgumentException
	if( ! isset( $wp_query->query_vars['download_id'] ) ) {
		return;
	}

	echo'<div id="edd-free-downloads-modal" class="edd-free-downloads-mobile">';

	ob_start();
	edd_get_template_part( 'download', 'redirect' );
	echo ob_get_clean();

	echo '</div>';
}
add_action( 'wp_head', 'edd_free_downloads_display_redirect' );
