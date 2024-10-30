<?php

/**
 * Plugin Name: Material Dashboard
 * Description: The best material dashboard for WordPress! If you want to delete this plugin, delete its data from cleanup menu first.
 * Plugin URI: https://amatris.ir/amd
 * Author: Hossein
 * Version: 1.0.5
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

# Load version control
require_once( __DIR__ . "/vc.php" );

/**
 * Get this plugin info, also you can use it for plugin availability check
 * @return array
 * @since 1.0.0
 */
function amd_plugin(){

	return get_plugin_data( __FILE__ );
}

/**
 * Initialize not indexable translations
 * @return void
 * @since 1.0.4
 */
function amd_plugin_translation(){
	__( "Database is not installed correctly or needs to be updated, do you want to do it now?", "material-dashboard" );
	__( "Not now", "material-dashboard" );
	__( "Updating database", "material-dashboard" );
	__( "Page has been created", "material-dashboard" );
	__( "This page already registered, do you want to make another page?", "material-dashboard" );
	__( "Page was already exists and new page has replaced", "material-dashboard" );
}

# Load required variables and constants
require_once( __DIR__ . "/var.php" );

# Initialize cores
require_once( AMD_CORE . '/init.php' );

# Load hooks
require_once( AMD_INCLUDES . "/hooks.php" );

# Require functions
require_once( __DIR__ . "/functions.php" );

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
		"welcome" => esc_html__( "Welcome", "material-dashboard" ),
		"avatar" => esc_html__( "Avatar image", "material-dashboard" ),
		"sending" => esc_html__( "Sending", "material-dashboard" ),
		"retry" => esc_html__( "Retry", "material-dashboard" ),
		"you" => esc_html__( "You", "material-dashboard" ),
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
		"deleting" => esc_html__( "Deleting", "material-dashboard" ),
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
		"change" => esc_html__( "Change", "material-dashboard" ),
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
		"move" => esc_html__( "Move", "material-dashboard" ),
		"move_up" => esc_html__( "Move up", "material-dashboard" ),
		"move_down" => esc_html__( "Move down", "material-dashboard" ),
		"reloading_tasks" => esc_html__( "Reloading tasks", "material-dashboard" ),
		"todo_double_click_to_edit" => esc_html__( "Double click on texts to edit", "material-dashboard" ),
		"todo_enter_to_save" => esc_html__( "Hit enter or double click on outside the text to save", "material-dashboard" ),
		"saving_orders" => esc_html__( "Saving orders", "material-dashboard" ),
		"write_a_text" => esc_html__( "Write anything", "material-dashboard" ),
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
        else if( $text = apply_filters( "amd_custom_page_state_badge", "", $id ) )
			$states[] = $badge . $text;

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

/**
 * Disable rocket lazy load for dashboard pages
 * @return void
 * @since 1.0.4
 */
function amd_disable_rocket_lazyload(){

	$is_admin_pages = false;
	if( is_admin() AND !empty( $_GET["page"] ) AND in_array( $_GET["page"], ["material-dashboard", "material-dashboard-pro"] ) )
		$is_admin_pages = true;

	if( amd_is_dashboard_page() OR $is_admin_pages ){
		add_filter( "do_rocket_lazyload", "__return_false" );
		if( function_exists( "rocket_lazyload" ) )
			remove_filter( "rocket_lazyload_excluded_src", "rocket_add_async_exclude", 10 );
	}

}
add_action( "wp", "amd_disable_rocket_lazyload" );
add_action( "admin_init", "amd_disable_rocket_lazyload" );

/**
 * Disable rocket defer from scripts in dashboard pages
 * @return void
 * @since 1.0.4
 */
function amd_remove_rocket_defer_attr( $excludes ){

	$is_admin_pages = false;
	if( is_admin() ){
		if( !empty( $_GET["page"] ) ){
			$page = sanitize_text_field( $_GET["page"] );
			if( in_array( $page, ["material-dashboard", "material-dashboard-pro"] ) OR amd_starts_with( $page, "amd-" ) OR amd_starts_with( $page, "adp-" ) )
			$is_admin_pages = true;
		}
	}

	if( amd_is_dashboard_page() OR $is_admin_pages ){
		$excludes[] = "jquery-core-js";
		$excludes[] = "jquery-migrate-js";
	}
	return $excludes;

}
add_action( "rocket_defer_inline_js_exclusions", "amd_remove_rocket_defer_attr" );

# Handle login attempt
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

# Initialize plugin
add_action( "init", function(){

	global /** @var AMDCache $amdCache */
	$amdCache;

	$locales = apply_filters( "amd_override_locales", amd_get_site_option( "locales" ) );
	$locales_exp = explode( ",", $locales );
	if( count( $locales_exp ) == 1 ){
		$locale = $locales[0] ?? "";
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
		amd_switch_locale( $locale );

} );

/**
 * Survey admin notice
 * @return void
 * @since 1.0.4
 */
function amd_ext__app_survey_notice(){

	if( amd_get_site_option( "survey_skipped" ) != "true" ){
		?>
		<div class="notice amd-admin-notice" id="amd-admin-survey-notice">
            <div style="width:max-content;text-align:center">
                <img style="width:32px;height:auto" src="<?php echo esc_url( amd_get_logo( 'svg_fit_default.svg' ) ); ?>" alt="<?php esc_html_e( "Material Dashboard", "material-dashboard" ); ?>">
                <p class="_title"><?php esc_html_e( "1 Minute Of Your Time", "material-dashboard" ); ?></p>
            </div>
			<p style="font-size:15px"><?php printf( esc_html__( "Please give us 1 minute of your time and participate in %sOur survey%s to improve this plugin", "material-dashboard" ), "<b>", "</b>" ); ?></p>
            <i style="font-size:14px;color:#5142fc;display:block;margin:4px 0 8px"><?php esc_html_e( "Material Dashboard", "material-dashboard" ); ?></i>
			<a href="<?php echo esc_url( amd_get_survey_url() ); ?>" target="_blank" class="button-primary _button"><?php esc_html_e( "Open survey", "material-dashboard" ); ?></a>
			<button type="button" id="amd-survey-button-skip" class="button-secondary _button"><?php esc_html_e( "Don't show again", "material-dashboard" ); ?></button>
		</div>
		<!-- @formatter off -->
		<style>.amd-admin-notice{border-right-color:#5142fc;border-top:none;border-left:none;border-bottom:none;padding:16px !important}  .amd-admin-notice ._title{font-size:18px;margin:8px 0;font-weight:bold;color:#5142fc}  .amd-admin-notice .button-primary,.amd-admin-notice .button-primary:hover{background:#5142fc;color:#fff}  .amd-admin-notice .button-secondary,.amd-admin-notice .button-secondary:hover{background:#e0dcfc;color:#5142fc}  .amd-admin-notice ._button{outline:none;border:none;border-radius:8px;padding:4px 16px;margin-inline-end:8px;margin-top:4px}</style>
		<!-- @formatter on -->
        <script>
            jQuery(a=>{a("#amd-survey-button-skip").click(function(){let e=a(this);e.attr("disabled","true");e.html('<?php esc_html_e( "Please wait", "material-dashboard" ); ?>...');let t=()=>{let e=a("#amd-admin-survey-notice");e.fadeOut(400);setTimeout(()=>e.remove(),500)};a.ajax({url:'<?php echo get_site_url() . "/wp-admin/admin-ajax.php"; ?>',type:"POST",dataType:"JSON",data:{action:"amd_ajax_handler",skip_survey:!0},success:()=>t(),error:()=>t()})})});
        </script>
		<?php
	}

}
add_action( "all_admin_notices", "amd_ext__app_survey_notice" );