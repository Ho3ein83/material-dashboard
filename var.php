<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

/*
 * Note: Directory separator for Linux and Windows is different,
 * but both of them supports slash, so you can use "/" instead of DIRECTORY_SEPARATOR
 */

/**
 * Main path
 * @since 1.0.0
 */
define( 'AMD_PATH', __DIR__ );

/**
 * Plugin directory name
 * @since 1.0.0
 */
define( 'AMD_DIRECTORY', 'material-dashboard' );

/**
 * Core path
 * @since 1.0.0
 */
define( 'AMD_CORE', AMD_PATH . DIRECTORY_SEPARATOR . 'core' );

/**
 * Assets path
 * @since 1.0.0
 */
define( 'AMD_ASSETS_PATH', AMD_PATH . DIRECTORY_SEPARATOR . "assets" );

/**
 * Assets directory
 * @since 1.0.0
 */
define( 'AMD_ASSETS', plugins_url( 'assets', __FILE__ ) );

/**
 * Extensions directory
 * @since 1.0.0
 */
define( 'AMD_EXT', AMD_PATH . DIRECTORY_SEPARATOR . 'ext' );

/**
 * Themes directory
 * @since 1.0.0
 */
define( 'AMD_THEMES', AMD_PATH . DIRECTORY_SEPARATOR . 'themes' );

/**
 * Extensions URL
 * @since 1.0.0
 */
define( 'AMD_EXT_URL', plugins_url( 'ext', __FILE__ ) );

/**
 * Themes URL
 * @since 1.0.0
 */
define( 'AMD_THEMES_URL', plugins_url( 'themes', __FILE__ ) );

/**
 * CSS style sheets directory
 * @since 1.0.0
 */
define( 'AMD_CSS', AMD_ASSETS . '/css' );

/**
 * JS scripts directory
 * @since 1.0.0
 */
define( 'AMD_JS', AMD_ASSETS . '/js' );

/**
 * Images directory
 * @since 1.0.0
 */
define( 'AMD_IMG', AMD_ASSETS . '/images' );

/**
 * Modules directory
 * @since 1.0.0
 */
define( 'AMD_MOD', AMD_ASSETS . '/modules' );

/**
 * Pages path
 * @since 1.0.0
 */
define( 'AMD_PAGES', AMD_PATH . DIRECTORY_SEPARATOR . 'pages' );

/**
 * Dashboard path
 * @since 1.0.0
 */
define( 'AMD_DASHBOARD', AMD_PATH . DIRECTORY_SEPARATOR . 'dashboard' );

/**
 * API handler
 * @since 1.0.0
 */
define( 'AMD_API_PATH', AMD_PATH . DIRECTORY_SEPARATOR . 'api' );

/**
 * Author website URL (for documentation and other resources)
 * @since 1.0.0
 */
define( 'AMD_AUTH_SITE', "http://amatris.ir" );

/**
 * Watermark
 * @since 1.0.0
 */
define( 'AMD_WATERMARK', "www.amatris.ir" );

/**
 * Default theme
 * @since 1.0.0
 */
define( 'AMD_DEFAULT_THEME', "amatris" );

/**
 * Templates path
 * @since 1.0.0
 */
define( 'AMD_TEMPLATES', AMD_PATH . DIRECTORY_SEPARATOR . 'templates' );

/**
 * Upload path
 * @since 1.0.0
 */
$_upload_base_dir = wp_get_upload_dir()['basedir'] ?? ( ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' );
define( 'AMD_UPLOAD_PATH', $_upload_base_dir . DIRECTORY_SEPARATOR . AMD_DIRECTORY );

/**
 * Version codes
 * @since 1.0.0
 */
const AMD_VERSION_CODES = array(
	'bundle' => '1.2.0',
	'structure' => '1.0.0',
	'admin' => '1.0.0',
	'vars' => '1.0.0',
	'mi' => '1.2.1',
);