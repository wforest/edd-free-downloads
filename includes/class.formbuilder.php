<?php
/**
 * Modal form builder
 *
 * @package     EDD\FreeDownloads\FormBuilder
 * @since       3.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main EDD_Free_Downloads_Form_Builder class
 *
 * @since       3.0.0
 */
class EDD_Free_Downloads_Form_Builder {


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function __construct() {
		$this->hooks();
	}


	/**
	 * Run actions and filters
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function hooks() {
		// Add the form builder
		add_filter( 'edd_tools_tabs', array( $this, 'add_tools_tab' ) );
		add_filter( 'edd_tools_tab_free_downloads', array( $this, 'render_form_builder' ) );
	}


	/**
	 * Add a tab to the EDD tols page
	 *
	 * @access      public
	 * @since       3.0.0
	 * @param       array $tabs The current tabs
	 * @return      array $tabs The updated tabs
	 */
	public function add_tools_tab( $tabs ) {
		$tabs['free_downloads'] = __( 'Free Downloads Forms', 'edd-free-downloads' );

		return $tabs;
	}


	/**
	 * Get registered field types
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      array $types The registered field types
	 */
	public function get_field_types() {
		$types = apply_filters( 'edd_free_downloads_field_types', array(
			'core' => array(
				'name'          => __( 'Standard Fields', 'edd-free-downloads' ),
				'tooltip_title' => __( 'Standard Fields', 'edd-free-downloads' ),
				'tooltip_desc'  => __( 'These fields make up the core of Free Downloads. While not explicitly required, many of them are pretty important!', 'edd-free-downloads' )
			),
			'newsletter' => array(
				'name'          => __( 'Newsletter Fields', 'edd-free-downloads' ),
				'tooltip_title' => __( 'Newsletter Fields', 'edd-free-downloads' ),
				'tooltip_desc'  => __( 'These fields are related to newsletter integration with Free Downloads.', 'edd-free-downloads' )
			),
			'download' => array(
				'name'          => __( 'Download Buttons', 'edd-free-downloads' ),
				'tooltip_title' => __( 'Download Buttons', 'edd-free-downloads' ),
				'tooltip_desc'  => __( 'These fields provide the download button options for Free Downloads. At least one of these must be included for yuor form to work!', 'edd-free-downloads' )
			),
			'custom' => array(
				'name'          => __( 'Custom Fields', 'edd-free-downloads' ),
				'tooltip_title' => __( 'Custom Fields', 'edd-free-downloads' ),
				'tooltip_desc'  => __( 'These fields store their response directly to customer meta, allowing you to integrate with third-party extensions.', 'edd-free-downloads' )
			),
			'extension' => array(
				'name'          => __( 'Extension Fields', 'edd-free-downloads' ),
				'tooltip_title' => __( 'Extension Fields', 'edd-free-downloads' ),
				'tooltip_desc'  => __( 'These fields are added by official or third-party extensions.', 'edd-free-downloads' )
			)
		) );

		return $types;
	}


	/**
	 * Get registered form fields
	 *
	 * @access      public
	 * @since       3.0.0
	 * @param       string $type The specific field type to get, or empty string for all
	 * @return      array $fields The registered form fields
	 */
	public function get_registered_fields( $type = '' ) {
		$fields = apply_filters( 'edd_free_downloads_registered_fields', array(
			'core' => apply_filters( 'edd_free_downloads_core_fields', array(
				'name'        => __( 'Name', 'edd-free-downloads' ),
				'email'       => __( 'Email', 'edd-free-downloads' ),
				'notes'       => __( 'Notes', 'edd-free-downloads' ),
				'login'       => __( 'Login/Register', 'edd-free-downloads' ),
				'html'        => __( 'HTML', 'edd-free-downloads' ),
				'separator'   => __( 'Separator', 'edd-free-downloads' ),
				'captcha'     => __( 'CAPTCHA', 'edd-free-downloads' )
			) ),
			'newsletter' => apply_filters( 'edd_free_downloads_newsletter_fields', array() ),
			'download'   => apply_filters( 'edd_free_downloads_download_fields', array(
				'standard-download' => __( 'Standard', 'edd-free-downloads' ),
				'direct-download'   => __( 'Direct', 'edd-free-downloads' )
			) ),
			'custom'     => apply_filters( 'edd_free_downloads_custom_fields', array() ),
			'extension'  => apply_filters( 'edd_free_downloads_extension_fields', array() )
		) );

		if ( $type && $type !== '' && array_key_exists( $type, $fields ) ) {
			$fields = $fields[ $type ];
		}

		return $fields;
	}


	/**
	 * Render the form builder
	 *
	 * @access      public
	 * @since       3.0.0
	 * @return      void
	 */
	public function render_form_builder() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		require_once EDD_FREE_DOWNLOADS_DIR . 'includes/admin/class.formbuilder-table.php';

		$forms_table = new EDD_Free_Downloads_Form_Table();
		$forms_table->prepare_items();
		$forms_table->display();


	}
}
