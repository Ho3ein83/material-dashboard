<?php

/**
 * Ajax handler (only logged-in users)
 * @param array $r
 *
 * @return void
 * @since 1.0.0
 */
function amd_ajax_target_ext_edd_private( $r ){

	if( isset( $r["remove_cart_item"] ) ){

		$item = $r["remove_cart_item"];

		if( $item == "*" OR $item == "all" )
			edd_empty_cart();
		else
			edd_remove_from_cart( $item );

		amd_send_api_success( ["msg" => ""] );

	}

	amd_send_api_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

}

/**
 * Get date format for download and order pages
 * @return mixed|string
 * @since 1.0.0
 */
function amd_ext_edd_get_date_format(){
	return amd_get_default( "ext_edd_date_format", "j F Y" );
}

/**
 * Get download svg icon
 * @return string
 * @since 1.0.0
 */
function amd_ext_edd_svg_download(){
	return '<svg viewBox="0 0 256 256" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="256" height="256" style="fill:rgba(var(--amd-primary-rgb),.2)"/><path d="M99.2917 163.333C97.5251 163.333 95.9792 162.671 94.6542 161.346C93.3292 160.021 92.6667 158.475 92.6667 156.708V140.919H99.2917V156.708H156.708V140.919H163.333V156.708C163.333 158.475 162.671 160.021 161.346 161.346C160.021 162.671 158.475 163.333 156.708 163.333H99.2917ZM128 146.44L106.69 125.129L111.438 120.381L124.688 133.631V92.6667H131.313V133.631L144.563 120.381L149.311 125.129L128 146.44Z" style="fill:var(--amd-primary)"/></svg>';
}

/**
 * Download icon svg
 * @return string
 * @since 1.0.0
 */
function amd_ext_edd_download_svg(){
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="fill:currentColor"><path d="M11 40q-1.2 0-2.1-.9Q8 38.2 8 37v-7.15h3V37h26v-7.15h3V37q0 1.2-.9 2.1-.9.9-2.1.9Zm13-7.65-9.65-9.65 2.15-2.15 6 6V8h3v18.55l6-6 2.15 2.15Z"/></svg>';
}

/**
 * Format amount number with text label
 * <br>Note: returned value can be overridden by EDD filters
 * @param string|int $amount
 *
 * @return string
 * @since 1.0.0
 */
function amd_ext_edd_format_amount( $amount ){

	return edd_currency_filter( edd_format_amount( $amount ) );

}

/**
 * Get EDD downloaded files count
 * @return int
 * @since 1.0.0
 */
function amd_ext_edd_get_downloads_count(){
	$all_orders = edd_get_orders( array(
		'user_id' => get_current_user_id(),
		'type' => 'sale',
		'status__not_in' => array( 'trash' )
	) );
	return count( $all_orders );
}

/**
 * Get EDD downloaded files amount
 * @return string
 * @since 1.0.0
 */
function amd_ext_edd_get_downloads_amount(){
	$all_orders = edd_get_orders( array(
		'user_id' => get_current_user_id(),
		'type' => 'sale',
		'status__not_in' => array( 'trash' )
	) );
	$total = 0;
	foreach( $all_orders as $item )
		$total += $item->total;
	return amd_ext_edd_format_amount( $total );
}