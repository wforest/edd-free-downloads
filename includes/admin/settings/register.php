<?php
/**
 * Register settings
 *
 * @package     EDD\FreeDownloads\Admin\Settings\Register
 * @since       1.1.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add settings
 *
 * @since       1.1.0
 * @param       array $settings The existing plugin settings
 * @return      array The modified plugin settings
 */
function edd_free_downloads_add_settings( $settings ) {
	$new_settings = array(
		array(
			'id'    => 'edd_free_downloads_settings',
			'name'  => '<strong>' . __( 'Free Downloads', 'edd-free-downloads' ) . '</strong>',
			'desc'  => '',
			'type'  => 'header'
		),
		array(
			'id'    => 'edd_free_downloads_button_label',
			'name'  => __( 'Button Label', 'edd-free-downloads' ),
			'desc'  => __( 'Specify the label for the download button.', 'edd-free-downloads' ),
			'type'  => 'text',
			'std'   => __( 'Download Now', 'edd-free-downloads' )
		),
		array(
			'id'    => 'edd_free_downloads_get_name',
			'name'  => __( 'Collect Name', 'edd-free-downloads' ),
			'desc'  => __( 'Should we collect the first and last name of the purchaser?', 'edd-free-downloads' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'edd_free_downloads_require_name',
			'name'  => __( 'Require Name', 'edd-free-downloads' ),
			'desc'  => __( 'Should we make the first and last name fields required?', 'edd-free-downloads' ),
			'type'  => 'checkbox'
		)
	);

	if( ! class_exists( 'EDD_Auto_Register' ) ) {
		$more_settings = array(
			array(
				'id'    => 'edd_free_downloads_user_registration',
				'name'  => __( 'User Registration', 'edd-free-downloads' ),
				'desc'  => __( 'Add a registration form to the download modal.' ),
				'type'  => 'checkbox'
			)
		);

		$new_settings = array_merge( $new_settings, $more_settings );
	}

	$more_settings = array(
		array(
			'id'    => 'edd_free_downloads_close_button',
			'name'  => __( 'Display Close Button', 'edd-free-downloads' ),
			'desc'  => __( 'Should we display a close button on the email collection form?', 'edd-free-downloads' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'edd_free_downloads_auto_download',
			'name'  => __( 'Auto Download', 'edd-free-downloads' ),
			'desc'  => __( 'Automatically download files rather than redirecting to the redirect URL set below. Only applies if download has a single downloadable file.', 'edd-free-downloads' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'edd_free_downloads_auto_download_redirect',
			'name'  => __( 'Redirect On Download', 'edd-free-downloads' ),
			'desc'  => __( 'With Auto Download enabled, users will not leave the download page. Check this to enforce redirects after download.', 'edd-free-downloads' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'edd_free_downloads_redirect',
			'name'  => __( 'Custom Redirect', 'edd-free-downloads' ),
			'desc'  => __( 'Enter a URL to redirect to on completion, or leave blank for the receipt page.', 'edd-free-downloads' ),
			'type'  => 'text'
		)
	);

	return array_merge( $settings, $new_settings, $more_settings );
}
add_filter( 'edd_settings_extensions', 'edd_free_downloads_add_settings' );