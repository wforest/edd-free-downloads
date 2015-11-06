<?php
/**
 * Helper functions
 *
 * @package     EDD\FreeDownloads\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Setup form fields
 *
 * @since       1.1.0
 * @return      array $fields The configured fields
 */
function edd_free_downloads_get_form_fields() {
	$fields = array(
		array(
			'id'        => 'edd_free_download_email',
			'type'      => 'text',
			'label'     => edd_get_option( 'edd_free_downloads_email_label', __( 'Email Address', 'edd-free-downloads' ) ),
			'required'  => true
		)
	);

	return apply_filters( 'edd_free_downloads_form_fields', $fields );
}


/**
 * Check if a download should use the modal dialog
 *
 * @since       1.0.0
 * @param       int $download_id The ID to check
 * @return      bool $use_modal True if we should use the modal, false otherwise
 */
function edd_free_downloads_use_modal( $download_id = false ) {
	$use_modal = false;

	if( $download_id && ! edd_has_variable_prices( $download_id ) && ! edd_is_bundled_product( $download_id ) ) {
		$price = floatval( edd_get_lowest_price_option( $download_id ) );

		if( $price == 0 ) {
			$use_modal = true;
		}
	}

	return $use_modal;
}
