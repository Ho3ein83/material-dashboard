<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Theme constants
define( 'ADP_THEME_YT_NAME_PATH', __DIR__ );
define( 'ADP_THEME_YT_NAME_VIEW', ADP_THEME_YT_NAME_PATH . DIRECTORY_SEPARATOR . 'view' );

/**
 * Just for translations and theme availability check
 * @return void
 */
function adp_theme_YT_NAME(){

}

/**
 * Check if theme is ready and requirements are installed
 * @return bool
 */
function adp_theme_YT_NAME_ready(){

	return function_exists( "adp_theme_YT_NAME" );

}