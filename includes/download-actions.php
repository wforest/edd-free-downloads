<?php
/**
 * Download Actions
 *
 * @package     EDD\FreeDownloads\Download\Actions
 * @since       2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Process downloads
 *
 * @since       1.0.0
 * @return      void
 */
function edd_free_download_process() {

	// No spammers please!
	if ( ! empty( $_POST['edd_free_download_check'] ) ) {
		wp_die( __( 'Bad spammer, no download!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if ( ! isset( $_POST['edd_free_download_nonce'] ) || ! wp_verify_nonce( $_POST['edd_free_download_nonce'], 'edd_free_download_nonce' ) ) {
		wp_die( __( 'Cheatin&#8217; huh?', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	if ( ! isset( $_POST['edd_free_download_email'] ) ) {
		wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
	}

	if ( ! is_user_logged_in() ) {
		// Bypass auto-registration
		if ( edd_get_option( 'edd_free_downloads_bypass_auto_register', false ) && class_exists( 'EDD_Auto_Register' ) ) {
			remove_action( 'edd_auto_register_insert_user', array( EDD_Auto_Register::get_instance(), 'email_notifications' ), 10, 3 );
			remove_action( 'edd_insert_payment', array( EDD_Auto_Register::get_instance(), 'maybe_insert_user' ), 10, 2 );
		}
	}

	if ( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() && ! class_exists( 'EDD_Auto_Register' ) ) {
		// If we are registering a user, make sure the required fields are filled out
		if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! class_exists( 'EDD_Auto_Register' ) ) {
			if ( ! isset( $_POST['edd_free_download_username'] ) || ! isset( $_POST['edd_free_download_pass'] ) || ! isset( $_POST['edd_free_download_pass2'] ) ) {
				wp_die( __( 'The username and password fields are required, please try again.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
			}
		}

		if ( $_POST['edd_free_download_pass'] != $_POST['edd_free_download_pass2'] ) {
			wp_die( __( 'Password and password confirmation fields don\'t match, please try again,', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		}

		// Make sure the username doesn't already exist
		$username = trim( $_POST['edd_free_download_username'] );

		if ( username_exists( $username ) ) {
			wp_die( __( 'The specified username already exists, please log in or try again.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		} elseif ( ! edd_validate_username( $username ) ) {
			// Invalid username
			if ( is_multisite() ) {
				wp_die( __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
			} else {
				wp_die( __( 'Invalid username.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
			}
		}

		// Make sure the email doesn't already exist
		if ( email_exists( $_POST['edd_free_download_email'] ) ) {
			wp_die( __( 'The specified email has already been used, please log in or try again.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
		}
	}

	$email = sanitize_email( trim( $_POST['edd_free_download_email'] ) );
	$user  = get_user_by( 'email', $email );

	if ( ! is_email( $_POST['edd_free_download_email'] ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		/**
		 * This appears to actually be tested by the popup modal, stopping a user from moving forward with the download
		 */
		wp_die( esc_html__( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
	}

	// No banned emails please!
	if ( edd_is_email_banned( $email ) ) {
		/**
		 * @todo  Update translation files
		 */
		wp_die(
			esc_html__( 'Your email address or domain are not allowed to download content. Please contact support if you feel this is an error.', 'edd-free-downloads'  ),
			esc_html__( 'Oops!', 'edd-free-downloads' )
		);
	}

	$download_id = isset( $_POST['edd_free_download_id'] ) ? intval( $_POST['edd_free_download_id'] ) : false;
	/**
	 * @todo  Update translation files
	 */
	if ( empty( $download_id ) ) {
		wp_die( esc_html__( 'An internal error has occurred, please try again or contact support. $download_id is empty', 'edd-free-downloads' ), esc_html__( 'Oops!', 'edd-free-downloads' ) );
	}

	$download = get_post( $download_id );
	// Bail if this isn't a valid download
	/**
	 * @todo  update translation files
	 */
	if ( ! $download instanceof WP_Post ) {
		wp_die( esc_html__( 'An internal error has occurred, please try again or contact support. Download is not a valid object', 'edd-free-downloads' ), esc_html__( 'Oops!', 'edd-free-downloads' ) );
	}

	/**
	 * @todo  update translation files
	 */
	if ( 'download' != $download->post_type ) {
		wp_die( esc_html__( 'An internal error has occurred, please try again or contact support. Invalid object type', 'edd-free-downloads' ), esc_html__( 'Oops!', 'edd-free-downloads' ) );
	}

	// Bail if this isn't a published download (or the current user can't edit it)
	/**
	 * @todo  update translation files
	 */
	if ( ! current_user_can( 'edit_post', $download->ID ) && 'publish' != $download->post_status ) {
		wp_die( esc_html__( 'An internal error has occurred, please try again or contact support. User does not have access to this download or the download is not available', 'edd-free-downloads' ), esc_html__( 'Oops!', 'edd-free-downloads' ) );
	}

	if ( isset( $_POST['edd_free_download_fname'] ) ) {
		$user_first = sanitize_text_field( $_POST['edd_free_download_fname'] );
	} else {
		$user_first = $user ? $user->first_name : '';
	}

	if ( isset( $_POST['edd_free_download_lname'] ) ) {
		$user_last = sanitize_text_field( $_POST['edd_free_download_lname'] );
	} else {
		$user_last = $user ? $user->last_name : '';
	}

	$user_info = array(
		'id'         => $user ? $user->ID : '-1',
		'email'      => $email,
		'first_name' => $user_first,
		'last_name'  => $user_last,
		'discount'   => 'none'
	);

	$cart_details = array();
	$price_ids    = isset( $_POST['edd_free_download_price_id'] ) ? $_POST['edd_free_download_price_id'] : false;

	if ( ! $price_ids && isset( $_GET['price_ids'] ) ) {
		$price_ids = sanitize_text_field( $_GET['price_ids'] );
	}

	$download_files = array();

	if ( isset( $price_ids ) && is_array( $price_ids ) ) {
		foreach ( $price_ids as $cart_id => $price_id ) {
			if ( ! edd_is_free_download( $download_id, $price_id ) ) {
				wp_die( __( 'The requested product is not a free product! Please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
			}

			$download_files[] = edd_get_download_files( $download_id, $price_id );

			$cart_details[ $cart_id ] = array(
				'name'        => get_the_title( $download_id ),
				'id'          => $download_id,
				'price'       => edd_format_amount( 0 ),
				'subtotal'    => edd_format_amount( 0 ),
				'quantity'    => 1,
				'tax'         => edd_format_amount( 0 ),
				'item_number' => array(
					'id'       => $download_id,
					'quantity' => 1,
					'options'  => array(
						'quantity' => 1,
						'price_id' => $price_id
					)
				)
			);
		}
	} elseif( isset( $price_ids ) && ! is_array( $price_ids ) ) {
		if ( ! edd_is_free_download( $download_id, $price_ids ) ) {
			wp_die( __( 'The requested product is not a free product! Please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
		}

		$download_files[] = edd_get_download_files( $download_id, $price_ids );

		$cart_details[0] = array(
			'name'        => get_the_title( $download_id ),
			'id'          => $download_id,
			'price'       => edd_format_amount( 0 ),
			'subtotal'    => edd_format_amount( 0 ),
			'quantity'    => 1,
			'tax'         => edd_format_amount( 0 ),
			'item_number' => array(
				'id'       => $download_id,
				'quantity' => 1,
				'options'  => array(
					'quantity' => 1,
					'price_id' => $price_ids
				)
			)
		);
	} else {
		if ( ! edd_is_free_download( $download_id ) ) {
			wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
		}

		$download_files[] = edd_get_download_files( $download_id, false );

		$cart_details[0] = array(
			'name'     => get_the_title( $download_id ),
			'id'       => $download_id,
			'price'    => edd_format_amount( 0 ),
			'subtotal' => edd_format_amount( 0 ),
			'quantity' => 1,
			'tax'      => edd_format_amount( 0 )
		);
	}

	$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

	/**
	 * Gateway set to manual because manual + free lists as 'Free Purchase' in order details
	 */
	$purchase_data = array(
		'price'        => edd_format_amount( 0 ),
		'tax'          => edd_format_amount( 0 ),
		'post_date'    => $date,
		'purchase_key' => strtolower( md5( uniqid() ) ),
		'user_email'   => $email,
		'user_info'    => $user_info,
		'currency'     => edd_get_currency(),
		'downloads'    => array( $download_id ),
		'cart_details' => $cart_details,
		'gateway'      => 'manual',
		'status'       => 'pending'
	);

	$payment_id = edd_insert_payment( $purchase_data );

	// Disable purchase emails
	if ( edd_get_option( 'edd_free_downloads_disable_emails', false ) ) {
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );

		if ( function_exists( 'Receiptful' ) ) {
			remove_action( 'edd_complete_purchase', array( Receiptful()->email, 'send_transactional_email' ) );
		}
	}

	edd_update_payment_status( $payment_id, 'publish' );
	edd_insert_payment_note( $payment_id, __( 'Purchased through EDD Free Downloads', 'edd-free-downloads' ) );
	edd_empty_cart();
	edd_set_purchase_session( $purchase_data );

	if ( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() && ! class_exists( 'EDD_Auto_Register' ) ) {
		$account = array(
			'user_login' => trim( $_POST['edd_free_download_username'] ),
			'user_pass'  => trim( $_POST['edd_free_download_pass'] ),
			'user_email' => $email,
			'first_name' => $user_first,
			'last_name'  => $user_last
		);

		edd_register_and_login_new_user( $account );
	}

	$payment_meta       = edd_get_payment_meta( $payment_id );
	$on_complete        = edd_get_option( 'edd_free_downloads_on_complete', 'default' );
	$success_page       = edd_get_success_page_uri();
	$custom_url         = edd_get_option( 'edd_free_downloads_redirect', false );
	$custom_url         = $custom_url ? esc_url( $custom_url ) : $success_page;
	$mobile_custom_url  = edd_get_option( 'edd_free_downloads_mobile_redirect', false );
	$mobile_custom_url  = $mobile_custom_url ? esc_url( $mobile_custom_url ) : $success_page;
	$apple_custom_url   = edd_get_option( 'edd_free_downloads_apple_redirect', false );
	$appple_custom_url  = $apple_custom_url ? esc_url( $apple_custom_url ) : $success_page;
	$mobile_on_complete = edd_get_option( 'edd_free_downloads_mobile_on_complete', 'default' );
	$apple_on_complete  = edd_get_option( 'edd_free_downloads_apple_on_complete', 'default' );

	switch ( $on_complete ) {
		case 'default' :
			$redirect_url = $success_page;
			break;
		case 'redirect' :
			$redirect_url = $custom_url;
			break;
		case 'auto-download' :
			$redirect_url = add_query_arg( array(
				'edd_action' => 'free_downloads_process_download',
				'payment-id' => $payment_id
			) );
			break;
	}

	if ( wp_is_mobile() ) {
		$mobile = new Mobile_Detect;
		$is_ios = $mobile->isiOS();

		if ( ( $is_ios && $apple_on_complete == 'default' ) || ( ! $is_ios && $mobile_on_complete == 'default' ) ) {
			$redirect_url = $redirect_url;
		} elseif( ( $is_ios && $apple_on_complete == 'confirmation' ) || ( ! $is_ios && $mobile_on_complete == 'confirmation' ) ) {
			$redirect_url = $success_page;
		} elseif( ( ! $is_ios && $mobile_on_complete == 'auto-download' ) ) {
			$redirect_url = add_query_arg( array(
				'edd_action' => 'free_downloads_process_download',
				'payment-id' => $payment_id
			) );
		} elseif( ( $is_ios && $apple_on_complete == 'redirect' ) || ( ! $is_ios && $mobile_on_complete == 'redirect' ) ) {
			$redirect_url = $is_ios ? $apple_custom_url : $mobile_custom_url;
		}
	}

	$redirect_url = $redirect_url ? $redirect_url : $success_page;

	// Support Conditional Success Redirects
	if ( function_exists( 'edd_csr_is_redirect_active' ) && $redirect_url == $success_page ) {
		if ( edd_csr_is_redirect_active( edd_csr_get_redirect_id( $payment_meta['cart_details'][0]['id'] ) ) ) {
			$redirect_id = edd_csr_get_redirect_id( $payment_meta['cart_details'][0]['id'] );

			$redirect_url = edd_csr_get_redirect_page_id( $redirect_id );
			$redirect_url = get_permalink( $redirect_url );
		}
	}

	wp_redirect( apply_filters( 'edd_free_downloads_redirect', $redirect_url, $payment_id ) );
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
	if ( ! isset( $_GET['payment-id'] ) && ! $_GET['download_id'] ) {
		return;
	}

	if ( ! function_exists( 'edd_get_file_ctype' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/process-download.php';
	}

	$download_files = array();

	if ( isset( $_GET['payment-id'] ) ) {
		$payment_meta = edd_get_payment_meta( $_GET['payment-id'] );
		$cart         = edd_get_payment_meta_cart_details( $_GET['payment-id'], true );

		if ( $cart ) {
			foreach ( $cart as $key => $item ) {
				$download_id = $item['id'];
				$archive_url = get_post_meta( $download_id, '_edd_free_downloads_file', true );

				if ( $archive_url && $archive_url != '' ) {
					$download_files = array_merge( $download_files, array( basename( $archive_url ) => $archive_url ) );
				} else {
					if ( array_key_exists( 'item_number', $item ) ) {
						$download_files = array_merge( $download_files, edd_free_downloads_get_files( $download_id, $item['item_number']['options']['price_id'] ) );
					} else {
						$download_files = array_merge( $download_files, edd_free_downloads_get_files( $download_id ) );
					}
				}
			}
		}
	} else {
		$download_id = absint( $_GET['download_id'] );
		$price_ids   = '';

		if ( isset( $_GET['price_ids'] ) && $_GET['price_ids'] != '' ) {
			$price_ids = sanitize_text_field( $_GET['price_ids'] );
		} else {
			if ( edd_has_variable_prices( $download_id ) ) {
				$price_ids = edd_get_default_variable_price( $download_id );
			}
		}

		$archive_url = get_post_meta( $download_id, '_edd_free_downloads_file', true );

		if ( $archive_url && $archive_url != '' ) {
			$download_files = array_merge( $download_files, array( basename( $archive_url ) => $archive_url ) );
		} elseif ( ! edd_is_bundled_product( $download_id ) ) {
			if ( isset( $price_ids ) && $price_ids != '' ) {
				$price_ids = explode( ',', trim( $price_ids ) );

				foreach ( $price_ids as $price_id ) {
					$download_files = array_merge( $download_files, edd_free_downloads_get_files( $download_id, $price_id ) );
				}
			} else {
				$download_files = array_merge( $download_files, edd_free_downloads_get_files( $download_id ) );
			}
		} else {
			$download_files = array_merge( $download_files, edd_free_downloads_get_files( $download_id ) );
		}
	}


	$download_files = array_unique( $download_files );

	if( is_array( $download_files ) && count( $download_files ) > 0 ) {
		if ( count( $download_files ) > 1 ) {
			$download_url = edd_free_downloads_compress_files( $download_files, $download_id );
			$download_url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $download_url );

			// Prevent errors with edd_free_downloads_download_file()
			$hosted = 'multi';
		} else {

			$download_url = array_values( $download_files );
			$download_url = $download_url[0];

			$hosted = edd_free_downloads_get_host( $download_url );
			if ( 'local' !== $hosted ) {
				$download_url = edd_free_downloads_fetch_remote_file( $download_url, $hosted );
				$download_url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $download_url );
			}
		}

		edd_free_downloads_download_file( $download_url, $hosted );
	}
}
add_action( 'edd_free_downloads_process_download', 'edd_free_downloads_process_auto_download' );
