<?php
/**
 * Order details page meta box
 *
 * @package     EDD\FreeDownloads\Admin\Payments\ViewOrderDetails
 * @since       
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Add our meta box to the view order details page
 *
 * @since
 * @param       int $payment_id The ID of a given payment
 * @return      void
 */
function edd_free_downloads_view_order_details( $payment_id ) {
    $payment_meta   = edd_get_payment_meta( $payment_id );
    $total_price    = edd_get_payment_amount( $payment_id );

    // Only show if free
    if( $total_price != 0 ) {
        return;
    }
    ?>
    <div id="edd-free-downloads-payment-data" class="postbox">
        <h3 class="hndle"><span><?php _e( 'Free Downloads Notes', 'edd-free-downloads' ); ?></span></h3>
        <div class="inside">
<?php var_dump( $payment_meta ); ?>
        </div>
    </div>
    <?php
}
add_action( 'edd_view_order_details_main_after', 'edd_free_downloads_view_order_details' );
