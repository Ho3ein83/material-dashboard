<?php

if( !session_id() )
    session_start();

$redirect = sanitize_text_field( $_GET["redirect"] ?? "" );
$auth = sanitize_text_field( $_GET["auth"] ?? "" );
if( $redirect )
    $_SESSION["redirect_pending"] = $redirect;

if( is_user_logged_in() ){
	wp_safe_redirect( amd_get_dashboard_page() );
	exit();
}

if( !amd_can_users_register() ){
	wp_safe_redirect( amd_get_login_page() );
	exit();
}

do_action( "amd_begin_dashboard" );

/**
 * Begin dashboard registration hook
 * @since 1.0.5
 */
do_action( "amd_begin_dashboard_register" );

if( amd_template_exists( "register" ) ){
	amd_load_template( "register" );
	return;
}

$current_locale = get_locale();
$direction = is_rtl() ? "rtl" : "ltr";
$theme = amd_get_current_theme_mode();

$lazy_load = amd_get_site_option( "lazy_load", "true" );
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "true" );

$phone_field = amd_get_site_option( "phone_field" ) == "true";
$phone_field_required = amd_get_site_option( "phone_field_required" ) == "true";

$login_after_registration = amd_get_site_option( "login_after_registration", "true" ) == "true";
$lastname_field = amd_get_site_option( "lastname_field" ) == "true";
$username_field = amd_get_site_option( "username_field" ) == "true";
$password_conf_field = amd_get_site_option( "password_conf_field", "true" ) == "true";

$register_title = amd_get_site_option( "register_page_title" );
if( empty( $register_title ) )
	$register_title = esc_html_x( "Create new account", "Sign-up title", "material-dashboard" );

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
	if( $id == "_register" )
		continue;
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

$regions = amd_get_regions();
$cc_count = $regions["count"];
$first_cc = $regions["first"];
$format = $regions["format"];
$countries_cc_html = $regions["html"];

$icon_pack = amd_get_icon_pack();
$theme_id = amd_get_theme_property( "id" );

$default_body_bg = "";
$extra_classes = [];
if( amd_theme_support( "image_background" ) ) {
    $register_form_bg = intval( amd_get_site_option( "register_form_bg" ) );
    $default_body_bg = $register_form_bg ? wp_get_attachment_url( $register_form_bg ) : $default_body_bg;
    $theme = $default_body_bg ? apply_filters( "amd_backgrounded_form_theme", $theme ) : $theme;
    $forced_ui_mode = boolval( $default_body_bg );
    $use_glass_morphism = apply_filters( "amd_use_glass_morphism_form", amd_theme_support( "glass_morphism_forms" ), "register" );
    $extra_classes = [$use_glass_morphism ? "use-glass-morphism" : "", $default_body_bg ? "image-bg" : "", $forced_ui_mode ? "forced-ui-mode" : ""];
}

amd_add_element_class( "body", array_merge( [$theme, $direction, $current_locale, "icon-$icon_pack", "theme-$theme_id"], $extra_classes ) );

$bodyBG = apply_filters( "amd_dashboard_bg", $default_body_bg );

$allow_only_persian_names = amd_get_site_option( "allow_only_persian_names" ) == "true";
$name_keys = $allow_only_persian_names ? "[\u0622-\u064A\u0672-\u06D5\s]" : ".";
$name_pattern = $allow_only_persian_names ? "[\u0622-\u064A\u0672-\u06D5\s]+" : ".*";

?><!doctype html>
<html lang="<?php bloginfo_rss( 'language' ); ?>">
<head>
	<?php do_action( "amd_dashboard_header" ); ?>
	<?php do_action( "amd_registration_header" ); ?>
	<?php amd_load_part( "header" ); ?>
    <title><?php echo esc_html( apply_filters( "amd_registration_page_title", esc_html__( "Sign-up", "material-dashboard" ) ) ); ?></title>
</head>
<body class="<?php echo esc_attr( amd_element_classes( "body" ) ); ?>" <?php echo !empty( $bodyBG ) ? "style=\"background-image:url('" . esc_attr( $bodyBG ) . "')\"" : ""; ?>>
<div class="amd-quick-options"><?php do_action( "amd_auth_quick_option" ); ?></div>
<div class="amd-form-loading --full _suspend_screen_">
	<?php amd_load_part( "suspension_loader" ); ?>
</div>
<div class="amd-lr-form <?php echo esc_attr( amd_element_classes( "auth_form" ) ); ?>" id="form" data-form="register">
    <div class="--logo"><img src="" alt=""></div>
    <h1 class="--title"><?php echo esc_html( $register_title ); ?></h1>
    <h4 class="--sub-title"></h4>
    <p id="register-log" class="amd-form-log _bg_"></p>
    <div class="h-10"></div>
    <form id="register-fields">
        <?php if( isset( $_GET["nofill"] ) OR apply_filters( "amd_register_form_nofill", false ) ): ?>
            <input type="text" name="_username" style="width: 0; height: 0; border: 0; padding: 0" />
            <input type="email" name="_email" style="width: 0; height: 0; border: 0; padding: 0" />
            <input type="password" name="_password" style="width: 0; height: 0; border: 0; padding: 0" />
        <?php endif; ?>

        <?php
            /**
             * Before registration fields
             * @since 1.0.5
             */
            do_action( "amd_registration_fields_before" );
        ?>

        <?php if( $lastname_field ): ?>
            <div class="ht-input-row">
                <label class="ht-input">
                    <input type="text" data-field="firstname" data-next="lastname" data-pattern="<?php echo esc_attr( $name_pattern ); ?>" data-keys="<?php echo esc_attr( $name_keys ); ?>" placeholder="" required>
                    <span><?php esc_html_e( "Firstname", "material-dashboard" ); ?><?php echo $allow_only_persian_names ? " (فارسی)" : ""; ?></span>
					<?php _amd_icon( "person" ); ?>
                </label>
                <label class="ht-input">
                    <input type="text" data-field="lastname" data-next="username|phone|email" data-pattern="<?php echo esc_attr( $name_pattern ); ?>" data-keys="<?php echo esc_attr( $name_keys ); ?>" placeholder="" required>
                    <span><?php esc_html_e( "Last name", "material-dashboard" ); ?><?php echo $allow_only_persian_names ? " (فارسی)" : ""; ?></span>
					<?php _amd_icon( "person" ); ?>
                </label>
            </div>
		<?php else: ?>
            <label class="ht-input">
                <input type="text" data-field="firstname" data-next="username|phone|email" data-pattern="<?php echo esc_attr( $name_pattern ); ?>" data-keys="<?php echo esc_attr( $name_keys ); ?>" placeholder="" required>
                <span><?php esc_html_e( "Fullname", "material-dashboard" ); ?><?php echo $allow_only_persian_names ? " (فارسی)" : ""; ?></span>
				<?php _amd_icon( "person" ); ?>
            </label>
            <input type="hidden" data-field="lastname" placeholder="">
		<?php endif; ?>

	    <?php
            /**
             * After registration name fields
             * @since 1.0.5
             */
            do_action( "amd_registration_fields_after_name" );
	    ?>

        <?php if( $username_field ): ?>
            <label class="ht-input">
                <input type="text" name="username" value="<?php echo esc_attr( sanitize_text_field( $_GET['username'] ?? '' ) ); ?>" data-field="username" data-pattern="%username%" data-next="phone|email" data-translate-digits="*" placeholder="" required>
                <span><?php esc_html_e( "Username", "material-dashboard" ); ?></span>
				<?php _amd_icon( "" ); ?>
            </label>
		<?php endif; ?>

	    <?php
            /**
             * After registration username field
             * @since 1.0.5
             */
            do_action( "amd_registration_fields_after_username" );
	    ?>

        <label class="ht-input">
            <input type="text" name="email" value="<?php echo esc_attr( sanitize_text_field( $_GET['email'] ?? '' ) ); ?>" data-field="email" data-pattern="%email%" data-next="password" data-translate-digits="*" placeholder="" required>
            <span><?php esc_html_e( "Email", "material-dashboard" ); ?></span>
			<?php _amd_icon( "email" ); ?>
        </label>

	    <?php
            /**
             * After registration email field
             * @since 1.0.5
             */
            do_action( "amd_registration_fields_after_email" );
	    ?>

		<?php if( $password_conf_field ): ?>
            <label class="ht-input">
                <input type="password" data-field="password" minlength="8" data-next="password-conf" placeholder=""
                       required>
                <span><?php esc_html_e( "Password", "material-dashboard" ); ?></span>
				<?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
				<?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
            </label>
            <label class="ht-input">
                <input type="password" data-field="password-conf" minlength="8" data-match="password"
                       data-next="country_code|phone_number|submit"
                       placeholder="" required>
                <span><?php esc_html_e( "Password confirmation", "material-dashboard" ); ?></span>
				<?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
				<?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
            </label>
		<?php else: ?>
            <label class="ht-input">
                <input type="password" name="password" data-field="password" minlength="8" data-next="country_code|phone_number|submit"
                       placeholder=""
                       required>
                <span><?php esc_html_e( "Password", "material-dashboard" ); ?></span>
				<?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
				<?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
            </label>
		<?php endif; ?>
    </form>

	<?php
        /**
         * After registration password field
         * @since 1.0.5
         */
        do_action( "amd_registration_fields_after_password" );
	?>

    <!-- Phone number fields -->
    <?php amd_phone_fields(); ?>
    <?php
        /** @since 1.2.0 */
        do_action( "amd_after_registration_phone_field" );
    ?>

	<?php
        /**
         * After registration phone field
         * @since 1.0.5
         */
        do_action( "amd_registration_fields_after_phone" );
	?>

	<?php
        /**
         * After registration fields
         * @since 1.0.5
         */
        do_action( "amd_registration_fields_after" );
	?>

    <div class="mt-20"></div>

    <div class="amd-lr-buttons">
        <button type="button" class="btn" id="btn-register" data-submit="register"><?php esc_html_e( "Register", "material-dashboard" ); ?></button>
        <button type="button" class="btn btn-text" data-auth="login"><?php esc_html_e( "Login to your account", "material-dashboard" ); ?></button>
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
        log_id: "register-log"
    });

    var login_after_registration = `<?php echo $login_after_registration ? "true" : "false"; ?>`;
    var $firstname = form.$getField("firstname");
    var $lastname = form.$getField("lastname");
    var $email = form.$getField("email");
    var $password = form.$getField("password");
    var $username = form.$getField("username");
    var $phone = form.$getField("phone_number");

    (function(){

        function exportCustomFields(){
            let fields = {};
            for(let [key, value] of Object.entries(form.getFieldsData())){
                if(key.startsWith("Custom:"))
                    fields[key.replaceAll("Custom:", "")] = value.value;
            }
            return fields;
        }

        form.on("before_submit", () => form.log(null));
        form.on("submit", () => {
            let data = form.getFieldsData();
            let email = data.email.value;
            let firstname = data.firstname.value;
            let lastname = data.lastname.value;
            let password = data.password.value;
            let username = $username.length > 0 ? $username.val() : "";
            let phone = (data.phone_number || {value: ""}).value || "";
            let vCode = (data.vCode || {value: ""}).value || "";
            login_after_registration = login_after_registration === "true";
            let custom_fields = exportCustomFields();
            network.clean();
            network.put("register", {
                firstname,
                lastname,
                username,
                email,
                password,
                phone,
                custom_fields,
                vCode,
                login_after_registration
            });
            network.on.start = () => {
                dashboard.suspend();
            }
            network.on.end = (resp, error) => {
                if(!error) {
                    if(resp.success) {
                        let url = resp.data.url || "";
                        let query = resp.data.query || "";
                        if(url) location.href = url;
                        else if(query) $amd.openQuery(query);
                        else $amd.openQuery("auth=login");
                    }
                    else {
                        dashboard.resume();
                        if(typeof resp.data.pause !== "undefined" && resp.data.pause){
                            $amd.doEvent("registration_pause", resp);
                            return;
                        }
                        setTimeout(() => {
                            let errors = resp.data.errors || [];
                            for(let i = 0; i <= errors.length; i++) {
                                let err = errors[i] || {};
                                if(typeof err.error !== "undefined") dashboard.toast(err.error);
                                if(typeof err.field !== "undefined") form.$getField(err.field).setInvalid(true, "invalid", true);
                            }
                        }, 300);
                    }
                }
                else {
                    dashboard.resume();
                    setTimeout(() => $amd.alert(_t("sign_up"), _t("error")), 300);
                }
            }
            network.post();
        });
        form.on("invalid_code", data => {
            let {field, code} = data;
            let id = field.id || "";
            if(id === "firstname") {
                dashboard.toast(_t("control_fields"));
            }
            else if(id === "email") {
                dashboard.toast(_t("invalid_email"));
            }
            else if(id === "password") {
                if(code === 4 || code === 1)
                    dashboard.toast(_t("password_8"));
            }
            else if(id === "password-conf") {
                if(code === 2 || code === 1)
                    dashboard.toast(_t("password_match"));
            }
            else if(id === "phone_number") {
                dashboard.toast(_t("phone_incorrect"));
            }
            else if(id === "vCode") {
                dashboard.toast(_t("vCode_invalid"));
            }
        });
    }());
</script>
<?php do_action( "amd_after_registration_page" ); ?>
</body>
</html>