<?php
/**
 * Actions
 *
 * @package     EDD\FreeDownloads\Actions
 * @since       1.1.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Maybe override straight to checkout
 *
 * @since       1.1.0
 * @param       bool $ret Whether or not to go straight to checkout
 * @global      object $post The WordPress post object
 * @return      bool $ret Whether or not to go straight to checkout
 */
function edd_free_downloads_override_redirect( $ret ) {
	global $post;

	$id = get_the_ID();

	if( is_single( $id ) && get_post_type( $id ) == 'download' && edd_is_free_download( $id ) ) {
		$ret = false;
	}

	return $ret;
}
add_filter( 'edd_straight_to_checkout', 'edd_free_downloads_override_redirect' );


/**
 * Registers a new rewrite endpoint
 *
 * @since       1.1.0
 * @param       array $rewrite_rules The existing rewrite rules
 * @return      void
 */
function edd_free_downloads_add_endpoint( $rewrite_rules ) {
	add_rewrite_endpoint( 'edd-free-download', EP_ALL );
}
add_action( 'init', 'edd_free_downloads_add_endpoint' );


/**
 * Add a new query var
 *
 * @since       1.1.0
 * @param       array $vars The current query vars
 * @return      array $vars The new query vars
 */
function edd_free_downloads_query_vars( $vars ) {
	$vars[] = 'download_id';

	return $vars;
}
add_filter( 'query_vars', 'edd_free_downloads_query_vars', -1 );


/**
 * Listen for edd-free-download queries and handle accordingly
 *
 * @since       1.1.0
 * @return      void
 */
function edd_free_downloads_display_redirect() {
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

	if( edd_get_option( 'edd_free_downloads_newsletter_optin', false ) ) {
		$modal .= '<p>';
		$modal .= '<input type="checkbox" name="edd_free_download_optin" id="edd_free_download_optin" checked="checked" />';
		$modal .= '<label for="edd_free_download_optin" class="edd-free-downloads-checkbox-label">' . edd_get_option( 'edd_free_downloads_newsletter_optin_label', __( 'Subscribe to our newsletter', 'edd-free-downloads' ) ) . '</label>';
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
add_action( 'wp_head', 'edd_free_downloads_display_redirect' );


/**
 * Listen for edd-free-download queries and handle accordingly
 *
 * @since       1.0.1
 * @return      void
 */
function edd_free_downloads_display_inline() {
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

	if( edd_get_option( 'edd_free_downloads_newsletter_optin', false ) ) {
		$modal .= '<p>';
		$modal .= '<input type="checkbox" name="edd_free_download_optin" id="edd_free_download_optin" checked="checked" />';
		$modal .= '<label for="edd_free_download_optin" class="edd-free-downloads-checkbox-label">' . edd_get_option( 'edd_free_downloads_newsletter_optin_label', __( 'Subscribe to our newsletter', 'edd-free-downloads' ) ) . '</label>';
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
add_action( 'wp_footer', 'edd_free_downloads_display_inline' );