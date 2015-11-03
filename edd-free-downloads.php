<?php
/**
 * Plugin Name:     Easy Digital Downloads - Free Downloads
 * Plugin URI:      https://easydigitaldownloads.com/extensions/free-downloads/
 * Description:     Adds better handling for directly downloading free products to EDD
 * Version:         1.0.10
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-free-downloads
 *
 * @package         EDD\FreeDownloads
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'EDD_Free_Downloads' ) ) {


	/**
	 * Main EDD_Free_Downloads class
	 *
	 * @since       1.0.0
	 */
	class EDD_Free_Downloads {


		/**
		 * @var         EDD_Free_Downloads $instance The one true EDD_Free_Downloads
		 * @since       1.0.0
		 */
		private static $instance;


		public $maybe_exit = false;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      self::$instance The one true EDD_Free_Downloads
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new EDD_Free_Downloads();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_FREE_DOWNLOADS_VER', '1.0.10' );

			// Plugin path
			define( 'EDD_FREE_DOWNLOADS_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_FREE_DOWNLOADS_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once EDD_FREE_DOWNLOADS_DIR . 'includes/scripts.php';
			require_once EDD_FREE_DOWNLOADS_DIR . 'includes/functions.php';
			require_once EDD_FREE_DOWNLOADS_DIR . 'includes/templates/template-overrides.php';

			if( is_admin() ) {
				require_once EDD_FREE_DOWNLOADS_DIR . 'includes/admin/settings/register.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {
			global $wp_query;

			// Add rewrite endpoint
			add_action( 'init', array( $this, 'add_endpoint' ) );

			// Add query var
			add_filter( 'query_vars', array( $this, 'query_vars' ), -1 );

			// Handle template redirect
			add_action( 'wp_head', array( $this, 'display_redirect' ) );

			// Handle inline display
			add_action( 'wp_footer', array( $this, 'display_inline' ) );

			// Maybe override straight to checkout
			add_filter( 'edd_straight_to_checkout', array( $this, 'override_redirect' ) );

			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Free Downloads', EDD_FREE_DOWNLOADS_VER, 'Daniel J Griffiths' );
			}
		}





		/**
		 * Maybe override straight to checkout
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       bool $ret Whether or not to go straight to checkout
		 * @global      object $post The WordPress post object
		 * @return      bool $ret Whether or not to go straight to checkout
		 */
		public function override_redirect( $ret ) {
			global $post;

			$id = get_the_ID();

			if( is_single( $id ) && get_post_type( $id ) == 'download' && edd_is_free_download( $id ) ) {
				$ret = false;
			}

			return $ret;
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'edd_free_downloads_language_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), '' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-free-downloads', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/edd-free-downloads/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-free-downloads/ folder
				load_textdomain( 'edd-free-downloads', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-free-downloads/ folder
				load_textdomain( 'edd-free-downloads', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-free-downloads', false, $lang_dir );
			}
		}


		/**
		 * Registers a new rewrite endpoint
		 *
		 * @access      public
		 * @since       1.0.1
		 * @param       array $rewrite_rules The existing rewrite rules
		 * @return      void
		 */
		public function add_endpoint( $rewrite_rules ) {
			add_rewrite_endpoint( 'edd-free-download', EP_ALL );
		}


		/**
		 * Add a new query var
		 *
		 * @access      public
		 * @since       1.0.1
		 * @param       array $vars The current query vars
		 * @return      array $vars The new query vars
		 */
		public function query_vars( $vars ) {
			$vars[] = 'download_id';

			return $vars;
		}


		/**
		 * Listen for edd-free-download queries and handle accordingly
		 *
		 * @access      public
		 * @since       1.0.1
		 * @return      void
		 */
		public function display_redirect() {
			global $wp_query;

			// Check for edd-free-download variable
			if( ! isset( $wp_query->query_vars['edd-free-download'] ) ) {
				return;
			}

			// Pull user data if available
			if( is_user_logged_in() ) {
				$user = new WP_User( get_current_user_id() );
			}

			$email = isset( $user ) ? $user->user_email : '';
			$fname = isset( $user ) ? $user->user_firstname : '';
			$lname = isset( $user ) ? $user->user_lastname : '';

			$rname = edd_get_option( 'edd_free_downloads_require_name', false ) ? ' <span class="edd-free-downloads-required">*</span>' : '';

			// Get EDD vars
			$color = edd_get_option( 'checkout_color', 'blue' );
			$color = ( $color == 'inherit' ) ? '' : $color;
			$label = edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) );

			// Build the modal
			$modal  = '<div id="edd-free-downloads-modal" class="edd-free-downloads-mobile">';
			$modal .= '<form id="edd_free_download_form" method="post">';

			// Email is always required
			$modal .= '<p>';
			$modal .= '<label for="edd_free_download_email" class="edd-free-downloads-label">' . __( 'Email Address', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
			$modal .= '<input type="text" name="edd_free_download_email" id="edd_free_download_email" class="edd-free-download-field" placeholder="' . __( 'Email Address', 'edd-free-downloads' ) . '" value="' . $email . '" />';
			$modal .= '</p>';

			if( edd_get_option( 'edd_free_downloads_get_name', false ) ) {
				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_fname" class="edd-free-downloads-label">' . __( 'First Name', 'edd-free-downloads' ) . $rname . '</label>';
				$modal .= '<input type="text" name="edd_free_download_fname" id="edd_free_download_fname" class="edd-free-download-field" placeholder="' . __( 'First Name', 'edd-free-downloads' ) . '" value="' . $fname . '" />';
				$modal .= '</p>';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_lname" class="edd-free-downloads-label">' . __( 'Last Name', 'edd-free-downloads' ) . $rname . '</label>';
				$modal .= '<input type="text" name="edd_free_download_lname" id="edd_free_download_lname" class="edd-free-download-field" placeholder="' . __( 'Last Name', 'edd-free-downloads' ) . '" value="' . $lname . '" />';
				$modal .= '</p>';
			}

			if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() && ! class_exists( 'EDD_Auto_Register' ) ) {
				$modal .= '<hr />';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_username" class="edd-free-downloads-label">' . __( 'Username', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
				$modal .= '<input type="text" name="edd_free_download_username" id="edd_free_download_username" class="edd-free-download-field" placeholder="' . __( 'Username', 'edd-free-downloads' ) . '" value="" />';
				$modal .= '</p>';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_pass" class="edd-free-downloads-label">' . __( 'Password', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
				$modal .= '<input type="password" name="edd_free_download_pass" id="edd_free_download_pass" class="edd-free-download-field" />';
				$modal .= '</p>';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_pass2" class="edd-free-downloads-label">' . __( 'Confirm Password', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
				$modal .= '<input type="password" name="edd_free_download_pass2" id="edd_free_download_pass2" class="edd-free-download-field" />';
				$modal .= '</p>';
			}

			// Honeypot
			$modal .= '<input type="hidden" name="edd_free_download_check" value="" />';

			// Nonce
			$modal .= wp_nonce_field( 'edd_free_download_nonce', 'edd_free_download_nonce', true, false );

			$modal .= '<div class="edd-free-download-errors">';
			$modal .= '<p id="edd-free-download-error-email-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a valid email address', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-email-invalid"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Invalid email', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-fname-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter your first name', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-lname-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter your last name', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-username-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a username', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-password-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a password', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-password2-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please confirm your password', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-password-unmatch"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Password and password confirmation do not match', 'edd-free-downloads' ) . '</p>';
			$modal .= '</div>';

			$modal .= '<input type="hidden" name="edd_action" value="free_download_process" />';
			$modal .= '<input type="hidden" name="edd_free_download_id" value="' . $wp_query->query_vars['download_id'] . '" />';
			$modal .= '<button name="edd_free_download_submit" class="edd-free-download-submit edd-submit button ' . $color . '"><span>' . $label . '</span></button>';
			$modal .= '<button name="edd_free_download_cancel" class="edd-free-download-cancel edd-submit button ' . $color . '"><span>' . __( 'Cancel', 'edd-free-downloads' ) . '</span></button>';

			$modal .= '</form>';
			$modal .= '</div>';

			$modal .= '<script type="text/javascript">';
			$modal .= 'jQuery(document).ready(function ($) {';
			$modal .= '    $("#edd_free_download_email").focus();';
			$modal .= '    $("#edd_free_download_email").select();';
			$modal .= '});';
			$modal .= '</script>';

			echo $modal;

			exit;
		}


		/**
		 * Listen for edd-free-download queries and handle accordingly
		 *
		 * @access      public
		 * @since       1.0.1
		 * @return      void
		 */
		public function display_inline() {
			// Pull user data if available
			if( is_user_logged_in() ) {
				$user = new WP_User( get_current_user_id() );
			}

			$email = isset( $user ) ? $user->user_email : '';
			$fname = isset( $user ) ? $user->user_firstname : '';
			$lname = isset( $user ) ? $user->user_lastname : '';

			$rname = edd_get_option( 'edd_free_downloads_require_name', false ) ? ' <span class="edd-free-downloads-required">*</span>' : '';

			// Get EDD vars
			$color = edd_get_option( 'checkout_color', 'blue' );
			$color = ( $color == 'inherit' ) ? '' : $color;
			$label = edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) );

			// Build the modal
			$modal  = '<div id="edd-free-downloads-modal" class="edd-free-downloads-hidden">';
			$modal .= '<form id="edd_free_download_form" method="post">';

			// Email is always required
			$modal .= '<p>';
			$modal .= '<label for="edd_free_download_email" class="edd-free-downloads-label">' . __( 'Email Address', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
			$modal .= '<input type="text" name="edd_free_download_email" id="edd_free_download_email" class="edd-free-download-field" placeholder="' . __( 'Email Address', 'edd-free-downloads' ) . '" value="' . $email . '" />';
			$modal .= '</p>';

			if( edd_get_option( 'edd_free_downloads_get_name', false ) ) {
				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_fname" class="edd-free-downloads-label">' . __( 'First Name', 'edd-free-downloads' ) . $rname . '</label>';
				$modal .= '<input type="text" name="edd_free_download_fname" id="edd_free_download_fname" class="edd-free-download-field" placeholder="' . __( 'First Name', 'edd-free-downloads' ) . '" value="' . $fname . '" />';
				$modal .= '</p>';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_lname" class="edd-free-downloads-label">' . __( 'Last Name', 'edd-free-downloads' ) . $rname . '</label>';
				$modal .= '<input type="text" name="edd_free_download_lname" id="edd_free_download_lname" class="edd-free-download-field" placeholder="' . __( 'Last Name', 'edd-free-downloads' ) . '" value="' . $lname . '" />';
				$modal .= '</p>';
			}

			if( edd_get_option( 'edd_free_downloads_user_registration', false ) && ! is_user_logged_in() && ! class_exists( 'EDD_Auto_Register' ) ) {
				$modal .= '<hr />';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_username" class="edd-free-downloads-label">' . __( 'Username', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
				$modal .= '<input type="text" name="edd_free_download_username" id="edd_free_download_username" class="edd-free-download-field" placeholder="' . __( 'Username', 'edd-free-downloads' ) . '" value="" />';
				$modal .= '</p>';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_pass" class="edd-free-downloads-label">' . __( 'Password', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
				$modal .= '<input type="password" name="edd_free_download_pass" id="edd_free_download_pass" class="edd-free-download-field" />';
				$modal .= '</p>';

				$modal .= '<p>';
				$modal .= '<label for="edd_free_download_pass2" class="edd-free-downloads-label">' . __( 'Confirm Password', 'edd-free-downloads' ) . ' <span class="edd-free-downloads-required">*</span></label>';
				$modal .= '<input type="password" name="edd_free_download_pass2" id="edd_free_download_pass2" class="edd-free-download-field" />';
				$modal .= '</p>';
			}

			// Honeypot
			$modal .= '<input type="hidden" name="edd_free_download_check" value="" />';

			// Nonce
			$modal .= wp_nonce_field( 'edd_free_download_nonce', 'edd_free_download_nonce', true, false );

			$modal .= '<div class="edd-free-download-errors">';
			$modal .= '<p id="edd-free-download-error-email-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a valid email address', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-email-invalid"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Invalid email', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-fname-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter your first name', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-lname-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter your last name', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-username-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a username', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-password-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a password', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-password2-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please confirm your password', 'edd-free-downloads' ) . '</p>';
			$modal .= '<p id="edd-free-download-error-password-unmatch"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Password and password confirmation do not match', 'edd-free-downloads' ) . '</p>';
			$modal .= '</div>';

			$modal .= '<input type="hidden" name="edd_action" value="free_download_process" />';
			$modal .= '<input type="hidden" name="edd_free_download_id" />';
			$modal .= '<button name="edd_free_download_submit" class="edd-free-download-submit edd-submit button ' . $color . '"><span>' . $label . '</span></button>';

			$modal .= '</form>';
			$modal .= '</div>';

			echo $modal;
		}
	}
}


/**
 * The main function responsible for returning the one true EDD_Free_Downloads
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Free_Downloads The one true EDD_Free_Downloads
 */
function edd_free_downloads() {
	if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if( ! class_exists( 'S214_EDD_Activation' ) ) {
			require_once 'includes/libraries/class.s214-edd-activation.php';
		}

		$activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		return EDD_Free_Downloads::instance();
	} else {
		return EDD_Free_Downloads::instance();
	}
}
add_action( 'plugins_loaded', 'edd_free_downloads' );
