<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Extension constants
define( 'AMD_EXT__API_PATH', __DIR__ );

/**
 * Just for translations and extension availability check
 * @return void
 */
function amd_ext__api(){}

# Add custom API handler
add_action( "amd_api_add_handler", function( $id, $handler, $allowed_methods ){

    global /** @var AMDFirewall $amdWall */
	$amdWall;

    $amdWall->addAPIHandler( $id, $handler, $allowed_methods );

}, 10, 3 );

# Before API initialization
add_action( "amd_api_init", function(){

	# Add API handler
	do_action( "amd_api_add_handler", "_api_handler", "amd_ext__api_handler_all", "*" );

} );

# Enable API
add_filter( "amd_api_enabled", function(){
	return amd_get_site_option( "api_enabled" ) == "true" OR is_user_logged_in();
} );