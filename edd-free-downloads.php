<?php
/**
 * Plugin Name:     Easy Digital Downloads - Free Downloads
 * Plugin URI:      https://wordpress.org/plugins/easy-digital-downloads-free-downloads
 * Description:     Adds better handling for directly downloading free products to EDD
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-free-downloads
 *
 * @package         EDD\FreeDownloads
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


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
            define( 'EDD_FREE_DOWNLOADS_VER', '1.0.0' );
            
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
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Add our extension settings
            add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );

            // Replace download form
            add_filter( 'edd_purchase_download_form', array( $this, 'download_form' ), 200, 2 );
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing plugin settings
         * @return      array The modified plugin settings
         */
        public function add_settings( $settings ) {
            $new_settings = array(
                array(
                    'id'    => 'edd_free_downloads_settings',
                    'name'  => '<strong>' . __( 'Free Downloads Settings', 'edd-free-downloads' ) . '</strong>',
                    'desc'  => '',
                    'type'  => 'header'
                ),
                array(
                    'id'    => 'edd_free_downloads_button_label',
                    'name'  => __( 'Button Label', 'edd-free-downloads' ),
                    'desc'  => __( 'Specify the label for the download button.', 'edd-free-downloads' ),
                    'type'  => 'text',
                    'std'   => __( 'Download Now', 'edd-free-downloads' )
                ),
                array(
                    'id'    => 'edd_free_downloads_get_name',
                    'name'  => __( 'Collect Name', 'edd-free-downloads' ),
                    'desc'  => __( 'Should we collect the first and last name of the purchaser?', 'edd-free-downloads' ),
                    'type'  => 'checkbox'
                ),
                array(
                    'id'    => 'edd_free_downloads_close_button',
                    'name'  => __( 'Display Close Button', 'edd-free-downloads' ),
                    'desc'  => __( 'Should we display a close button on the email collection form?', 'edd-free-downloads' ),
                    'type'  => 'checkbox'
                )
            );

            return array_merge( $settings, $new_settings );
        }


        /**
         * Override the download form
         *
         * @access      public
         * @since       1.0.0
         * @param       string $form The existing download form
         * @param       array $args Arguements passed to the form
         * @return      string $form The updated download form
         */
        public function download_form( $form, $args ) {
            $download_id = absint( $args['download_id'] );

            if( $download_id && ! edd_has_variable_prices( $download_id ) && ! edd_is_bundled_product( $download_id ) ) {
                $price = floatval( edd_get_lowest_price_option( $args['download_id'] ) );
                
                if( $price == 0 ) {
                    $download_file = edd_get_download_files( $download_id );

                    if( count( $download_file ) == 1 && ! empty( $download_file[0]['file'] ) ) {
                        $form_id        = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $args['download_id'];
                        $download_url   = $download_file[0]['file'];
                        $download_label = edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) );
                        $download_class = implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ), 'edd-free-download' ) );

                        $form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
                        $form .= '<div class="edd_purchase_submit_wrapper">';

                        if( edd_is_ajax_enabled() ) {
                            $form .= sprintf(
                                '<div class="edd-add-to-cart %1$s" href="#edd-free-download-modal"><span>%2$s</span></div>',
                                $download_class,
                                esc_attr( $download_label )
                            );
                        } else {
                            $form .= sprintf(
                                '<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" href="#edd-free-download-modal" />',
                                $download_class,
                                esc_attr( $download_label )
                            );
                        }

                        $form .= '</div>';
                        $form .= '</form>';
                    }
                }
            }

            return $form;
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
            require_once 'includes/class.s214-edd-activation.php';
        }

        $activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
        
        return EDD_Free_Downloads::instance();
    } else {
        return EDD_Free_Downloads::instance();
    }
}
add_action( 'plugins_loaded', 'edd_free_downloads' );
