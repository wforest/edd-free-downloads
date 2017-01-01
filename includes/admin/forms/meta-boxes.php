<?php
/**
 * Meta boxes
 *
 * @package     EDD\FreeDownloads\Admin\Forms\MetaBoxes
 * @since       1.1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register meta boxes for the form builder
 *
 * @since       3.0.0
 * @return      void
 */
function edd_free_downloads_add_form_meta_boxes() {
	foreach ( edd_free_downloads()->formbuilder->get_field_types() as $type => $data ) {
		$fields = edd_free_downloads()->formbuilder->get_registered_fields( $type );

		if ( ! empty( $fields ) ) {
			$field_name = esc_attr( $data['name'] ) . '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . esc_attr( $data['tooltip_title'] ) . '</strong>: ' . esc_attr( $data['tooltip_desc' ] ) . '"></span>';

			add_meta_box( 'edd-free-downloads-' . $type . '-fields', $field_name, 'edd_free_downloads_render_fields_meta_box', 'free_downloads_form', 'side', 'default', array( 'type' => $type ) );
		}
	}
}
add_action( 'add_meta_boxes', 'edd_free_downloads_add_form_meta_boxes' );


/**
 * Add our custom preview button
 *
 * @since       3.0.0
 * @return      void
 */
function edd_free_downloads_add_preview_button() {
	$screen = get_current_screen();

	if ( $screen->post_type = 'free_downloads_form' ) {
		?>
		<div class="misc-pub-section misc-pub-free-downloads-form-preview">
			<?php _e( 'Preview:', 'edd-free-downloads' ); ?>
			<a class="edd-free-downloads-form-preview" href="#preview-form" role="button"><?php _e( 'Display Form', 'edd-free-downloads' ); ?></a>
		</div>
		<?php
	}
}
add_action( 'post_submitbox_misc_actions', 'edd_free_downloads_add_preview_button' );


/**
 * Render fields meta boxes
 *
 * @since       3.0.0
 * @param       object $form The post object for this form
 * @param       array $callback_args The arguements passed to the callback function
 * @return      void
 */
function edd_free_downloads_render_fields_meta_box( $form, $callback_args ) {
	$type   = $callback_args['args']['type'];
	$fields = edd_free_downloads()->formbuilder->get_registered_fields( $type );
	$args   = array(
		'type'   => $type,
		'fields' => $fields
	);

	do_action( 'edd_free_downloads_before_' . $type . '_meta_box', $args );
	?>
	<ol class="edd-free-downloads-form-builder-fields">
		<?php foreach( $fields as $field_name => $field_title ) : ?>
			<li><input type="button" class="button button-secondary" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_title ); ?>" /></li>
		<?php endforeach; ?>
	</ol>
	<?php
	do_action( 'edd_free_downloads_after_' . $type . '_meta_box', $args );
}
