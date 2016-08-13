<?php
/**
 * Plugin Name:     Easy Digital Downloads - Free Downloads
 * Plugin URI:      https://easydigitaldownloads.com/extensions/free-downloads/
 * Description:     Adds better handling for directly downloading free products to EDD
 * Version:         1.2.4
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
			define( 'EDD_FREE_DOWNLOADS_VER', '1.2.4' );

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
			require_once EDD_FREE_DOWNLOADS_DIR . 'includes/actions.php';
			require_once EDD_FREE_DOWNLOADS_DIR . 'includes/templates/template-overrides.php';

			if( is_admin() ) {
				require_once EDD_FREE_DOWNLOADS_DIR . 'includes/admin/settings/register.php';
				require_once EDD_FREE_DOWNLOADS_DIR . 'includes/admin/downloads/meta-boxes.php';
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
			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Free Downloads', EDD_FREE_DOWNLOADS_VER, 'Daniel J Griffiths' );
			}
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
