<?php
/**
 * Modal form builder
 *
 * @package     EDD\FreeDownloads\FormBuilder
 * @since       2.2.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main EDD_Free_Downloads_Form_Builder class
 *
 * @since       2.2.0
 */
class EDD_Free_Downloads_Form_Builder {


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       2.2.0
	 * @return      void
	 */
	public function __construct() {
		$this->hooks();
	}


	/**
	 * Run actions and filters
	 *
	 * @access      public
	 * @since       2.2.0
	 * @return      void
	 */
	public function hooks() {
		// Add the form builder
		add_filter( 'edd_tools_tabs', array( $this, 'add_tools_tab' ) );
		add_filter( 'edd_tools_tab_form_builder', array( $this, 'render_form_builder' ) );
	}


	/**
	 * Add a tab to the EDD tols page
	 *
	 * @access      public
	 * @since       2.2.0
	 * @param       array $tabs The current tabs
	 * @return      array $tabs The updated tabs
	 */
	public function add_tools_tab( $tabs ) {
		$tabs['form_builder'] = __( 'Form Builder', 'edd-free-downloads' );

		return $tabs;
	}


	/**
	 * Get registered field types
	 *
	 * @access      public
	 * @since       2.2.0
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
	 * @since       2.2.0
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
				'captcha'     => __( 'CAPTCHA', 'edd-free-downloads' ),
				'direct-link' => __( 'Direct Download', 'edd-free-downloads' )
			) ),
			'newsletter' => apply_filters( 'edd_free_downloads_newsletter_fields', array() ),
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
	 * @since       2.2.0
	 * @return      void
	 */
	public function render_form_builder() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		ob_start(); ?>
		<div id="poststuff" class="edd-free-downloads-form-builder">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" style="position: relative;">
					<div class="postbox">
						<h2 class="header"><span><?php _e( 'Getting Started', 'edd-free-downloads' ); ?></span></h2>
						<div class="inside">
							<p>
								<?php _e( 'Welcome to the Free Downloads form builder! Basic instructions will go here once I\'ve had a chance to write them...', 'edd-free-downloads' ); ?>
							</p>
						</div>
					</div>
					<div class="postbox">
						<h2 class="header">
							<span>
								<?php _e( 'Free Downloads Form', 'edd-free-downloads' ); ?>
							</span>
							<a class="preview"><?php _e( 'Preview Form', 'edd-free-downloads' ); ?></a>
						</h2>
						<div class="inside">
							<p>
								<?php _e( 'Looks like you haven\'t added any fields yet! Why not add one now?', 'edd-free-downloads' ); ?>
							</p>
						</div>
					</div>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="submitdiv" class="postbox">
						<h2 class="header"><span><?php _e( 'Save', 'edd-free-downloads' ); ?></span></h2>
						<div class="inside">
							<div id="submitpost" class="submitbox">
								<div id="major-publishing-actions">
									<div id="delete-action">
										<a class="submitdelete deletion"><?php _e( 'Reset Form', 'edd-free-downloads' ); ?></a>
									</div>
									<div id="publishing-action">
										<input id="publish" class="button button-primary button-large" value="<?php _e( 'Update', 'edd-free-downloads' ); ?>" type="submit" />
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
					<?php foreach( $this->get_field_types() as $type => $data ) : ?>
						<?php
						$fields = $this->get_registered_fields( $type );

						if( ! empty( $fields ) ):
						?>
							<div id="<?php echo esc_attr( $type ); ?>fieldsdiv" class="fieldsdiv postbox">
								<h2 class="header"><span><?php echo esc_attr( $data['name'] ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong><?php echo esc_attr( $data['tooltip_title'] ); ?></strong>: <?php echo esc_attr( $data['tooltip_desc' ] ); ?>"></span></span></h2>
								<div class="inside">
									<ol class="field_type">
										<?php foreach( $fields as $field_name => $field_title ) : ?>
											<li><input type="button" class="button button-secondary" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_title ); ?>" /></li>
										<?php endforeach; ?>
									</ol>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
		echo ob_get_clean();
	}
}
new EDD_Free_Downloads_Form_Builder();
