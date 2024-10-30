<?php

/** @var AMDCore $amdCore */
$amdCore = null;

class AMDCore{

    /**
     * Themes data array
     * <br><b>More dashboard themes and templates are available in premium version</b>
     * @var array[]
     * @since 1.0.0
     */
    protected $themes;

    /**
     * Sign-in methods
     * @var array[]
     * @since 1.0.0
     */
    protected $signMethods;

    /**
     * Cleanup variants
     * @var array
     * @since 1.0.1
     */
    protected $cleanup_variants;

    /**
     * HBCore constructor
     */
    public function __construct(){

        $this->signMethods = [];

        $this->themes = [];

        $this->cleanup_variants = [];

        # Initialize shortcodes
        self::initShortcodes();

        # Initialize admin pages, menu, etc.
        self::initAdmin();

        # Initialize hooks
        self::initHooks();

        # Enqueue wp-media in wp-admin
        add_action( "admin_enqueue_scripts", function(){
            wp_enqueue_media();
        } );

    }

    /**
     * Init hooks & filters
     * @return void
     * @since 1.0.0
     */
    public function initHooks(){

        # Get default locale
        add_filter( "amd_get_default_locale", function(){
            return "fa_IR";
        } );

        # Set allowed settings options
        add_action( "amd_allowed_options", function( $data ){
            global /** @var AMDCache $amdCache */
            $amdCache;

            $amdCache->setScopeGroup( "allowed_options", $data );
        } );

        # Get allowed settings options
        add_filter( "amd_get_allowed_options", function(){
            global /** @var AMDCache $amdCache */
            $amdCache;

            return $amdCache->getScopeGroup( "allowed_options" );
        } );

        # Set admin settings item
        add_action( "amd_setting_tabs", function( $data ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            $amdCache->setScopeGroup( "setting_tabs", $data );

        } );

        # Set admin appearance item
        add_action( "amd_appearance_tabs", function( $data ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            $amdCache->setScopeGroup( "appearance_tabs", $data );

        } );

        # Add string for frontend translation
        add_action( "amd_add_front_string", function( $data ){

            global $amdCache;

            $amdCache->setScopeGroup( "front_strings", $data );

        } );

        # Check user role filters
        add_filter( "amd_user_filter_role", function( $role ){
            $u = amd_get_current_user();

            return $u ? $role == $u->role : false;
        } );

        # Get frontend translation strings
        add_filter( "amd_get_front_strings", function(){
            global /** @var AMDCache $amdCache */
            $amdCache;

            return $amdCache->getScopeGroup( "front_strings" );
        } );

        # Get admin settings item
        add_filter( "amd_get_setting_tabs", function(){
            global /** @var AMDCache $amdCache */
            $amdCache;
            $data = $amdCache->getScopeGroup( "setting_tabs", null, true );

            return $data;
        } );

        # Get admin appearance item
        add_filter( "amd_get_appearance_tabs", function(){
            global /** @var AMDCache $amdCache */
            $amdCache;
            $data = $amdCache->getScopeGroup( "appearance_tabs" );

            return $data;
        } );

        # Set allowed default pages
        add_action( "amd_allowed_pages", function( $data ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            $amdCache->setScopeGroup( "allowed_pages", $data );

        } );

        # Set export variants
        add_action( "amd_export_variant", function( $data ){

            global /** @var AMD_DB $amdCache */
            $amdDB;

            $amdDB->registerExportVariant( $data );

        } );

        # Set icon pack
        add_action( "amd_set_icon_pack", function( $pack_id ){

            global /** @var AMDIcon $amdIcon */
            $amdIcon;

            $amdIcon->setIconPack( $pack_id );

        } );

        # Add country code
        add_action( "amd_add_country_code", function( $id ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            $amdCache->setScopeGroup( "country_code", $id );

        } );

        # Get country codes
        add_filter( "amd_country_codes", function(){

            global /** @var AMDCache $amdCache */
            $amdCache;

            return $amdCache->getScopeGroup( "country_code" );

        } );

        # Get allowed default pages (Dashboard page, login page, API page, etc.)
        add_filter( "amd_get_allowed_pages", function(){

            global /** @var AMDCache $amdCache */
            $amdCache;

            return $amdCache->getScopeGroup( "allowed_pages" );

        } );

        # Get icon packs
        add_filter( "amd_get_icon_packs", function(){

            global $amdCache;

            return $amdCache->getScopeGroup( "icon_packs" );

        } );

        # Get default languages
        add_filter( "amd_locales", function( $locales ){

            $locales["fa_IR"] = array(
                "name" => json_decode( "\"\u0641\u0627\u0631\u0633\u06cc\"" ),
                "_name" => "Persian",
                "region" => json_decode( "\"\u0627\u06cc\u0631\u0627\u0646\"" ),
                "_region" => "Iran",
                "flag" => AMD_IMG . "/flags/iran-flag.png"
            );

            $locales["en_US"] = array(
                "name" => "English",
                "_name" => "English",
                "region" => "United states",
                "_region" => "United states",
                "flag" => AMD_IMG . "/flags/usa-flag.png"
            );

            return $locales;

        } );

        # Register new theme
        add_action( "amd_register_theme", function( $id, $name, $scope, $data, $overwrite = false ){

            global /** @var AMDCore $amdCore */
            $amdCore;

            $amdCore->registerTheme( $id, $name, $scope, $data, $overwrite );

        }, 10, 5 );

        # Register sign-in method
        add_action( "amd_register_sign_in_method", function( $id, $config ){

            global /** @var AMDCore $amdCore */
            $amdCore;

            $amdCore->registerSignInMethod( $id, $config );

        }, 10, 2 );

        /**
         * Remove sign-in method
         * @since 1.0.3
         */
        add_action( "amd_remove_sign_in_method", function( $id ){

            global /** @var AMDCore $amdCore */
            $amdCore;

            $amdCore->removeSignInMethod( $id );

        } );

        # Get sign-in methods
        add_filter( "amd_sign_in_methods", function(){

            global /** @var AMDCore $amdCore */
            $amdCore;

            return $amdCore->getSignInMethods();

        } );

        # Register mime type
        add_filter( "amd_mime_types", function(){

            return array(
                "video/3gpp2" => "3g2",
                "video/3gp" => "3gp",
                "video/3gpp" => "3gp",
                "application/x-compressed" => "7zip",
                "audio/x-acc" => "aac",
                "audio/ac3" => "ac3",
                "application/postscript" => "ai",
                "audio/x-aiff" => "aif",
                "audio/aiff" => "aif",
                "audio/x-au" => "au",
                "video/x-msvideo" => "avi",
                "video/msvideo" => "avi",
                "video/avi" => "avi",
                "application/x-troff-msvideo" => "avi",
                "application/macbinary" => "bin",
                "application/mac-binary" => "bin",
                "application/x-binary" => "bin",
                "application/x-macbinary" => "bin",
                "image/bmp" => "bmp",
                "image/x-bmp" => "bmp",
                "image/x-bitmap" => "bmp",
                "image/x-xbitmap" => "bmp",
                "image/x-win-bitmap" => "bmp",
                "image/x-windows-bmp" => "bmp",
                "image/ms-bmp" => "bmp",
                "image/x-ms-bmp" => "bmp",
                "application/bmp" => "bmp",
                "application/x-bmp" => "bmp",
                "application/x-win-bitmap" => "bmp",
                "application/cdr" => "cdr",
                "application/coreldraw" => "cdr",
                "application/x-cdr" => "cdr",
                "application/x-coreldraw" => "cdr",
                "image/cdr" => "cdr",
                "image/x-cdr" => "cdr",
                "zz-application/zz-winassoc-cdr" => "cdr",
                "application/mac-compactpro" => "cpt",
                "application/pkix-crl" => "crl",
                "application/pkcs-crl" => "crl",
                "application/x-x509-ca-cert" => "crt",
                "application/pkix-cert" => "crt",
                "text/css" => "css",
                "text/x-comma-separated-values" => "csv",
                "text/comma-separated-values" => "csv",
                "application/vnd.msexcel" => "csv",
                "application/x-director" => "dcr",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
                "application/x-dvi" => "dvi",
                "message/rfc822" => "eml",
                "application/x-msdownload" => "exe",
                "video/x-f4v" => "f4v",
                "audio/x-flac" => "flac",
                "video/x-flv" => "flv",
                "image/gif" => "gif",
                "application/gpg-keys" => "gpg",
                "application/x-gtar" => "gtar",
                "application/x-gzip" => "gzip",
                "application/mac-binhex40" => "hqx",
                "application/mac-binhex" => "hqx",
                "application/x-binhex40" => "hqx",
                "application/x-mac-binhex40" => "hqx",
                "text/html" => "html",
                "image/x-icon" => "ico",
                "image/x-ico" => "ico",
                "image/vnd.microsoft.icon" => "ico",
                "text/calendar" => "ics",
                "application/java-archive" => "jar",
                "application/x-java-application" => "jar",
                "application/x-jar" => "jar",
                "image/jp2" => "jp2",
                "video/mj2" => "jp2",
                "image/jpx" => "jp2",
                "image/jpm" => "jp2",
                "image/jpeg" => "jpeg",
                "application/x-javascript" => "js",
                "application/json" => "json",
                "text/json" => "json",
                "application/vnd.google-earth.kml+xml" => "kml",
                "application/vnd.google-earth.kmz" => "kmz",
                "text/x-log" => "log",
                "audio/x-m4a" => "m4a",
                "audio/mp4" => "m4a",
                "application/vnd.mpegurl" => "m4u",
                "audio/midi" => "mid",
                "application/vnd.mif" => "mif",
                "video/quicktime" => "mov",
                "video/x-sgi-movie" => "movie",
                "audio/mpeg" => "mp3",
                "audio/mpg" => "mp3",
                "audio/mpeg3" => "mp3",
                "audio/mp3" => "mp3",
                "video/mp4" => "mp4",
                "video/mpeg" => "mpeg",
                "application/oda" => "oda",
                "audio/ogg" => "ogg",
                "video/ogg" => "ogg",
                "application/ogg" => "ogg",
                "font/otf" => "otf",
                "application/x-pkcs10" => "p10",
                "application/pkcs10" => "p10",
                "application/x-pkcs12" => "p12",
                "application/x-pkcs7-signature" => "p7a",
                "application/pkcs7-mime" => "p7c",
                "application/x-pkcs7-mime" => "p7c",
                "application/x-pkcs7-certreqresp" => "p7r",
                "application/pkcs7-signature" => "p7s",
                "application/pdf" => "pdf",
                "application/octet-stream" => "pdf",
                "application/x-x509-user-cert" => "pem",
                "application/x-pem-file" => "pem",
                "application/pgp" => "pgp",
                "application/x-httpd-php" => "php",
                "application/php" => "php",
                "application/x-php" => "php",
                "text/php" => "php",
                "text/x-php" => "php",
                "application/x-httpd-php-source" => "php",
                "image/png" => "png",
                "image/x-png" => "png",
                "application/powerpoint" => "ppt",
                "application/vnd.ms-powerpoint" => "ppt",
                "application/vnd.ms-office" => "ppt",
                "application/msword" => "doc",
                "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx",
                "application/x-photoshop" => "psd",
                "image/vnd.adobe.photoshop" => "psd",
                "audio/x-realaudio" => "ra",
                "audio/x-pn-realaudio" => "ram",
                "application/x-rar" => "rar",
                "application/rar" => "rar",
                "application/x-rar-compressed" => "rar",
                "audio/x-pn-realaudio-plugin" => "rpm",
                "application/x-pkcs7" => "rsa",
                "text/rtf" => "rtf",
                "text/richtext" => "rtx",
                "video/vnd.rn-realvideo" => "rv",
                "application/x-stuffit" => "sit",
                "application/smil" => "smil",
                "text/srt" => "srt",
                "image/svg+xml" => "svg",
                "application/x-shockwave-flash" => "swf",
                "application/x-tar" => "tar",
                "application/x-gzip-compressed" => "tgz",
                "image/tiff" => "tiff",
                "font/ttf" => "ttf",
                "text/plain" => "txt",
                "text/x-vcard" => "vcf",
                "application/videolan" => "vlc",
                "text/vtt" => "vtt",
                "audio/x-wav" => "wav",
                "audio/wave" => "wav",
                "audio/wav" => "wav",
                "application/wbxml" => "wbxml",
                "video/webm" => "webm",
                "image/webp" => "webp",
                "audio/x-ms-wma" => "wma",
                "application/wmlc" => "wmlc",
                "video/x-ms-wmv" => "wmv",
                "video/x-ms-asf" => "wmv",
                "font/woff" => "woff",
                "font/woff2" => "woff2",
                "application/xhtml+xml" => "xhtml",
                "application/excel" => "xl",
                "application/msexcel" => "xls",
                "application/x-msexcel" => "xls",
                "application/x-ms-excel" => "xls",
                "application/x-excel" => "xls",
                "application/x-dos_ms_excel" => "xls",
                "application/xls" => "xls",
                "application/x-xls" => "xls",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",
                "application/vnd.ms-excel" => "xlsx",
                "application/xml" => "xml",
                "text/xml" => "xml",
                "text/xsl" => "xsl",
                "application/xspf+xml" => "xspf",
                "application/x-compress" => "z",
                "application/x-zip" => "zip",
                "application/zip" => "zip",
                "application/x-zip-compressed" => "zip",
                "application/s-compressed" => "zip",
                "multipart/x-zip" => "zip",
                "text/x-scriptzsh" => "zsh",
            );

        } );

        # Initialize
        add_action( "init", [ $this, "initRegisteredHooks" ] );
        add_action( "admin_init", [ $this, "initRegisteredHooks" ] );
        # add_action( "amd_dashboard_init", [ $this, "initRegisteredHooks" ] );

        # Enqueue color picker scripts
        add_action( "admin_enqueue_scripts", function(){
            wp_enqueue_style( "wp-color-picker" );
            wp_enqueue_script( "wp-color-picker" );
        } );

        # Config script
        add_action( "amd_config_script", function(){
            ?>
            <script src="<?php echo esc_url( amd_get_api_url( "?scripts=_config&cache=" . time() ) ); ?>"></script>
            <?php
        } );

        # Admin pages header
        add_action( "amd_admin_header", function(){
            ?>
            <script>
                $(document).on("click", "[data-for]", function() {
                    let f = $(this).attr("data-for");
                    if(f) $("#" + f).click();
                })
                $("body").addClass(`<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>`);
                $(document).on("click", "[data-media]", function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    let eid = $btn.attr("data-media");
                    let link = $btn.attr("data-link");
                    let isMultiple = $btn.hasAttr("data-multiple", true, "false") === "true";
                    let libType = $btn.hasAttr("data-library", true, "");
                    var $input = $("#" + eid);
                    var $image = $("#" + link);

                    var image_frame;

                    if(image_frame) image_frame.open();

                    image_frame = wp.media({
                        title: _t("select_file"),
                        multiple: isMultiple,
                        library: libType ? {type: libType} : {}
                    });

                    image_frame.on("select", function() {
                        var selection = image_frame.state().get("selection");
                        var gallery_ids = [];
                        var my_index = 0;
                        selection.each(function(attachment) {
                            gallery_ids[my_index] = attachment["id"];
                            my_index++;
                        });
                        var ids = gallery_ids.join(",");
                        $input.val(ids);
                        $input.trigger("change");
                        if(!isMultiple) {
                            var lastHtml = $btn.html();
                            $btn.html(_t("wait_td"));
                            $btn.setWaiting();
                            $btn.blur();
                            $image.setWaiting();
                            amd_refresh_image(
                                ids,
                                function(url) {
                                    $btn.html(lastHtml);
                                    $btn.setWaiting(false);
                                    $image.setWaiting(false);
                                    $image.attr("src", url);
                                },
                                function(error) {
                                    $btn.html(lastHtml);
                                    $btn.setWaiting(false);
                                    console.log(error)
                                }
                            );
                        }
                    });

                    image_frame.on("open", function() {
                        var selection = image_frame.state().get("selection");
                        var ids = $input.val().split(",");
                        ids.forEach(function(id) {
                            var attachment = wp.media.attachment(id);
                            attachment.fetch();
                            selection.add(attachment ? [attachment] : []);
                        });

                    });

                    image_frame.open();

                });

                function amd_refresh_image(the_id, onSuccess, onFailed) {
                    send_ajax({
                            action: amd_conf.ajax.private,
                            get_atc_url: the_id
                        },
                        resp => {
                            if(resp.success) onSuccess(resp.data.url)
                            else onFailed(resp.data);
                        },
                        (xhr, options, error) => {
                            onFailed(error);
                        });
                }
            </script><?php
        } );

        # Add custom class for dashboard elements
        add_action( "amd_add_element_class", function( $element, $classes ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            if( is_string( $classes ) )
                $classes = explode( ",", $classes );

            $_classes = $amdCache->cacheExists( "element_class_$element", true, [] );

            $_classes = array_unique( array_merge( $_classes, $classes ) );

            $amdCache->setCache( "element_class_$element", $_classes );

        }, 10, 2 );

        # Remove registered dashboard elements custom class
        add_action( "amd_remove_element_class", function( $element, $classes ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            if( is_string( $classes ) )
                $classes = explode( ",", $classes );

            $_classes = $amdCache->cacheExists( "element_class_$element", true, [] );

            $_classes = array_diff( $_classes, $classes );

            $amdCache->setCache( "element_class_$element", $_classes );

        }, 10, 2 );

        # Get registered custom class for dashboard elements
        add_filter( "amd_get_element_class", function( $element ){

            global /** @var AMDCache $amdCache */
            $amdCache;

            return $amdCache->cacheExists( "element_class_$element", true, [] );

        } );

        /**
         * Register cleanup variants
         * @since 1.0.1
         */
        add_action( "amd_register_cleanup_variant", [$this, "registerCleanupVariant"], 10, 2 );

        /**
         * Handle login for login reports
         * @since 1.0.5
         */
        add_action( "amd_login", function( $wp_user ){

            $uid = $wp_user->ID ?? 0;

            if( !$uid )
                return;

            global $amdDB;

            $user = amd_get_user_by( "ID", $uid );

            if( $amdDB AND $user ){

                if( $amdDB ){

                    global $amdWall, $amdCache;

                    $agent = $amdWall->parseAgent();

                    $report = $amdDB->addReport( "login", "null", $user->ID, ["ip" => $amdWall->getActualIP(), "identity" => $agent->export()] );

                    if( $report )
                        $amdCache->setCookie( "login_pending", $report, 1 );

                }

            }

        } );

        /**
         * Add cookie for login reports handler
         * @since 1.0.5
         */
        add_action( "amd_after_cores_init", function(){

            global $amdCache, $amdDB;

            $login_report = $amdCache->getCache( "login_pending", "cookie" );

            if( $login_report ){

                $report = $amdDB->getReport( $login_report );
                $thisuser = amd_get_current_user();
                if( $report AND $thisuser ){
                    $token = wp_get_session_token();
                    $token = amd_encrypt_aes( $token, $thisuser->secretKey );
                    $amdDB->editReport( $login_report, ["report_value" => $token] );
                }

                $amdCache->removeCookie( "login_pending" );

            }

        } );

        /**
         * Add login card
         * @since 1.0.5
         */
        add_action( "amd_dashboard_init", function(){

            $display_login_reports = amd_get_site_option( "display_login_reports", "true" );

            if( $display_login_reports == "true" ){
                do_action( "amd_add_dashboard_card", array(
                    "login_reports" => array(
                        "type" => "content_card",
                        "page" => AMD_DASHBOARD . "/cards/acc_login_reports.php",
                        "priority" => 20
                    )
                ) );
            }

        } );

    }

    /**
     * Initialize registered hooks & filters
     * @return void
     * @since 1.0.0
     */
    public function initRegisteredHooks(){

        # Set allowed options
        do_action( "amd_allowed_options", array(
            "optimizer" => "BOOL",
            "force_ssl" => "BOOL",
            "avatar_upload_allowed" => "BOOL",
            "dash_logo" => "STRING",
            "dashboard_page" => "NUMBER",
            "login_page" => "NUMBER",
            "api_page" => "NUMBER",
            "primary_color" => [
                "type" => "STRING",
                "filter" => function( $v ){
                    if( in_array( $v, [ "red", "green", "blue", "orange" ] ) OR amd__validate_color_code( $v ) )
                        return $v;

                    return "purple";
                }
            ],
            "menu_indicator" => [
                "type" => "STRING",
                "filter" => function( $v ){
                    if( in_array( $v, [ "no-indicator", "indicator-1", "indicator-2", "indicator-3" ] ) )
                        return $v;

                    return "indicator-1";
                }
            ],
            "icon_pack" => "STRING",
            "dashboard_tooltip" => [
                "type" => "STRING",
                "filter" => function( $v ){
                    if( in_array( $v, [ "dashboard_default", "system_default", "disabled" ] ) )
                        return $v;

                    return "dashboard_default";
                }
            ],
            "indexing" => [
                "type" => "STRING",
                "filter" => function( $v ){
                    if( in_array( $v, [ "no-index" ] ) )
                        return $v;

                    return "index";
                }
            ],
            "locales" => "STRING",
            "translate_show_regions" => "BOOL",
            "translate_show_flags" => "BOOL",
            "country_codes" => "STRING",
            "login_after_registration" => "BOOL",
            "lastname_field" => "BOOL",
            "username_field" => "BOOL",
            "password_conf_field" => "BOOL",
            "phone_field" => "BOOL",
            "phone_field_required" => "BOOL",
            "single_phone" => "BOOL",
            "login_page_title" => "STRING",
            "register_page_title" => "STRING",
            "reset_password_page_title" => "STRING",
            "api_enabled" => "BOOL",
            "hide_login" => "BOOL",
            "hide_users_rest_api" => "BOOL",
            "auto_generated_pages" => "BOOL",
            "wizard_dismissed" => "BOOL",
            "login_attempts" => "NUMBER",
            "login_attempts_time" => "NUMBER",
            "change_email_allowed" => "BOOL",
            "use_lazy_loading" => "BOOL",
            "extensions" => "STRING",
            "theme" => "STRING",
            "db_version" => "NUMBER",
            "use_login_2fa" => "BOOL",
            "force_login_2fa" => "BOOL",
            "display_login_reports" => "BOOL",
            "tera_wallet_support" => "BOOL",
            "search_hint_approved" => "BOOL"
        ) );

        # Set default export variants
        do_action( "amd_export_variant", array(
            "site_options" => array(
                "title" => esc_html_x( "Site options", "Admin", "material-dashboard" ),
                "export_type" => "json",
                "export" => function(){
                    global /** @var AMD_DB $amdCore */
                    $amdDB;

                    return $amdDB->exportSiteOptions();
                },
                "import" => function( $data ){

                    $progress = [];
                    foreach( $data as $option_name => $option_value ){
                        amd_set_site_option( $option_name, $option_value );
                        $progress[$option_name] = true;
                    }
                    return [true, esc_html_x( "Site settings imported", "Admin", "material-dashboard" ), $progress, 0];

                }
            ),
            "users_meta" => array(
                "title" => esc_html_x( "Users meta", "Admin", "material-dashboard" ),
                "export_type" => "json",
                "export" => function(){
                    global /** @var AMD_DB $amdCore */
                    $amdDB;

                    return $amdDB->exportUsersMeta();
                },
                "import" => function( $data, $overwrite ){

                    $progress = [];
                    $missed = 0;
                    foreach( $data as $uid => $user_meta ){

                        $user = get_user_by( "ID", $uid );

                        if( !$user ){
                            $missed++;
                            continue;
                        }

                        foreach( $user_meta as $meta_name => $meta_value ){

                            if( $overwrite OR !amd_user_has_meta( $uid, $meta_name ) )
                                amd_set_user_meta( $uid, $meta_name, $meta_value );

                        }

                        $progress[$uid] = true;

                    }
                    return [true, esc_html_x( "Users meta-data imported", "Admin", "material-dashboard" ), $progress, $missed];

                }
            ),
            "temp" => array(
                "title" => esc_html_x( "Temporarily data", "Admin", "material-dashboard" ) . amd_doc_url( "temp_data", true ),
                "export_type" => "json",
                "export" => function(){
                    global /** @var AMD_DB $amdCore */
                    $amdDB;

                    return $amdDB->exportTempData();
                },
                "import" => function( $data, $overwrite ){

                    $progress = [];
                    $missed = 0;
                    foreach( $data as $temp_key => $temp_data ){

                        $temp_value = $temp_data->value ?? null;
                        $expire = $temp_data->expire ?? null;

                        if( $expire == null OR $expire <= time() OR $temp_value == null ){
                            $missed++;
                            continue;
                        }

                        if( $overwrite OR !amd_temp_exists( $temp_key ) )
                            amd_set_temp( $temp_key, $temp_value, $expire-time() );

                        $progress[$temp_key] = true;

                    }
                    return [true, esc_html_x( "Temporarily data imported", "Admin", "material-dashboard" ), $progress, $missed];

                }
            ),
            "users_avatar" => array(
                "title" => esc_html_x( "Users avatar", "Admin", "material-dashboard" ),
                "requirements" => [ "ZipArchive extension" => "class:ZipArchive" ],
                "export_type" => "zip",
                "export" => function(){
                    global /** @var AMD_DB $amdCore */
                    $amdDB;

                    return $amdDB->exportAvatars();
                },
                "backup_pattern" => "/^backup_avatars_(.*).zip$/",
                "import" => function( $zip_file ){

                    $fail_msg = esc_html_x( "Cannot import users avatars", "Admin", "material-dashboard" );

                    if( !class_exists( "ZipArchive" ) )
                        return [false, $fail_msg];

                    $zip = new ZipArchive();
                    $dir = AMD_UPLOAD_PATH . "/temp";
                    $target_dir = "$dir/contents/users_avatar";

                    # Create missing directories
                    if( !file_exists( "$dir/contents" ) )
                        mkdir( "$dir/contents" );
                    if( !file_exists( "$dir/contents/users_avatar" ) )
                        mkdir( "$dir/contents/users_avatar" );

                    # Open zip archive
                    if( $zip->open( $zip_file, ZipArchive::CREATE ) !== true )
                        return [false, $fail_msg];

                    # Extract zip archive to 'contents' directory
                    $zip->extractTo( $target_dir );

                    $avatars_dir = amd_get_avatars_path();
                    $f = copy_dir( "$target_dir/avatars", $avatars_dir );

                    if( $f AND !is_wp_error( $f ) )
                        return [true, esc_html_x( "Users avatars imported successfully", "Admin", "material-dashboard" )];

                    return [false, $fail_msg];

                }
            )
        ) );

        # Set admin settings item
        do_action( "amd_setting_tabs", array(
            "general" => array(
                "id" => "general",
                "title" => esc_html_x( "General", "Admin", "material-dashboard" ),
                "icon" => "tools",
                "page" => AMD_PAGES . "/admin-tabs/tab_general.php",
                "priority" => 10
            ),
            "pages" => array(
                "id" => "pages",
                "title" => esc_html_x( "Pages", "Admin", "material-dashboard" ),
                "icon" => "page",
                "page" => AMD_PAGES . "/admin-tabs/tab_pages.php",
                "priority" => 20
            ),
            "languages" => array(
                "id" => "languages",
                "title" => esc_html_x( "Languages", "Admin", "material-dashboard" ),
                "icon" => "translate",
                "page" => AMD_PAGES . "/admin-tabs/tab_translate.php",
                "priority" => 30
            ),
            "registration" => array(
                "id" => "registration",
                "title" => esc_html_x( "Registration", "Admin", "material-dashboard" ),
                "icon" => "add_user",
                "page" => AMD_PAGES . "/admin-tabs/tab_registration.php",
                "priority" => 40
            ),
            "login" => array(
                "id" => "login",
                "title" => esc_html__( "Login", "material-dashboard" ),
                "icon" => "person",
                "page" => AMD_PAGES . "/admin-tabs/tab_login.php",
                "priority" => 40
            ),
            "forms" => array(
                "id" => "forms",
                "title" => esc_html_x( "Forms", "Admin", "material-dashboard" ),
                "icon" => "form",
                "page" => AMD_PAGES . "/admin-tabs/tab_forms.php",
                "priority" => 50
            ),
            "api" => array(
                "id" => "api",
                "title" => esc_html_x( "API", "Admin", "material-dashboard" ),
                "icon" => "api",
                "page" => AMD_PAGES . "/admin-tabs/tab_api.php",
                "priority" => 60
            ),
            "database" => array(
                "id" => "database",
                "title" => esc_html_x( "Database", "Admin", "material-dashboard" ),
                "icon" => "database",
                "page" => AMD_PAGES . "/admin-tabs/tab_database.php",
                "priority" => 70
            )
        ) );

        # Set admin settings item
        do_action( "amd_appearance_tabs", array(
            "colors" => array(
                "id" => "colors",
                "title" => esc_html_x( "Colors", "Admin", "material-dashboard" ),
                "icon" => "colors",
                "page" => AMD_PAGES . "/admin-tabs/tab_ap_colors.php",
            ),
            "icon-pack" => array(
                "id" => "icon-pack",
                "title" => esc_html_x( "Icons", "Admin", "material-dashboard" ),
                "icon" => "icon",
                "page" => AMD_PAGES . "/admin-tabs/tab_ap_icons.php",
            ),
            "dashboard" => array(
                "id" => "dashboard",
                "title" => esc_html_x( "Dashboard", "Admin", "material-dashboard" ),
                "icon" => "dashboard",
                "page" => AMD_PAGES . "/admin-tabs/tab_ap_dashboard.php",
            ),
        ) );

        # Set allowed default pages
        do_action( "amd_allowed_pages", [ "dashboard", "login", "api" ] );

        # Register cleanup variants (database)
        do_action( "amd_register_cleanup_variant", "database", array(
            "id" => "database",
            "title" => _x( "Database", "Admin", "material-dashboard" ),
            "auto_generates" => true,
            "callback" => function(){
                global /** @var AMD_DB $amdDB */
                $amdDB;

                $amdDB->cleanup();

                return [_x( "Database cleaned up", "Admin", "material-dashboard" )];
            }
        ) );

        # Register cleanup variants (files)
        do_action( "amd_register_cleanup_variant", "files", array(
            "id" => "files",
            "title" => _x( "Files", "Admin", "material-dashboard" ),
            "auto_generates" => false,
            "callback" => function(){
                global /** @var AMDExplorer $amdExp */
                $amdExp;

                # Get plugin uploads path
                $path = $amdExp->getPath( "" );

                # Delete the whole plugin uploads directory
                amd_delete_directory( $path, true );

                # Remove plugin uploads directory itself (double-checking)
                rmdir( $path );

                return [_x( "Files cleaned up", "Admin", "material-dashboard" )];
            }
        ) );

        /**
         * Initialize cleanup variants
         * @since 1.0.1
         */
        do_action( "amd_init_cleanup_variants" );

        # Set icon pack
        $icon_pack = amd_get_site_option( "icon_pack", amd_get_default( "icon_pack" ) );
        do_action( "amd_set_icon_pack", $icon_pack );

        # Set default options for database repairing
        global /** @var AMDCache $amdCache */
        $amdCache;

        $defaultCountryCode = amd_get_default( "country_code" );
        $data = array(
            "optimizer" => "true",
            "force_ssl" => "false",
            "avatar_upload_allowed" => "false",
            "dash_logo" => amd_get_default( "dash_logo" ),
            "indexing" => "no-index",
            "icon_pack" => amd_get_default( "icon_pack", "material-icons" ),
            "tooltip" => "dashboard_default",
            "primary_color" => "purple",
            "locales" => "",
            "translate_show_regions" => "false",
            "translate_show_flags" => "false",
            "login_after_registration" => "true",
            "lastname_field" => "false",
            "username_field" => "false",
            "password_conf_field" => "false",
            "country_codes" => json_encode( $defaultCountryCode ),
            "phone_field" => "false",
            "phone_field_required" => "false",
            "single_phone" => "true",
            "login_page_title" => "",
            "register_page_title" => "",
            "reset_password_page_title" => "",
            "api_enabled" => "false",
            "hide_login" => "false",
            "hide_users_rest_api" => "true",
            "auto_generated_pages" => "false",
            "wizard_dismissed" => "false",
            "login_attempts" => "0",
            "login_attempts_time" => "0",
            "change_email_allowed" => "false",
            "use_lazy_loading" => "true",
            "extensions" => "",
            "theme" => AMD_DEFAULT_THEME,
            "db_version" => "1",
            "use_login_2fa" => "false",
            "force_login_2fa" => "false",
            "display_login_reports" => "true",
            "tera_wallet_support" => "true",
            "search_hint_approved" => "false"
        );

        $amdCache->setDefault( "options_data", json_encode( $data ) );

    }

    /**
     * Init shortcodes
     * @return void
     * @since 1.0.0
     */
    public function initShortcodes(){

        # Dashboard page
        add_shortcode( "amd-dashboard", function(){

            if( is_page() ){
                $thisPage = get_post();
                if( !empty( $thisPage ) ){
                    $dashboardPage = intval( amd_get_site_option( "dashboard_page" ) );
                    if( $dashboardPage == $thisPage->ID ){
                        amd_dump_dashboard();
                    }
                    else{
                        if( amd_is_admin() ){
                            $adminSettings = esc_url( admin_url() . "/admin.php?page=amd-settings" );
                            wp_die( sprintf( esc_html_x( "You are using dashboard shortcodes without applying template, please set %s in %sdashboard settings%s", "Admin", "material-dashboard" ), esc_html_x( "dashboard page", "Admin", "material-dashboard" ), "<a href=\"$adminSettings\" target=\"_blank\">", "</a>" ) . " (" . sprintf( esc_html__( "Error %s", "material-dashboard" ), amd_get_error_code( "template_error" ) ) . ")" );
                        }
                        else{
                            wp_die( esc_html_x( "An error has occurred, login as admin to see error details", "Admin", "material-dashboard" ) );
                        }
                    }
                }
            }

        } );

        # Login page
        add_shortcode( "amd-login", function(){

            if( is_page() ){
                $thisPage = get_post();
                if( !empty( $thisPage ) ){
                    $loginPage = intval( amd_get_site_option( "login_page" ) );
                    if( $loginPage == $thisPage->ID OR is_user_logged_in() ){
                        amd_dump_login();
                    }
                    else{
                        $adminSettings = esc_url( admin_url() . "/admin.php?page=amd-settings" );
                        wp_die( sprintf( esc_html_x( "You are using dashboard shortcodes without applying template, please set %s in %sdashboard settings%s", "Admin", "material-dashboard" ), esc_html_x( "login page", "Admin", "material-dashboard" ), "<a href=\"$adminSettings\" target=\"_blank\">", "</a>" ) . " (" . sprintf( esc_html__( "Error %s", "material-dashboard" ), amd_get_error_code( "template_error" ) ) . ")" );
                    }
                }
            }

        } );

        # API page
        add_shortcode( "amd-api", function(){

            if( is_page() ){
                $thisPage = get_post();
                if( !empty( $thisPage ) ){
                    $APIPage = intval( amd_get_site_option( "api_page" ) );
                    if( $APIPage == $thisPage->ID OR is_user_logged_in() ){
                        amd_load_api();
                    }
                    else{
                        $adminSettings = esc_url( admin_url() . "/admin.php?page=amd-settings" );
                        wp_die( sprintf( esc_html_x( "You are using dashboard shortcodes without applying template, please set %s in %sdashboard settings%s", "Admin", "material-dashboard" ), esc_html_x( "API page", "Admin", "material-dashboard" ), "<a href=\"$adminSettings\" target=\"_blank\">", "</a>" ) . " (" . sprintf( esc_html__( "Error %s", "material-dashboard" ), amd_get_error_code( "template_error" ) ) . ")" );
                    }
                }
            }

        } );

        /**
         * Display icons for shortcodes
         * @since 1.0.5
         */
        add_shortcode( "amd-icon", function( $attr ){

            $attrs = shortcode_atts( array(
                "icon" => ""
            ), $attr, 'amd-icon' );

            if( !empty( $attrs["icon"] ) )
                _amd_icon( $attr["icon"] );

        } );

        # Change dashboard pages template
        add_action( "template_include", function( $template ){
            $loginPage = intval( amd_get_site_option( "login_page" ) );
            $dashboardPage = intval( amd_get_site_option( "dashboard_page" ) );
            $apiPage = intval( amd_get_site_option( "api_page" ) );
            if( is_page() ){
                $post = get_post();
                if( $post AND in_array( $post->ID, [ $loginPage, $dashboardPage, $apiPage ] ) )
                    $template = AMD_TEMPLATES . "/template-amd-raw.php";
            }

            return $template;
        } );

    }

    /**
     * Init admin menu
     * @return void
     * @since 1.0.0
     */
    public function initAdmin(){

        add_action( "admin_head", function(){
            # @formatter off
            ?><style>.toplevel_page_material-dashboard .wp-menu-image>img{width:21px;height:auto;padding:6px !important}</style><?php
            # @formatter on
        } );

        add_action( "admin_menu", function(){

            $capability = apply_filters( "amd_menu_items_capability", "manage_options" );

            add_menu_page( esc_html_x( "Material dashboard", "Admin", "material-dashboard" ), esc_html_x( "Dashboard", "Admin", "material-dashboard" ), $capability, "material-dashboard", [
                $this,
                "page_admin_dashboard"
            ], AMD_IMG . "/logo/menu_icon.svg", 3 );

            add_submenu_page( "material-dashboard", esc_html_x( "Settings", "Admin", "material-dashboard" ), esc_html_x( "Settings", "Admin", "material-dashboard" ), $capability, "amd-settings", [
                $this,
                "submenu_admin_settings"
            ] );

            add_submenu_page( "material-dashboard", esc_html_x( "Appearance", "Admin", "material-dashboard" ), esc_html_x( "Appearance", "Admin", "material-dashboard" ), $capability, "amd-appearance", [
                $this,
                "submenu_admin_appearance"
            ] );

            /**
             * @since 1.1.0
             */
            add_submenu_page( "material-dashboard", esc_html_x( "Menu settings", "Admin", "material-dashboard" ), esc_html_x( "Menu settings", "Admin", "material-dashboard" ), $capability, "amd-menu", [
                $this,
                "submenu_admin_menu"
            ] );

            add_submenu_page( "material-dashboard", esc_html_x( "Manage data", "Admin", "material-dashboard" ), esc_html_x( "Manage data", "Admin", "material-dashboard" ), $capability, "amd-data", [
                $this,
                "submenu_admin_manage_data"
            ] );

            add_submenu_page( "material-dashboard", esc_html_x( "Security", "Admin", "material-dashboard" ), esc_html_x( "Security", "Admin", "material-dashboard" ), $capability, "amd-security", [
                $this,
                "submenu_admin_security"
            ] );

            add_submenu_page( "material-dashboard", esc_html_x( "Wizard", "Admin", "material-dashboard" ), esc_html_x( "Wizard", "Admin", "material-dashboard" ), $capability, "amd-wizard", [
                $this,
                "admin_wizard_page"
            ] );

            add_submenu_page( "material-dashboard", esc_html_x( "Extensions", "Admin", "material-dashboard" ), esc_html_x( "Extensions", "Admin", "material-dashboard" ), $capability, "amd-ext", [
                $this,
                "submenu_admin_extensions"
            ] );

            add_submenu_page( "material-dashboard", esc_html_x( "Themes", "Admin", "material-dashboard" ), esc_html_x( "Themes", "Admin", "material-dashboard" ), $capability, "amd-themes", [
                $this,
                "submenu_admin_themes"
            ] );

            add_submenu_page( "material-dashboard", esc_html__( "Advanced search", "material-dashboard" ), esc_html__( "Advanced search", "material-dashboard" ), $capability, "amd-search", [
                $this,
                "submenu_admin_search_engine"
            ] );

            add_submenu_page( "material-dashboard", esc_html__( "Custom hooks", "material-dashboard" ), esc_html__( "Custom hooks", "material-dashboard" ), $capability, "amd-hooks", [
                $this,
                "submenu_admin_hooks_manager"
            ] );

            add_submenu_page( "material-dashboard", esc_html__( "More", "material-dashboard" ), esc_html__( "More", "material-dashboard" ), $capability, "amd-more", [
                $this,
                "submenu_admin_more"
            ] );

        } );

        # Admin stylesheet which loads from API stylesheet request
        add_action( "amd_api_admin_stylesheet", function(){
            # @formatter off
            echo /** @lang CSS */ ".amd-table{max-width:100%;overflow-x:auto;border-radius:8px;-ms-overflow-style:none;scrollbar-width:none;background:var(--amd-wrapper-bg)}
.amd-table::-webkit-scrollbar{display:none}
.amd-table>table{width:100%;border-collapse:collapse}
.amd-table>table tr>th{font-family:var(--amd-title-font),sans-serif;background:rgba(var(--amd-primary-rgb),.7);color:var(--amd-wrapper-fg);text-align:start;font-size:var(--amd-size-lg);height:30px;padding:8px 16px}
.amd-table>table tr>th{color:#fff}
body.rtl .amd-table>table tr>th:first-child{border-top-right-radius:8px}
body.rtl .amd-table>table tr>th:last-child{border-top-left-radius:8px}
body.ltr .amd-table>table tr>th:first-child{border-top-left-radius:8px}
body.ltr .amd-table>table tr>th:last-child{border-top-right-radius:8px}
.amd-table>table tr>td{text-align:start;font-size:var(--amd-size-md);color:var(--amd-text-color);height:30px;padding:8px 16px;background-color:transparent;transition:background-color ease .2s}
.amd-table>table tr:nth-child(even)>td{background-color:rgba(var(--amd-wrapper-fg-rgb),.2)}
.amd-table>table tr:nth-child(odd)>td{background-color:var(--amd-wrapper-fg)}
.amd-table>table tr:hover>td{background-color:rgba(var(--amd-primary-rgb),.08);}";
            # @formatter on

        } );

        # Since 1.1.0
        if( apply_filters( "amd_do_admin_checkin", true ) ){
            add_action( "amd_admin_header", function(){
                $checkin_interval = intval( amd_get_default( "checkin_interval", 30000 ) );
                ?>
                <script>
                    jQuery($ => {
                        $(document).ready(function(){
                            if(typeof send_ajax === "function"){
                                const checkin = () => {
                                    send_ajax({
                                        action: amd_conf.ajax.private,
                                        _checkin: true
                                    }, () => {}, () => {});
                                }
                                window.amd_checkin_interval = setInterval(checkin, parseInt(`<?php echo $checkin_interval; ?>`));
                            }
                        });
                    });
                </script>
                <?php
            } );
        }

    }

    /**
     * Run main core
     * @return void
     * @since 1.0.0
     */
    public function run(){

        # Init
        self::init();

        # Initialize at first use
        if( amd_is_first_use() )
            self::initialize();

    }

    /**
     * Initialize
     * @return void
     * @since 1.0.0
     */
    public function initialize(){

        global /** @var AMD_DB $amdDB */
        /** @var AMDLoader $amdLoader */
        $amdDB, $amdLoader;

        # Install database
        $amdDB->init( true );

        # Set default value for first time
        $amdDB->repairData( amd_get_default_options() );

        # Enable default extensions
        foreach( $amdLoader->getDefaultEnabledExtensions() as $ext )
            $amdLoader->enableExtension( $ext );

        # Mark as initialized
        amd_set_site_option( "initialized", time() );

    }

    /**
     * Load API page
     * @return void
     * @since 1.0.0
     */
    public function loadAPI(){

        require_once( AMD_API_PATH . "/index.php" );

    }

    /**
     * Initialize
     * @return void
     * @since 1.0.0
     */
    public function init(){

        if( is_admin() )
            do_action( "amd_admin_core_init" );
        else
            do_action( "amd_core_init" );

    }

    /**
     * Register new theme / Update existing theme
     *
     * @param string $id
     * Theme ID
     * @param string $name
     * Theme title
     * @param string $scope
     * Theme scope: e.g: "dark", "light"
     * @param array $data
     * Theme variables data. e.g:
     * <code>
     * array(
     *    "--abc-color" => "#fff",
     *    ...
     * )
     * </code>
     * @param bool $overwrite
     * Whether to replace theme (if exist) or not
     *
     * @return void
     * @since 1.0.0
     */
    public function registerTheme( $id, $name, $scope, $data, $overwrite = false ){

        if( !empty( $this->themes[$id] ) AND !$overwrite )
            return;

        $key = !empty( $scope ) ? "{$scope}_$id" : $id;

        $this->themes[$key] = array(
            "id" => $id,
            "name" => $name,
            "scope" => $scope,
            "data" => $data
        );

    }

    /**
     * Edit (override) existing theme. You can also use it after themes initialized
     *
     * @param string $id
     * Theme ID
     * @param string $scope
     * Theme scope. e.g: "dark", "light"
     * @param string $name
     * Property name. e.g: "--abc-color"
     * @param string $newValue
     * Property new value. e.g: "#fff"
     *
     * @return void
     * @since 1.0.0
     */
    public function editTheme( $id, $scope, $name, $newValue ){

        if( !empty( $this->themes[$id] ) )
            return;

        $key = !empty( $scope ) ? "{$scope}_$id" : $id;

        $this->themes[$key]["data"][$name] = $newValue;

    }

    /**
     * Check if sign-in method exists
     *
     * @param string $id
     * Method ID. e.g: "Google"
     * @param bool $get
     * Whether to get method data or only check existing
     *
     * @return array|bool|null
     * @since 1.0.0
     */
    public function signMethodExists( $id, $get = false ){

        if( !empty( $this->signMethods[$id] ) )
            return $get ? $this->signMethods[$id] : true;

        return $get ? null : false;

    }

    /**
     * Get registered sign-in methods
     * @return array[]
     * @since 1.0.0
     */
    public function getSignInMethods(){

        /**
         * Validate sign-in methods or modify them before displaying
         * @since 1.0.3
         */
        do_action( "amd_before_modify_sign_in_methods" );

        return $this->signMethods;

    }

    /**
     * Register new sign-in method
     *
     * @param string $id
     * Method ID. e.g: "google"
     * @param array $data
     * Method data
     *
     * @return void
     * @since 1.0.0
     */
    public function registerSignInMethod( $id, $data ){

        if( self::signMethodExists( $id ) )
            return;

        $this->signMethods[$id] = $data;

    }

    /**
     * Remove registered sign-in method
     *
     * @param string $id
     * Method ID. e.g: "google"
     *
     * @return void
     * @since 1.0.3
     */
    public function removeSignInMethod( $id ){

        if( !self::signMethodExists( $id ) )
            return;

        $this->signMethods[$id] = null;
        unset( $this->signMethods[$id] );

    }

    /**
     * Register cleanup variant
     * @param int $id
     * Variant ID
     * @param array $data
     * Variant data array
     *
     * @return void
     * @since 1.0.1
     */
    public function registerCleanupVariant( $id, $data ){

        $this->cleanup_variants[$id] = $data;

    }

    /**
     * Get cleanup variants
     * @return array
     * @since 1.0.1
     */
    public function getCleanupVariants(){

        /**
         * Initialize cleanup variants
         * @since 1.0.1
         */
        do_action( "amd_init_cleanup_variants" );

        return $this->cleanup_variants;

    }

    /**
     * Get all of registered themes
     *
     * @param bool $override
     * Whether to get original values or overridden values
     *
     * @return array[]
     * Theme data
     * @since 1.0.0
     */
    public function getThemes( $override = true ){

        $theme = $this->themes;

        if( $override ){
            $primaryColor = amd_get_site_option( "primary_color", "purple" );
            foreach( $theme as $id => $data ){
                $themeColors = $data["data"];
                $hex = "";
                $rgb = "";
                if( amd__validate_color_code( $primaryColor ) ){
                    $hex = $primaryColor;
                    $rgb = amd_hex_to_rgb( $primaryColor, "r, g, b" );
                }
                else{
                    if( !empty( $themeColors["--amd-color-$primaryColor"] ) )
                        $hex = $themeColors["--amd-color-$primaryColor"];
                    if( !empty( $themeColors["--amd-color-$primaryColor-rgb"] ) )
                        $rgb = $themeColors["--amd-color-$primaryColor-rgb"];
                }
                $theme[$id]["data"]["--amd-primary"] = $hex;
                $theme[$id]["data"]["--amd-primary-rgb"] = $rgb;
            }
        }

        return $theme;

    }

    /**
     * Get theme variables for CSS
     * @return array
     * @since 1.0.0
     */
    public function getThemeColors(){

        $theme = [];
        foreach( self::getThemes() as $id => $data ){
            $theme[$id] = $data["data"];
        }

        return $theme;

    }

    /**
     * Load admin dashboard
     * @return void
     * @since 1.0.0
     */
    public function page_admin_dashboard(){

        require_once AMD_PAGES . "/admin-dashboard.php";

    }

    /**
     * Load dashboard settings page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_settings(){

        require_once AMD_PAGES . "/smp-settings.php";

    }

    /**
     * Load appearance page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_appearance(){

        require_once AMD_PAGES . "/smp-appearance.php";

    }

    /**
     * Load menu settings page
     * @return void
     * @since 1.1.0
     */
    public function submenu_admin_menu(){

        require_once AMD_PAGES . "/smp-menu.php";

    }

    /**
     * Load manage data page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_manage_data(){

        require_once AMD_PAGES . "/smp-manage-data.php";

    }

    /**
     * Security page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_security(){

        require_once AMD_PAGES . "/smp-security.php";

    }

    /**
     * Extensions page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_extensions(){

        require_once AMD_PAGES . "/smp-extensions.php";

    }

    /**
     * Themes page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_themes(){

        require_once AMD_PAGES . "/smp-themes.php";

    }

    /**
     * Support page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_more(){

        require_once AMD_PAGES . "/smp-more.php";

    }

    /**
     * Advanced search page
     * @return void
     * @since 1.0.0
     */
    public function submenu_admin_search_engine(){

        require_once AMD_PAGES . "/smp-search.php";

    }

    /**
     * WP hooks management page
     * @return void
     * @since 1.1.2
     */
    public function submenu_admin_hooks_manager(){

        require_once AMD_PAGES . "/smp-hooks.php";

    }

    /**
     * It's only for error prevention, the wizard page is already loaded in '_app' extension
     * @return void
     * @see amd_ext__app_dump_wizard_page()
     * @since 1.0.0
     */
    public function admin_wizard_page(){ }

    /**
     * Remove plugin data
     *
     * @param array $variants
     * Variants data
     *
     * @return array
     * @since 1.0.0
     */
    public function cleanup( $variants ){

        $out = [];
        foreach( $variants as $variant ){

            if( !empty( $this->cleanup_variants[$variant] ) ){

                $v = $this->cleanup_variants[$variant];

                $callback = $v["callback"] ?? null;

                if( is_callable( $callback ) )
                    $out = array_merge( $out, call_user_func( $callback ) );

            }

        }

        return $out;

    }

    /**
     * Edit user fields for 'Edit user' page in admin dashboard
     *
     * @param null|int|WP_User $u
     * User ID, pass null to get current user
     *
     * @return void
     * @since 1.0.0
     */
    public function editUserFields( $u = null ){

        if( !$u )
            return;

        if( empty( $u ) OR empty( $u->ID ) )
            $uid = sanitize_text_field( $_GET["user_id"] ?? "0" );
        else
            $uid = $u->ID;

        if( !$uid OR !is_numeric( $uid ) )
            return;

        $user = amd_get_user_by( "ID", $uid );
        if( !$user )
            return;

        if( !current_user_can( "edit_user", $user->ID ) )
            return;

        $is_me = $user->ID == get_current_user_id();
        $phone_field = amd_get_site_option( "phone_field" ) == "true";
        $isOnline = $is_me ? true : $user->isOnline();
        $lastSeen = $user->getLastSeen();
        ?>
        <br>
        <h2><?php esc_html_e( "Dashboard", "material-dashboard" ); ?></h2>
        <table class="form-table" role="presentation">
            <tbody>
            <?php if( $phone_field ): ?>
                <tr class="amd-user-wrap">
                    <th>
                        <label for="amd_phone"><?php esc_html_e( "Phone", "material-dashboard" ); ?></label>
                    </th>
                    <td>
                        <span dir="auto"><?php echo esc_html( $user->phone ); ?></span>
                    </td>
                </tr>
            <?php endif; ?>

            <tr class="user-first-name-wrap">
                <th><?php esc_html_e( "Status", "material-dashboard" ); ?></th>
                <td>
                    <p style="width:max-content;color:#fff;padding:6px 12px;border-radius:6px;<?php echo esc_attr( "background:" . ( $isOnline ? "#3dda84" : "#f55753" ) ); ?>"><?php echo $isOnline ? esc_html__( "Online", "material-dashboard" ) : esc_html__( "Offline", "material-dashboard" ); ?></p>
                </td>
            </tr>

            <?php if( function_exists( "amd_ext_edd_ready" ) AND amd_ext_edd_ready() ): ?>
                <?php
                $downloads_counts = amd_ext_edd_get_downloads_count();
                $amount = amd_ext_edd_get_downloads_amount();
                ?>
                <tr class="user-first-name-wrap">
                    <th><?php esc_html_e( "Downloads", "material-dashboard" ); ?></th>
                    <td>
                        <p><?php echo esc_html( sprintf( _n( "%d download", "%d downloads", $downloads_counts, "material-dashboard" ), $downloads_counts ) . " ($amount)" ); ?></p>
                    </td>
                </tr>
            <?php endif; ?>


            <?php if( function_exists( "amd_ext_wc_ready" ) AND amd_ext_wc_ready() ): ?>
                <?php
                $purchases_count = count( amd_ext_wc_get_orders_for_uid( $user->ID ) );
                $amount = amd_ext_wc_get_purchases_amount( $user->ID );
                ?>
                <tr class="user-first-name-wrap">
                    <th><?php esc_html_e( "Purchases", "material-dashboard" ); ?></th>
                    <td>
                        <p><?php echo esc_html( sprintf( _n( "%d purchase", "%d purchases", $purchases_count, "material-dashboard" ), $purchases_count ) ) . " ($amount)"; ?></p>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if( !$isOnline AND !empty( $lastSeen["time"] ) ): ?>
                <tr class="user-first-name-wrap">
                    <th><?php esc_html_e( "Last seen", "material-dashboard" ); ?></th>
                    <td><p><?php echo esc_html( $lastSeen["str"] ); ?></p></td>
                </tr>
            <?php endif; ?>

            <tr class="user-first-name-wrap">
                <th><?php esc_html_e( "Registration", "material-dashboard" ); ?></th>
                <td>
                    <p><?php echo esc_html( amd_true_date( "j F Y", $user->getRegistrationTime() ) . " (" . $user->getRegistration()["pass_ago"] . ")" ); ?></p>
                </td>
            </tr>

            <?php
            /**
             * After edit profile table items
             * @since 1.0.1
             */
            do_action( "amd_after_user_edit_profile_items", $u );
            ?>

            </tbody>
        </table>
        <?php
        /**
         * After edit profile table
         * @since 1.0.1
         */
        do_action( "amd_after_user_edit_profile_table" );
        ?>
        <br>
        <?php

    }

}