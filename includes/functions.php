<?php
/**
 * Helper functions
 *
 * @package     EDD\FreeDownloads\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


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

    if( ! wp_verify_nonce( $_POST['edd_free_download_nonce'], 'edd_free_download_nonce' ) ) {
        wp_die( __( 'Cheatin&#8217; huh?', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
    }

    if ( ! isset( $_POST['edd_free_download_email'] ) || ! is_email( $_POST['edd_free_download_email'] ) ) {
        wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ), array( 'back_link' => true ) );
    }

    if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() ) {
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

    $email       = strip_tags( trim( $_POST['edd_free_download_email'] ) );
    $user        = get_user_by( 'email', $email );

    // No banned emails please!
    if( edd_is_email_banned( $email ) ) {
        wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
    }

    $download_id = isset( $_POST['edd_free_download_id'] ) ? intval( $_POST['edd_free_download_id'] ) : false;
    if ( empty( $download_id ) ) {
        wp_die( __( 'An internal error has occurred, please try again or contact support.', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
    }

    $post_type = get_post_type( $download_id );
    if ( 'download' !== $post_type ) {
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
    $download_files = edd_get_download_files( $download_id );
    $item_price     = edd_get_download_price( $download_id );

    if ( ! edd_is_free_download( $download_id ) ) {
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

    $date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

    $downloads = array();
    foreach( $download_files as $file ) {
        $downloads[] = array(
            'id'    => $file['attachment_id']
        );
    }

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
        'downloads'     => $downloads,
        'cart_details'  => $cart_details,
        'gateway'       => 'manual',
        'status'        => 'pending'
    );

    $payment_id = edd_insert_payment( $purchase_data );

    edd_update_payment_status( $payment_id, 'publish' );
    edd_insert_payment_note( $payment_id, __( 'Purchased through EDD Free Downloads', 'edd-free-downloads' ) );
    edd_empty_cart();
    edd_set_purchase_session( $purchase_data );

    if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() ) {
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
    $redirect_url = $redirect_url ? esc_url( $redirect_url ) : edd_get_success_page_url();

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
            $download_url = edd_get_download_file_url( $payment_meta['key'], $payment_meta['user_info']['email'], 0, $payment_meta['cart_details'][0]['id'] );

            wp_safe_redirect( $download_url );
            edd_die();
        }
    }

    wp_redirect( apply_filters( 'edd_free_downloads_redirect', $redirect_url, $payment_id, $purchase_data ) );
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
        $download_url = edd_get_download_file_url( $payment_meta['key'], $payment_meta['user_info']['email'], 0, $payment_meta['cart_details'][0]['id'] );

        echo '<script type="text/javascript">jQuery(document).ready(function($){$(location).attr("href", "' . $download_url . '")});</script>';
    }
}
add_action( 'wp_head', 'edd_free_downloads_process_auto_download' );


/**
 * Check if a download should use the modal dialog
 *
 * @since       1.0.0
 * @param       int $download_id The ID to check
 * @return      bool $ret True if we should use the modal, false otherwise
 */
function edd_free_downloads_use_modal( $download_id = false ) {
    $ret = false;

    if( $download_id && ! edd_has_variable_prices( $download_id ) && ! edd_is_bundled_product( $download_id ) ) {
        $price = floatval( edd_get_lowest_price_option( $download_id ) );

        if( $price == 0 ) {
            $ret = true;
        }
    }

    return $ret;
}
