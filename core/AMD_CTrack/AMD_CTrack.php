<?php

/** @var AMD_CTrack $amdCTrack */
$amdCTrack = null;

class AMD_CTrack {

    public function __construct(){

        add_action( "init", [$this, "init"] );
        add_action( "admin_init", [$this, "init"] );

    }

    /**
     * Initialize hooks
     * @return void
     * @since 1.1.0
     */
    private function init_hooks() {

        # Initialize tasks
        add_action( "amd_checkin", [$this, "check_in"] );

        /**
         * Run tasks
         * @since 1.1.0
         */
        add_filter( "amd_task_run", function( $executed, $task, $data ){

            if( !$executed AND $task instanceof AMDTasks_Object AND is_array( $data ) ){

                if( !apply_filters( "amd_cTRack_collect_on_localhost", false ) AND amd_is_localhost() )
                    return false;

                $action = $data["action"] ?? "";
                $cmd = $data["data"] ?? "";
                $scopes = $data["args"] ?? "";
                $success = false;
                if( $action == "cTrack" ){
                    global $amdCTrack;
                    $success = $amdCTrack->runCommand( sanitize_text_field( "$cmd/$scopes" ) );
                }
                return $success;

            }

            return $executed;

        }, 10, 3 );

        add_action( "amd_before_admin_head", function(){

            global $amdDB;

            if( !$amdDB->isInstalled() )
                return;

            $API_OK = amd_api_page_required();
            if( !$API_OK )
                return;

            $ctrack_allowed = amd_get_site_option( "ctrack_allowed", "unset" );
            $page = sanitize_text_field( $_GET["page"] ?? "" );

            if( $ctrack_allowed == "unset" AND $page != "amd-more" ){
                ?>
                <div class="_amd_tracker_screens_ amd-tracker-consent-overlay"></div>
                <div class="_amd_tracker_screens_ amd-tracker-consent text-center">
                    <h1 class="font-title color-primary"><?php echo esc_html_x( "Help us to improve our products", "Admin", "material-dashboard" ); ?></h1>
                    <p><?php echo esc_html_x( "By letting us to collect your non-sensitive data we can see how you are using this plugin, so we can improve our products' for better experience.", "Admin", "material-dashboard" ); ?> <?php echo esc_html_x( "You can always disable or enable this option in 'More' submenu.", "Admin", "material-dashboard" ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=amd-more#data-collect' ) ); ?>"><?php esc_html_e( "More info", "material-dashboard" ); ?></a>
                    <div class="h-20"></div>
                    <div class="--buttons">
                        <button type="button" class="amd-admin-button" data-change-ctrack="allow"><?php echo esc_html_x( "I allow", "Admin", "material-dashboard" ); ?></button>
                        <button type="button" class="amd-admin-button --primary --text" data-change-ctrack="deny"><?php echo esc_html_x( "Ignore this", "Admin", "material-dashboard" ); ?></button>
                    </div>
                </div>
                <!-- @formatter off -->
                <style>.amd-tracker-consent{position:fixed;top:100px;left:calc(50% - 300px);width:600px;height:max-content;background:var(--amd-wrapper-fg);border-radius:16px;padding:16px;z-index:10;opacity:0}.amd-tracker-consent.--loaded{opacity:1;transition:opacity ease .3s}.amd-tracker-consent-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0);z-index:9;transition:all ease .3s}.amd-tracker-consent-overlay.--loaded{backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);background:rgba(0,0,0,.3);transition:background-color ease .3s}.amd-tracker-consent .--buttons{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;flex-direction:column}.amd-tracker-consent .--buttons>*{width:100%;height:50px;margin:4px 0}</style>
                <!-- @formatter on -->
                <script>
                    $(window).on("load", function(){
                        $(".amd-tracker-consent, .amd-tracker-consent-overlay").addClass("--loaded");
                    });
                    jQuery($ => {
                        $("[data-change-ctrack]").click(function(){
                            let $el = $(this);
                            let $buttons = $("[data-change-ctrack]");
                            let n = new AMDNetwork();
                            let action = $el.attr("data-change-ctrack");
                            n.clean();
                            n.setAction(amd_conf.ajax.private);
                            n.put("save_options", {
                                ctrack_allowed: action
                            });
                            n.on.start = () => {
                                $buttons.setWaiting();
                                $el.waitHold(_t("wait_td"));
                            }
                            n.on.end = (resp, error) => {
                                $buttons.setWaiting(false);
                                $el.waitRelease();
                                if(!error){
                                    if(resp.success) $("._amd_tracker_screens_").removeSlow();
                                    else $amd.toast(resp.data.msg);
                                }
                                else{
                                    $amd.toast(_t("error"));
                                    console.log(resp.xhr);
                                }
                            }
                            n.post();
                        });
                    });
                </script>
                <?php
            }

        } );

        add_action( "amd_after_option_saved", function( $key, $value ){

            if( $key == "ctrack_allowed" ) {
                global $amdCTrack;
                $task = $amdCTrack->check_in();
                if( $value == "true" AND $task instanceof AMDTasks_Object )
                    $task->run( false );
            }

        }, 10, 2 );

    }

    public function init() {

        do_action( "amd_allowed_options", array(
            "ctrack_allowed" => [
                "type" => "STRING",
                "filter" => function( $v ){
                    if( $v == "allow" )
                        return "true";
                    else if( $v == "deny" )
                        return "false";
                    else if( in_array( $v, [ "unset", "true", "false" ] ) )
                        return $v;
                    return "unset";
                }
            ]
        ) );

    }

    /**
     * Initialize tasks
     * @return false|AMDTasks_Object
     * False on failure, {@see AMDTasks_Object} on success
     * @since 1.1.0
     */
    public function check_in(){

        $ctrack_allowed = amd_get_site_option( "ctrack_allowed", "unset" );

        global $amdTasks, $amdCache;

        $task_key = amd_encrypt_aes( "cTrack:collect", "cTrack" );

        if( $ctrack_allowed != "true" ) {
            $amdTasks->deleteTasksByKey( $task_key );
            return false;
        }

        $tasks = $amdTasks->getTasks( ["task_key" => $task_key], true, true );

        $collectionScopes = apply_filters( "amd_cTrack_collection_scopes", "*" );
        $collectionPeriod = apply_filters( "amd_cTrack_collection_period", $amdCache::STAMPS["day"] * 7 );

        if( empty( $tasks ) ){

            $id = $amdTasks->addTask( null, $task_key, esc_html_x( "cTrack data collect", "Admin", "material-dashboard" ), array(
                "action" => "cTrack",
                "data" => "collect",
                "args" => $collectionScopes,
            ), 1, $collectionPeriod );

            if( $id )
                return $amdTasks->getTaskByID( $id );

            return false;
        }

        $amdTasks->runQueuedTasks();

        return false;

    }

    public function collect( $scopes ) {

        if( !is_array( $scopes ) )
            return false;

        /**
         * Collect data with specific scopes
         * @since 1.1.0
         */
        do_action( "amd_cTrack_collect", $scopes );

        global $amdDB;

        $data = [];

        global $wp_version;

        $data["info"] = array(
            "date" => date( "Y-m-d H:i:s" ),
            "time" => time(),
            "is_premium" => function_exists( "adp_plugin" ),
            "premium_version" => function_exists( "adp_plugin" ) ? adp_plugin()["Version"] ?? "unknown" : null,
            "version" => amd_plugin()["Version"] ?? "unknown",
            "wp_version" => !empty( $wp_version ) ? $wp_version : "unknown",
            "php_version" => phpversion(),
            "from" => amd_replace_url( "%domain%" )
        );

        if( !empty( array_intersect( ["*", "site_options"], $scopes ) ) )
            $data["site_options"] = $amdDB->exportVariant( "site_options" );

        $data = apply_filters( "amd_cTRack_collection_data", $data, $scopes );

        $hash = base64_encode( wp_json_encode( $data ) );
        $time = time();

        $admin_email = sanitize_email( is_user_logged_in() ? amd_get_current_user()->email : "" );

        $result = wp_remote_post( AMD_CTRACK_COLLECT_URL, array(
            "body" => array(
                "data" => "application:material-dashboard;encryption:unknown;admin:$admin_email;time:$time;hash:$hash",
                "patch" => AMD_PATCH_HASH,
                "domain" => amd_replace_url( "%domain%" )
            ),
            "timeout" => 30000
        ) );

        if( !is_wp_error( $result ) ) {
            $code = $result["response"]["code"] ?? null;
            if( $code == 200 )
                return true;
        }

        return false;

    }

    public function runCommand( $command ) {

        $exp = explode( "/", $command );
        $cmd = $exp[0];
        $_scopes = strval( $exp[1] ?? "" );
        $scopes = explode( ",", $_scopes );

        if( $cmd == "collect" )
            return self::collect( $scopes );

        return false;

    }

    /**
     * Run core
     * @return void
     * @since 1.1.0
     */
    public function run() {

        self::init_hooks();

    }

}