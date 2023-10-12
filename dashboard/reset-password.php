<?php

if( is_user_logged_in() ){
	wp_safe_redirect( amd_get_dashboard_page() );
	exit();
}

do_action( "amd_begin_dashboard" );

if( amd_template_exists( "reset-password" ) ){
	amd_load_template( "reset-password" );
	return;
}

$current_locale = get_locale();
$direction = is_rtl() ? "rtl" : "ltr";
$theme = amd_get_current_theme_mode();

$lazy_load = amd_get_site_option( "lazy_load", "true" );
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "true" );

$rp_title = amd_get_site_option( "reset_password_page_title" );
if( empty( $rp_title ) )
	$rp_title = esc_html_x( "Reset your password", "Reset password title", "material-dashboard" );

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
foreach( $signInMethods as $id => $method ){
	$s_methods[$id] = [];
	$name = $method["name"] ?? "";
	$icon = $method["icon"] ?? "";
	$action = $method["action"] ?? "";
	$s_methods[$id]["name"] = $name;
	$s_methods[$id]["html"] = "<div class=\"form-field item\" data-auth=\"$action\"><span>$name</span>$icon</div>";
	$s_methods[$id]["icon"] = $icon;
}

$icon_pack = amd_get_icon_pack();

amd_add_element_class( "body", [$theme, $direction, $current_locale, $icon_pack] );

$bodyBG = apply_filters( "amd_dashboard_bg", "" );

?><!doctype html>
<html lang="<?php bloginfo_rss( 'language' ); ?>">
<head>
	<?php do_action( "amd_dashboard_header" ); ?>
	<?php do_action( "amd_reset_password_header" ); ?>
	<?php amd_load_part( "header" ); ?>
    <title><?php echo esc_html( apply_filters( "amd_reset_password_page_title", esc_html__( "Reset password", "material-dashboard" ) ) ); ?></title>
</head>
<body class="<?php echo esc_attr( amd_element_classes( "body" ) ); ?>" <?php echo !empty( $bodyBG ) ? "style=\"background-image:url('" . esc_attr( $bodyBG ) . "')\"" : ""; ?>>
<div class="amd-quick-options"><?php do_action( "amd_auth_quick_option" ); ?></div>
<div class="amd-form-loading --full _suspend_screen_">
	<?php amd_load_part( "suspension_loader" ); ?>
</div>
<div class="amd-lr-form <?php echo esc_attr( amd_element_classes( "auth_form" ) ); ?>" id="form" data-form="rp">
    <div class="--logo">
        <img src="" alt="">
    </div>
    <h1 class="--title"><?php echo esc_html( $rp_title ); ?></h1>
    <h4 class="--sub-title"></h4>
    <p id="rp-log" class="amd-form-log _bg_"></p>
    <div class="h-10"></div>
    <div id="rp-fields">
        <label class="ht-input" data-step="1">
            <input type="text" data-field="email" data-pattern="%email%" data-next="submit" placeholder="" required>
            <span><?php esc_html_e( "Email", "material-dashboard" ); ?></span>
			<?php _amd_icon( "email" ); ?>
        </label>
        <label class="ht-magic-input" data-step="2" data-length="6" data-keys="[0-9]">
            <input type="text" data-field="vCode" data-next="submit" placeholder="" required>
            <span class="--title"><?php esc_html_e( "Verification code", "material-dashboard" ); ?></span>
            <span class="--keys"></span>
            <?php _amd_icon( "password" ); ?>
        </label>
        <label class="ht-input" data-step="3">
            <input type="password" data-field="new_password" minlength="8" data-next="submit" placeholder="" required>
            <span><?php esc_html_e( "New password", "material-dashboard" ); ?></span>
	        <?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
	        <?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
        </label>
        <input type="hidden" data-field="_none_" required>
    </div>
    <div class="mt-20"></div>
    <div class="amd-lr-buttons">
        <button type="button" class="btn" data-submit="rp"><?php esc_html_e( "Next", "material-dashboard" ); ?></button>
        <button type="button" class="btn btn-text" data-auth="login"><?php esc_html_e( "Login to your account", "material-dashboard" ); ?></button>
    </div>
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
        log_id: "rp-log"
    });
    var $submit = $('[data-submit="rp"]'), lastHtml = $submit.html();
    var step = 1;
    var setStep = s => {
        $("[data-step]").fadeOut(0);
        $(`[data-step="${s}"]`).fadeIn();
        if(s === 3){
            $submit.html(_t("submit"));
        }
        else{
            $submit.html(lastHtml);
        }
    };
    setStep(1);
    let submit_once = true;
    form.on("before_submit", () => submit_once = true);
    form.on("valid", () => {
        if(!submit_once) return;
        var data = {}, onSuccess = () => {}, targetStep =1;
        if(step === 1){
            data = {
                email: form.getField("email").value
            };
            onSuccess = () => form.$getField("vCode").focus();
            targetStep = 2;
        }
        else if(step === 2){
            data = {
                email: form.getField("email").value,
                vCode: form.getField("vCode").value
            };
            onSuccess = () => form.$getField("new_password").focus();
            targetStep = 3;
        }
        else if(step === 3){
            data = {
                email: form.getField("email").value,
                vCode: form.getField("vCode").value,
                new_password: form.getField("new_password").value
            };
            onSuccess = () => {
                $amd.alert(_t("reset_password"), _t("password_changed"), {
                    confirmButton: _t("ok"),
                    cancelButton: false,
                    onConfirm: () => {
                        dashboard.suspend();
                        $amd.openQuery("auth=login")
                    }
                });
            };
            targetStep = 4;
        }

        network.clean();
        network.put("reset_password", data);
        network.on.start = () => dashboard.suspend();
        network.on.end = (resp, error) => {
            dashboard.resume();
            form.clearValidationState();
            if(!error){
                if(resp.success){
                    form.log(resp.data.msg, "green");
                    step = targetStep;
                    setStep(step);
                    onSuccess();
                }
                else{
                    form.log(resp.data.msg, "red");
                }
            }
            else{
                $amd.toast(_t("error"));
            }
        }
        network.post();

        submit_once = false;
    });
    form.on("invalid_code", data => {
        let {field, code} = data;
        let id = field.id || "";
        if(step === 1){
            if(id === "email")
                dashboard.toast(_t("invalid_email"))
        }
        else if(step === 3){
            if(id === "new_password" && (code === 4 || code === 1))
                dashboard.toast(_t("password_8"))
        }
    });
    form.$getField("vCode").on("change", function(){
        let $el = $(this), v = $el.val();
        let length = $el.parent().hasAttr("data-length", true);
        if(!length) return;
        if(length && parseInt(length) === v.length) form.submit();
    });
</script>
<?php do_action( "amd_after_reset_password_page" ); ?>
</body>
</html>