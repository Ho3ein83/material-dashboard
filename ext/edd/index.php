<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Extension constants
define( 'AMD_EXT_EDD_PATH', __DIR__ );
define( 'AMD_EXT_EDD_URL', AMD_EXT_URL . '/edd' );
define( 'AMD_EXT_EDD_VIEW', AMD_EXT_EDD_PATH . '/view' );

/**
 * Just for translations and extension availability check
 * @return void
 * @since 1.0.0
 */
function amd_ext_edd(){
	_x( "Amatris", "edd", "material-dashboard" );
	_x( "Easy digital downloads support", "edd", "material-dashboard" );
	_x( "Let you clients manage their transactions and downloads from edd plugin", "edd", "material-dashboard" );
}

/**
 * Check if extension is ready and requirements are installed
 * @return bool
 * @since 1.0.0
 */
function amd_ext_edd_ready(){
	return class_exists( "EDD_Requirements_Check" );
}

# Initialize registered hooks
add_action( "amd_dashboard_init", function(){

	# Restrict access
	$restricted = apply_filters( "amd_restrict_capability_edd_downloads", false );

	if( $restricted AND apply_filters( "amd_deep_restriction", false ) )
		return;

	if( !amd_ext_edd_ready() )
		return;

	# Default date format
	amd_set_default( "ext_edd_date_format", "j F Y" );

	if( !$restricted AND is_user_logged_in() ){

		# Register lazy-loading pages
		amd_register_lazy_page( "edd_cart", esc_html__( "Cart", "material-dashboard" ), AMD_EXT_EDD_VIEW . '/page_edd_cart.php', "cart" );
		amd_register_lazy_page( "edd_downloads", esc_html__( "Downloads", "material-dashboard" ), AMD_EXT_EDD_VIEW . '/page_edd_downloads.php', "downloads" );

		# Add dashboard cards (icon cards)
		$downloads_counts = amd_ext_edd_get_downloads_count();
		do_action( "amd_add_dashboard_card", array(
			"ic_edd" => array(
				"type" => "icon_card",
				"title" => esc_html__( "Downloads", "material-dashboard" ),
				"text" => sprintf( _n( "%d download", "%d downloads", $downloads_counts, "material-dashboard" ), $downloads_counts ),
				"subtext" => '&#160;',
				"footer" => amd_ext_edd_get_downloads_amount(),
				"icon" => "downloads",
				"color" => "blue",
				"priority" => 10
			)
		) );

		# Add dashboard cards (content cards)
		do_action( "amd_add_dashboard_card", array(
			"edd_downloads" => array(
				"type" => "content_card",
				"page" => AMD_EXT_EDD_VIEW . "/cards/edd_card_downloads.php",
				"priority" => 10
			)
		) );

	}

} );

add_action( "amd_init_sidebar_items", function(){

	# Add menu item
	do_action( "amd_add_dashboard_sidebar_menu", array(
		"edd_downloads" => array(
			"text" => esc_html__( "Downloads", "material-dashboard" ),
			"icon" => "downloads",
			"void" => "edd_downloads",
			"url" => "?void=edd_downloads",
			"priority" => 10
		)
	) );

	# If cart is not empty
	if( edd_get_cart_contents() ){

		# Add dashboard quick options
		do_action( "amd_add_dashboard_navbar_item", "left", array(
			"cart" => array(
				"icon" => "cart",
				"tooltip" => esc_html__( "Cart", "material-dashboard" ),
				"url" => "?void=edd_cart",
				"turtle" => "lazy"
			)
		) );

	}

} );