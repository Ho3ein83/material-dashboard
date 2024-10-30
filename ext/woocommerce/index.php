<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Extension constants
define( 'AMD_EXT_WC_PATH', __DIR__ );
define( 'AMD_EXT_WC_VIEW', AMD_EXT_WC_PATH . '/view' );
define( 'AMD_EXT_WC_URL', AMD_EXT_URL . "/woocommerce" );

/**
 * Just for translations and extension availability check
 * @return void
 */
function amd_ext_wc(){

	_x( "Amatris", "wc", "material-dashboard" );
	_x( "WooCommerce support", "wc", "material-dashboard" );
	_x( "WooCommerce", "wc", "material-dashboard" );
	_x( "Manage WooCommerce transactions, payments and tracking them", "wc", "material-dashboard" );

}

/**
 * Check if extension is ready and requirements are installed
 * @return bool
 */
function amd_ext_wc_ready(){
	return class_exists( "WooCommerce" );
}

# Initialize registered hooks
add_action( "amd_dashboard_init", function(){

	# Restrict access
	$restricted = apply_filters( "amd_restrict_capability_edd_downloads", false );

	if( $restricted AND apply_filters( "amd_deep_restriction", false ) )
		return;

	if( !amd_ext_wc_ready() )
		return;

	# Default date format
	amd_set_default( "ext_wc_date_format", "j F Y" );

	if( !$restricted AND is_user_logged_in() ){

		# Register lazy-loading pages
		amd_register_lazy_page( "wc_cart", esc_html__( "Cart", "material-dashboard" ), AMD_EXT_WC_VIEW . '/page_wc_cart.php', "cart" );
		amd_register_lazy_page( "wc_purchases", esc_html__( "Purchases", "material-dashboard" ), AMD_EXT_WC_VIEW . '/page_wc_purchases.php', "products" );

		$thisuser = amd_get_current_user();

		# Add dashboard cards (icon cards)
		$purchases_count = count( amd_ext_wc_get_orders_for_uid( $thisuser->ID ) );
		do_action( "amd_add_dashboard_card", array(
			"ic_wc" => array(
				"type" => "icon_card",
				"title" => esc_html__( "Purchases", "material-dashboard" ),
				"text" => sprintf( _n( "%d purchase", "%d purchases", $purchases_count, "material-dashboard" ), $purchases_count ),
				"subtext" => '&#160;',
				"footer" => amd_ext_wc_get_purchases_amount( $thisuser->ID ),
				"icon" => "products",
				"color" => "purple",
				"priority" => 9
			)
		) );

		# Add dashboard cards (content cards)
		do_action( "amd_add_dashboard_card", array(
			"wc_purchases" => array(
				"type" => "content_card",
				"page" => AMD_EXT_WC_VIEW . "/cards/wc_card_purchases.php",
				"priority" => 9
			)
		) );

		$tera_wallet_support = amd_get_site_option( "tera_wallet_support", "true" );

		if( $tera_wallet_support == "true" AND function_exists( "woo_wallet" ) ){

			$balance = woo_wallet()->wallet->get_wallet_balance( get_current_user_id() );

			do_action( "amd_add_dashboard_card", array(
				"ic_woo_wallet" => array(
					"type" => "icon_card",
					"title" => esc_html__( "Wallet", "woo-wallet" ),
					"text" => $balance,
					"subtext" => "&#160;",
					"footer" => "<a href=\"?void=wc_purchases&wallet=tera\" data-turtle=\"lazy\">" . esc_html__( "Manage", "material-dashboard" ) . "</a href=\"javascript:void(0)\">",
					"icon" => "money",
					"color" => "orange",
					"priority" => 10
				)
			) );
		}

	}

} );

add_action( "amd_init_sidebar_items", function(){

	# Add menu item
	do_action( "amd_add_dashboard_sidebar_menu", array(
		"wc_purchases" => array(
			"text" => esc_html__( "Purchases", "material-dashboard" ),
			"icon" => "products",
			"void" => "wc_purchases",
			"url" => "?void=wc_purchases",
			"priority" => 9
		)
	) );

	# If cart is not empty
	if( !empty( amd_ext_wc_get_cart() ) ){

		# Add dashboard quick options
		do_action( "amd_add_dashboard_navbar_item", "left", array(
			"wc_cart" => array(
				"icon" => "cart",
				"tooltip" => esc_html__( "Cart", "material-dashboard" ),
				"url" => "?void=wc_cart",
				"turtle" => "lazy"
			)
		) );

	}

} );