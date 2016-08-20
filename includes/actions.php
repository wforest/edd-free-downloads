<?php
/**
 * Actions
 *
 * @package     EDD\FreeDownloads\Actions
 * @since       1.1.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Maybe override straight to checkout
 *
 * @since       1.1.0
 * @param       bool $ret Whether or not to go straight to checkout
 * @global      object $post The WordPress post object
 * @return      bool $ret Whether or not to go straight to checkout
 */
function edd_free_downloads_override_redirect( $ret ) {
	global $post;

	$id = get_the_ID();

	if( is_single( $id ) && get_post_type( $id ) == 'download' && edd_is_free_download( $id ) ) {
		$ret = false;
	}

	return $ret;
}
add_filter( 'edd_straight_to_checkout', 'edd_free_downloads_override_redirect' );


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
 * Add a new query var
 *
 * @since       1.1.0
 * @param       array $vars The current query vars
 * @return      array $vars The new query vars
 */
function edd_free_downloads_query_vars( $vars ) {
	$vars[] = 'download_id';

	return $vars;
}
add_filter( 'query_vars', 'edd_free_downloads_query_vars', -1 );


/**
 * Listen for edd-free-download queries and handle accordingly
 *
 * @since       1.1.0
 * @return      void
 */
function edd_free_downloads_display_redirect() {
	ob_start();

	edd_get_template_part( 'download', 'redirect' );

	return ob_get_clean();
}
add_action( 'wp_head', 'edd_free_downloads_display_redirect' );


/**
 * Listen for edd-free-download queries and handle accordingly
 *
 * @since       1.0.1
 * @return      void
 */
function edd_free_downloads_display_inline() {
	ob_start();

	edd_get_template_part( 'download', 'modal' );

	return ob_get_clean();
}
add_action( 'wp_footer', 'edd_free_downloads_display_inline' );


/**
 * Process downloads
 *
 * @since       1.0.0
 * @return      void
 */
function edd_free_download_process() {
	// No spammers please!
	if( ! empty( $_POST['edd_free_download_check'] ) ) {
		wp_die( __( 'Bad spammer, no download!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if( ! isset( $_POST['edd_free_download_nonce'] ) || ! wp_verify_nonce( $_POST['edd_free_download_nonce'], 'edd_free_download_nonce' ) ) {
		wp_die( __( 'Cheatin&#8217; huh?', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if ( ! isset( $_POST['edd_free_download_email'] ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
	}

	if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() && ! class_exists( 'EDD_Auto_Register' ) ) {
		// If we are registering a user, make sure the required fields are filled out
		if( ! isset( $_POST['edd_free_download_username'] ) || ! isset( $_POST['edd_free_download_pass'] ) || ! isset( $_POST['edd_free_download_pass2'] ) ) {
			wp_die( __( 'The username and password fields are required, please try again.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		}

		if( $_POST['edd_free_download_pass'] != $_POST['edd_free_download_pass2'] ) {
			wp_die( __( 'Password and password confirmation fields don\'t match, please try again,', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		}

		// Make sure the username doesn't already exist
		$username = trim( $_POST['edd_free_download_username'] );

		if( username_exists( $username ) ) {
			wp_die( __( 'The specified username already exists, please log in or try again.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		} elseif( ! edd_validate_username( $username ) ) {
			// Invalid username
			if( is_multisite() ) {
				wp_die( __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
			} else {
				wp_die( __( 'Invalid username.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
			}
		}

		// Make sure the email doesn't already exist
		if( email_exists( $_POST['edd_free_download_email'] ) ) {
			wp_die( __( 'The specified email has already been used, please log in or try again.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		}
	}

	$email       = sanitize_email( trim( $_POST['edd_free_download_email'] ) );
	$user        = get_user_by( 'email', $email );

	if( ! is_email( $_POST['edd_free_download_email'] ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	// No banned emails please!
	if( edd_is_email_banned( $email ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	$download_id = isset( $_POST['edd_free_download_id'] ) ? intval( $_POST['edd_free_download_id'] ) : false;
	if ( empty( $download_id ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	$download = get_post( $download_id );

	// Bail if this isn't a valid download
	if( ! is_object( $download ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if( 'download' != $download->post_type ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	// We don't currently support bundled products
	if( edd_is_bundled_product( $download_id ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	// Bail if this isn't a published download (or the current user can't edit it)
	if( ! current_user_can( 'edit_post', $download->ID ) && $download->post_status != 'publish' ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if( isset( $_POST['edd_free_download_fname'] ) ) {
		$user_first = sanitize_text_field( $_POST['edd_free_download_fname'] );
	} else {
		$user_first = $user ? $user->first_name : '';
	}

	if( isset( $_POST['edd_free_download_lname'] ) ) {
		$user_last = sanitize_text_field( $_POST['edd_free_download_lname'] );
	} else {
		$user_last = $user ? $user->last_name : '';
	}

	$user_info = array(
		'id'        => $user ? $user->ID : '-1',
		'email'     => $email,
		'first_name'=> $user_first,
		'last_name' => $user_last,
		'discount'  => 'none'
	);

	$cart_details   = array();
	$price_id       = ! empty( $_POST['edd_free_download_price_id'] ) ? intval( $_POST['edd_free_download_price_id'] ) : false;
	$download_files = edd_get_download_files( $download_id, $price_id );

	if ( ! edd_is_free_download( $download_id, $price_id ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	$cart_details[0] = array(
		'name'      => get_the_title( $download_id ),
		'id'        => $download_id,
		'price'     => edd_format_amount( 0 ),
		'subtotal'  => edd_format_amount( 0 ),
		'quantity'  => 1,
		'tax'       => edd_format_amount( 0 )
	);

	if( edd_has_variable_prices( $download_id ) ) {
		$cart_details[0]['price_id'] = $price_id;
	}

	$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

	/**
	 * Gateway set to manual because manual + free lists as 'Free Purchase' in order details
	 */
	$purchase_data  = array(
		'price'         => edd_format_amount( 0 ),
		'tax'           => edd_format_amount( 0 ),
		'post_date'     => $date,
		'purchase_key'  => strtolower( md5( uniqid() ) ),
		'user_email'    => $email,
		'user_info'     => $user_info,
		'currency'      => edd_get_currency(),
		'downloads'     => array( $download_id ),
		'cart_details'  => $cart_details,
		'gateway'       => 'manual',
		'status'        => 'pending'
	);

	$payment_id = edd_insert_payment( $purchase_data );

	// Disable purchase emails
	if( edd_get_option( 'edd_free_downloads_disable_emails', false ) ) {
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );

		if( function_exists( 'Receiptful' ) ) {
			remove_action( 'edd_complete_purchase', array( Receiptful()->email, 'send_transactional_email' ) );
		}
	}

	edd_update_payment_status( $payment_id, 'publish' );
	edd_insert_payment_note( $payment_id, __( 'Purchased through EDD Free Downloads', 'edd-free-downloads' ) );
	edd_empty_cart();
	edd_set_purchase_session( $purchase_data );

	if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() && ! class_exists( 'EDD_Auto_Register' ) ) {
		$account = array(
			'user_login'    => trim( $_POST['edd_free_download_username'] ),
			'user_pass'     => trim( $_POST['edd_free_download_pass'] ),
			'user_email'    => $email,
			'first_name'    => $user_first,
			'last_name'     => $user_last
		);

		edd_register_and_login_new_user( $account );
	}

	$payment_meta = edd_get_payment_meta( $payment_id );

	$redirect_url = edd_get_option( 'edd_free_downloads_redirect', false );
	$redirect_url = $redirect_url ? esc_url( $redirect_url ) : edd_get_success_page_uri();

	// Support Conditional Success Redirects
	if( function_exists( 'edd_csr_is_redirect_active' ) ) {
		if( edd_csr_is_redirect_active( edd_csr_get_redirect_id( $payment_meta['cart_details'][0]['id'] ) ) ) {
			$redirect_id = edd_csr_get_redirect_id( $payment_meta['cart_details'][0]['id'] );

			$redirect_url = edd_csr_get_redirect_page_id( $redirect_id );
			$redirect_url = get_permalink( $redirect_url );
		}
	}

	if( edd_get_option( 'edd_free_downloads_auto_download', false ) && count( $download_files ) == 1 ) {
		if( edd_get_option( 'edd_free_downloads_auto_download_redirect', false ) ) {
			$redirect_url = add_query_arg( 'auto-download', $payment_id, $redirect_url );
		} else {
			$download_files = edd_get_download_files( $payment_meta['cart_details'][0]['id'] );
			$download_index = array_keys( $download_files );
			$download_url = edd_get_download_file_url( $payment_meta['key'], $payment_meta['user_info']['email'], $download_index[0], $payment_meta['cart_details'][0]['id'] );

			wp_safe_redirect( $download_url );
			edd_die();
		}
	}

	wp_redirect( apply_filterss( 'edd_free_downloads_redirect', $redirect_url, $payment_id, $purchase_data ) );
	edd_die();
}
add_action( 'edd_free_download_process', 'edd_free_download_process' );


/**
 * Process auto download
 *
 * @since       1.0.8
 * @return      void
 */
function edd_free_downloads_process_auto_download() {
	if( isset( $_GET['auto-download'] ) ) {
		$payment_meta = edd_get_payment_meta( $_GET['auto-download'] );
		$download_files = edd_get_download_files( $payment_meta['cart_details'][0]['id'] );
		$download_index = array_keys( $download_files );
		$download_url = edd_get_download_file_url( $payment_meta['key'], $payment_meta['user_info']['email'], $download_index[0], $payment_meta['cart_details'][0]['id'] );

		echo '<script type="text/javascript">jQuery(document).ready(function($){$(location).attr("href", "' . $download_url . '")});</script>';
	}
}
add_action( 'wp_head', 'edd_free_downloads_process_auto_download' );


/**
 * Handle newsletter opt-in
 *
 * @since       1.1.0
 * @return      void
 */
function edd_free_downloads_remove_optin() {
	if( ! isset( $_POST['edd_free_download_email'] ) ) {
		return;
	}

	// Are we allowing opt-outs?
	if( edd_get_option( 'edd_free_downloads_newsletter_optin', false ) && ! isset( $_POST['edd_free_download_optin'] ) ) {
		// Opt-out for MailChimp
		if( class_exists( 'EDD_MailChimp' ) ) {
			global $edd_mc;
			remove_action( 'edd_complete_download_purchase', array( $edd_mc, 'completed_download_purchase_signup' ) );
		}

		// Opt-out for GetResponse
		if( class_exists( 'EDD_GetResponse' ) ) {
			remove_action( 'edd_complete_download_purchase', array( EDD_GetResponse_load()->newsletter, 'completed_download_purchase_signup' ) );
		}

		// Opt-out for Aweber
		if( class_exists( 'EDD_Aweber' ) ) {
			global $edd_aweber;
			remove_action( 'edd_complete_download_purchase', array( $edd_aweber, 'completed_download_purchase_signup' ) );
		}

		// Opt-out for MailPoet
		if( class_exists( 'EDD_MailPoet' ) ) {
			global $edd_mp;
			remove_action( 'edd_complete_download_purchase', array( $edd_mp, 'completed_download_purchase_signup' ) );
		}

		// Opt-out for Sendy
		if( class_exists( 'EDD_Sendy' ) ) {
			global $edd_sendy;
			remove_action( 'edd_complete_download_purchase', array( $edd_sendy, 'completed_download_purchase_signup' ) );
		}

		// Opt-out for Convert Kit
		if( class_exists( 'EDD_ConvertKit' ) ) {
			global $edd_convert_kit;
			remove_action( 'edd_complete_download_purchase', array( $edd_convert_kit, 'completed_download_purchase_signup' ) );
		}
	}
}
add_action( 'edd_update_payment_status', 'edd_free_downloads_remove_optin', -10 );


/**
 * Adds our templates dir to the EDD template stack
 *
 * @since 2.7
 */
function edd_free_downloads_add_template_stack( $paths ) {
	$paths[55] = EDD_FREE_DOWNLOADS_DIR . 'templates/';

	return $paths;
}
add_filter( 'edd_template_paths', 'edd_free_downloads_add_template_stack' );
