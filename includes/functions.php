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
	$sold_out  = false;

	if( class_exists( 'EDD_Purchase_Limit' ) ) {
		$price_id = false;

		if( is_user_logged_in() ) {
			$user = new WP_User( get_current_user_id() );
		}

		$email = isset( $user ) ? $user->user_email : false;

		if( edd_has_variable_prices( $download_id ) ) {
			$prices = edd_get_variable_prices( $download_id );

			foreach( $prices as $price ) {
				if( floatval( $price['amount'] ) == 0 ) {
					$price_id = $price['index'];
				}
			}
		}

		$sold_out = edd_pl_is_item_sold_out( $download_id, $price_id, $email );
	}

	if( get_post_meta( $download_id, '_edd_free_downloads_bypass', true ) !== 'on' && ! $sold_out ) {
		if( $download_id && ! edd_has_variable_prices( $download_id ) && ! edd_is_bundled_product( $download_id ) ) {
			$price = floatval( edd_get_lowest_price_option( $download_id ) );

			if( $price == 0 ) {
				$use_modal = true;
			}
		} elseif( edd_has_variable_prices( $download_id ) ) {
			$price = floatval( edd_get_lowest_price_option( $download_id ) );

			if( $price == 0 ) {
				$use_modal = true;
			}
		}
	}

	return $use_modal;
}


/**
 * Check for supported newsletter plugins
 *
 * @since       1.1.0
 * @return      bool $plugin_exists True if a supported plugin is active, false otherwise
 */
function edd_free_downloads_has_newsletter_plugin() {
	$plugin_exists = false;

	/**
	 * The $supported_plugins array is an array of
	 * plugin CLASSES which use the EDD_Newsletter class
	 */
	$supported_plugins = apply_filters( 'edd_free_downloads_supported_plugins', array(
		'EDD_GetResponse',
		'EDD_MailChimp',
		'EDD_Aweber',
		'EDD_MailPoet',
		'EDD_Sendy',
		'EDD_ConvertKit'
	) );

	foreach( $supported_plugins as $plugin_class ) {
		if( class_exists( $plugin_class ) ) {
			$plugin_exists = true;
		}
	}

	return $plugin_exists;
}