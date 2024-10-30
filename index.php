<?php

/**
 * Plugin Name: Material Dashboard
 * Description: The best material dashboard for WordPress! If you want to delete this plugin, delete its data from cleanup menu first.
 * Plugin URI: https://amatris.ir/amd
 * Author: Hossein
 * Version: 1.2.1
 * Requires at least: 5.2
 * Requires PHP: 7.4.0
 * Tested up to: 6.6
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

    if( !function_exists( "get_plugin_data" ) )
        return [];

	return get_plugin_data( __FILE__ );
}

/**
 * Initialize not index-able translations
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
	__( "Enable", "material-dashboard" );
	_x( "Enabled", "Admin", "material-dashboard" );
    _x( "Raw page", "Template Name", "material-dashboard" );
    _x( "Blank page", "Template Name", "material-dashboard" );
}

# Load required variables and constants
require_once( __DIR__ . "/var.php" );

# Initialize cores
require_once( AMD_CORE . '/init.php' );

# Load hooks
require_once( AMD_INCLUDES . "/hooks.php" );

# Load templates hooks
require_once( AMD_INCLUDES . "/template-hooks.php" );

# Require functions
require_once( __DIR__ . "/functions.php" );

# Autoload hooks
amd_hooks_load_all();

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
		"success" => __( "Success", "material-dashboard" ),
		"failed" => __( "Failed", "material-dashboard" ),
		"error" => __( "An error has occurred", "material-dashboard" ),
		"saved" => __( "Saved", "material-dashboard" ),
		"save" => __( "Save", "material-dashboard" ),
		"not_saved" => __( "Not saved", "material-dashboard" ),
		"copy" => __( "Copy", "material-dashboard" ),
		"settings" => __( "Settings", "material-dashboard" ),
		"wait" => __( "Please wait", "material-dashboard" ),
		"ok" => __( "Okay", "material-dashboard" ),
		"yes" => __( "Yes", "material-dashboard" ),
		"no" => __( "No", "material-dashboard" ),
		"close" => __( "Close", "material-dashboard" ),
		"cancel" => __( "Cancel", "material-dashboard" ),
		"confirm" => __( "Confirm", "material-dashboard" ),
		"login" => __( "Login", "material-dashboard" ),
		"back" => __( "Back", "material-dashboard" ),
		"copied" => __( "Copied", "material-dashboard" ),
		"submit" => __( "Submit", "material-dashboard" ),
		"register" => __( "Register", "material-dashboard" ),
		"redirecting" => __( "Redirecting", "material-dashboard" ),
		"reload" => __( "Reload", "material-dashboard" ),
		"refresh" => __( "Refresh", "material-dashboard" ),
		"welcome" => __( "Welcome", "material-dashboard" ),
		"avatar" => __( "Avatar image", "material-dashboard" ),
		"sending" => __( "Sending", "material-dashboard" ),
		"retry" => __( "Retry", "material-dashboard" ),
		"you" => __( "You", "material-dashboard" ),
		"more" => __( "More", "material-dashboard" ),
		"general" => __( "General", "material-dashboard" ),
		"database" => __( "Database", "material-dashboard" ),
		"pages" => __( "Pages", "material-dashboard" ),
		"appearance" => __( "Appearance", "material-dashboard" ),
		"hello_world" => __( "Hello world", "material-dashboard" ),
		"lorem_ipsum" => __( "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.", "material-dashboard" ),
		"import" => __( "Import", "material-dashboard" ),
		"export" => __( "Export", "material-dashboard" ),
		"private_error" => __( "An error has occurred, please check console", "material-dashboard" ),
		"file_invalid" => __( "Selected file has not a valid content or an error has occurred while reading it", "material-dashboard" ),
		"site_options" => __( "Site options", "material-dashboard" ),
		"users_meta" => __( "Users meta", "material-dashboard" ),
		"temp_data" => __( "Temporarily data", "material-dashboard" ),
		"date" => __( "Date", "material-dashboard" ),
		"backup_author" => __( "Backup author", "material-dashboard" ),
		"version" => __( "Version", "material-dashboard" ),
		"backup_from" => __( "Source", "material-dashboard" ),
		"no_file_selected" => __( "No file selected", "material-dashboard" ),
		"backup" => __( "Backup", "material-dashboard" ),
		"download" => __( "download", "material-dashboard" ),
		"delete" => __( "delete", "material-dashboard" ),
		"backup_files" => __( "Backup files", "material-dashboard" ),
		"delete_backup_files" => __( "Delete backup files", "material-dashboard" ),
		"delete_confirm" => __( "Are you sure about deleting selected items?", "material-dashboard" ),
		"deleting" => __( "Deleting", "material-dashboard" ),
		"note" => __( "Note ", "material-dashboard" ),
		"least_one" => __( "You must select at lease one item!", "material-dashboard" ),
		"svg_icon" => __( "SVG icon", "material-dashboard" ),
		"svg_icons_alert" => __( "Icons that marked with %s are SVG icon which takes more data usage and may slow your dashboard pages, please make sure you have enough resources in your host.", "material-dashboard" ),
		"console_assert" => __( "Something went wrong! please check console for more details", "material-dashboard" ),
		"dashboard" => __( "Dashboard", "material-dashboard" ),
		"s_extension" => __( "%s extension", "material-dashboard" ),
		"country_codes" => __( "Country codes", "material-dashboard" ),
		"cc_exists" => __( "This country code already exists", "material-dashboard" ),
		"notice" => __( "Notice", "material-dashboard" ),
		"unsaved_changes_notice" => __( "This operation will reload this page after saving, please save them first if you have unsaved changes.", "material-dashboard" ),
		"continue" => __( "Continue", "material-dashboard" ),
		"password_8" => __( "Password must be at least 8 characters", "material-dashboard" ),
		"password_match" => __( "Password must be same as password confirm", "material-dashboard" ),
		"reset_password" => __( "Reset password", "material-dashboard" ),
		"password_changed" => __( "Your password has changed successfully", "material-dashboard" ),
		"control_fields" => __( "Please fill out all fields correctly", "material-dashboard" ),
		"phone_incorrect" => __( "Please enter your phone number correctly", "material-dashboard" ),
		"logging_in" => __( "Logging in", "material-dashboard" ),
		"logout" => __( "Logout", "material-dashboard" ),
		"logout_confirmation" => __( "Do you want to logout from your account?", "material-dashboard" ),
		"select_file" => __( "Select file", "material-dashboard" ),
		"select_one_item" => __( "Select one item", "material-dashboard" ),
		"invalid_email" => __( "Please enter a valid email address", "material-dashboard" ),
		"vCode_invalid" => __( "Please enter verification code correctly", "material-dashboard" ),
		"sign_up" => __( "Sign-up", "material-dashboard" ),
		"results" => _x( "Results", "Search results", "material-dashboard" ),
		"no_results" => _x( "No results found", "Search results", "material-dashboard" ),
		"cart" => __( "Cart", "material-dashboard" ),
		"cart_empty_confirmation" => __( "Are you sure your want to remove all items in your cart?", "material-dashboard" ),
		"account_info" => __( "Account information", "material-dashboard" ),
		"change" => __( "Change", "material-dashboard" ),
		"uploading" => __( "Uploading", "material-dashboard" ),
		"avatar_changed" => __( "Avatar image changed", "material-dashboard" ),
		"change_password" => __( "Change password", "material-dashboard" ),
		"todo_list" => __( "Todo list", "material-dashboard" ),
		"saving" => __( "Saving", "material-dashboard" ),
		"do_not_leave_page" => __( "Please do not leave this page", "material-dashboard" ),
		"edit" => __( "Edit", "material-dashboard" ),
		"browse" => _x( "Browse", "Admin", "material-dashboard" ),
		"single:n_files" => _nx( "%s file", "%s files", 1, "Admin", "material-dashboard" ),
		"plural:n_files" => _nx( "%s file", "%s files", 2, "Admin", "material-dashboard" ),
		"single:n_days_ago" => _nx( "%s day ago", "%s days ago", 1, "Admin", "material-dashboard" ),
		"plural:n_days_ago" => _nx( "%s day ago", "%s days ago", 2, "Admin", "material-dashboard" ),
		"backup_includes" => __( "Backup includes", "material-dashboard" ),
		"move" => __( "Move", "material-dashboard" ),
		"move_up" => __( "Move up", "material-dashboard" ),
		"move_down" => __( "Move down", "material-dashboard" ),
		"reloading_tasks" => __( "Reloading tasks", "material-dashboard" ),
		"todo_double_click_to_edit" => __( "Double click on texts to edit", "material-dashboard" ),
		"todo_enter_to_save" => __( "Hit enter or double click on outside the text to save", "material-dashboard" ),
		"saving_orders" => __( "Saving orders", "material-dashboard" ),
		"write_a_text" => __( "Write anything", "material-dashboard" ),
		"hint" => __( "Hint", "material-dashboard" ),
		"exporting" => __( "Exporting", "material-dashboard" ),
        "single:n_results_found" => _n( "%s result found", "%s result found", 1, "material-dashboard" ),
        "plural:n_results_found" => _n( "%s result found", "%s result found", 2, "material-dashboard" ),
		"database_collation_fix_confirm" => __( "Are you sure about changing database collations? this can take a while", "material-dashboard" ),
		"search" => __( "Search", "material-dashboard" ),
        "select_users" => __( "Select users", "material-dashboard" ),
        "single:n_users_selected" => ( _n( "%s user selected", "%s users selected", 1, "material-dashboard" ) ),
        "plural:n_users_selected" => ( _n( "%s user selected", "%s users selected", 2, "material-dashboard" ) ),
	) );

    if( amd_is_admin() ){
        do_action( "amd_add_front_string", array(
            "task_n_times" => _x( "%s times", "Task", "material-dashboard" ),
            "task_every_n" => _x( "Every %s", "Task", "material-dashboard" ),
            "run" => _x( "Run", "Task", "material-dashboard" ),
            "task_delete_confirm" => _x( "Are you sure about deleting this task? it may be created again.", "Task", "material-dashboard" ),
            "task_run_confirm" => _x( "Do you want to run this task?", "Task", "material-dashboard" ),
            "more_than_one_day" => _x( "More than a day", "Task", "material-dashboard" ),
            "more_than_one_week" => _x( "More than a week", "Task", "material-dashboard" ),
            "more_than_one_month" => _x( "More than a month", "Task", "material-dashboard" ),
            "single:within_n_days" => ( _n( "Within %s day", "Within %s days", 1, "material-dashboard" ) ),
            "plural:within_n_days" => ( _n( "Within %s day", "Within %s days", 2, "material-dashboard" ) ),
            "menu_settings" => __( "Menu settings", "material-dashboard" ),
        ) );
    }

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

	global $amdWall;

	$amdWall->handleLoginAttempt( $ip, $username, $error );

}, 10, 3 );

# Handle rest API endpoint
add_filter( "rest_endpoints", function( $endpoints ){

	global $amdWall;

	return $amdWall->handleRestEndpoints( $endpoints );

} );

# Initialize plugin
add_action( "init", function(){

	global $amdCache;

	$locales = apply_filters( "amd_override_locales", amd_get_site_option( "locales" ) );
	$locales_exp = explode( ",", $locales );
    $is_user_logged_in = is_user_logged_in();
    $current_user = amd_get_current_user();
    $current_user_id = $current_user ? $current_user->ID : 0;
	if( count( $locales_exp ) == 1 ){
		$locale = $locales[0] ?? "";
	}
	else{
		if( !empty( $_GET["lang"] ) )
            $amdCache->setLocale( sanitize_text_field( $_GET["lang"] ) );

		if( $is_user_logged_in )
			$locale = get_user_meta( $current_user_id, "locale", true );
		else
			$locale = $amdCache->getCache( "locale" );

		if( !in_array( $locale, $locales_exp ) )
			$locale = current( $locales_exp );
	}

	if( !empty( $locale ) ) {
        amd_switch_locale( $locale );
        if( $is_user_logged_in ) {
            $user_locale = get_user_meta( $current_user_id, "locale", true );
            if( empty( $user_locale ) )
                update_user_meta( $current_user_id, "locale", $locale );
        }
    }

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
