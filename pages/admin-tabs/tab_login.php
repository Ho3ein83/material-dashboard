<?php

$use_login_2fa = amd_get_site_option( "use_login_2fa", "false" );
$force_login_2fa = amd_get_site_option( "force_login_2fa", "false" );
$display_login_reports = amd_get_site_option( "display_login_reports", "true" );

$defaultSettings = array(
    "use_login_2fa" => $use_login_2fa,
    "force_login_2fa" => $force_login_2fa,
    "display_login_reports" => $display_login_reports,
);

?>
<!-- 2FA -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php esc_html_e( "Two factor authentication", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="use-login-2fa"><?php esc_html_e( "Enable 2 factor authentication", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="use_login_2fa" value="true" id="use-login-2fa">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "Two factor authentication (2FA) is a security layer for users authentication. If users uses 2FA they should enter the authentication code before they can log into their account.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
        </div>
        <div class="__option_grid _show_on_login_2fa_enabled_">
            <div class="-item">
                <div class="-sub-item">
                    <label for="force-login-2fa"><?php esc_html_e( "Force 2FA", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="force_login_2fa" value="true" id="force-login-2fa">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "Normally users can choose whether to use 2FA or not, but if you force it they have to pass two factor authentication to login.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <?php
                /**
                 * 2-Factor Authentication options
                 * @since 1.0.5
                 */
                do_action( "amd_tab_login_2fa_options" );
            ?>
        </div>
    </div>
</div>

<!-- Login reports -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php esc_html_e( "Login reports", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="display-login-reports"><?php esc_html_e( "Display login reports to users", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="display_login_reports" value="true" id="display-login-reports">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        let $use_2fa = $("#use-login-2fa"), $force_2fa = $("#force-login-2fa"),
            $display_reports = $("#display-login-reports");
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
        $use_2fa.change(function(){
            let $e = $("._show_on_login_2fa_enabled_");
            if($use_2fa.is(":checked")) $e.fadeIn();
            else $e.fadeOut();
        }).change();
        $amd.addEvent("on_settings_saved", () => {
            return {
                "use_login_2fa": $use_2fa.is(":checked") ? "true" : "false",
                "force_login_2fa": $force_2fa.is(":checked") ? "true" : "false",
                "display_login_reports": $display_reports.is(":checked") ? "true" : "false"
            };
        });
    }());
</script>