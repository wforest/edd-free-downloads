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


/**
 * Get Free Downloads form errors
 *
 * @since       1.2.5
 * @return      array $errors The existing errors
 */
function edd_free_downloads_form_errors() {
	$errors = apply_filters( 'edd_free_downloads_form_errors', array(
		'email-required'     => __( 'Please enter a valid email address', 'edd-free-downloads' ),
		'email-invalid'      => __( 'Invalid email', 'edd-free-downloads' ),
		'fname-required'     => __( 'Please enter your first name', 'edd-free-downloads' ),
		'lname-required'     => __( 'Please enter your last name', 'edd-free-downloads' ),
		'username-required'  => __( 'Please enter a username', 'edd-free-downloads' ),
		'password-required'  => __( 'Please enter a password', 'edd-free-downloads' ),
		'password2-required' => __( 'Please confirm your password', 'edd-free-downloads' ),
		'password-unmatch'   => __( 'Password and password confirmation do not match', 'edd-free-downloads' )
	) );

	return $errors;
}


/**
 * Get an array of files for a given download
 *
 * @since       2.0.0
 * @param       int $download_id The download to fetch files for
 * @param       int $price_id An optional price ID for this download
 * @return      array $files The array of files for this download
 */
function edd_free_downloads_get_files( $download_id = 0, $price_id = null ) {
	$download_files = edd_get_download_files( $download_id, $price_id );
	$files          = array();

	if( ! empty( $download_files ) && is_array( $download_files ) ) {
		foreach( $download_files as $filekey => $file ) {
			$filename         = basename( $file['file'] );
			$files[$filename] = $file['file'];
		}
	}

	return $files;
}


/**
 * Compress the files for a given download
 *
 * @since       2.0.0
 * @param       array $files The files to compress
 * @return      string $file The URL of the compressed file
 */
function edd_free_downloads_compress_files( $files = array(), $download_id = 0 ) {
	$file = false;

	if( class_exists( 'ZipArchive' ) ) {
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'] . '/edd-free-downloads-cache';
		$zip_name   = apply_filters( 'edd_free_downloads_zip_name', strtolower( str_replace( ' ', '-', get_bloginfo( 'name' ) ) ) . '-bundle-' . $download_id . '.zip' );
		$zip_file   = $upload_dir . '/' . $zip_name;

		if( ! file_exists( $zip_file ) ) {
			$zip = new ZipArchive();

			if( $zip->open( $zip_file, ZIPARCHIVE::CREATE ) !== TRUE ) {
				edd_die( __( 'An unknown error occurred, please try again!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
				exit;
			}

			foreach( $files as $file_name => $file_path ) {
				$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $file_path );

				$zip->addFile( $file_path, $file_name );
			}

			$zip->close();
		}

		$file = $zip_file;
	}

	return $file;
}


/**
 * Download a given file
 *
 * @since       2.0.0
 * @param       string $download_url The URL of the file to download
 * @return      void
 */
function edd_free_downloads_download_file( $download_url ) {
	// If no file found, bail
	if( ! $download_url ) {
		edd_die( __( 'An unknown error occurred, please try again!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	$file_name      = basename( $download_url );
	$file_extension = edd_get_file_extension( $download_url );
	$ctype          = edd_get_file_ctype( $file_extension );
	$method         = edd_get_file_download_method();

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit(0);
	}

	if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() && version_compare( phpversion(), '5.4', '<' ) ) {
		set_magic_quotes_runtime(0);
	}

	@session_write_close();
	if( function_exists( 'apache_setenv' ) ) {
		@apache_setenv('no-gzip', 1);
	}
	@ini_set( 'zlib.output_compression', 'Off' );

	nocache_headers();
	header("Robots: none");
	header("Content-Type: " . $ctype . "");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=\"" . $file_name . "\"");
	header("Content-Transfer-Encoding: binary");

	if( 'x_sendfile' == $method && ( ! function_exists( 'apache_get_modules' ) || ! in_array( 'mod_xsendfile', apache_get_modules() ) ) ) {
		// If X-Sendfile is selected but is not supported, fallback to Direct
		$method = 'direct';
	}

	$file_details = parse_url( $download_url );
	$schemes      = array( 'http', 'https' ); // Direct URL schemes

	if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $download_url ) ) {

		/**
		 * Download method is seto to Redirect in settings but an absolute path was provided
		 * We need to switch to a direct download in order for the file to download properly
		 */
		$method = 'direct';

	}

	switch( $method ) :

		case 'redirect' :

			// Redirect straight to the file
			edd_deliver_download( $download_url, true );
			break;

		case 'direct' :
		default:

			$direct    = false;
			$file_path = $download_url;

			if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $download_url ) ) {

				/** This is an absolute path */
				$direct    = true;
				$file_path = $download_url;

			} else if( defined( 'UPLOADS' ) && strpos( $download_url, UPLOADS ) !== false ) {

				/**
				 * This is a local file given by URL so we need to figure out the path
				 * UPLOADS is always relative to ABSPATH
				 * site_url() is the URL to where WordPress is installed
				 */
				$file_path  = str_replace( site_url(), '', $download_url );
				$file_path  = realpath( ABSPATH . $file_path );
				$direct     = true;

			} else if( strpos( $download_url, content_url() ) !== false ) {

				/** This is a local file given by URL so we need to figure out the path */
				$file_path  = str_replace( content_url(), WP_CONTENT_DIR, $download_url );
				$file_path  = realpath( $file_path );
				$direct     = true;

			} else if( strpos( $download_url, set_url_scheme( content_url(), 'https' ) ) !== false ) {

				/** This is a local file given by an HTTPS URL so we need to figure out the path */
				$file_path  = str_replace( set_url_scheme( content_url(), 'https' ), WP_CONTENT_DIR, $download_url );
				$file_path  = realpath( $file_path );
				$direct     = true;

			}

			// Set the file size header
			header( "Content-Length: " . @filesize( $file_path ) );

			// Now deliver the file based on the kind of software the server is running / has enabled
			if ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {

				header( "X-LIGHTTPD-send-file: $file_path" );

			} elseif ( $direct && ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) ) {

				// We need a path relative to the domain
				$file_path = str_ireplace( realpath( $_SERVER['DOCUMENT_ROOT'] ), '', $file_path );
				header( "X-Accel-Redirect: /$file_path" );

			}

			if( $direct ) {

				edd_deliver_download( $file_path );

			} else {

				// The file supplied does not have a discoverable absolute path
				edd_deliver_download( $download_url, true );

			}

			break;

	endswitch;

	edd_die();
}
