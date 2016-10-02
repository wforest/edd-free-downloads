<?php
/**
 * Actions
 *
 * @package     EDD\FreeDownloads\Actions
 * @since       1.1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers a new rewrite endpoint
 *
 * @since       1.1.0
 * @param       array $rewrite_rules The existing rewrite rules
 * @return      void
 */
function edd_free_downloads_add_endpoint( $rewrite_rules ) {
	add_rewrite_endpoint( 'edd-free-download', EP_ALL );
}
add_action( 'init', 'edd_free_downloads_add_endpoint' );


/**
 * Handle newsletter opt-in
 *
 * @since       1.1.0
 * @return      void
 */
function edd_free_downloads_remove_optin() {
	if ( ! isset( $_POST['edd_free_download_email'] ) ) {
		return;
	}

	if ( edd_get_option( 'edd_free_downloads_newsletter_optin', false ) ) {
		// Build user info array for global opt-in
		$user_info = array(
			'email'      => $_POST['edd_free_download_email'],
			'first_name' => ( isset( $_POST['edd_free_download_fname'] ) ? $_POST['edd_free_download_fname'] : false ),
			'last_name'  => ( isset( $_POST['edd_free_download_lname'] ) ? $_POST['edd_free_download_lname'] : false ),
		);

		// MailChimp
		if ( class_exists( 'EDD_MailChimp' ) ) {
			global $edd_mc;

			if ( isset( $_POST['edd_free_download_optin'] ) ) {
				$edd_mc->subscribe_email( $user_info );
			} else {
				remove_action( 'edd_complete_download_purchase', array( $edd_mc, 'completed_download_purchase_signup' ) );
			}
		}

		// GetResponse
		if ( class_exists( 'EDD_GetResponse' ) ) {
			if ( isset( $_POST['edd_free_download_optin'] ) ) {
				edd_getresponse()->newsletter->subscribe_email( $user_info );
			} else {
				remove_action( 'edd_complete_download_purchase', array( edd_getresponse()->newsletter, 'completed_download_purchase_signup' ) );
			}
		}

		// Aweber
		if ( class_exists( 'EDD_Aweber' ) ) {
			global $edd_aweber;

			if ( isset( $_POST['edd_free_download_optin'] ) ) {
				$edd_aweber->subscribe_email( $user_info );
			} else {
				remove_action( 'edd_complete_download_purchase', array( $edd_aweber, 'completed_download_purchase_signup' ) );
			}
		}

		// MailPoet
		if ( class_exists( 'EDD_MailPoet' ) ) {
			global $edd_mp;

			if ( isset( $_POST['edd_free_download_optin'] ) ) {
				$edd_mp->subscribe_email( $user_info );
			} else {
				remove_action( 'edd_complete_download_purchase', array( $edd_mp, 'completed_download_purchase_signup' ) );
			}
		}

		// Sendy
		if ( class_exists( 'EDD_Sendy' ) ) {
			global $edd_sendy;

			if ( isset( $_POST['edd_free_download_optin'] ) ) {
				$edd_sendy->subscribe_email( $user_info );
			} else {
				remove_action( 'edd_complete_download_purchase', array( $edd_sendy, 'completed_download_purchase_signup' ) );
			}
		}

		// Convert Kit
		if ( class_exists( 'EDD_ConvertKit' ) ) {
			global $edd_convert_kit;

			if ( isset( $_POST['edd_free_download_optin'] ) ) {
				$edd_convert_kit->subscribe_email( $user_info );
			} else {
				remove_action( 'edd_complete_download_purchase', array( $edd_convert_kit, 'completed_download_purchase_signup' ) );
			}
		}
	}
}
add_action( 'edd_update_payment_status', 'edd_free_downloads_remove_optin', -10 );


/**
 * Callback function for the Compression Status setting
 *
 * @since       2.0.0
 * @return      void
 */
function edd_free_downloads_zip_status() {
	if ( class_exists( 'ZipArchive' ) ) {
		$html  = '<span class="edd-free-downloads-zip-status-available">' . __( 'Available', 'edd-free-downloads' ) . '</span>';
		$html .= '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . __( 'Compression Status', 'edd-free-downloads' ) . '</strong>: ' . sprintf( __( 'Great! It looks like you have the ZipArchive class available! That means that we can auto-compress the files for multi-file %s.', 'edd-free-downloads' ), edd_get_label_plural( true ) ) . '"></span>';
	} else {
		$html  = '<span class="edd-free-downloads-zip-status-unavailable">' . __( 'Unavailable', 'edd-free-downloads' ) . '</span>';
		$html .= '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . __( 'Compression Status', 'edd-free-downloads' ) . '</strong>: ' . sprintf( __( 'Oops! It looks like you don\'t have the ZipArchive class available! If you want us to auto-compress the files for multi-file %s, please make sure that your PHP instance is compiled with ZipArchive support.', 'edd-free-downloads' ), edd_get_label_plural( true ) ) . '"></span>';
	}

	echo $html;
}
add_action( 'edd_free_downloads_zip_status', 'edd_free_downloads_zip_status' );


/**
 * Ensure the cache directory exists
 *
 * @since       2.0.0
 * @return      void
 */
function edd_free_downloads_directory_exists() {
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'] . '/edd-free-downloads-cache/';

	// Ensure that the cache directory exists
	if ( ! is_dir( $upload_dir ) ) {
		wp_mkdir_p( $upload_dir );
	}

	// Top level blank index.php
	if ( ! file_exists( $upload_dir . 'index.php' ) ) {
		@file_put_contents( $upload_dir . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	// Top level .htaccess
	$rules = "Options -Indexes";
	if ( file_exists( $upload_dir . '.htaccess' ) ) {
		$contents = @file_get_contents( $upload_dir . '.htaccess' );

		if ( $contents !== $rules || ! $contents ) {
			@file_put_contents( $upload_dir . '.htaccess', $rules );
		}
	} else {
		@file_put_contents( $upload_dir . '.htaccess', $rules );
	}
}
add_action( 'admin_init', 'edd_free_downloads_directory_exists' );


function edd_free_downloads_delete_cached_files() {
	if ( ! isset( $_GET['post'] ) ) {
		wp_die( __( 'No download specified!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_free_downloads_cache_nonce' ) ) {
		wp_die( __( 'Nonce verification failed!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	$upload_dir  = wp_upload_dir();
	$upload_dir  = trailingslashit( $upload_dir['basedir'] . '/edd-free-downloads-cache' );
	$download_id = absint( $_GET['post'] );

	// Delete cached remote files
	$files = edd_free_downloads_get_files( $download_id );

	foreach ( $files as $file_name => $file_path ) {
		if( file_exists( $upload_dir . $file_name ) ) {
			unlink( $upload_dir . $file_name );
		}
	}

	// Delete cached zip file
	$zip_name = apply_filters( 'edd_free_downloads_zip_name', strtolower( str_replace( ' ', '-', get_bloginfo( 'name' ) ) ) . '-bundle-' . $download_id . '.zip' );
	$zip_file = $upload_dir . '/' . $zip_name;

	if( file_exists( $zip_file ) ) {
		unlink( $zip_file );
	}

	$redirect_url = add_query_arg( array( 'edd-action' => null, '_wpnonce' => null, 'edd-message' => 'fd-files-deleted' ) );
	wp_safe_redirect( $redirect_url );
	die();
}
add_action( 'edd_free_downloads_delete_cached_files', 'edd_free_downloads_delete_cached_files' );
