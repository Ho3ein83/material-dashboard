<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Theme constants
define( 'AMD_THEME_AMATRIS_PATH', __DIR__ );
define( 'AMD_THEME_AMATRIS_VIEW', AMD_THEME_AMATRIS_PATH . DIRECTORY_SEPARATOR . 'view' );

/**
 * Just for translations and theme availability check
 * @return void
 */
function amd_theme_amatris(){

	_x( "Material theme", "material-theme", "material-dashboard" );
	_x( "User friendly dashboard UI with material components and mono-chrome icons. Customizable and free!", "material-theme", "material-dashboard" );
	_x( "Amatris", "material-theme", "material-dashboard" );

}

# Register elements extra classes
add_action( "amd_dashboard_start", function(){

	if( apply_filters( "amd_spaced_navbar", true ) )
		amd_add_element_class( "navbar", [ "spaced" ] );

	if( apply_filters( "amd_spaced_sidebar", false ) )
		amd_add_element_class( "sidebar", [ "spaced" ] );

	amd_add_element_class( "navbar", [ "sticky" ] );

} );

function amd_theme_amatris_init_themes(){

	# Register theme (light)
	do_action( "amd_register_theme", "default", esc_html__( "Default", "material-dashboard" ), "light", array(
		'--amd-font-family' => '"shabnam", "roboto", sans-serief',
		'--amd-title-font' => '"kalameh", "roboto", sans-serief',
		'--amd-wrapper-bg' => '#f3f3f3',
		'--amd-wrapper-bg-rgb' => '243, 243, 243',
		'--amd-wrapper-fg' => '#fff',
		'--amd-wrapper-fg-rgb' => '255, 255, 255',
		'--amd-primary' => '#039be5',
		'--amd-primary-rgb' => '3, 155, 229',
		'--amd-primary-x-low' => '#e0dcfc',
		'--amd-input-bg' => '#f3f1fe',
		'--amd-input-bg-' => '243, 241, 254',
		'--amd-color-black' => '#000',
		'--amd-color-black-rgb' => '0, 0, 0',
		'--amd-color-white' => '#fff',
		'--amd-color-white-rgb' => '255, 255, 255',
		'--amd-title-color' => '#18052a',
		'--amd-title-color-rgb' => '24, 5, 42',
		'--amd-text-color' => '#414141',
		'--amd-text-color-rgb' => '65, 65, 65',
		'--amd-color-blue' => '#039be5',
		'--amd-color-blue-rgb' => '3, 155, 229',
		'--amd-color-green' => '#3dda84',
		'--amd-color-green-rgb' => '61, 218, 132',
		'--amd-color-red' => '#f55753',
		'--amd-color-red-rgb' => '245, 87, 83',
		'--amd-color-orange' => '#f6a04d',
		'--amd-color-orange-rgb' => '246, 160, 77',
		'--amd-color-purple' => '#602acc',
		'--amd-color-purple-rgb' => '96, 42, 204',
		'--amd-shadow' => '0 0 4px 0 #00000009',
		'--amd-pointer' => 'pointer',
	) );

	# Register theme (dark)
	do_action( "amd_register_theme", "default", esc_html__( "Default", "material-dashboard" ), "dark", array(
		'--amd-font-family' => '"shabnam", "roboto", sans-serief',
		'--amd-title-font' => '"kalameh", "roboto", sans-serief',
		'--amd-wrapper-bg' => '#0a0a0a',
		'--amd-wrapper-bg-rgb' => '10, 10, 10',
		'--amd-wrapper-fg' => '#1e1e1e',
		'--amd-wrapper-fg-rgb' => '30, 30, 30',
		'--amd-primary' => '#645dbe',
		'--amd-primary-rgb' => '100, 93, 190',
		'--amd-primary-x-low' => '#323235',
		'--amd-input-bg' => '#2f2f2f',
		'--amd-input-bg-rgb' => '47, 47, 47',
		'--amd-color-black' => '#efefef',
		'--amd-color-black-rgb' => '239, 239, 239',
		'--amd-color-white' => '#414141',
		'--amd-color-white-rgb' => '65, 65, 65',
		'--amd-title-color' => '#8d80ff',
		'--amd-title-color-rgb' => '141, 128, 255',
		'--amd-text-color' => '#eaeaea',
		'--amd-text-color-rgb' => '234, 234, 234',
		'--amd-color-blue' => '#0474a9',
		'--amd-color-blue-rgb' => '4, 116, 169',
		'--amd-color-green' => '#19bd63',
		'--amd-color-green-rgb' => '25, 189, 99',
		'--amd-color-red' => '#f55753',
		'--amd-color-red-rgb' => '245, 87, 83',
		'--amd-color-orange' => '#f6a04d',
		'--amd-color-orange-rgb' => '246, 160, 77',
		'--amd-color-purple' => '#602acc',
		'--amd-color-purple-rgb' => '96, 42, 204',
		'--amd-shadow' => '0 1px 4px 0 #00000008',
		'--amd-pointer' => 'pointer',
	) );

}

# Register theme colors and variables
add_action( "amd_core_init", "amd_theme_amatris_init_themes" );
add_action( "amd_admin_core_init", "amd_theme_amatris_init_themes" );

# Register dashboard tab content for admin settings
add_action( "amd_appearance_tab_dashboard", "amd_theme_amatris_dashboard_tab" );