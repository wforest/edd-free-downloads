<?php
$download_id = get_the_ID();
$download_file = edd_get_download_files( $download_id );

$form = '<div class="edd_download_buy_button">';
if( edd_free_downloads_use_modal( $download_id ) && ! edd_has_variable_prices( $download_id ) ) {
	$form_id        = 'edd_purchase_' . $download_id;
	$download_label = esc_attr( edd_get_option( 'edd_free_downloads_button_label', __( 'Download Now', 'edd-free-downloads' ) ) );

	if( ! is_user_logged_in() || edd_get_option( 'edd_free_downloads_bypass_logged_in', false ) === false ) {
		$download_class = implode( ' ', array( edd_get_option( 'button_style', 'button' ), edd_get_option( 'checkout_color', 'blue' ), 'edd-submit edd-free-download edd-free-download-single' ) );

		$form .= '<form id="' . $form_id . '" class="edd_download_purchase_form">';
		$form .= '<div class="edd_free_downloads_form_class">';

		if( wp_is_mobile() ) {
			$href = esc_url( add_query_arg( array( 'edd-free-download' => 'true', 'download_id' => $args['download_id'] ) ) );
		} else {
			$href = '#edd-free-download-modal';
		}

		if( edd_is_ajax_enabled() ) {
			$form .= apply_filters( 'edd_free_downloads_button_override', sprintf(
				'<a class="edd-add-to-cart %1$s" href="' . $href . '" data-download-id="%3$s">%2$s</a>',
				$download_class,
				$download_label,
				$download_id
			), $download_id );
		} else {
			$form .= apply_filters( 'edd_free_downloads_button_override', sprintf(
				'<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" href="' . $href . '" data-download-id="%3$s" />',
				$download_class,
				$download_label,
				$download_id
			), $download_id );
		}

		$form .= '</div>';
		$form .= '</form>';
	} else {
		$download_class = implode( ' ', array( edd_get_option( 'button_style', 'button' ), edd_get_option( 'checkout_color', 'blue' ), 'edd-submit' ) );

		$form .= apply_filters( 'edd_free_downloads_button_override', sprintf(
			'<a href="#" class="edd-free-downloads-direct-download-link %1$s" data-download-id="%3$s">%2$s</a>',
			$download_class,
			$download_label,
			$download_id
		), $download_id );
	}
} else {
	$form .= edd_get_purchase_link( array( 'download_id' => get_the_ID() ) );
}
$form .= '</div>';

echo $form;
