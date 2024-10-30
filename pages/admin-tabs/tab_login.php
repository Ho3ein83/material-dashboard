<?php

$use_login_2fa = amd_get_site_option( "use_login_2fa", "false" );
$force_login_2fa = amd_get_site_option( "force_login_2fa", "false" );
$use_login_otp = amd_get_site_option( "use_login_otp", "false" );
$use_email_otp = amd_get_site_option( "use_email_otp", "false" );
$prefer_otp = amd_get_site_option( "prefer_otp", "false" );
$allow_phone_login = amd_get_site_option( "allow_phone_login", "false" );
$display_login_reports = amd_get_site_option( "display_login_reports", "true" );
$password_reset_enabled = amd_get_site_option( "password_reset_enabled", "true" );
$password_reset_methods = amd_get_site_option( "password_reset_methods", "email" );
$_password_reset_methods = explode( ",", $password_reset_methods );
$enable_email_login_notification = amd_get_site_option( "enable_email_login_notification", "false" );
$enable_sms_login_notification = amd_get_site_option( "enable_sms_login_notification", "false" );

$defaultSettings = array(
    "use_login_2fa" => $use_login_2fa,
    "force_login_2fa" => $force_login_2fa,
    "use_login_otp" => $use_login_otp,
    "use_email_otp" => $use_email_otp,
    "prefer_otp" => $prefer_otp,
    "allow_phone_login" => $allow_phone_login,
    "display_login_reports" => $display_login_reports,
    "password_reset_enabled" => $password_reset_enabled,
    "password_reset_methods" => $password_reset_methods,
    "enable_email_login_notification" => $enable_email_login_notification,
    "enable_sms_login_notification" => $enable_sms_login_notification,
);

?>
<!-- General -->
<div class="amd-admin-card --setting-card">
    <?php amd_dump_admin_card_keywords( ["حساب کاربری", "user account", "secure", "security", "امنیت"] ); ?>
    <h3 class="--title"><?php esc_html_e( "General", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_login_general_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_login_general_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="allow-phone-login"><?php esc_html_e( "Allow users to login with phone number", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="allow_phone_login" value="true" id="allow-phone-login">
                        <span></span>
                    </label>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_login_general_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_login_general_content" );
        ?>
    </div>
</div>

<!-- 2FA -->
<div class="amd-admin-card --setting-card">
    <?php amd_dump_admin_card_keywords( ["حساب کاربری", "user account", "secure", "security", "امنیت"] ); ?>
    <h3 class="--title"><?php esc_html_e( "Two factor authentication", "material-dashboard" ); ?></h3>
    <p class="color-red _show_on_login_otp_enabled_" style="display:none"><?php echo esc_html_x( "One time password authentication is enabled, please remember 2FA will not work anymore! You may want to disable it to prevent any confusion.", "Admin", "material-dashboard" ); ?></p>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_2fa_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_2fa_items" );
            ?>
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
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_2fa_dependencies_items" );
            ?>
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
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_2fa_dependencies_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_2fa_content" );
        ?>
    </div>
</div>

<!-- OTP -->
<div class="amd-admin-card --setting-card">
    <?php amd_dump_admin_card_keywords( ["حساب کاربری", "user account", "secure", "security", "امنیت"] ); ?>
    <h3 class="--title"><?php esc_html_e( "One time password authentication", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_otp_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_otp_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="use-login-otp"><?php esc_html_e( "Enable OTP (One-Time-Password)", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="use_login_otp" value="true" id="use-login-otp">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "One time password (OTP) is a method to login to your account with SMS or email. If you enable it, two factor authentication (2FA) won't work anymore.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_otp_items" );
            ?>
        </div>
        <div class="__option_grid _show_on_login_otp_enabled_">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_otp_dependencies_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="use-email-otp"><?php esc_html_e( "Use email OTP instead of SMS", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="use_email_otp" value="true" id="use-email-otp">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "You can use email to send one-time passwords to users, however please note that this isn't the most secure way, but it can be helpful if you don't have SMS webservice enabled.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="prefer-otp"><?php esc_html_e( "Prefer OTP over password authentication", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="prefer_otp" value="true" id="prefer-otp">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "If you enable this option, by default, users get into OTP form in login page, otherwise the default login page will be your current one.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <?php
                /**
                 * 2-Factor Authentication options
                 * @since 1.0.5
                 */
                do_action( "amd_tab_login_otp_options" );
            ?>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_otp_dependencies_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_otp_content" );
        ?>
        <div class="h-10"></div>
        <p class="color-low w-bold"><?php echo esc_html_x( "Noticeable points", "Admin", "material-dashboard" ); ?></p>
        <button type="button" class="amd-admin-button --sm --primary --text" onclick="$(this).fadeOut(0);$(this).next('div').slideDown();"><?php esc_html_e( "Show", "material-dashboard" ); ?></button>
        <div style="display:none">
            <p class="color-low"><?php echo esc_html_x( "The difference between OTP and 2FA is that in two factor authentication users have to enter their correct username and password and before redirecting to dashboard, they need to enter the received code. In OTP, users don't need to enter they password, instead they use the received code from SMS or email", "Admin", "material-dashboard" ); ?></p>
            <p class="color-low"><?php echo esc_html_x( "Sometimes users prefer their password or messaging web services are out of service, in those cases users still can login with their password, that's why you can't force OTP.", "Admin", "material-dashboard" ); ?></p>
            <p class="color-low"><?php echo esc_html_x( "If the user haven't registered a phone number or verified it, they can't use OTP and must login with normal method.", "Admin", "material-dashboard" ); ?></p>
            <p class="color-low"><?php echo esc_html_x( "In general, OTP is more suitable than 2FA, because it's more simpler for users and they won't get stuck in special cases like web service outage.", "Admin", "material-dashboard" ); ?></p>
        </div>
    </div>
</div>

<!-- Login reports -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php esc_html_e( "Login reports", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_login_reports_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_login_reports_items" );
            ?>
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
            <div class="-item">
                <div class="-sub-item">
                    <label for="enable-email-login-notification"><?php esc_html_e( "Notify user about logins (with email)", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="enable_email_login_notification" value="true" id="enable-email-login-notification">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <span class="tiny-text color-primary">
                        <?php esc_html_e( "Let users know when a new login happens with their account", "material-dashboard" ); ?>
                    </span>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="enable-sms-login-notification"><?php esc_html_e( "Notify user about logins (with SMS)", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="enable_sms_login_notification" value="true" id="enable-sms-login-notification">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
            <span class="tiny-text color-primary">
                <?php esc_html_e( "Let users know when a new login happens with their account", "material-dashboard" ); ?>
            </span>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_login_reports_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_login_reports_content" );
        ?>
    </div>
</div>

<!-- Password reset -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php esc_html_e( "Password reset", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_password_reset_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_password_reset_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label class="clickable" for="password-reset-enabled"><?php esc_html_e( "Enable password reset", "material-dashboard" ); ?></label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="password_reset_enabled" id="password-reset-enabled" value="true">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="amd-admin-card _show_on_password_reset_enabled_">
            <h4 class="--title"><?php esc_html_e( "Password reset verification methods", "material-dashboard" ); ?></h4>
            <span class="tiny-text-im color-low"><?php esc_html_e( "Password reset verification code will be sent from these methods, if you choose multiple methods, best choice will be used or user should select one.", "material-dashboard" ); ?></span>
            <div class="--content">
                <div class="__option_grid">
                    <?php foreach( apply_filters( "amd_password_reset_methods", [] ) as $method ): ?>
                    <?php if( !$method["active"] ) continue; ?>
                        <div class="-item">
                            <div class="-sub-item">
                                <label class="clickable w-bold" for="<?php echo esc_attr( "pr-method-" . $method["id"] ); ?>"><?php echo esc_html( $method["title"] ); ?></label>
                                <div class="h-10"></div>
                                <span class="tiny-text-im color-blue"><?php echo esc_html( $method["desc"] ); ?></span>
                            </div>
                            <div class="-sub-item">
                                <label class="hb-switch">
                                    <input type="checkbox" role="switch" name="pr_method" value="<?php echo esc_attr( $method["id"] ); ?>" id="<?php echo esc_attr( "pr-method-" . $method["id"] ); ?>" <?php echo in_array( $method["id"], $_password_reset_methods ) ? "checked" : ""; ?>>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_password_reset_content" );
        ?>
    </div>
</div>

<script>
    (function(){
        let $use_2fa = $("#use-login-2fa"), $force_2fa = $("#force-login-2fa"),
            $display_reports = $("#display-login-reports"), $pr_enabled = $("#password-reset-enabled");
        let $use_otp = $("#use-login-otp"), $use_email_otp = $("#use-email-otp");
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
        $use_2fa.change(function(){
            const $e = $("._show_on_login_2fa_enabled_");
            $use_2fa.is(":checked") ? $e.fadeIn() : $e.fadeOut();
        }).change();
        $use_otp.change(function(){
            const $e = $("._show_on_login_otp_enabled_");
            $use_otp.is(":checked") ? $e.fadeIn() : $e.fadeOut();
        }).change();
        $pr_enabled.change(function(){
            const $e = $("._show_on_password_reset_enabled_");
            $pr_enabled.is(":checked") ? $e.fadeIn() : $e.fadeOut();
        }).change();
        function get_pr_methods(){
            let methods = [];
            $('input[name="pr_method"]').each(function(){
                const $e = $(this);
                if($e.is(":checked"))
                    methods.push($e.attr("value"));
            });
            return methods;
        }

        $amd.addEvent("on_settings_saved", () => {
            const pr_methods = get_pr_methods().join(",") || "None";
            let pr_enabled = $("#password-reset-enabled").is(":checked") ? "true" : "false";
            if(pr_methods === "None") pr_enabled = "false";
            return {
                "use_login_2fa": $use_2fa.is(":checked") ? "true" : "false",
                "force_login_2fa": $force_2fa.is(":checked") ? "true" : "false",
                "use_login_otp": $use_otp.is(":checked") ? "true" : "false",
                "use_email_otp": $use_email_otp.is(":checked") ? "true" : "false",
                "prefer_otp": $("#prefer-otp").is(":checked") ? "true" : "false",
                "allow_phone_login": $("#allow-phone-login").is(":checked") ? "true" : "false",
                "display_login_reports": $display_reports.is(":checked") ? "true" : "false",
                "password_reset_enabled": pr_enabled,
                "password_reset_methods": pr_methods,
                "enable_email_login_notification": $("#enable-email-login-notification").is(":checked"),
                "enable_sms_login_notification": $("#enable-sms-login-notification").is(":checked"),
            };
        });
    }());
</script>