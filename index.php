<?php

/**
 * Plugin Name: Material Dashboard
 * Description: The best material dashboard for WordPress! If you want to delete this plugin, delete its data from cleanup menu first.
 * Plugin URI: https://amatris.ir/amd
 * Author: Hossein
 * Version: 1.0.1
 * Requires at least: 5.2
 * Requires PHP: 7.4.0
 * Tested up to: 6.2
 * Author URI: https://amatris.ir/amd/author
 * Text Domain: material-dashboard
 * Domain Path: /languages/
 *
 * @package Amatris
 * @author Hossein
 * @copyright 2023 © Amatris
 * @license GPL-3
 *
 */

# Check if plugin is loaded from WordPress
defined( 'ABSPATH' ) or die();

/**
 * Get this plugin info, also you can use it for plugin availability check
 * @return array
 */
function amd_plugin(){

	return get_plugin_data( __FILE__ );
}

# Load required variables and constants
require_once( "var.php" );

# Initialize cores
require_once( AMD_CORE . '/init.php' );

# Require functions
require_once( "functions.php" );

# Initialize plugin hook
do_action( "amd_init" );

# Initialize core
add_action( "init", function(){

	# Check for force SSL
	$forceSSL = amd_is_ssl_forced();
	if( $forceSSL == "true" AND !amd_using_https() AND !is_admin() ){
		wp_safe_redirect( preg_replace( "/^http:\/\//", "https://", amd_replace_url( "%full_uri%" ) ) );
		exit();
	}

} );

/**
 * Initialize plugin defaults value
 * @return void
 * @since 1.0.0
 */
function amd_init_plugin_defaults(){

	# Default icon pack
	amd_set_default( "icon_pack", "material-icons" );

	# Default dashboard 404 page
	amd_set_default( "dashboard_404", AMD_DASHBOARD . "/pages/404.php" );

	# Default dashboard logo
	amd_set_default( "dash_logo", amd_get_logo( "fit_default_512.png" ) );

}

/**
 * Initialize and install database if needed
 *
 * @param bool $install
 * Whether to install database or just initialize, default is true
 *
 * @return void
 * @since 1.0.0
 */
function amd_init_plugin( $install = true ){

	amd_init_plugin_defaults();

	global /** @var AMDCore $amdCore */
	$amdCore;

	if( $install )
		$amdCore->run();
	else
		$amdCore->init();

}

# Init plugin
add_action( "init", function(){

	amd_init_plugin( false );

} );

# Add translation strings for frontend
add_action( "amd_init_translation", function(){

	do_action( "amd_add_front_string", array(
		"success" => esc_html__( "Success", "material-dashboard" ),
		"failed" => esc_html__( "Failed", "material-dashboard" ),
		"error" => esc_html__( "An error has occurred", "material-dashboard" ),
		"saved" => esc_html__( "Saved", "material-dashboard" ),
		"save" => esc_html__( "Save", "material-dashboard" ),
		"not_saved" => esc_html__( "Not saved", "material-dashboard" ),
		"settings" => esc_html__( "Settings", "material-dashboard" ),
		"wait" => esc_html__( "Please wait", "material-dashboard" ),
		"ok" => esc_html__( "Okay", "material-dashboard" ),
		"yes" => esc_html__( "Yes", "material-dashboard" ),
		"no" => esc_html__( "No", "material-dashboard" ),
		"close" => esc_html__( "Close", "material-dashboard" ),
		"cancel" => esc_html__( "Cancel", "material-dashboard" ),
		"confirm" => esc_html__( "Confirm", "material-dashboard" ),
		"login" => esc_html__( "Login", "material-dashboard" ),
		"back" => esc_html__( "Back", "material-dashboard" ),
		"copied" => esc_html__( "Copied", "material-dashboard" ),
		"submit" => esc_html__( "Submit", "material-dashboard" ),
		"register" => esc_html__( "Register", "material-dashboard" ),
		"redirecting" => esc_html__( "Redirecting", "material-dashboard" ),
		"reload" => esc_html__( "Reload", "material-dashboard" ),
		"refresh" => esc_html__( "Refresh", "material-dashboard" ),
		"avatar" => esc_html__( "Avatar image", "material-dashboard" ),
		"sending" => esc_html__( "Sending", "material-dashboard" ),
		"retry" => esc_html__( "Retry", "material-dashboard" ),
		"more" => esc_html__( "More", "material-dashboard" ),
		"general" => esc_html__( "General", "material-dashboard" ),
		"database" => esc_html__( "Database", "material-dashboard" ),
		"pages" => esc_html__( "Pages", "material-dashboard" ),
		"appearance" => esc_html__( "Appearance", "material-dashboard" ),
		"hello_world" => esc_html__( "Hello world", "material-dashboard" ),
		"lorem_ipsum" => esc_html__( "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.", "material-dashboard" ),
		"import" => esc_html__( "Import", "material-dashboard" ),
		"export" => esc_html__( "Export", "material-dashboard" ),
		"private_error" => esc_html__( "An error has occurred, please check console", "material-dashboard" ),
		"file_invalid" => esc_html__( "Selected file has not a valid content or an error has occurred while reading it", "material-dashboard" ),
		"site_options" => esc_html__( "Site options", "material-dashboard" ),
		"users_meta" => esc_html__( "Users meta", "material-dashboard" ),
		"temp_data" => esc_html__( "Temporarily data", "material-dashboard" ),
		"date" => esc_html__( "Date", "material-dashboard" ),
		"backup_author" => esc_html__( "Backup author", "material-dashboard" ),
		"version" => esc_html__( "Version", "material-dashboard" ),
		"backup_from" => esc_html__( "Source", "material-dashboard" ),
		"no_file_selected" => esc_html__( "No file selected", "material-dashboard" ),
		"backup" => esc_html__( "Backup", "material-dashboard" ),
		"download" => esc_html__( "download", "material-dashboard" ),
		"delete" => esc_html__( "delete", "material-dashboard" ),
		"backup_files" => esc_html__( "Backup files", "material-dashboard" ),
		"delete_backup_files" => esc_html__( "Delete backup files", "material-dashboard" ),
		"delete_confirm" => esc_html__( "Are you sure about deleting selected items?", "material-dashboard" ),
		"note" => esc_html__( "Note ", "material-dashboard" ),
		"least_one" => esc_html__( "You must select at lease one item!", "material-dashboard" ),
		"svg_icon" => esc_html__( "SVG icon", "material-dashboard" ),
		"svg_icons_alert" => esc_html__( "Icons that marked with %s are SVG icon which takes more data usage and may slow your dashboard pages, please make sure you have enough resources in your host.", "material-dashboard" ),
		"console_assert" => esc_html__( "Something went wrong! please check console for more details", "material-dashboard" ),
		"dashboard" => esc_html__( "Dashboard", "material-dashboard" ),
		"s_extension" => esc_html__( "%s extension", "material-dashboard" ),
		"country_codes" => esc_html__( "Country codes", "material-dashboard" ),
		"cc_exists" => esc_html__( "This country code already exists", "material-dashboard" ),
		"notice" => esc_html__( "Notice", "material-dashboard" ),
		"unsaved_changes_notice" => esc_html__( "This operation will reload this page after saving, please save them first if you have unsaved changes.", "material-dashboard" ),
		"continue" => esc_html__( "Continue", "material-dashboard" ),
		"password_8" => esc_html__( "Password must be at least 8 characters", "material-dashboard" ),
		"password_match" => esc_html__( "Password must be same as password confirm", "material-dashboard" ),
		"reset_password" => esc_html__( "Reset password", "material-dashboard" ),
		"password_changed" => esc_html__( "Your password has changed successfully", "material-dashboard" ),
		"control_fields" => esc_html__( "Please fill out all fields correctly", "material-dashboard" ),
		"phone_incorrect" => esc_html__( "Please enter your phone number correctly", "material-dashboard" ),
		"logging_in" => esc_html__( "Logging in", "material-dashboard" ),
		"logout" => esc_html__( "Logout", "material-dashboard" ),
		"logout_confirmation" => esc_html__( "Do you want to logout from your account?", "material-dashboard" ),
		"select_file" => esc_html__( "Select file", "material-dashboard" ),
		"invalid_email" => esc_html__( "Please enter a valid email address", "material-dashboard" ),
		"vCode_invalid" => esc_html__( "Please enter verification code correctly", "material-dashboard" ),
		"sign_up" => esc_html__( "Sign-up", "material-dashboard" ),
		"results" => esc_html_x( "Results", "Search results", "material-dashboard" ),
		"no_results" => esc_html_x( "No results found", "Search results", "material-dashboard" ),
		"cart" => esc_html__( "Cart", "material-dashboard" ),
		"cart_empty_confirmation" => esc_html__( "Are you sure your want to remove all items in your cart?", "material-dashboard" ),
		"account_info" => esc_html__( "Account information", "material-dashboard" ),
		"change" => esc_html__( "Account information", "material-dashboard" ),
		"uploading" => esc_html__( "Uploading", "material-dashboard" ),
		"avatar_changed" => esc_html__( "Avatar image changed", "material-dashboard" ),
		"change_password" => esc_html__( "Change password", "material-dashboard" ),
		"todo_list" => esc_html__( "Todo list", "material-dashboard" ),
		"saving" => esc_html__( "Saving", "material-dashboard" ),
		"do_not_leave_page" => esc_html__( "Please do not leave this page", "material-dashboard" ),
		"edit" => esc_html__( "Edit", "material-dashboard" ),
		"browse" => esc_html_x( "Browse", "Admin", "material-dashboard" ),
		"single:n_files" => _nx( "%s file", "%s files", 1, "Admin", "material-dashboard" ),
		"plural:n_files" => _nx( "%s file", "%s files", 2, "Admin", "material-dashboard" ),
		"single:n_days_ago" => _nx( "%s day ago", "%s days ago", 1, "Admin", "material-dashboard" ),
		"plural:n_days_ago" => _nx( "%s day ago", "%s days ago", 2, "Admin", "material-dashboard" ),
		"backup_includes" => esc_html__( "Backup includes", "material-dashboard" ),
	) );

} );

# Install database on activation
register_activation_hook( __FILE__, function(){

	amd_require_all();

	global /** @var AMDCore $amdCore */
	$amdCore;

	$amdCore->initHooks();

	$amdCore->initRegisteredHooks();

	amd_init_plugin();

	global $amdLoader;

	if( !empty( $amdLoader ) ){
		$active_theme = $amdLoader->getActiveTheme( true );
		if( !$active_theme )
			$amdLoader->enableTheme( $amdLoader::DEFAULT_THEME );
	}

} );

# Add custom action links
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), function( $links ){

	$custom = [];

	$custom["pro"] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer" style="color:#4a12c5;font-weight:bold">%s</a>', esc_url( amd_doc_url( null ) ), esc_attr_x( "Documentation", "Admin", "material-dashboard" ) );

	return array_merge( $custom, (array) $links );

} );

# Initialize translating
add_action( 'after_setup_theme', function(){

	load_plugin_textdomain( 'material-dashboard', false, basename( AMD_PATH ) . '/languages/' );

} );

# Change plugin pages state
add_filter( "display_post_states", function( $states, $post ){

	if( !empty( $post->ID ) ){

		$id = $post->ID;
		$badge = "<span style=\"background:#5f39f7;color:#fff;font-size:13px;font-weight:normal;padding:2px 10px 1px;border-radius:30px\" dir=\"auto\">" . esc_html__( "Amatris", "material-dashboard" ) . "</span> ";

		if( $id == amd_get_site_option( "login_page" ) )
			$states[] = $badge . esc_html__( "Login page", "material-dashboard" );
		else if( $id == amd_get_site_option( "dashboard_page" ) )
			$states[] = $badge . esc_html__( "Dashboard page", "material-dashboard" );
		else if( $id == amd_get_site_option( "api_page" ) )
			$states[] = $badge . esc_html__( "API page", "material-dashboard" );

	}

	return $states;

}, 10, 2 );

# Handle wp-login requests
add_action( "login_init", function(){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	$amdWall->loginInit();

} );

# Handle login failed attempt
add_action( 'wp_login_failed', function( $username, $error ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	$ip = $amdWall->getActualIP();

	do_action( "amd_login_attempt", $ip, $username, $error );

}, 10, 2 );

add_action( "amd_login_attempt", function( $ip, $username, $error ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	$amdWall->handleLoginAttempt( $ip, $username, $error );

}, 10, 3 );

# Handle rest API endpoint
add_filter( "rest_endpoints", function( $endpoints ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	return $amdWall->handleRestEndpoints( $endpoints );

} );

add_action( "init", function(){

	global /** @var AMDCache $amdCache */
	$amdCache;

	$locales = apply_filters( "amd_override_locales", amd_get_site_option( "locales" ) );
	$locales_exp = explode( ",", $locales );
	if( count( $locales_exp ) == 1 ){
		$locale = $locales;
	}
	else{
		if( !empty( $_GET["lang"] ) )
			$amdCache->setLocale( sanitize_text_field( $_GET["lang"] ) );

		if( is_user_logged_in() )
			$locale = get_user_meta( get_current_user_id(), "locale", true );
		else
			$locale = $amdCache->getCache( "locale" );

		if( !in_array( $locale, $locales_exp ) )
			$locale = current( $locales_exp );
	}

	if( !empty( $locale ) )
		switch_to_locale( $locale );

} );
