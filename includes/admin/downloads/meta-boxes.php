<?php
/**
 * Meta boxes
 *
 * @package     EDD\FreeDownloads\Admin\Downloads\MetaBoxes
 * @since       1.2.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Bypass Free Downloads settings
 *
 * @since       1.2.0
 * @param       int $post_id Download (Post) ID
 * @return      void
 */
function edd_free_downloads_render_bypass_options( $post_id = 0 ) {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$bypass = get_post_meta( $post_id, '_edd_free_downloads_bypass', true ) ? 1 : 0;
	?>
	<p><strong><?php _e( 'Bypass Free Downloads Modal:', 'edd-free-downloads' ); ?></strong></p>
	<label for="_edd_free_downloads_bypass">
		<?php echo EDD()->html->checkbox( array(
			'name'    => '_edd_free_downloads_bypass',
			'current' => $bypass
		) ); ?>
		<?php _e( 'Bypass the Free Downloads modal for this download', 'edd-free-downloads' ); ?>
	</label>
<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_free_downloads_render_bypass_options', 30 );


/**
 * Add our meta box fields to the save array
 *
 * @since       1.2.0
 * @param       array $fields The existing fields array
 * @return      array The updated fields array
 */
function edd_free_downloads_meta_box_save( $fields ) {
	$new_fields = array(
		'_edd_free_downloads_bypass'
	);

	return array_merge( $fields, $new_fields );
}
add_filter( 'edd_metabox_fields_save', 'edd_free_downloads_meta_box_save' );