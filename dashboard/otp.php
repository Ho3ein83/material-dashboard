<?php

# One Time Password (OTP) authentication form (since 1.2.0)

/**
 * Begin dashboard login hook
 * @since 1.0.5
 */
do_action( "amd_begin_dashboard_otp" );

if( amd_template_exists( "otp" ) ){
    amd_load_template( "otp" );
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

$use_email_otp = amd_get_site_option( "use_email_otp", "false" ) == "true";

/**
 * The number of seconds that users can request OTP resend
 * @since 1.2.0
 */
$resend_time = apply_filters( "amd_otp_resend_interval", 60 );

/**
 * One time password length
 * @sicne 1.2.0
 */
$otp_length = apply_filters( "amd_otp_code_length", 6 );

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
<div class="amd-lr-form <?php echo esc_attr( amd_element_classes( "auth_form" ) ); ?>" id="form" data-form="otp">
    <div class="--logo">
        <img src="" alt="">
    </div>
    <h1 class="--title"><?php echo wp_kses( $login_title, amd_allowed_tags_with_attr( "br,span,a" ) ); ?></h1>
    <h4 class="--sub-title"></h4>
    <p id="login-log" class="amd-form-log _bg_"></p>
    <div class="h-10"></div>
    <div id="login-fields">
        <?php do_action( "amd_before_otp_form" ); ?>
        <?php if( !$use_email_otp ): ?>
            <?php amd_phone_fields( true ); ?>
        <?php else: ?>
            <label class="ht-input">
                <input type="text" data-field="user" data-translate-digits="*" placeholder="" required>
                <span><?php esc_html_e( "Username or email", "material-dashboard" ); ?></span>
                <?php _amd_icon( "person" ); ?>
            </label>
        <?php endif; ?>
        <div class="_code_field" style="display:none">
            <label class="ht-input">
                <input type="text" data-field="code" data-next="submit" data-ignore data-pattern=".+" maxlength="<?php echo esc_attr( $otp_length ); ?>" placeholder="" required>
                <span><?php esc_html_e( "Received code", "material-dashboard" ); ?></span>
                <?php _amd_icon( "key" ); ?>
            </label>
        </div>
        <button type="button" class="btn btn-text _resend_otp_" style="display:none;gap:5px"><?php esc_html_e( "Resend", "material-dashboard" ); ?><span class="_resend_timeout_"></span></button>
        <?php do_action( "amd_between_otp_form" ); ?>
        <div class="amd-opt-grid _remember_me" style="display:none">
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
        <?php do_action( "amd_after_otp_form" ); ?>
    </div>
    <div class="mt-20"></div>
    <div class="amd-lr-buttons">
        <button type="button" class="btn" data-submit="otp"><?php esc_html_e( "Continue", "material-dashboard" ); ?></button>
        <a href="?auth=login&prefer=password" class="btn btn-text"><?php esc_html_e( "Login with password", "material-dashboard" ); ?></a>
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
    const resend_time = parseInt(`<?php echo intval( $resend_time ); ?>`) || 0;
    const otp_length = parseInt(`<?php echo intval( $otp_length ); ?>`) || 0;
    const $resend_btn = $("._resend_otp_");
    let resend_interval, _resend_user, _resend_phone, _code_listener_once = false;
    const set_resend_timeout = seconds => {
        let $span = $("._resend_timeout_");
        const reset = () => {
            $span.html("(" + $amd.secondsToClock(seconds) + ")");
            $resend_btn.setWaiting();
        }
        resend_interval = setInterval(() => {
            if(seconds <= 0){
                clearInterval(resend_interval);
                resend_interval = null;
                $span.html("");
                $resend_btn.setWaiting(false);
                return;
            }
            seconds--;
            reset();
        }, 1000);
        reset();
    }
    $resend_btn.click(function(){
        if(resend_interval)
            return;
        let n = dashboard.createNetwork();
        n.clean();
        n.setAction(amd_conf.ajax.public);
        n.put("otp", {
            login: _resend_user,
            phone: _resend_phone,
            resend: true,
        });
        n.on.start = () => {
            $resend_btn.waitHold(_t("wait_td"));
        }
        n.on.end = (resp, error) => {
            $resend_btn.waitRelease();
            if(!error){
                if(resp.success){
                    set_resend_timeout(resp.data.resend);
                }
                $amd.toast(resp.data.msg);
            }
            else{
                $amd.toast(_t("error"));
                console.log(resp.xhr);
            }
        }
        n.post();
    });
    form.on("submit", () => {
        let data = form.getFieldsData();

        let user_login = (data.user || {value: ""}).value || "";
        let user_phone = (data.phone_number || {value: ""}).value || "";
        let otp_code = (data.code || {value: ""}).value || "";
        let remember_me = data.remember.value || false;

        const show_login = () => {
            if(typeof data.user !== "undefined")
                $(data.user.element).parent().setWaiting();
            $("._phone_field_holder_").setWaiting();
            $("._code_field").fadeIn().find("input[data-field]").removeAttr("data-ignore");
            $("._remember_me").fadeIn();
            form.reload_fields();
            form.$getField("code").on("keydown keyup", function(){
                const len = $(this).val().length;
                if(len === otp_length) {
                    if(!_code_listener_once) {
                        form.submit();
                        _code_listener_once = true;
                    }
                }
                else if(len < otp_length){
                    _code_listener_once = false;
                }
            });
        }
        // show_login();return;

        if(!user_login && !user_phone)
            return;

        $amd.doEvent("otp_form_submit", data);
        network.clean();
        network.put("otp", {
            login: user_login,
            phone: user_phone,
            code: otp_code,
            remember: remember_me
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
                form.enable();
                if (resp.success) {
                    form.log(resp.data.msg, "green");
                    show_login();
                }
                else {
                    form.log(resp.data.msg, "red");
                }
                if(typeof resp.data.url !== "undefined") {
                    form.disable();
                    location.href = resp.data.url;
                }
                const resend = resp.data.resend ?? 0;
                $resend_btn.fadeIn();
                _resend_user = user_login;
                _resend_phone = user_phone;
                if(resend && resend > 0)
                    set_resend_timeout(parseInt(resend));
            }
            else {
                form.enable();
                form.log(_t("error"), "red");
            }
        };
        network.post();
    });
    form.on("invalid", field => {
        let {id} = field;
        if(id === "phone_number")
            dashboard.toast(_t("phone_incorrect"));
        if(id === "user")
            dashboard.toast(_t("control_fields"));
        $amd.doEvent("login_form_invalid", field);
        $amd.doEvent("otp_form_invalid", field);
    });
</script>
<?php do_action( "amd_after_login_page" ); ?>
</body>
</html>