<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Extension constants
define( 'AMD_EXT__APP_PATH', __DIR__ );
define( 'AMD_EXT__APP_WIZARD', AMD_EXT__APP_PATH . DIRECTORY_SEPARATOR . 'wizard' );

/**
 * Just for translations and extension availability check
 * @return void
 */
function amd_ext__app(){ }

/**
 * Admin and fronted init hook
 * @return void
 */
function amd_ext__app_init(){

	# Register country codes
	do_action( "amd_add_country_code", array(
		"IR" => array(
			"name" => "ایران",
			"flag" => AMD_IMG . "/flags/iran-flag.png",
			"digit" => "98",
			"format" => "XXX-XXX-XXXX"
		),
		"US" => array(
			"name" => "United states",
			"flag" => AMD_IMG . "/flags/usa-flag.png",
			"digit" => "1",
			"format" => "XXX-XXX-XXXX"
		)
	) );

	# Default country code
	$cc = apply_filters( "amd_country_codes", [] );
	amd_set_default( "country_code", current( $cc ) );

    # Checking interval
    amd_set_default( "checkin_interval", 30000 );

}

# Init
add_action( "admin_init", "amd_ext__app_init" );
add_action( "amd_dashboard_init", "amd_ext__app_init" );

# Mark user as online
add_action( "amd_checkin", function(){

    $user = amd_get_current_user();

    if( !$user ) return;

    $interval = intval( amd_get_default( "checkin_interval", 30000 ) ) / 1000;

    amd_set_temp( "checkin_" . $user->ID, time(), $interval );

    amd_set_user_meta( $user->ID, "last_seen", time() );

} );

# Mark as offline if user is online
add_action( "amd_checkout", function(){

    $user = amd_get_current_user();

    if( !$user ) return;

    amd_delete_temp( "checkin_" . $user->ID );

} );

# Load wizard page
add_action( "admin_init", function(){

    $_get = amd_sanitize_get_fields( $_GET );

	$page = !empty( $_get["page"] ) ? $_get["page"] : "";

	if( $page == "amd-wizard" ){

		if( !current_user_can( 'manage_options' ) )
			return;

		if( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		set_current_screen();

		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		amd_ext__app_dump_wizard_page();

		exit();

	}

} );

# Set dashboard cards
add_action( "amd_add_dashboard_card", function( $data ){

	global $amdDashboard;

	$amdDashboard->registerCard( $data );

} );

# Get dashboard cards
add_filter( "amd_get_dashboard_cards", function( $filter ){

	global $amdDashboard;

	return $amdDashboard->getCards( $filter, true );

} );

# Set dashboard sidebar menu item
add_action( "amd_add_dashboard_sidebar_menu", function( $data ){

	global $amdDashboard;

	$amdDashboard->addMenuItem( "sidebar_menu", $data );

} );

# Get dashboard sidebar menu items
add_filter( "amd_get_dashboard_sidebar_menu", function( $void ){

	global $amdDashboard;

	return $amdDashboard->getMenu( "sidebar_menu", $void );

} );

# Block users for a while after too many attempts for admin login
add_filter( "amd_login_too_many_attempts_expire", function(){
    // TODO: check if this value came from site options
	return intval( amd_get_site_option( "login_attempts_time", "10" ) );
} );

# Block users after attempting n times for admin login
add_filter( "amd_login_too_many_attempts_limit", function(){
	return intval( amd_get_site_option( "login_attempts", "10" ) );
} );

# "new verification code" message
add_filter( "amd_new_verification_code_message", function( $uid, $code ){
	$user = amd_get_user( $uid );
	if( !$user )
		return "";

    # Switch to user language
    $user->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE] - Password reset verification code
    $message = amd_get_user_message_template( "password_reset_verification", $user, $code );

    # Rollback language to current session language
    $user->rollbackLocale();

    return $message;

}, 10, 2 );

# "password changed" message
add_filter( "amd_password_changed_message", function( $uid ){
	$user = amd_get_user( $uid );
	if( !$user )
		return "";

    # Switch to user language
    $user->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE] - Password changed
    $message = amd_get_user_message_template( "password_changed", $user, time() );

    # Rollback language to current session language
    $user->rollbackLocale();

    return $message;

} );

# "change email" message
add_filter( "amd_change_email_message", function( $uid, $new_email, $url ){
	$user = amd_get_user( $uid );
	if( !$user )
		return "";

    //$a_tag = '<a class="link --fill" href="' . esc_url( $url ) . '">' . __( "Change email", "material-dashboard" ) . '</a>';

    # Switch to user language
    $user->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE] - Confirm email change
    $message = amd_get_user_message_template( "change_email_confirm", $user, $new_email, $url );

    # Rollback language to current session language
    $user->rollbackLocale();

    return $message;

}, 10, 3 );

# "email changed" message
add_filter( "amd_email_changed_message", function( $uid, $new_email ){
	$user = amd_get_user( $uid );
	if( !$user )
		return "";

    # Switch to user language
    $user->rollLocale();
    # [USER_TEMPLATE][MSG_TEMPLATE] - Email changed
    $message = amd_get_user_message_template( "email_changed", $user, $new_email );

    # Rollback language to current session language
    $user->rollbackLocale();

	return $message;

}, 10, 2 );

/**
 * 2FA code send message
 * @since 1.0.5
 */
add_action( "amd_send_2fa_code", function( $code, $user_id ){
	$user = amd_get_user( $user_id );
	if( !$user )
		return;

    global $amdWarn;

    /**
     * Message method ("email", "sms", "email,sms")
     * @since 1.0.5
     */
    $method = apply_filters( "amd_2fa_code_send_method", "email,sms" );
    $methods = explode( ",", $method );

    # Switch to user language
    $user->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE] - 2FA code
    $message_id = "2fa_code";
    $message = amd_get_user_message_template( $message_id, $user, $code );

    $data = [
        "email" => $user->email,
        "phone" => $user->phone,
        "subject" => __( "Two factor authentication", "material-dashboard" ),
        "message" => $message
    ];

    # Rollback language to current session language
    $user->rollbackLocale();

    # MARKME: Send by pattern (done - no schedule)

    # Send message by pattern if pattern exist, otherwise send it normally
    if( in_array( "sms", $methods ) ){
        $pattern_send = amd_send_message_by_pattern( $user->phone, $message_id, ["firstname" => $user->getSafeName(), "code" => $code] );
        if( $pattern_send === false || $pattern_send > 0 )
            unset( $methods[array_search( "sms", $methods )] );
    }

    $amdWarn->sendMessage( $data, implode( ",", $methods ) );

}, 10, 2 );

# Set dashboard navbar items
add_action( "amd_add_dashboard_navbar_item", function( $side, $data ){

	global $amdDashboard;

	$amdDashboard->addNavItem( $side, $data );

}, 10, 2 );

# Get dashboard sidebar menu items
add_filter( "amd_get_dashboard_navbar_item", function( $filter ){

	global /** @var AMDDashboard $amdDashboard */
	$amdDashboard;

	return $amdDashboard->getNav( $filter );

} );

# Set dashboard quick options
add_action( "amd_add_dashboard_quick_options", function( $data ){

	global /** @var AMDDashboard $amdDashboard */
	$amdDashboard;

	$amdDashboard->addMenuItem( "quick_options", $data );

} );

# Get dashboard sidebar quick options
add_filter( "amd_get_dashboard_quick_options", function(){

	global /** @var AMDDashboard $amdDashboard */
	$amdDashboard;

	return $amdDashboard->getMenu( "quick_options" );

} );

# Change email operation
add_filter( "amd_action_change_email", function( $scope ){

	$failed = array(
		"error" => true,
		"title" => __( "An error has occurred", "material-dashboard" ),
		"text" => __( "The address you are looking for is not valid or has been expired", "material-dashboard" )
	);

	if( empty( $scope ) )
		return $failed;

	$uid = explode( "_", $scope )[0];

	$user = amd_get_user_by( "ID", $uid );

	if( !$user )
		return $failed;

	$temp = amd_get_temp( "change_email:" . $scope );

	if( empty( $temp ) )
		return $failed;

	if( !amd_validate( $temp, "%email%" ) OR email_exists( $temp ) )
		return array(
			"error" => true,
			"title" => __( "An error has occurred", "material-dashboard" ),
			"text" => __( "Requested email address is not valid or cannot be changed", "material-dashboard" )
		);

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	$success = $amdSilu->changeEmail( $uid, $temp );

	if( $success ){
        amd_delete_user_meta( $user->ID, "email_verified" );
        amd_clean_temps_keys( "change_email:($user->ID)_(.*){8}" );
    }

	return !$success ? $failed : array(
		"error" => false,
		"title" => __( "Your email has changed", "material-dashboard" ),
		"text" => __( "Your email has been changed successfully", "material-dashboard" )
	);

} );

/**
 * 2FA login
 * @since 1.0.5
 */
add_filter( "amd_action_user_2fa", function( $scope ){

	$failed = array(
		"error" => true,
		"title" => __( "An error has occurred", "material-dashboard" ),
		"text" => __( "The address you are looking for is not valid or has been expired", "material-dashboard" )
	);

	if( empty( $scope ) )
		return $failed;

	$temp = amd_get_temp( "user_2fa:" . $scope, false );
    $expire = ( $temp->expire ?? 0 ) - time();
    if( $expire <= 0 )
        return $failed;

    $uid = intval( amd_get_temp( "user_2fa:" . $scope ) );

	$user = amd_get_user_by( "ID", $uid );

	if( !$user )
		return $failed;

	return array(
		"error" => false,
		"title" => __( "2 Factor Authentication", "material-dashboard" ),
		"page" => AMD_DASHBOARD . "/pages/toolkit-2fa.php",
		"show_btn" => false
	);

} );

# Block user from login page for a while after too many attempts to login
add_filter( "amd_action_too_many_attempts", function(){

	$failed = array(
		"error" => true,
		"title" => __( "An error has occurred", "material-dashboard" ),
		"text" => __( "The address you are looking for is not valid or has been expired", "material-dashboard" )
	);

	$data = amd_login_attempts_reached( true );
	// $max_attempts = $data["max_attempts"] ?? 0;
	// $attempts = $data["attempts"] ?? 0;
	$expire = $data["expire"] ?? 0;
	$reached = $data["reached"] ?? false;
	// $ip_address = $data["ip_address"] ?? "";

    if( !$reached ){
        wp_safe_redirect( amd_get_login_page() );
        exit();
    }

	$success = true;

	return !$success ? $failed : array(
		"error" => false,
		"title" => __( "Too many attempts", "material-dashboard" ) . "!",
        "text" => "",
        "btn_text" => __( "Back to site", "material-dashboard" ),
        "btn_url" => get_site_url()
	);

} );

add_action( "amd_action_content_too_many_attempts", function(){

    $data = amd_login_attempts_reached( true );
	// $max_attempts = $data["max_attempts"] ?? 0;
	// $attempts = $data["attempts"] ?? 0;
	$expire = $data["expire"] ?? 0;
	$reached = $data["reached"] ?? false;
	// $ip_address = $data["ip_address"] ?? "";

    if( $reached ){
        esc_html_e( "You can login to your account again in", "material-dashboard" );
    ?>
        <br><span id="many-attempts-timer"><span>
        <script>
            let expire = parseInt(`<?php echo $expire - time() + 5; ?>`);
            if(!isNaN(expire)) {
                let $el = $("#many-attempts-timer");
                let diff = expire;
                let timer;
                let recheck = () => {
                    if(diff > 0){
                        let ss = diff % 60;
                        let mm = parseInt(diff / 60 % 60);
                        let hh = parseInt(diff / 3600);
                        if(hh > 0) $el.html(`${hh.toString().addZero()}:${mm.toString().addZero()}:${ss.toString().addZero()}`);
                        else $el.html(`${mm.toString().addZero()}:${ss.toString().addZero()}`);
                    }
                    else{
                        clearInterval(timer);
                        location.reload();
                    }
                    diff--;
                }
                recheck();
                timer = setInterval(() => recheck(), 1000);
            }
        </script>
    <?php
    }

} );

# Icon-card: date text
add_filter( "amd_ic_date_text", function(){
    return amd_true_date( "l j F" );
} );

# Icon-card: date sub-text
add_filter( "amd_ic_date_subtext", function(){
    return amd_true_date( "Y/m/d" );
} );

/**
 * Initialize sidebar items
 * @since 1.0.1
 */
add_action( "amd_init_sidebar_items", function(){

    # Add dashboard sidebar item
	do_action( "amd_add_dashboard_sidebar_menu", array(
		"dashboard" => array(
			"text" => __( "Dashboard", "material-dashboard" ),
			"icon" => "dashboard",
			"void" => "home",
			"url" => "?void=home",
			"priority" => 3
		),
		"account" => array(
			"text" => __( "Account", "material-dashboard" ),
			"icon" => "person",
			"void" => "account",
			"url" => "?void=account",
			"priority" => 4
		),
	) );

    $am_i_admin = amd_is_admin();

    /**
      * Check if user has access to admin panel
      * @since 1.0.6
      */
    if( apply_filters( "amd_can_i_access_admin_dashboard", $am_i_admin ) ){

        do_action( "amd_add_dashboard_sidebar_menu", array(
            "admin" => array(
                "text" => __( "WP admin", "material-dashboard" ),
                "comment" => __( "This item is only visible for admins and normal users won't see it", "material-dashboard" ),
                "icon" => "tools",
                "void" => null,
                "url" => admin_url( $am_i_admin ? "admin.php?page=material-dashboard" : "" ),
                "priority" => 20,
                "turtle" => "blank",
                "attributes" => ["target" => "_blank"]
            )
	    ) );

    }

    # Add dashboard navbar item (Left side)
	do_action( "amd_add_dashboard_navbar_item", "left", array(
		"toggle_sidebar" => array(
			"icon" => "menu",
			"tooltip" => __( "Toggle sidebar", "material-dashboard" ),
			"attributes" => [
				"data-toggle-sidebar" => ""
			],
			"extraClass" => "no-desktop"
		),
		"translate" => array(
			"icon" => "translate",
			"tooltip" => __( "Language", "material-dashboard" ),
			"attributes" => [
				"data-change-locale" => ""
			]
		)
	) );

    if( amd_theme_support( "night_mode" ) ){

        do_action( "amd_add_dashboard_navbar_item", "left", array(
            "theme_dark" => array(
                "icon" => "dark_mode",
                "tooltip" => __( "Dark mode", "material-dashboard" ),
                "attributes" => [
                    "data-switch-theme" => "dark"
                ],
                "extraClass" => "no-dark"
            ),
            "theme_light" => array(
                "icon" => "light_mode",
                "tooltip" => __( "Light mode", "material-dashboard" ),
                "attributes" => [
                    "data-switch-theme" => "light"
                ],
                "extraClass" => "no-light"
            )
	    ) );

    }

	# Add dashboard navbar item (Right side)
	do_action( "amd_add_dashboard_navbar_item", "right", array(
		"account" => array(
			"icon" => "account",
			"tooltip" => __( "Account", "material-dashboard" ),
			"url" => "?void=account",
			"turtle" => "lazy"
		)
	) );

	# Add dashboard quick options
	do_action( "amd_add_dashboard_quick_options", array(
		"logout" => array(
			"icon" => "logout",
			"tooltip" => __( "Logout", "material-dashboard" ),
			"attributes" => [
				"data-action" => "logout"
			]
		)
	) );

    # Quick options in auth pages
	add_action( "amd_auth_quick_option", function(){
		?>
		<a href="<?php echo esc_url( apply_filters( "amd_auth_return_url", get_site_url() ) ); ?>" data-tooltip="<?php printf( esc_html__( "Back to %s", "material-dashboard" ), apply_filters( "amd_auth_return_name", get_bloginfo( 'name' ) ) ); ?>">
			<?php _amd_icon( "home" ); ?>
        </a>
        <?php if( amd_is_multilingual( true ) ): ?>
        <a href="javascript: void(0)" data-tooltip="<?php esc_attr_e( "Language", "material-dashboard" ); ?>" data-change-locale="">
			<?php _amd_icon( "translate" ); ?>
        </a>
        <?php endif; ?>
        <?php if( amd_theme_support( "night_mode" ) ): ?>
        <a href="javascript: void(0)" class="no-dark" data-tooltip="<?php esc_attr_e( "Dark mode", "material-dashboard" ); ?>"
           data-switch-theme="dark">
			<?php _amd_icon( "dark_mode" ); ?>
        </a>
        <a href="javascript: void(0)" class="no-light" data-tooltip="<?php esc_attr_e( "Light mode", "material-dashboard" ); ?>"
           data-switch-theme="light">
			<?php _amd_icon( "light_mode" ); ?>
        </a>
        <?php endif; ?>
		<?php
	} );

} );

# Initialize registered hooks
add_action( "amd_dashboard_init", function(){

	# Add dashboard cards (icon cards)
	do_action( "amd_add_dashboard_card", array(
		"ic_date" => array(
			"type" => "icon_card",
			"title" => __( "Date", "material-dashboard" ),
			"text" => apply_filters( "amd_ic_date_text", "" ),
			"subtext" => apply_filters( "amd_ic_date_subtext", "" ),
			"footer" => '<span class="__live_clock"></span>',
			"icon" => "calendar_1",
			"color" => "red",
			"priority" => 1
		)
	) );

	# Let users know when they passwords changes
	add_filter( "amd_notify_password_change", "__return_true" );

	# Register login page as auth page
	add_action( "amd_auth_login", function(){

		require_once( AMD_DASHBOARD . '/login.php' );

	} );

	# Register registration page as auth page
	add_action( "amd_auth_register", function(){

		require_once( AMD_DASHBOARD . '/register.php' );

	} );

	# Register password reset page as auth page
	add_action( "amd_auth_reset-password", function(){

        require_once( AMD_DASHBOARD . '/reset-password.php' );

	} );

    # Dashboard page
	add_action( "amd_auth_dashboard", function(){

        require_once( AMD_DASHBOARD . '/reset-password.php' );

	} );

	# Redirect to dashboard
	add_action( "amd_dashboard_redirect", function(){

        wp_safe_redirect( is_user_logged_in() ? amd_get_dashboard_page() : amd_get_login_page() );
		exit();

	} );

	# Action handler
	add_action( "amd_auth_action", function(){

		require_once( AMD_DASHBOARD . '/action.php' );

	} );

	# If registration is enabled ('users_can_register' in site options equals to '1')
	if( amd_can_users_register() AND apply_filters( "amd_add_registration_method", true )  ){
		# Register new sign-in method for new users registration
		do_action( "amd_register_sign_in_method", "_register", array(
			"name" => __( "Create new account", "material-dashboard" ),
			"icon" => amd_icon( "account" ),
			"action" => "register"
		) );
	}



} );

add_action( "amd_dashboard_start", function(){

    $optimizer = amd_get_site_option( "optimizer", "true" );

    add_filter( "amd_use_optimizer", $optimizer == "true" ? "__return_true" : "__return_false" );

} );

add_action( "amd_dashboard_start", function(){

    $optimize = apply_filters( "amd_use_optimizer", true );

    amd_add_element_class( "body", $optimize ? ["optimized"] : ["not_optimized"] );

} );

add_action( "amd_dashboard_init", function(){

    $optimize = apply_filters( "amd_use_optimizer", true );

    if( !$optimize )
        add_action( "amd_after_dashboard_page", "wp_footer" );

} );

# Dashboard and login page header
add_action( "amd_dashboard_header", function(){
	global /** @var AMDCache $amdCache */
	$amdCache;
	$API_URL = amd_get_api_url();
	$current_locale = get_locale();
    $indexing = amd_get_site_option( "indexing", "no-index" );
    $optimize = apply_filters( "amd_use_optimizer", true );
    if( $optimize ){
    ?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=7">
        <?php if( $indexing == "no-index" ): ?>
            <meta name="robots" content="noindex,nofollow">
        <?php endif; ?>

    <?php
    }
    else{
        wp_head();
    }
	?>
    <!-- @formatter off -->
    <?php amd_wp_head_alternate(); ?>

<!-- Dashboard -->
	<link rel="stylesheet" href="<?php echo esc_url( amd_merge_url_query( $API_URL, "stylesheets=_fonts&locale=$current_locale&screen=dashboard&ver=" . AMD_VER ) ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( amd_merge_url_query( $API_URL, 'stylesheets=_components,_dashboard&ver' . AMD_VER ) ); ?>">

    <!-- Icon pack -->
    <?php $amdCache->dumpStyles( "icon" ); ?>
    <!-- Global -->
    <?php $amdCache->dumpStyles( "global" ); ?>
	<!-- Frontend -->
    <?php $amdCache->dumpStyles(); ?>

	<!-- Dashboard -->
	<script src="<?php echo esc_url( amd_merge_url_query( $API_URL, 'scripts=_config&cache=' . time() ) ); ?>"></script>
	<!-- Icon pack -->
    <?php $amdCache->dumpScript( "icon" ); ?>
    <?php
    if( amd_is_dashboard_page() )
        $amdCache->dumpScript( "dashboard" );
    ?>
	<!-- Global -->
    <?php $amdCache->dumpScript( "global" ); ?>
	<!-- Frontend -->
    <?php $amdCache->dumpScript(); ?>
	<script src="<?php echo esc_url( AMD_JS . '/dashboard.js?ver=' . AMD_VER ); ?>"></script>

	<?php
    if( amd_is_dashboard_page() )
        $amdCache->dumpStyles( "dashboard" );
    ?>

	<!-- @formatter on -->

    <!-- Inline -->
        <style>
        .amd-lr-form {
            position: relative;
            width: 85%;
            max-width: 500px;
            border-radius: 12px;
            padding: 16px;
            margin: 64px auto;
            text-align: center;
            background: var(--amd-wrapper-fg)
        }

        .amd-lr-form input:-webkit-autofill,
        .amd-lr-form input:-webkit-autofill:focus {
            transition: background-color 600000s 0s, color 600000s 0s;
        }
        .amd-lr-form input[data-autocompleted] {
            background-color: transparent !important;
        }

        .amd-lr-form > .--title {
            font-family: var(--amd-title-font);
            color: var(--amd-title-color);
            font-size: 26px;
            margin: 0
        }

        .amd-lr-form > .--sub-title {
            font-family: var(--amd-font-family);
            color: rgba(var(--amd-title-color-rgb), .9);
            font-size: 16px;
            font-weight: normal;
            margin: 0
        }

        .amd-lr-form > .--logo {
            width: 100px;
            height: 100px;
            margin: auto
        }

        .amd-lr-form > .--logo > img {
            width: 100%;
            height: 100%;
            border-radius: 16px;
            object-fit: contain
        }

        .amd-lr-buttons {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            margin: 8px
        }

        .amd-lr-buttons > .btn {
            flex: 0 0 100%;
            font-size: 16px;
            height: 50px;
            line-height: 50px;
            border-radius: 8px;
            margin: 4px
        }

        .amd-lr-buttons > .btn:not(.btn-text) {
            box-shadow: 0 0 8px rgba(var(--amd-primary-rgb), .2)
        }

        .amd-login-methods, .amd-login-methods > .item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center
        }

        body.ltr .amd-login-methods, body.ltr .amd-login-methods > .item {
            flex-direction: row-reverse
        }

        .amd-login-methods > .item {
            position: relative;
            flex-wrap: nowrap;
            background: rgba(var(--amd-primary-rgb), .2);
            color: var(--amd-primary);
            border-radius: 8px;
            width: 100%;
            height: 55px;
            margin: 5px;
            cursor: var(--amd-pointer);
            transition: all ease .2s
        }

        .amd-login-methods > .item:hover {
            background: var(--amd-primary);
            color: #fff;
            box-shadow: 0 0 8px rgba(var(--amd-primary-rgb), .2)
        }

        .amd-login-methods > .item > ._icon_ {
            position: absolute;
            font-size: 24px;
            height: 24px;
            width: auto;
            left: 4px;
            padding: 8px;
            margin: 0 4px;
            border-radius: 8px;
        }
        .amd-login-methods > .item.fill-icon > ._icon_ {
            fill: currentColor;
        }

        .amd-login-methods > .item > span {
            font-size: 15px;
            margin: 0 4px;
            display: inline-block;
            white-space: nowrap;
            text-align: center;
            overflow: hidden;
            animation: text_close ease 1s;
            animation-iteration-count: 1
        }

        .divider {
            position: relative;
            margin: 32px 0
        }

        .divider:before {
            content: ' ';
            display: block;
            width: 100%;
            height: 1px;
            border-bottom: 2px dashed rgba(var(--amd-text-color-rgb), .2);
            clip-path: polygon(0px -200%, 0px 200%, 34.60% 658.83%, 35% -200%, 65% -200%, 65% 200%, 100% 200%, 100% -200%);
        }

        .divider.--more:after {
            content: '<?php esc_html_e( "More", "material-dashboard" );?>';
            color: rgba(var(--amd-text-color-rgb), .8)
        }

        .divider:after {
            display: block;
            position: absolute;
            font-size: 16px;
            width: 110px;
            text-align: center;
            top: -10px;
            left: calc(50% - 55px)
        }

        .amd-opt-grid {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: start;
            margin: 8px
        }

        .amd-opt-grid > div {
            display: block;
            flex: 1;
            color: rgba(var(--amd-text-color-rgb), .9);
            padding-inline-start: 8px
        }

        .amd-opt-grid > div:first-child {
            text-align: start
        }

        .amd-opt-grid > div:last-child {
            text-align: end
        }

        .amd-form-log {
            color: var(--amd-wrapper-fg);
            font-size: 15px;
            padding: 16px;
            border-radius: 9px;
            margin: 16px 8px;
            font-weight: bold
        }

        .amd-form-log > a {
            color: var(--amd-wrapper-fg);
            text-decoration: underline
        }</style>
    <?php if( amd_is_login_page() ): ?>
        <!-- @formatter off -->
        <style>
        .amd-quick-options{display:flex;flex-wrap:wrap;flex-direction:row-reverse;align-items:center;justify-content:center;height:max-content;position:absolute;top:0;left:0;margin:8px;background:rgba(var(--amd-wrapper-bg-rgb),.8);border-radius:11px;backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px)}.amd-quick-options>a{display:flex;flex-wrap:nowrap;align-items:center;justify-content:center;color:rgba(var(--amd-text-color-rgb),.9);margin:4px;text-decoration:none;width:24px;height:auto;aspect-ratio:1;padding:8px;border-radius:8px;background:transparent;transition:background-color ease .3s,color ease .2s}.amd-quick-options>a:hover{background:rgba(var(--amd-primary-rgb),.2);color:var(--amd-primary)}.amd-quick-options>a>.mi{font-size:24px}.amd-quick-options>a>.bi{font-size:18px}.amd-quick-options>a>svg.iconhub{width:20px;height:auto}@media(min-width:993px){.amd-quick-options{opacity:.3;transition:opacity ease .3s;}.amd-quick-options:hover{opacity:1}}
        body.image-bg{min-height:calc(100vh - 64px);background-size:cover;background-repeat:no-repeat;background-position:center;}
        body.backdrop-supported.use-glass-morphism .amd-lr-form{background:rgba(var(--amd-wrapper-bg-rgb),.5) !important;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
        </style>
        <style>@media(max-width:993px){.ht-input-row{margin:8px}}</style>
        <!-- @formatter on -->
        <script>
        $(document).on("click", "[data-auth]", function(e) {
            let auth = $(this).attr("data-auth");
            if(auth){
                let allowed = true;
                if(typeof e.isDefaultPrevented !== "undefined")
                    allowed = !e.isDefaultPrevented();
                else if(typeof e.defaultPrevented !== "undefined")
                    allowed = !e.defaultPrevented;
                let v = $amd.doEvent("amd_auth", { auth, e });
                if(v === null){
                    if(typeof dashboard !== "undefined")
                            dashboard.suspend();
                    $amd.openQuery("auth=" + auth);
                }
                else{
                    let allow = typeof v.allow === "undefined" ? true : v.allow;
                    if(allow){
                        let url = v.url || "";
                        if(url !== "#"){
                            if(typeof dashboard !== "undefined")
                                dashboard.suspend();
                            if(url)
                                location.href = url;
                            else
                                $amd.openQuery("auth=" + auth);
                        }
                    }
                }
            }
        });
        </script>
    <?php endif; ?>
	<?php
} );

# Dashboard page open-graph meta tags
add_action( "amd_dashboard_header_single", function(){


    $dashboard_title = _x( "Dashboard", "material-dashboard" );
    $content = __( "You can manage your account information, transactions, payments and purchases by logging in to your dashboard", "material-dashboard" );

    $dash = amd_get_dash_logo();
    $dashLogoURL = $dash["url"] ?? "";

    ?>
    <meta name="og:title" content="<?php echo esc_html( apply_filters( "amd_dashboard_og_title", "$dashboard_title - " . get_bloginfo( 'name' ) ) ); ?>">
    <meta name="og:content" content="<?php echo esc_html( apply_filters( "amd_dashboard_og_content", $content ) ); ?>">
    <meta name="og:image" content="<?php echo esc_url( apply_filters( "amd_dashboard_og_image", $dashLogoURL ) ) ?>">
    <?php

} );

# Login page open-graph meta tags
add_action( "amd_login_header", function(){

    $login_title = amd_get_site_option( "login_page_title" );
    if( empty( $login_title ) )
	    $login_title = _x( "Login to your account", "Login title", "material-dashboard" );

    $content = __( "You can manage your account information, transactions, payments and purchases by logging in to your dashboard", "material-dashboard" );

    $dash = amd_get_dash_logo();
    $dashLogoURL = $dash["url"] ?? "";

    ?>
    <meta name="og:title" content="<?php echo esc_html( apply_filters( "amd_login_og_title", "$login_title - " . get_bloginfo( 'name' ) ) ); ?>">
    <meta name="og:content" content="<?php echo esc_html( apply_filters( "amd_login_og_content", $content ) ); ?>">
    <meta name="og:image" content="<?php echo esc_url( apply_filters( "amd_login_og_image", $dashLogoURL ) ) ?>">
    <?php

} );

# Registration page open-graph meta tags
add_action( "amd_registration_header", function(){

    $register_title = amd_get_site_option( "register_page_title" );
    if( empty( $register_title ) )
	    $register_title = _x( "Create new account", "Sign-up title", "material-dashboard" );

    $content = __( "You can manage your account information, transactions, payments and purchases by logging in to your dashboard", "material-dashboard" );

    $dash = amd_get_dash_logo();
    $dashLogoURL = $dash["url"] ?? "";

    ?>
    <meta name="og:title" content="<?php echo esc_html( apply_filters( "amd_registration_og_title", "$register_title - " . get_bloginfo( 'name' ) ) ); ?>">
    <meta name="og:content" content="<?php echo esc_html( apply_filters( "amd_registration_og_content", $content ) ); ?>">
    <meta name="og:image" content="<?php echo esc_url( apply_filters( "amd_registration_og_image", $dashLogoURL ) ) ?>">
    <?php

} );

# Firewall AES encryption nonce
add_filter( "amd_firewall_aes_nonce", function(){

	/*
	 * DO NOT CHANGE IT
	 * if you override this hook and change nonce value
	 * every encoded string in your database (which added by this plugin) will be lost for good!
	 * change it only if you know what you are doing, or you have no data to be worried about.
	 */

	return "K0qk6pdtdv2ZmPdv";
} );

# Allow users to change their email
/*
 * NOTE: By changing email, users downloads and purchases won't be lost!
 * If you are using 'Easy Digital Downloads' or 'WooCommerce' plugins users data
 * won't be lost, however we can't say that about your other plugins, please make sure
 * that your site users data are not depended on their emails.
 */
add_filter( "amd_change_email_allowed", function(){
    return amd_get_site_option( "change_email_allowed", "false" ) == "true";
} );

# Register new avatar
add_action( "amd_add_avatar", function( $path ){

	global /** @var AMDCache $amdCache */
	$amdCache;

	$amdCache->setScopeGroup( "avatars", $path );

} );

/**
 * Unregister avatar group
 * @since 1.0.4
 */
add_action( "amd_remove_avatar", function( $group_id ){

	global /** @var AMDCache $amdCache */
	$amdCache;

    $avatars = $amdCache->getScopeGroup( "avatars" );

    if( isset( $avatars[$group_id] ) ){
        unset( $avatars[$group_id] );
        $amdCache->updateScopeGroup( "avatars", $avatars );
    }

} );

# Get registered avatars
add_filter( "amd_get_avatars", function(){

	global /** @var AMDCache $amdCache */
	$amdCache;

	return $amdCache->getScopeGroup( "avatars" );

} );

# Check if current user is admin
add_filter( "amd_is_admin", function(){

	if( !is_user_logged_in() )
		return false;

	return amd_get_current_user()->isAdmin();

} );

# Change admin panel avatar image (disabled since 1.2.1)
add_filter( "_get_avatar", function( $avatar, $id_or_email, $size, $default, $alt ) {

    $change = apply_filters( "amd_change_admin_panel_avatar", true );

    if( !$change )
        return $avatar;

    $user = null;

    if ( !is_numeric( $id_or_email ) AND is_email( $id_or_email->comment_author_email ?? "" ) ) {
        $user = get_user_by( "email", $id_or_email );
        if ( $user )
            $id_or_email = $user->ID;
    }

    if( !is_numeric( $id_or_email ) )
        return $avatar;

    $attachment_id  = get_user_meta( $id_or_email, "image", true );

    if ( !empty( $attachment_id  ) )
        return wp_get_attachment_image( $attachment_id, [ $size, $size ], false, ["alt" => $alt] );

    if( !$user )
        return $avatar;

    $user_profile = amd_get_user_by( "ID", $user->ID );

    return "<img class=\"avatar avatar-$size\" src=\"$user_profile\" width=\"$size\" height=\"$size\" alt=\"$alt\"/>";

}, 10, 5 );

# Check if custom avatar upload is allowed
add_filter( "amd_is_avatar_upload_allowed", function(){
    return amd_get_site_option( "avatar_upload_allowed", "true" ) == "true";
} );

# Register avatars
function amd_ext__app_register_avatars(){
	do_action( "amd_add_avatar", [ "amd_default" => AMD_ASSETS_PATH . "/images/avatars/flat" ] );
}
add_action( "amd_api_init", "amd_ext__app_register_avatars" );
add_action( "amd_dashboard_init", "amd_ext__app_register_avatars" );
add_action( "admin_init", "amd_ext__app_register_avatars" );

# Initialize dashboard
add_action( "amd_dashboard_init", function(){

	# Account card
	do_action( "amd_add_dashboard_card", array(
		"acc_avatar" => array(
			"page" => AMD_DASHBOARD . '/cards/acc_avatar.php',
			"type" => "acc_card",
			"priority" => 10
		),
		"acc_personal_info" => array(
			"page" => AMD_DASHBOARD . '/cards/acc_personal.php',
			"type" => "acc_card",
			"priority" => 10
		),
		"change_phone" => array(
			"page" => AMD_DASHBOARD . '/cards/acc_change_phone.php',
			"type" => "acc_card",
			"priority" => 10
		),
        "security" => array(
			"page" => AMD_DASHBOARD . '/cards/acc_security.php',
			"type" => "acc_card",
			"priority" => 10
		),
		"acc_change_password" => array(
			"page" => AMD_DASHBOARD . '/cards/acc_change_password.php',
			"type" => "acc_card",
			"priority" => 10
		),
	) );

} );

add_filter( "amd_welcome_alert_title", function( $thisuser ){
    return __( "Welcome", "material-dashboard" );
} );

add_filter( "amd_welcome_alert_text", function( $thisuser ){

    /** @var AMDUser $thisuser */

    # Switch to user language
    $thisuser->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE] - Welcome notification
	$message = amd_get_user_message_template( "welcome", $thisuser );

    # Rollback language to current session language
    $thisuser->rollbackLocale();

    return $message;

} );

# Before home page content
add_action( "amd_before_page_home", function(){

    $thisuser = amd_get_current_user();

    # Show welcome message
    if( apply_filters( "amd_welcome_message_enabled", true ) AND amd_get_user_meta( $thisuser->ID, "welcome_pending" ) == "true" ){
        # @formatter off
        ?><script>dashboard.queue(()=>$amd.alert(`<?php echo apply_filters( "amd_welcome_alert_title", $thisuser ); ?>`, `<?php echo apply_filters( "amd_welcome_alert_text", $thisuser ) ?>`));</script><?php
        # @formatter on
        amd_delete_user_meta( $thisuser->ID, "welcome_pending" );
    }

} );

/**
 * Set emails header template
 *
 * @param string $to
 * Email receptors
 * @param string $subject
 * Email subject
 * @param string $message
 * Email message
 *
 * @return false|string
 * @since 1.0.0
*/
function amd_ext__app_email_head( $to, $subject, $message ){

    $blog_name = get_bloginfo( "name" );
    $site_desc = get_bloginfo( "description" );

    $dash = amd_get_dash_logo();
    $dash_logo_url = $dash["url"];
    $image_url = apply_filters( "amd_email_header_image", $dash_logo_url );
    $is_rtl = is_rtl();
    ob_start();
    # @formatter on
    ?><!DOCTYPE HTML>
<html lang="<?php bloginfo_rss( 'language' ); ?>">
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo esc_html( $subject ); ?></title>
    <style>
      img {
        border: none;
        -ms-interpolation-mode: bicubic;
        max-width: 100%;
      }
      body {
        background-color: #f6f6f6;
        font-family: sans-serif;
        -webkit-font-smoothing: antialiased;
        font-size: 14px;
        line-height: 1.4;
        margin: 0;
        padding: 0;
        direction: <?php echo $is_rtl ? "rtl" : "ltr"; ?>;
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
      }
      table {
        border-collapse: separate;
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        width: 100%;
      }
      table td {
        font-family: sans-serif;
        font-size: 14px;
        vertical-align: top;
      }
      .body {
        background-color: #f6f6f6;
        width: 100%;
      }
      .container {
        display: block;
        margin: 0 auto !important;
        /* makes it centered */
        max-width: 580px;
        padding: 10px;
        width: 580px;
      }
      .content {
        box-sizing: border-box;
        display: block;
        margin: 0 auto;
        max-width: 580px;
        padding: 10px;
      }
      .main {
        background: #ffffff;
        border-radius: 3px;
        width: 100%;
      }
      .wrapper {
        box-sizing: border-box;
        padding: 20px;
      }
      .content-block {
        padding-bottom: 10px;
        padding-top: 10px;
      }
      .footer {
        clear: both;
        margin-top: 10px;
        text-align: center;
        width: 100%;
      }
      .footer td,
      .footer p,
      .footer span,
      .footer a {
        color: #999999;
        font-size: 12px;
        text-align: center;
      }
      h1, h2, h3, h4 {
        color: #000000;
        font-family: sans-serif;
        font-weight: 400;
        line-height: 1.4;
        margin: 0;
        margin-bottom: 30px;
      }
      h1 {
        font-size: 35px;
        font-weight: 300;
        text-align: center;
        text-transform: capitalize;
      }
      p, ul, ol {
        font-family: sans-serif;
        font-size: 14px;
        font-weight: normal;
        margin: 0;
        margin-bottom: 15px;
      }
      p li, ul li, ol li {
        list-style-position: inside;
        margin-left: 5px;
      }
      a {
        color: #3498db;
        text-decoration: underline;
      }
      .wrapper .btn {
        background-color: #ffffff;
        border: solid 1px #3498db;
        border-radius: 5px;
        box-sizing: border-box;
        color: #3498db;
        cursor: pointer;
        display: inline-block;
        font-size: 14px;
        font-weight: bold;
        margin: 0;
        padding: 12px 25px;
        text-decoration: none;
        text-transform: capitalize;
      }
      .wrapper .btn {
        background-color: #3498db;
        border-color: #3498db;
        color: #ffffff;
      }
      .preheader {
        color: transparent;
        display: none;
        height: 0;
        max-height: 0;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
        mso-hide: all;
        visibility: hidden;
        width: 0;
      }
      .powered-by a {
        text-decoration: none;
      }
      hr {
        border: 0;
        border-bottom: 1px solid #f6f6f6;
        margin: 20px 0;
      }
      @media only screen and (max-width: 620px) {
        table.body h1 {
          font-size: 28px !important;
          margin-bottom: 10px !important;
        }
        table.body p,
        table.body ul,
        table.body ol,
        table.body td,
        table.body span,
        table.body a {
          font-size: 16px !important;
        }
        table.body .wrapper,
        table.body .article {
          padding: 10px !important;
        }
        table.body .content {
          padding: 0 !important;
        }
        table.body .container {
          padding: 0 !important;
          width: 100% !important;
        }
        table.body .main {
          border-left-width: 0 !important;
          border-radius: 0 !important;
          border-right-width: 0 !important;
        }
        table.body .btn table {
          width: 100% !important;
        }
        table.body .btn a {
          width: 100% !important;
        }
        table.body .img-responsive {
          height: auto !important;
          max-width: 100% !important;
          width: auto !important;
        }
      }
      @media all {
        .ExternalClass {
          width: 100%;
        }
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
          line-height: 100%;
        }
        .apple-link a {
          color: inherit !important;
          font-family: inherit !important;
          font-size: inherit !important;
          font-weight: inherit !important;
          line-height: inherit !important;
          text-decoration: none !important;
        }
        #MessageViewBody a {
          color: inherit;
          text-decoration: none;
          font-size: inherit;
          font-family: inherit;
          font-weight: inherit;
          line-height: inherit;
        }
        .btn-primary table td:hover {
          background-color: #34495e !important;
        }
        .btn-primary a:hover {
          background-color: #34495e !important;
          border-color: #34495e !important;
        }
      }
    </style>
  </head>
    <body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>
          <img style="display:block; margin:16px auto 0px; border-radius:8px;" width="100" src="<?php echo esc_attr( $image_url ); ?>" alt="<?php echo esc_attr( $blog_name ); ?>">
        </td>
      </tr>
    </table>
    <span class="preheader"><?php echo esc_html( $site_desc ); ?></span>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>&nbsp;</td>
        <td class="container">
          <div class="content">
            <table role="presentation" class="main">
              <tr>
                <td class="wrapper">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <p style="font-size:24px"><?php echo esc_html( $subject ); ?></p>
    <?php
    # @formatter on
    return ob_get_clean();
}
add_filter( "amd_email_head", "amd_ext__app_email_head", 10, 3 );

/**
 * Set emails content template
 *
 * @param string $to
 * Email receptors
 * @param string $subject
 * Email subject
 * @param string $message
 * Email message
 *
 * @return false|string
 * @since 1.0.0
*/
function amd_ext__app_email_content( $to, $subject, $message ){

    ob_start();
    ?>
                        <p><?php echo wp_kses( $message, amd_allowed_tags_with_attr( "a,p,div,span,img,br,i,b,small,strong,u" ) ); ?></p>
                        <p style="color:#484848; font-size:13px; margin:8px 0;"><?php echo esc_html( amd_true_date( "j F Y H:i" ) ); ?></p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
<?php
    return ob_get_clean();

}
add_filter( "amd_email_content", "amd_ext__app_email_content", 10, 3 );

/**
 * Set emails footer template
 * 
 * @param string $to
 * Email receptors
 * @param string $subject
 * Email subject
 * @param string $message
 * Email message
 * 
 * @return false|string
 * @since 1.0.0
 */
function amd_ext__app_email_foot( $to, $subject, $message ){
    $domain = amd_replace_url( "%domain%" );
    $site_url = get_site_url();
    ob_start();
    ?>
            <div class="footer">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-block">
                    <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html( $domain ); ?></a>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
    </body>
</html>
    <?php
    return ob_get_clean();
}
add_filter( "amd_email_foot", "amd_ext__app_email_foot", 10, 3 );

# Change email content type
add_filter( "wp_mail_content_type", function(){
    return "text/html";
});