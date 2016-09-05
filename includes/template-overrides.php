<?php
/**
 * Template overrides
 *
 * @package     EDD\FreeDownloads\Templates\Overrides
 * @since       1.1.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Override the download form
 *
 * @since       1.1.0
 * @param       string $form The existing download form
 * @param       array $args Arguements passed to the form
 * @return      string $form The updated download form
 */
function edd_free_downloads_download_form( $form, $args ) {
	$download_id = absint( $args['download_id'] );
	$download_file = edd_get_download_files( $download_id );

	if( edd_free_downloads_use_modal( $download_id ) && ! edd_has_variable_prices( $download_id ) ) {
		$form_id        = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $args['download_id'];
		$download_label = edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) );
		$download_class = implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ), 'edd-free-download edd-free-download-single' ) );

		$form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
		$form .= '<div class="edd_purchase_submit_wrapper">';

		if( wp_is_mobile() ) {
			$href = esc_url( add_query_arg( array( 'edd-free-download' => 'true', 'download_id' => $args['download_id'] ) ) );
		} else {
			$href = '#edd-free-download-modal';
		}

		if( edd_is_ajax_enabled() ) {
			$form .= sprintf(
				'<a class="edd-add-to-cart %1$s" href="' . $href . '" data-download-id="%3$s">%2$s</a>',
				$download_class,
				esc_attr( $download_label ),
				$download_id
			);
		} else {
			$form .= sprintf(
				'<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" href="' . $href . '" data-download-id="%3$s" />',
				$download_class,
				esc_attr( $download_label ),
				$download_id
			);
		}

		$form .= '</div>';
		$form .= '</form>';
	}

	return $form;
}
add_filter( 'edd_purchase_download_form', 'edd_free_downloads_download_form', 200, 2 );
