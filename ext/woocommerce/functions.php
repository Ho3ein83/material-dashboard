<?php

/**
 * Handle AJAX requests
 * @param array $r
 *
 * @return void
 * @since 1.0.0
 */
function amd_ajax_target_ext_wc_private( $r ){

	if( isset( $r["remove_cart_item"] ) ){

		$item = $r["remove_cart_item"];

		if( $item == "*" OR $item == "all" )
			WC()->cart->empty_cart();
		else
			WC()->cart->remove_cart_item( $item );

		amd_send_api_success( ["msg" => ""] );

	}

	amd_send_api_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

}

/**
 * Get all user's order
 *
 * @param int|null $uid
 * User ID, pass null to get current user's orders
 *
 * @return int[]|WP_Post[]
 * @since 1.0.0
 */
function amd_ext_wc_get_orders_for_uid( $uid=null ){

	return get_posts(
		apply_filters(
			'woocommerce_my_account_my_orders_query',
			array(
				'meta_key'    => '_customer_user',
				'meta_value'  => $uid == null ? get_current_user_id() : $uid,
				'post_type'   => wc_get_order_types( 'view-orders' ),
				'post_status' => array_keys( wc_get_order_statuses() ),
			)
		)
	);

}

/**
 * Get user's total purchases amount
 * @param int $uid
 * User ID
 *
 * @return mixed|null
 * @since 1.0.0
 */
function amd_ext_wc_get_purchases_amount( $uid ){

	$amount = 0;
	$formatted_amount = wc_price( $amount, array( 'currency' => get_woocommerce_currency() ) );
	$formatted_amount = apply_filters( 'woocommerce_get_formatted_order_total', $formatted_amount );

	$orders = amd_ext_wc_get_orders_for_uid( $uid );

	foreach( $orders as $item ){

		$order = wc_get_order( $item );

		$amount += intval( $order->get_total() );

		$formatted_amount = wc_price( $amount, array( 'currency' => get_woocommerce_currency() ) );
		$formatted_amount = apply_filters( 'woocommerce_get_formatted_order_total', $formatted_amount, $order );

	}

	return $formatted_amount;
}

/**
 * Get date format
 * @return mixed|string
 * @since 1.0.0
 */
function amd_ext_wc_get_date_format(){
	return amd_get_default( "ext_wc_date_format", "j F Y" );
}

/**
 * Get purchase SVG icon
 * @return string
 * @since 1.0.0
 */
function amd_ext_wc_purchase_svg(){
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="fill:currentColor"><path d="M9 44q-1.2 0-2.1-.9Q6 42.2 6 41V14.5q0-1.2.9-2.1.9-.9 2.1-.9h5.5q0-3.95 2.65-6.725Q19.8 2 23.75 2q3.95 0 6.85 2.775 2.9 2.775 2.9 6.725H39q1.2 0 2.1.9.9.9.9 2.1V41q0 1.2-.9 2.1-.9.9-2.1.9Zm0-3h30V14.5H9V41Zm15-14.5q3.95 0 6.85-2.9 2.9-2.9 2.9-6.85h-3q0 2.75-2 4.75t-4.75 2q-2.75 0-4.75-2t-2-4.75h-3q0 3.95 2.9 6.85 2.9 2.9 6.85 2.9Zm-6.5-15h13q0-2.75-1.875-4.625T24 5q-2.75 0-4.625 1.875T17.5 11.5ZM9 41V14.5 41Z"/></svg>';
}

/**
 * Get cart items
 * @return array
 * @since 1.0.0
 */
function amd_ext_wc_get_cart(){

	global $woocommerce;

	if( empty( $woocommerce->cart ) )
		return [];

	return $woocommerce->cart->get_cart();

}

/**
 * Escape status code to HTML
 * @param string $status
 * Status code
 * @param bool $get
 * Whether to get the status or print the span tag (since 1.2.0)
 *
 * @return string[]
 * @since 1.0.0
 */
function amd_ext_wc_esc_status_html( $status, $get = false ){

    if( $status == "completed" ) {
        $s = esc_html_x( "completed", "Purchase status", "material-dashboard" );
        $c = "color-green";
    }
    else if( $status == "cancelled" ) {
        $s = esc_html_x( "cancelled", "Purchase status", "material-dashboard" );
        $c = "color-red";
    }
    else if( $status == "failed" ) {
        $s = esc_html_x( "failed", "Purchase status", "material-dashboard" );
        $c = "color-red";
    }
    else if( $status == "processing" ) {
        $s = esc_html_x( "processing", "Purchase status", "material-dashboard" );
        $c = "color-blue";
    }
    else{
        $s = wc_get_order_status_name( $status );
        $c = "";
    }

    if( !$get ){
        ?><span class="tiny-text <?php echo esc_attr( $c ); ?>"><?php echo esc_html( $s ); ?></span><?php
    }

    return [$s, $c];

}

function adp_ext_wc_checkout_fields( $fields ){

    if( !is_user_logged_in() )
        return $fields;

    $user = amd_get_current_user();

    if( empty( $fields["billing"]["billing_first_name"]["default"] ) )
	    $fields["billing"]["billing_first_name"]["default"] = $user->firstname;

    if( empty( $fields["billing"]["billing_last_name"]["default"] ) )
	    $fields["billing"]["billing_last_name"]["default"] = $user->lastname;

    return $fields;
}
add_filter( "woocommerce_checkout_fields", "adp_ext_wc_checkout_fields" );