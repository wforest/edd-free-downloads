<?php
/**
 * Modal form builder table
 *
 * @package     EDD\FreeDownloads\FormBuilder\Table
 * @since       3.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


/**
 * Main EDD_Free_Downloads_Form_Table class
 *
 * @since       3.0.0
 */
class EDD_Free_Downloads_Form_Table extends WP_List_Table {


	/**
	 * @var         int Number of items per page
	 * @since       3.0.0
	 */
	public $per_page = 30;


	/**
	 * @var         object Query results
	 * @since       3.0.0
	 */
	private $forms;


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => __( 'Form', 'edd-free-downloads' ),
			'plural'   => __( 'Forms', 'edd-free-downloads' ),
			'ajax'     => false,
		) );

		$this->query();
	}


	/**
	 * Gets the name of the primary column.
	 *
	 * @access      protected
	 * @since       3.0.0
	 * @return      string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'title';
	}


	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access      public
	 * @since       3.0.0
	 * @param       array $item Contains all the data of the forms
	 * @param       string $column_name The name of the column
	 * @return      string Column name
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ){
			default:
				return $item[ $column_name ];
		}
	}


	/**
	 * Output the title column
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function column_title( $item ) {
		//Build row actions
		$actions = array();
		$base    = admin_url( 'edit.php?post_type=download&page=edd-tools&tab=free_downloads' );
		$base    = wp_nonce_url( $base, 'edd_free_downloads_form_nonce' );
		$form    = get_post( $item['ID'] );

		$title = sprintf( '<strong><a class="row-title" href="post.php?post=%s&action=edit">%s</a></strong>', $item['ID'], get_the_title( $item['ID'] ) );

		$actions['edit']   = sprintf( '<a href="post.php?post=%s&action=edit">' . __( 'Edit', 'edd-free-downloads' ) . '</a>', $item['ID'] );
		$actions['delete'] = sprintf( '<a href="%s&view=%s&free_downloads_form=%s">' . __( 'Delete', 'edd-free-downloads' ) . '</a>', $base, 'delete', $item['ID'] );

		// Filter the existing actions and include the license object.
		$actions = apply_filters( 'edd_free_downloads_form_row_actions', $actions, $form );

		return $title . $this->row_actions( $actions );
	}


	/**
	 * Retrieve the table columns
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'title' => __( 'Form', 'edd-free-downloads' )
		);

		return $columns;
	}


	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'title' => array( 'title', true )
		);
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Retrieve count of total forms
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      int The number of forms
	 */
	public function total_items() {
		$total  = 0;
		$counts = wp_count_posts( 'free_downloads_form', 'readable' );

		foreach ( $counts as $status => $count ) {
			$total += $count;
		}

		return $total;
	}


	/**
	 * Setup available bulk actions
	 *
	 * @access      public
	 * @since       3.0.0
	 * @param       string $which Whether this is the top or bottom of the form
	 * @return      void
	 */

	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		if ( $which == 'top' ) {
			printf( '<h1 class="wp-heading-inline edd-free-downloads-table-header">%s</h1><a class="page-title-action" href="%s">%s</a>', __( 'Free Downloads Forms', 'edd-free-downloads' ), admin_url( 'post-new.php?post_type=free_downloads_form' ), __( 'Add New', 'edd-free-downloads' ) );
		}
	}


	/**
	 * Performs the form query
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function query() {
		$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order   = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';

		$args = array(
			'post_type'        => 'free_downloads_form',
			'post_status'      => 'publish',
			'order'            => $order,
			'fields'           => 'ids',
			'posts_per_page'   => $this->per_page,
			'paged'            => $this->get_paged(),
			'suppress_filters' => true,
		);

		switch ( $orderby ) :
			case 'title' :
				$args['orderby'] = 'title';
				break;
		endswitch;

		$args = apply_filters( 'edd_free_downloads_forms_prepare_items_args', $args, $this );

		$this->forms = new WP_Query( $args );
	}


	/**
	 * Build all the forms data
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      array $forms_data All the data for forms
	 */
	public function forms_data() {
		$forms_data = array();

		$forms = $this->forms->posts;

		if ( $forms ) {
			foreach ( $forms as $form ) {
				$forms_data[] = array(
					'ID'    => $form,
					'title' => get_the_title( $form )
				);
			}
		}

		return $forms_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable, 'title' );

		$data = $this->forms_data();

		$total_items = $this->total_items();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
