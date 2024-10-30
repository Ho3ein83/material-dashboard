<?php

# If user is logged-in, redirect to dashboard
if( is_user_logged_in() ){
	wp_safe_redirect( amd_get_dashboard_page() );
	exit();
}

# If maximum login attempts reached
if( amd_login_attempts_reached() ){
    wp_safe_redirect( amd_make_action_url_without_temp( "too_many_attempts" ) );
    exit();
}

do_action( "amd_begin_dashboard" );

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

amd_add_element_class( "body", [$theme, $direction, $current_locale, "icon-$icon_pack", "theme-$theme_id"] );

$bodyBG = apply_filters( "amd_dashboard_bg", "" );

/**
 * Allow users to use login with phone
 * @since 1.0.4
 */
$allow_phone_login = apply_filters( "amd_allow_login_with_phone", true );

$auth = "login-email";
if( $allow_phone_login ){
	$auth = sanitize_text_field( $_GET["auth"] ?? "" );
	if( !in_array( $auth, [ "login-phone", "login-email" ] ) )
		$auth = apply_filters( "amd_default_login_auth_mode", "login-email" );
}

$regions = amd_get_regions();

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
                <input type="text" data-field="user" data-next="password" placeholder="" required>
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

        <button type="button" class="btn btn-text" data-auth="reset-password"><?php esc_html_e( "Reset password", "material-dashboard" ); ?></button>
    </div>
	<?php if( !empty( $s_methods ) ): ?>
        <div class="divider --more"></div>
        <div class="amd-login-methods">
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
                if(typeof resp.data.url !== "undefined")
                    location.href = resp.data.url;
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

    let $country_code = form.$getField("country_code");
    let $phone_number = form.$getField("phone_number");
    let country_codes = {};
    $country_code.parent().parent().find(".--options > span").each(function() {
        let cc = $(this).hasAttr("data-value", true);
        let format = $(this).hasAttr("data-format", true, "");
        if(cc) {
            country_codes[cc] = {
                "$e": $(this),
                "format": format.toUpperCase()
            };
        }
    });

    var getSelectedCC = () => {
        return $country_code.hasAttr("data-value", true, "");
    }
    $country_code.on("change", function() {
        let cc = getSelectedCC();
        if(cc) {
            $phone_number.blur();
            $phone_number.focus();
            $phone_number.val("+" + cc);
        }
    });
    var formatPhoneNumber = (number, format, clean = true) => {
        let cc = getSelectedCC();
        let num = number;
        num.replaceAll(" ", "");
        num = num.replaceAll("+" + cc, "");
        num = num.replaceAll("+", "");
        num = num.replace(cc, "");
        let out = format;
        for(let i = 0; i < num.length; i++) {
            let n = num[i] || "";
            out = out.replace("X", n);
        }
        if(clean) {
            out = out.replaceAll("X", "");
            out = out.replaceAll("-", " ");
        }
        return "+" + cc + " " + out;
    }
    let formatted = "";
    $phone_number.on("input", function(e) {
        let key = e.key;
        let $el = $(this);
        let v = $el.val();
        v = v.replaceAll("+", "");
        v = v.replaceAll(" ", "");
        if(v && !amd_conf.forms.isSpecialKey(key)) {
            if(typeof country_codes[v] !== "undefined") {
                $phone_number.val("+" + v);
                $country_code.val(v);
                $country_code.trigger("change");
            }
            let _cc = getSelectedCC();
            let ff = typeof country_codes[_cc] !== "undefined" ? (country_codes[_cc].format || "") : "";
            if(ff) {
                formatted = formatPhoneNumber(v, ff);
                $phone_number.val(formatted.trimChar(" "));
            }
        }
    });
    $country_code.on("change", function(){
        let cc = $(this).hasAttr("data-value", true, "");
        let _format = (country_codes[cc] || {format: ""}).format || "";
        if(_format) {
            let _f = _format;
            _f = _f.replaceAll("-", "\\s?");
            _f = _f.replaceAll("X", "[0-9]");
            $phone_number.attr("data-pattern", `^\\+${cc}\\s?${_f}$`);
        }
        let val = $country_code.val();
        for(let i = 0; i < val.length; i++)
            val = val.replaceAll("  ", " ");
        $country_code.val(val.trimChar(" "));
    });
    $country_code.trigger("change");
</script>
<?php do_action( "amd_after_login_page" ); ?>
</body>
</html>