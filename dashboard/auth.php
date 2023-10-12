<?php

$_get = amd_sanitize_get_fields( $_GET );
$auth = $_get["auth"] ?? "";

if( empty( $auth ) ){
	wp_safe_redirect( is_user_logged_in() ? amd_get_dashboard_page() : amd_get_login_page() );
	exit();
}

do_action( "amd_begin_dashboard" );

/**
 * Begin dashboard auth hook
 * @since 1.0.5
 */
do_action( "amd_begin_dashboard_auth" );

do_action( "amd_auth_begin_$auth" );

if( amd_template_exists( "auth" ) ){
	amd_load_template( "auth" );
	return;
}

$lazy_load = amd_get_site_option( "lazy_load", "true" );
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "true" );

$current_locale = get_locale();
$direction = is_rtl() ? "rtl" : "ltr";
$theme = amd_get_current_theme_mode();

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
	$locales_count++;
}
$json_locales = json_encode( $json_locales );
if( !amd_is_multilingual( true ) ) $json_locales = "{}";

$data = apply_filters( "amd_auth_data", [] );

$error = $data["error"] ?? true;
$title = $data["title"] ?? esc_html__( "An error has occurred", "material-dashboard" );
$text = $data["text"] ?? esc_html__( "The address you are looking for is not valid or has been expired", "material-dashboard" );
$html = $data["html"] ?? "";
$show_btn = $data["show_btn"] ?? true;
$btn_text = $data["btn_text"] ?? "";
$btn_url = $data["btn_url"] ?? "";

$icon_pack = amd_get_icon_pack();
$theme_id = amd_get_theme_property( "id" );

amd_add_element_class( "body", [$theme, $direction, $current_locale, "icon-$icon_pack", "theme-$theme_id"] );

$bodyBG = apply_filters( "amd_dashboard_bg", "" );

$blog_name = get_bloginfo( "name" );

?><!doctype html>
<html lang="<?php bloginfo_rss( 'language' ); ?>">
<head>
	<?php do_action( "amd_dashboard_header" ); ?>
	<?php do_action( "amd_auth_header" ); ?>
	<?php amd_load_part( "header" ); ?>
    <title><?php echo esc_html( apply_filters( "amd_auth_page_title", $blog_name ) ); ?></title>
</head>
<body class="<?php echo esc_attr( amd_element_classes( "body" ) ); ?>" <?php echo !empty( $bodyBG ) ? 'style="' . "background-image:url('" . esc_attr( $bodyBG ) . "')" . '"' : ""; ?>>
<div class="amd-quick-options"><?php do_action( "amd_auth_quick_option" ); ?></div>
<div class="amd-form-loading --full _suspend_screen_">
	<?php amd_load_part( "suspension_loader" ); ?>
</div>
<div class="amd-lr-form <?php echo esc_attr( amd_element_classes( "auth_form" ) ); ?>" id="form" data-form="rp">
    <div class="--logo">
        <img src="<?php echo esc_url( $dashLogoURL ); ?>" alt="">
    </div>
    <h1 class="--title"><?php echo wp_kses( $title, amd_allowed_tags_with_attr( "br,span,a" ) ); ?></h1>
	<?php if( !empty( $text ) ): ?>
        <h4 class="--sub-title"><?php echo wp_kses( $text, amd_allowed_tags_with_attr( "br,span,a" ) ); ?></h4>
	<?php endif; ?>
	<?php echo wp_kses( $html, amd_allowed_tags_with_attr( "br,span,a,p,button" ) ); ?>
	<?php do_action( "amd_auth_content_$auth" ); ?>
    <div class="h-10"></div>
	<?php if( $show_btn ): ?>
        <div class="amd-lr-buttons">
			<?php if( !empty( $btn_text ) AND !empty( $btn_url ) ): ?>
                <a class="btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
			<?php else: ?>
				<?php if( is_user_logged_in() ): ?>
                    <button type="button" class="btn" data-auth="login"><?php esc_html_e( "Back to dashboard", "material-dashboard" ); ?></button>
				<?php else: ?>
                    <button type="button" class="btn" data-auth="login"><?php esc_html_e( "Login to your account", "material-dashboard" ); ?></button>
				<?php endif; ?>
			<?php endif; ?>
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
<?php do_action( "amd_after_auth_page" ); ?>
</body>
</html>