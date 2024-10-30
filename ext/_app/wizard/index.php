<?php

# Initialize hooks and repair database if required
amd_init_plugin();

global /** @var AMDLoader $amdLoader */
$amdLoader;

# Send error message if active theme is corrupted or missing
if( !$amdLoader->canLoadTheme() )
	amd_assert_error( "cannot_load_theme", sprintf( esc_html__( "There is no available theme for current page, your theme files may be removed or changed. Please check your theme in %ssettings%s to see what's wrong", "material-dashboard" ), "<a href=\"" . admin_url( "admin.php?page=amd-themes" ) . "\" target=\"_blank\">", "</a>" ) );

# Print steps content
function dump_step( $s ){
	$path = AMD_EXT__APP_WIZARD . "/steps/step_$s.php";
	if( file_exists( $path ) )
		require_once( $path );
}

# Localization
$current_locale = get_locale();
$direction = is_rtl() ? "rtl" : "ltr";
$theme = "light";

# Get dashboard logo
$dash = amd_get_dash_logo();
$dashLogoURL = $dash["url"];

global /** @var AMD_DB $amdDB */
$amdDB;

# Check if API page exists
$APIPage = intval( amd_get_site_option( "api_page" ) );
$_apiPost = get_post( $APIPage );
$API_PAGE_OK = !empty( $_apiPost ) ? $_apiPost->post_status == "publish" : null;

# Check if database is installed
$db_installed = $amdDB->isInstalled();

global /** @var AMDCache $amdCache */
$amdCache;

# Add body classes
amd_add_element_class( "body", [ $theme, $direction, $current_locale ] );
$bodyBG = apply_filters( "amd_dashboard_bg", "" );

?><!doctype html>
<html lang="<?php echo esc_attr( $current_locale ); ?>">
<head>
	<?php if( $db_installed AND $API_PAGE_OK ): ?>
		<?php do_action( "amd_dashboard_header" ); ?>
    <link rel="stylesheet" href="<?php echo esc_url( AMD_MOD . '/coloris/coloris.min.css' ); ?>">
        <script src="<?php echo esc_url( AMD_MOD . '/coloris/coloris.min.js' ); ?>"></script>
	<?php else: ?>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
		<?php amd_wp_head_alternate(); ?>
<!--    <link rel="stylesheet" href="--><?php //echo esc_url( AMD_CSS . '/fonts/fonts.css.php' ); ?><!--">-->
        <?php do_action( "amd_config_script" ); ?>
		<?php
            $amdCache->dumpStyles( "icon" );
            $amdCache->dumpStyles( "global" );
            $amdCache->dumpStyles( "admin" );
            $amdCache->dumpScript( "icon" );
            $amdCache->dumpScript( "admin" );
            $amdCache->dumpScript( "global" );
		?>
    <link rel="stylesheet" href="<?php echo esc_url( AMD_CSS . '/vars.css' ); ?>">
	<?php endif; ?>
    <!-- @formatter off -->
    <style>.__option_grid,.__option_grid>div{display:flex;flex-wrap:wrap;align-items:center;justify-content:start}.__option_grid>.-item{position:relative;flex:0 0 100%;padding:16px 0}.__option_grid>.-item:not(:last-child):after{content:' ';position:absolute;display:block;bottom:0;width:100%;height:0;border:1px dashed rgba(var(--amd-primary-rgb),.2)}.__option_grid>.-item>.-sub-item{flex:1}.__option_grid>.-item>.-sub-item.--full{flex:0 0 100%}.__option_grid>.-item>.-sub-item:nth-child(1){text-align:end}.__option_grid>.-item>.-sub-item:nth-child(2){text-align:start}.__option_grid>.-item{flex-direction:row-reverse;direction:ltr}.__option_grid>.-item>.-sub-item{text-align:start}.__option_grid>.-item{flex-direction:row-reverse;align-items:start;justify-content:start}.__option_grid>.-item>.-sub-item{margin:6px 0}.color-badge{position:relative;display:inline-block;bottom:-4px;width:14px;height:14px;border-radius:50%;}</style>
    <style>.amd-admin-alert p{font-size:15px !important}.amd-admin-alert{display:flex;flex-wrap:nowrap;align-items:center;justify-content:stretch;color:#fff;background:var(--amd-primary);padding:4px 16px;border-radius:8px;margin:8px 0;max-width:100%}.amd-admin-alert.size-sm{max-width:200px}.amd-admin-alert.size-md{max-width:300px}.amd-admin-alert.size-lg{max-width:500px}.amd-admin-alert.align-left{text-align:left}.amd-admin-alert.align-right{text-align:right}.amd-admin-alert.align-justify{text-align:justify}.amd-admin-alert p{margin:8px 0;flex:1}.amd-admin-alert a{text-decoration:underline !important;color:currentColor}.amd-admin-alert .bi,.amd-admin-alert .mi,.amd-admin-alert svg.iconhub{flex:0 0 30px;margin:8px;font-size:18px;display:inline-flex;width:auto;height:30px;aspect-ratio:1;align-items:center;justify-content:center;border-radius:8px;background:#fff;color:var(--amd-primary)}.amd-admin-alert svg.iconhub{box-sizing:border-box;padding:4px}.amd-admin-alert.info{background:var(--amd-color-blue)}.amd-admin-alert.info .bi{color:var(--amd-color-blue)}</style>
    <script>var network;if(typeof AMDNetwork !== "undefined")network = new AMDNetwork();network.setAction(amd_conf.ajax.private);const backToAdmin=()=>location.href = `<?php echo admin_url( "admin.php?page=material-dashboard&ref=wizard" ); ?>`;</script>
    <!-- @formatter on -->

    <link rel="icon" href="<?php echo amd_get_logo( 'default_64_round.png' ); ?>">
    <title><?php echo esc_html_x( "Wizard installation", "Admin", "material-dashboard" ); ?></title>
</head>
<body class="<?php echo esc_attr( amd_element_classes( "body" ) ); ?>" <?php echo !empty( $bodyBG ) ? 'style="' . "background-image:url('" . esc_attr( $bodyBG ) . "')" . '"' : ""; ?>>
<?php

# Setup API page if it doesn't exist
$API_OK = amd_api_page_required();

if( !$API_OK ){

    // Close HTML tags to prevent HTML from breaking
	echo "</body></html>";

    // Stop here and don't continue, the page reloads after setup completed
	return;

}

?>
<div id="screen" style="position:fixed;top:0;width:100vw;height:100vh;background:rgba(var(--amd-title-color-rgb),.9);z-index:999">
    <div class="lds-ellipsis _loader_" style="position:absolute;top:calc(50vh - 40px);left:calc(50vw - 4px);">
		<?php echo str_repeat( "<div></div>", 4 ); ?>
    </div>
</div>
<?php if( !$db_installed OR !$API_PAGE_OK ): ?>
    <!-- @formatter off -->
    <style>body{margin:0}.lds-ellipsis{display:inline-block;position:relative;width:80px;height:80px}.lds-ellipsis div{position:absolute;top:33px;width:13px;height:13px;border-radius:50%;background:var(--amd-wrapper-fg);animation-timing-function:cubic-bezier(0,1,1,0);}.lds-ellipsis div:nth-child(1){left:8px;animation:lds-ellipsis1 0.6s infinite}.lds-ellipsis div:nth-child(2){left:8px;animation:lds-ellipsis2 0.6s infinite}.lds-ellipsis div:nth-child(3){left:32px;animation:lds-ellipsis2 0.6s infinite;}.lds-ellipsis div:nth-child(4){left:56px;animation:lds-ellipsis3 0.6s infinite;}@keyframes lds-ellipsis1{0%{transform:scale(0)}100%{transform:scale(1)}}@keyframes lds-ellipsis3{0%{transform:scale(1)}100%{transform:scale(0)}}@keyframes lds-ellipsis2{0%{transform: translate(0,0)}100%{transform:translate(24px,0)}}</style>
    <!-- @formatter on -->
	<?php

    // Complete setup
    require_once( AMD_EXT__APP_WIZARD . "/initialize_script.php" );
	echo '</body>';
	exit();
?>
<?php endif; ?>
<div class="wizard-steps">
    <div class="--step fill" data-step-mark="1">
        <div class="-item -icon"><?php _amd_icon( "wizard" ); ?></div>
        <span class="-item -text"></span>
    </div>
    <div class="--step" data-step-mark="2">
        <div class="-item -icon"><?php _amd_icon( "page" ); ?></div>
        <span class="-item -text"></span>
    </div>
    <div class="--step" data-step-mark="3">
        <div class="-item -icon"><?php _amd_icon( "translate" ); ?></div>
        <span class="-item -text"></span>
    </div>
    <div class="--step" data-step-mark="4">
        <div class="-item -icon"><?php _amd_icon( "icon" ); ?></div>
        <span class="-item -text"></span>
    </div>
    <div class="--step" data-step-mark="5">
        <div class="-item -icon"><?php _amd_icon( "colors" ); ?></div>
        <span class="-item -text"></span>
    </div>
    <div class="--step" data-step-mark="6">
        <div class="-item -icon"><?php _amd_icon( "tools" ); ?></div>
        <span class="-item -text"></span>
    </div>
    <div class="--step --done" data-step-mark="end">
        <div class="-item -icon"><?php _amd_icon( "done" ); ?></div>
        <span class="-item -text"></span>
    </div>
</div>
<div class="wizard-card" id="wizard-card">
    <div data-step="1">
        <div class="--logo">
            <img src="<?php echo esc_url( amd_get_logo( 'svg_fit_default.svg' ) ); ?>" alt="">
        </div>
        <h1 class="--title"><?php echo esc_html_x( "Welcome to wizard installation!", "Admin", "material-dashboard" ); ?></h1>
        <p class="--content"><?php echo esc_html_x( "We'll guid you through each step to setup your dashboard in the easiest way", "Admin", "material-dashboard" ); ?></p>
    </div>
	<?php for( $i = 2; $i <= 6; $i++ ): ?>
        <div data-step="<?php echo $i; ?>"><?php dump_step( $i ); ?></div>
	<?php endfor; ?>
    <div data-step="end">
        <div class="--logo">
            <img src="<?php echo esc_url( amd_get_logo( 'svg_fit_default.svg' ) ); ?>" alt="">
        </div>
        <h1 class="--title"><?php echo esc_html_x( "Done", "Admin", "material-dashboard" ); ?></h1>
        <p><?php echo esc_html_x( "Your dashboard is ready now! you can find more options and styles in admin settings.", "Admin", "material-dashboard" ); ?></p>
    </div>
    <div class="h-10"></div>
    <div class="button-box">
        <button class="btn" id="btn-next"><?php echo esc_html_x( "Next", "Admin", "material-dashboard" ); ?></button>
        <button class="btn _back_to_admin_"
                id="btn-sub"><?php echo esc_html_x( "Back to dashboard", "Admin", "material-dashboard" ); ?></button>
        <button class="btn low btn-text" id="btn-skip"><?php echo esc_html_x( "Skip", "Admin", "material-dashboard" ); ?></button>
        <button class="btn btn-text" id="btn-prev"><?php echo esc_html_x( "Previous", "Admin", "material-dashboard" ); ?></button>
    </div>
</div>
<!-- @formatter off -->
<style>body{background-size:cover;background-repeat:no-repeat;min-height:100vh}.wizard-card .--card-progress{position:absolute;top:0;left:0;width:100%}.wizard-card{position:relative;background:var(--amd-wrapper-fg);width:calc(100% - 64px);margin:16px auto 8px;padding:24px;border-radius:16px;text-align:center;overflow:hidden}.--title{font-size:25px;margin:0 0 8px}.--content{font-size:16px;margin:0 0 8px}.--logo{display:flex;width:120px;margin:auto;aspect-ratio:1}.--logo>img{width:100%;height:100%;object-fit:contain}.wizard-steps,.wizard-steps>.--step,.wizard-steps>.--step>.-icon{display:flex;flex-wrap:wrap;align-items:center;justify-content:center}.wizard-steps{margin:16px;direction:rtl}.wizard-steps>.--step{flex-direction:column}.wizard-steps>.--step>.-item{text-align:center}.wizard-steps>.--step>.-icon{position:relative;background:rgba(var(--amd-primary-rgb),.2);color:var(--amd-primary);aspect-ratio:1;width:50px;height:auto;border-radius:50%;transition:background-color ease .2s,color ease .2s}@media(max-width:992px){.wizard-steps>.--step:not(.current){display:none}}.wizard-steps>.--step.fill>.-icon{background:var(--amd-primary);color:#fff}.wizard-steps>.--step.--done>.-icon{background:rgba(var(--amd-color-green-rgb),.2);color:var(--amd-color-green)}.wizard-steps>.--step.--done.fill>.-icon{background:var(--amd-color-green);color:var(--amd-wrapper-fg)}.wizard-steps>.--step>.-text{display:block;font-size:16px;padding:8px 0;color:rgba(var(--amd-text-color-rgb),.8);text-transform:lowercase}.button-box{width:90%;max-width:300px;margin:auto}.btn{height:40px}.btn.block{display:block;min-width:120px;border-radius:8px}.btn.block.center{margin:16px auto 0}.button-box>.btn{width:100%;height:47px;margin:4px 0;border-radius:8px;font-size:15px}.btn-text.low{background-color:rgba(var(--amd-primary-rgb),.2)}.wizard-select{display:inline-block;border:0;outline:0;background:rgba(var(--amd-primary-rgb),.2);color:var(--amd-text-color);padding:10px 16px;border-radius:8px;min-width:200px;height:46px;cursor:var(--amd-pointer)}.hr{display:block;width:100%;height:0;border-bottom:1px solid rgba(var(--amd-text-color-rgb),.3);margin:20px 0}@media(min-width:993px){.wizard-card{width:50vw;margin:32px auto 16px}.wizard-steps>.--step:not(:last-child)>.-icon{margin-inline-end:100px}.wizard-steps>.--step:not(:last-child)>.-icon:before{content:' ';display:block;position:absolute;top:23px;left:-93px;width:86px;height:4px;border-radius:4px;background:rgba(var(--amd-primary-rgb),.2)}}body.rtl p{direction:rtl}body.ltr p{direction:ltr}</style>
<!-- @formatter on -->
<script>
    var $screen = $("#screen");
    $(window).on("load", () => $screen.fadeOut());
    $(document).on("click", "._back_to_admin_", () => {
        $screen.fadeIn();
        backToAdmin();
    });
    function _step_1(done) {
        done(true);
    }
    (function() {

        var $card = $("#wizard-card");
        var $next = $("#btn-next"), $prev = $("#btn-prev"), $submit = $("#btn-sub"), $skip = $("#btn-skip");
        var step = 0, maxSteps = $("[data-step]").length, initialStep = 1;

        function markStep(s) {
            let $el = $(`[data-step-mark]`);
            $el.removeClass("fill current");
            if(s === "end") {
                let $e = $(`[data-step-mark="${s}"]`);
                if(s === "end") $el.addClass("fill");
                $e.addClass("current")
            }
            else if(typeof s === "number") {
                for(let i = 0; i <= s; i++) {
                    let $e = $(`[data-step-mark="${i}"]`);
                    $e.addClass("fill");
                    if(i === s) $e.addClass("current");
                }
            }
        }

        function handleStep(s, done) {
            let step_function = `_step_${s}`;
            let fn = window[step_function] || null;
            if(typeof fn === "function") {
                let d = fn(done);
                if(typeof d === "string") {
                    $amd.toast(d, {position: $amd.isMobile() ? "bm" : "mm", toastOffset: 50, timeout: 3000});
                }
            }
        }

        function setStep(s) {
            if(s === step) return;
            $(`[data-step]`).fadeOut(0);
            $(`[data-step="${s}"]`).fadeIn();
            markStep((s === "end" || s >= maxSteps) ? "end" : s);
            if(s >= maxSteps || s === "end") {
                $next.fadeOut(0);
                $submit.fadeIn(0);
                $prev.fadeOut(0);
                $skip.fadeOut(0);
            }
            else if(s === 1) {
                $next.fadeIn(0);
                $submit.fadeOut(0);
                $prev.fadeOut(0);
                $skip.fadeOut(0);
            }
            else if(s < maxSteps) {
                $next.fadeIn(0);
                $submit.fadeOut(0);
                if(s === 2) $prev.fadeOut(0);
                else $prev.fadeIn(0);
                $skip.fadeIn(0);
            }
        }

        setStep(initialStep);
        step = initialStep;
        $next.click(function() {
            $card.cardLoader();
            if(step === "end") step = maxSteps;
            handleStep(step, (r = true) => {
                $card.cardLoader(false);
                if(r) {
                    let s = step + 1;
                    if(s > maxSteps - 1) s = "end";
                    setStep(s);
                    step = s;
                    $next.blur();
                }
            });
        });
        $skip.click(function() {
            if(step === "end") step = maxSteps;
            let s = step + 1;
            if(s > maxSteps - 1) s = "end";
            setStep(s);
            step = s;
            $next.blur();
        });
        $prev.click(function() {
            if(step === "end") step = maxSteps;
            let s = step - 1;
            if(s < 1) s = 1;
            setStep(s);
            step = s;
            $prev.blur();
        });
    }());
</script>
</body>
</html>