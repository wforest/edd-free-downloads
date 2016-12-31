<?php
/**
 * Post type functions
 *
 * @package     EDD\FreeDownloads\PostTypes
 * @since       3.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register our new CPT
 *
 * @since       3.0.0
 * @return      void
 */
function edd_free_downloads_register_post_types() {
	$labels = apply_filters( 'edd_free_downloads_form_labels', array(
		'name'               => _x( 'Forms', 'free downloads form post type name', 'edd-free-downloads' ),
		'singular_name'      => _x( 'Form', 'singular free downloads form post type name', 'edd-free-downloads' ),
		'add_new'            => __( 'Add New', 'edd-free-downloads' ),
		'add_new_item'       => __( 'Add New Form', 'edd-free-downloads' ),
		'new_item'           => __( 'New Form', 'edd-free-downloads' ),
		'edit_item'          => __( 'Edit Form', 'edd-free-downloads' ),
		'all_items'          => __( 'All Forms', 'edd-free-downloads' ),
		'view_item'          => __( 'View Form', 'edd-free-downloads' ),
		'search_items'       => __( 'Search Forms', 'edd-free-downloads' ),
		'not_found'          => __( 'No forms found', 'edd-free-downloads' ),
		'not_found_in_trash' => __( 'No forms found in Trash', 'edd-free-downloads' )
	) );

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'edd_free_downloads_form_supports', array( 'title' ) )
	);

	register_post_type( 'free_downloads_form', apply_filters( 'edd_free_downloads_form_post_type_args', $args ) );
}
add_action( 'init', 'edd_free_downloads_register_post_types', 1 );


/**
 * Change default "Enter title here" placeholder
 *
 * @since       3.0.0
 * @param       string $title The default placeholder
 * @return      string $title The updated placeholder
 */
function edd_free_downloads_enter_title_here( $title ) {
	$screen = get_current_screen();

	if ( $screen->post_type = 'free_downloads_form' ) {
		$title = __( 'Enter a descriptive title here (will not be visible publicly)', 'edd-free-downloads' );
	}

	return $title;
}
add_filter( 'enter_title_here', 'edd_free_downloads_enter_title_here' );


/**
 * Update messages
 *
 * @since       3.0.0
 * @param       array $messages The default messages
 * @return      array $messages The updated messages
 */
function edd_free_downloads_updated_messages( $messages ) {
	$link = esc_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-tools', 'tab' => 'free_downloads' ), admin_url( 'edit.php' ) ) );
	$text = __( 'Return to Forms List', 'edd-free-downloads' );

	$messages['free_downloads_form'] = array(
		1 => sprintf( __( 'Form updated. <a href="%1$s">%2$s</a>', 'edd-free-downloads' ), $link, $text ),
		4 => sprintf( __( 'Form updated. <a href="%1$s">%2$s</a>', 'edd-free-downloads' ), $link, $text ),
		6 => sprintf( __( 'Form published. <a href="%1$s">%2$s</a>', 'edd-free-downloads' ), $link, $text ),
		7 => sprintf( __( 'Form saved. <a href="%1$s">%2$s</a>', 'edd-free-downloads' ), $link, $text ),
		8 => sprintf( __( 'Form submitted. <a href="%1$s">%2$s</a>', 'edd-free-downloads' ), $link, $text )
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'edd_free_downloads_updated_messages' );


/**
 * Update bulk messages
 *
 * @since       3.0.0
 * @param       array $bulk_messages Post updated messages
 * @param       array $bulk_counts Post counts
 * @return      array $bulk_messages Updated post updated messages
 */
function edd_free_downloads_bulk_updated_messages( $bulk_messages, $bulk_counts ) {
	$bulk_messages['free_downloads_form'] = array(
		'updated'   => sprintf( _n( '%1$s form updated.', '%1$s forms updated.', $bulk_counts['updated'], 'edd-free-downloads' ), $bulk_counts['updated'] ),
		'locked'    => sprintf( _n( '%1$s form not updated, somebody is editing it.', '%1$s forms not updated, somebody is editing them.', $bulk_counts['locked'], 'edd-free-downloads' ), $bulk_counts['locked'] ),
		'deleted'   => sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $bulk_counts['deleted'], 'edd-free-downloads' ), $bulk_counts['deleted'] ),
		'trashed'   => sprintf( _n( '%1$s form moved to the Trash.', '%1$s forms moved to the Trash.', $bulk_counts['trashed'], 'edd-free-downloads' ), $bulk_counts['trashed'] ),
		'untrashed' => sprintf( _n( '%1$s form restored from the Trash.', '%1$s form restored from the Trash.', $bulk_counts['untrashed'], 'edd-free-downloads' ), $bulk_counts['untrashed'] )
	);
	return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', 'edd_free_downloads_bulk_updated_messages', 10, 2 );


/**
 * Add a menu item for the form post type if debugging is enabled
 *
 * @since       3.0.0
 * @return      void
 */
function edd_free_downloads_add_menu_items() {
	if ( edd_free_downloads()->debugging ) {
		add_submenu_page( 'edit.php?post_type=download', __( 'Free Downloads Forms', 'edd-free-downloads' ), __( 'Free Downloads Forms', 'edd-free-downloads' ), 'manage_shop_settings', 'edit.php?post_type=free_downloads_form' );
	}
}
add_action( 'admin_menu', 'edd_free_downloads_add_menu_items', 10 );
