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

# Change the format of phone number display text
add_filter( "amd_phone_format_name", function( $name, $digit, $format ){

    return esc_html( "$name (+$digit)" );

}, 10, 3 );

# Phone change alert
add_action( "amd_before_page_home", function(){

	if( !amd_validate_phone_number( amd_get_user_meta( null, "phone" ) ) AND apply_filters( "amd_invalid_phone_alert", true ) ){
		?>
		<div class="row">
			<div class="col-lg-6">
				<?php
				amd_alert_box( array(
					"text" => sprintf( esc_html__( "Your phone number is not registered or it's not valid, please add it in your %sAccount settings%s.", "material-dashboard" ), "<a href=\"?void=account\" data-href=\"?void=account\" data-turtle=\"lazy\">", "</a>" ),
					"is_front" => true,
					"icon" => "phone",
					"type" => "primary"
				) );
				?>
			</div>
		</div>
		<?php
	}

} );
