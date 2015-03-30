<?php
/**
 * Plugin Name:     Easy Digital Downloads - Free Downloads
 * Plugin URI:      https://easydigitaldownloads.com/extensions/free-downloads/
 * Description:     Adds better handling for directly downloading free products to EDD
 * Version:         1.0.2
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
            define( 'EDD_FREE_DOWNLOADS_VER', '1.0.2' );
            
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
            global $wp_query;

            // Add our extension settings
            add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );

            // Replace download form
            add_filter( 'edd_purchase_download_form', array( $this, 'download_form' ), 200, 2 );

            // Add rewrite endpoint
            add_action( 'init', array( $this, 'add_endpoint' ) );

            // Add query var
            add_filter( 'query_vars', array( $this, 'query_vars' ), -1 );

            // Handle template redirect
            add_action( 'wp_head', array( $this, 'display_redirect' ) );

            // Handle inline display
            add_action( 'wp_footer', array( $this, 'display_inline' ) );

            // Maybe override straight to checkout
            add_action( 'template_redirect', array( $this, 'override_redirect' ) );

            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Free Downloads', EDD_FREE_DOWNLOADS_VER, 'Daniel J Griffiths' );
            }
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
                ),
                array(
                    'id'    => 'edd_free_downloads_redirect',
                    'name'  => __( 'Custom Redirect', 'edd-free-downloads' ),
                    'desc'  => __( 'Enter a URL to redirect to on completion, or leave blank for the receipt page.', 'edd-free-downloads' ),
                    'type'  => 'text'
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
            $download_file = edd_get_download_files( $download_id );

            if( edd_free_downloads_use_modal( $download_id ) ) {
                $form_id        = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $args['download_id'];
                $download_label = edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) );
                $download_class = implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ), 'edd-free-download' ) );

                $form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
                $form .= '<div class="edd_purchase_submit_wrapper">';

                if( wp_is_mobile() ) {
                    $href = add_query_arg( array( 'edd-free-download' => 'true', 'download_id' => $args['download_id'] ) );
                } else {
                    $href = '#edd-free-download-modal';
                }

                if( edd_is_ajax_enabled() ) {
                    $form .= sprintf(
                        '<div class="edd-add-to-cart %1$s" href="' . $href . '"><span>%2$s</span></div>',
                        $download_class,
                        esc_attr( $download_label )
                    );
                } else {
                    $form .= sprintf(
                        '<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" href="' . $href . '" />',
                        $download_class,
                        esc_attr( $download_label )
                    );
                }

                $form .= '</div>';
                $form .= '</form>';
            }

            return $form;
        }


        /**
         * Maybe override straight to checkout
         *
         * @access      public
         * @since       1.0.0
         * @global      object $post The WordPress post object
         * @return      void
         */
        public function override_redirect() {
            global $post;

            $id = get_the_ID();

            if( is_single( $id ) && get_post_type( $id ) == 'download' && edd_free_downloads_use_modal( $id ) ) {
                add_filter( 'edd_straight_to_checkout', '__return_false' );
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
            $modal .= '<input type="text" name="edd_free_download_email" id="edd_free_download_email" placeholder="' . __( 'Email Address', 'edd-free-downloads' ) . '" value="' . $email . '" />';
            $modal .= '</p>';
            
            if( edd_get_option( 'edd_free_downloads_get_name', false ) ) {
                $modal .= '<p>';
                $modal .= '<label for="edd_free_download_fname" class="edd-free-downloads-label">' . __( 'First Name', 'edd-free-downloads' ) . '</label>';
                $modal .= '<input type="text" name="edd_free_download_fname" id="edd_free_download_fname" placeholder="' . __( 'First Name', 'edd-free-downloads' ) . '" value="' . $fname . '" />';
                $modal .= '</p>';

                $modal .= '<p>';
                $modal .= '<label for="edd_free_download_lname" class="edd-free-downloads-label">' . __( 'Last Name', 'edd-free-downloads' ) . '</label>';
                $modal .= '<input type="text" name="edd_free_download_lname" id="edd_free_download_lname" placeholder="' . __( 'Last Name', 'edd-free-downloads' ) . '" value="' . $lname . '" />';
                $modal .= '</p>';
            }

            $modal .= '<div class="edd-free-download-errors">';
            $modal .= '<p id="edd-free-download-error-email-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a valid email address', 'edd-free-downloads' ) . '</p>';
            $modal .= '<p id="edd-free-download-error-email-invalid"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Invalid email', 'edd-free-downloads' ) . '</p>';
            $modal .= '</div>';

            $modal .= '<input type="hidden" name="edd_action" value="free_download_process" />';
            $modal .= '<input type="hidden" name="edd_free_download_id" value="' . $wp_query->query_vars['edd-free-download'] . '" />';
            $modal .= '<input type="button" name="edd_free_download_submit" class="edd-free-download-submit edd-submit button ' . $color . '" value="' . $label . '" />';
            $modal .= '<input type="button" name="edd_free_download_cancel" class="edd-free-download-cancel edd-submit button ' . $color . '" value="' . __( 'Cancel', 'edd-free-downloads' ) . '" />';

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
            $modal .= '<input type="text" name="edd_free_download_email" id="edd_free_download_email" placeholder="' . __( 'Email Address', 'edd-free-downloads' ) . '" value="' . $email . '" />';
            $modal .= '</p>';
            
            if( edd_get_option( 'edd_free_downloads_get_name', false ) ) {
                $modal .= '<p>';
                $modal .= '<label for="edd_free_download_fname" class="edd-free-downloads-label">' . __( 'First Name', 'edd-free-downloads' ) . '</label>';
                $modal .= '<input type="text" name="edd_free_download_fname" id="edd_free_download_fname" placeholder="' . __( 'First Name', 'edd-free-downloads' ) . '" value="' . $fname . '" />';
                $modal .= '</p>';

                $modal .= '<p>';
                $modal .= '<label for="edd_free_download_lname" class="edd-free-downloads-label">' . __( 'Last Name', 'edd-free-downloads' ) . '</label>';
                $modal .= '<input type="text" name="edd_free_download_lname" id="edd_free_download_lname" placeholder="' . __( 'Last Name', 'edd-free-downloads' ) . '" value="' . $lname . '" />';
                $modal .= '</p>';
            }

            $modal .= '<div class="edd-free-download-errors">';
            $modal .= '<p id="edd-free-download-error-email-required"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Please enter a valid email address', 'edd-free-downloads' ) . '</p>';
            $modal .= '<p id="edd-free-download-error-email-invalid"><strong>' . __( 'Error:', 'edd-free-downloads' ) . '</strong> ' . __( 'Invalid email', 'edd-free-downloads' ) . '</p>';
            $modal .= '</div>';

            $modal .= '<input type="hidden" name="edd_action" value="free_download_process" />';
            $modal .= '<input type="hidden" name="edd_free_download_id" />';
            $modal .= '<input type="button" name="edd_free_download_submit" class="edd-free-download-submit edd-submit button ' . $color . '" value="' . $label . '" />';

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
