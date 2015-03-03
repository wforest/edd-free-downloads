<?php
/**
 * Helper functions
 *
 * @package     EDD\FreeDownloads\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


function edd_free_download_process() {
    $user = get_user_by( 'email', $_POST['edd_free_download_email'] );

    $user_id    = $user ? $user->ID : 0;
    $email      = strip_tags( trim( $_POST['edd_free_download_email'] ) );
    $download_id= $_POST['edd_free_download_id'];

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
        'id'        => $user_id,
        'email'     => $email,
        'first_name'=> $user_first,
        'last_name' => $user_last,
        'discount'  => 'none'
    );

    $cart_details   = array();
    $item_price     = edd_get_download_price( $download_id );

    $cart_details[0] = array(
        'name'      => get_the_title( $download_id ),
        'id'        => $download_id,
        'price'     => edd_format_amount( 0 ),
        'subtotal'  => edd_format_amount( 0 ),
        'quantity'  => 1,
        'tax'       => edd_format_amount( 0 )
    );

    $date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

    $downloads[0] = array(
        'id'    => $download_id
    );

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

    $redirect_url = edd_get_option( 'edd_free_downloads_redirect', false );
    $redirect_url = $redirect_url ? $redirect_url : edd_get_success_page_url();

    wp_redirect( apply_filters( 'edd_free_downloads_redirect', $redirect_url, $payment_id, $purchase_data ) );
    edd_die();
}
add_action( 'edd_free_download_process', 'edd_free_download_process' );
