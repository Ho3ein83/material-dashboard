<?php

if( !is_user_logged_in() ){
	$_SESSION["redirect_pending"] = sanitize_url( amd_replace_url( "%full_uri%" ) );
	wp_safe_redirect( amd_get_login_page() );
	exit();
}

do_action( "amd_begin_dashboard" );

if( amd_template_exists( "dashboard" ) ){
	amd_load_template( "dashboard" );

	return;
}

do_action( "amd_dashboard_start" );

global /** @var AMDCache $amdCache */
$amdCache;

$_get = amd_sanitize_get_fields( $_GET );

$void = !empty( $_get["void"] ) ? $_get["void"] : "home";
$amdCache->setCache( "void", $void );

$current_locale = get_locale();
$direction = is_rtl() ? "rtl" : "ltr";

# $lazy_load = amd_get_site_option( "lazy_load", "true" );
$lazy_load = amd_is_lazy_loading_enabled();
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "true" );

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

$sidebarItems = apply_filters( "amd_get_dashboard_sidebar_menu", $void );
$navbarItems_left = apply_filters( "amd_get_dashboard_navbar_item", "left" );
$navbarItems_right = apply_filters( "amd_get_dashboard_navbar_item", "right" );
$quickOptions = apply_filters( "amd_get_dashboard_quick_options", "1" );

$checkin_interval = amd_get_default( "checkin_interval", 30000 );

$icon_pack = amd_get_icon_pack();

amd_add_element_class( "body", [ $direction, $current_locale, $icon_pack ] );

$bodyBG = apply_filters( "amd_dashboard_bg", "" );

$page_content = "";
$page_title = "";
if( !$lazy_load ){

    global /** @var AMDDashboard $amdDashboard */
	$amdDashboard;

    $page = $amdDashboard->getDashboardPage( $void );
    $page_title = $page["title"];
    $page_content = $page["content"];

}

?><!doctype html>
<html lang="<?php bloginfo_rss( 'language' ); ?>">
<head>
	<?php do_action( "amd_dashboard_header" ); ?>
	<?php do_action( "amd_dashboard_header_single" ); ?>
	<?php amd_load_part( "header" ); ?>
    <title><?php echo esc_html( !$lazy_load ? $page_title : esc_html__( "Dashboard", "material-dashboard" ) ); ?></title>
    <script>
        var network = new AMDNetwork(), _network = new AMDNetwork();
        network.setAction(amd_conf.ajax.private);
        var dashboard = new AMDDashboard({
            sidebar_id: "sidebar",
            navbar_id: "navbar",
            wrapper_id: "wrapper",
            content_id: "content",
            loader_id: "loader",
            api_url: amd_conf.api_url,
            loading_text: `<?php echo esc_html_x( "Getting data", "Loading text", "material-dashboard" ); ?><span class="_loader_dots_"></span>`,
            languages: JSON.parse(`<?php echo $json_locales; ?>`),
            network: _network,
            translate: {
                "show_region": <?php echo $show_regions == "true" ? "true" : "false"; ?>,
                "show_flag": <?php echo $show_flags == "true" ? "true" : "false"; ?>
            },
            lazy_load: <?php echo $lazy_load ? "true" : "false" ?>,
            sidebar_items: <?php echo json_encode( $sidebarItems, JSON_PRETTY_PRINT ); ?>,
            navbar_items: {
                "left": <?php echo json_encode( $navbarItems_left ); ?>,
                "right": <?php echo json_encode( $navbarItems_right ); ?>
            },
            quick_options: <?php echo json_encode( $quickOptions ); ?>,
            checkin_interval: <?php echo is_numeric( $checkin_interval ) ? $checkin_interval : "60000"; ?>
        });
    </script>
</head>
<body class="<?php echo esc_attr( amd_element_classes( "body" ) ); ?>" <?php echo !empty( $bodyBG ) ? "style=\"background-image:url('" . esc_attr( $bodyBG ) . "')\"" : ""; ?>>
<div class="amd-form-loading --full _suspend_screen_">
	<?php amd_load_part( "suspension_loader" ); ?>
</div>
<div id="sidebar" class="amd-sidebar <?php echo esc_attr( amd_element_classes( "sidebar" ) ); ?> collapse">
	<?php amd_load_part( "sidebar" ); ?>
</div>
<div id="loader">
	<?php amd_load_part( "loader" ); ?>
</div>
<div id="wrapper" class="amd-wrapper <?php echo esc_attr( amd_element_classes( "wrapper" ) ); ?>">
    <div id="navbar" class="amd-navbar <?php echo esc_attr( amd_element_classes( "navbar" ) ); ?>">
		<?php amd_load_part( "navbar" ); ?>
    </div>
    <div class="text-center _show_on_loader_">
        <h2 class="_loading_text_"></h2>
    </div>
	<?php do_action( "amd_before_dashboard_content" ); ?>
    <div id="content" class="<?php echo esc_attr( amd_element_classes( "content" ) ); ?>"><?php echo !$lazy_load ? $page_content : ""; ?></div>
	<?php do_action( "amd_after_dashboard_content" ); ?>
</div>
<script>
    dashboard.setUser(amd_conf.getUser());
    dashboard.init();
    dashboard.initTooltips();
    dashboard.awake();

    dashboard.addHotKey("control+shift+r", function(e) {
        e.preventDefault();
        location.reload();
    });

    dashboard.addHotKey("control+r", function(e) {
        e.preventDefault();
        dashboard.lazyReload();
        Hello.closeAll();
    });

    dashboard.addHotKey("control+h", function(e) {
        e.preventDefault();
        dashboard.lazyOpen("?void=home");
    });

    dashboard.addHotKey("control+l", function(e) {
        e.preventDefault();
        dashboard.toggleSidebar();
    });

    dashboard.addHotKey("control+shift+k", function(e) {
        e.preventDefault();
        dashboard.languagePopup(false)
    });

    let theme_once = false;
    dashboard.addHotKey("control+shift+m", function(e) {
        e.preventDefault();
        if(!theme_once) dashboard.switchTheme();
        theme_once = true;
    });
    dashboard.onKeyup("m,M", () => theme_once = false);

    dashboard.addLazyEvent("success", resp => {
        let _void = resp.data.void || "dashboard";

        // Set active menu
        $(".amd-menu-item").removeClass("active");
        $(`[data-menu-item="${_void}"]`).addClass("active");
        if(_void === "home" || _void === "dashboard")
            $(`[data-menu-item="dashboard"]`).addClass("active");

        // Set active navbar item
        $(".navbar-item").removeClass("active");
        $(`#nav-item-${_void}`).addClass("active");

        dashboard.awake();
        dashboard.initTooltips();
    });

    dashboard.addLazyEvent("start", () => dashboard.clearFloatingButtons());
    dashboard.addLazyEvent("reload", () => dashboard.clearFloatingButtons());

    dashboard.addAction("logout", (success, failed) => {
        let logout = () => {
            network.clean();
            network.put("logout", "");
            network.on.start = () => dashboard.suspend();
            network.on.end = (resp, error) => {
                if(!error) {
                    if(resp.success) {
                        success();
                        let url = resp.data.url || null;
                        if(url) location.href = url;
                        else location.reload();
                    }
                    else {
                        failed();
                        dashboard.resume();
                    }
                }
                else {
                    failed();
                    dashboard.resume();
                }
            }
            network.post();
        }
        $amd.alert(_t("logout"), _t("logout_confirmation"), {
            confirmButton: _t("yes"),
            cancelButton: _t("no"),
            onConfirm: () => logout(),
            onCancel: () => failed()
        });
    });
</script>
<?php do_action( "amd_after_dashboard_page" ); ?>
</body>
</html>