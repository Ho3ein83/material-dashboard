<?php

$login_page_title = amd_get_site_option( "login_page_title" );
$register_page_title = amd_get_site_option( "register_page_title" );
$reset_password_page_title = amd_get_site_option( "reset_password_page_title" );

$login_form_bg = intval( amd_get_site_option( "login_form_bg" ) );
$register_form_bg = intval( amd_get_site_option( "register_form_bg" ) );
$pr_form_bg = intval( amd_get_site_option( "pr_form_bg" ) );
$other_forms_bg = intval( amd_get_site_option( "other_forms_bg" ) );
$use_glass_morphism_forms = amd_get_site_option( "use_glass_morphism_forms", "false" );

$dash = amd_get_dash_logo();
$dashLogo = $dash["logo"];
$dashLogoURL = $dash["url"];
$dashLogoVal = $dash["id"];

$defaultSettings = array(
	"dash_logo" => $dashLogo,
    "login_page_title" => $login_page_title,
    "register_page_title" => $register_page_title,
    "reset_password_page_title" => $reset_password_page_title,
    "use_glass_morphism_forms" => $use_glass_morphism_forms
);

$bg_placeholder = AMD_IMG . "/bg-transparent.svg";

?>

<?php
    /**
     * Before forms tab settings
     * @since 1.0.5
     */
    do_action( "amd_settings_tab_forms_before" );
?>

<!-- Forms text -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "Forms title", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="form-title-login">
                        <?php echo esc_html_x( "login page", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="form-title-login" class="amd-admin-input" name="login_page_title" placeholder="<?php esc_attr_e( "default", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="form-title-register">
                        <?php echo esc_html_x( "registration page", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="form-title-register" class="amd-admin-input" name="register_page_title" placeholder="<?php esc_attr_e( "default", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="form-title-fp">
                        <?php echo esc_html_x( "Reset password page", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="form-title-fp" class="amd-admin-input" name="reset_password_page_title" placeholder="<?php esc_attr_e( "default", "material-dashboard" ); ?>">
                </div>
            </div>
        </div>
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "These texts will show on top of the login and registration forms, leave it empty to use default text and support multi language.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logo -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "Dashboard logo", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <button class="amd-admin-button" id="pick-dash-logo" data-media="dash-logo" data-library="image"
                            data-link="dash-logo-img"><?php echo esc_html_x( "Browse", "Admin", "material-dashboard" ); ?></button>
                    <label>
                        <input type="text" id="custom-dash-logo" class="amd-admin-input"
                               placeholder="<?php esc_attr_e( "Custom URL", "material-dashboard" ); ?>">
                    </label>
                </div>
                <div class="-sub-item">
                    <div style="width:100px;aspect-ratio:1;display:inline-block">
                        <img src="<?php echo esc_url( $dashLogoURL ); ?>" id="dash-logo-img" alt=""
                             style="width:100%;height:100%;object-fit:contain" class="clickable"
                             data-for="pick-dash-logo">
                        <input type="hidden" id="dash-logo" name="dash_logo" value="<?php echo esc_attr( $dashLogoVal ); ?>">
                    </div>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "Dashboard logo is your business logo and it'll show on the login form and some other places.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        let $input = $("#custom-dash-logo");
        let $dl = $("#dash-logo"), $img = $("#dash-logo-img");
        $dl.on("change input", () => $input.val(""));
        $input.on("change", function () {
            let src = $input.val();
            if(src) $img.attr("src", src);
        });
    }())
</script>

<!-- Forms background -->
<div class="amd-admin-card --setting-card" <?php echo amd_theme_support( "image_background" ) ? "" : "style=\"display:none\""; ?>>
    <?php amd_dump_admin_card_keywords( ["wallpaper", "پس‌زمینه"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Forms background", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label>
                        <?php echo esc_html_x( "Login form background", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <div class="text-center">
                        <img src="<?php echo esc_url( $login_form_bg ? wp_get_attachment_url( $login_form_bg ) : $bg_placeholder ); ?>" id="login-form-bg" alt="" class="clickable -small-img" data-for="pick-login-bg">
                        <button type="button" class="amd-admin-button --red --text --sm" id="clear-login-bg"><?php esc_html_e( "Remove", "material-dashboard" ); ?></button>
                        <button type="button" class="amd-admin-button --sm" id="pick-login-bg" data-link="login-form-bg" data-media="login-form-image" data-library="image"><?php echo esc_html_x( "Browse", "Admin", "material-dashboard" ); ?></button>
                        <input type="hidden" id="login-form-image" name="login_form_bg" value="<?php echo esc_attr( $login_form_bg ); ?>">
                    </div>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label>
                        <?php echo esc_html_x( "Registration form background", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <div class="text-center">
                        <img src="<?php echo esc_url( $register_form_bg ? wp_get_attachment_url( $register_form_bg ) : $bg_placeholder ); ?>" id="register-form-bg" alt="" class="clickable -small-img" data-for="pick-register-bg">
                        <button type="button" class="amd-admin-button --red --text --sm" id="clear-register-bg"><?php esc_html_e( "Remove", "material-dashboard" ); ?></button>
                        <button type="button" class="amd-admin-button --sm" id="pick-register-bg" data-link="register-form-bg" data-media="register-form-image" data-library="image"><?php echo esc_html_x( "Browse", "Admin", "material-dashboard" ); ?></button>
                        <input type="hidden" id="register-form-image" name="register_form_bg" value="<?php echo esc_attr( $register_form_bg ); ?>">
                    </div>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label>
                        <?php echo esc_html_x( "Password reset form background", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <div class="text-center">
                        <img src="<?php echo esc_url( $pr_form_bg ? wp_get_attachment_url( $pr_form_bg ) : $bg_placeholder ); ?>" id="pr-form-bg" alt="" class="clickable -small-img" data-for="pick-pr-bg">
                        <button type="button" class="amd-admin-button --red --text --sm" id="clear-pr-bg"><?php esc_html_e( "Remove", "material-dashboard" ); ?></button>
                        <button type="button" class="amd-admin-button --sm" id="pick-pr-bg" data-link="pr-form-bg" data-media="pr-form-image" data-library="image"><?php echo esc_html_x( "Browse", "Admin", "material-dashboard" ); ?></button>
                        <input type="hidden" id="pr-form-image" name="pr_form_bg" value="<?php echo esc_attr( $pr_form_bg ); ?>">
                    </div>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label>
                        <?php echo esc_html_x( "Other forms background", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <div class="text-center">
                        <img src="<?php echo esc_url( $other_forms_bg ? wp_get_attachment_url( $other_forms_bg ) : $bg_placeholder ); ?>" id="other-forms-bg" alt="" class="clickable -small-img" data-for="pick-others-bg">
                        <button type="button" class="amd-admin-button --red --text --sm" id="clear-others-bg"><?php esc_html_e( "Remove", "material-dashboard" ); ?></button>
                        <button type="button" class="amd-admin-button --sm" id="pick-others-bg" data-link="others-form-bg" data-media="others-form-image" data-library="image"><?php echo esc_html_x( "Browse", "Admin", "material-dashboard" ); ?></button>
                        <input type="hidden" id="other-forms-image" name="other_forms_bg" value="<?php echo esc_attr( $other_forms_bg ); ?>">
                    </div>
                </div>
            </div>
            <div class="-item" <?php echo amd_theme_support( "glass_morphism_forms" ) ? "" : "style=\"display:none\""; ?>>
                <div class="-sub-item">
                    <label for="use-glass-morphism-forms" class="clickable">
                        <?php echo esc_html_x( "Use glass morphism form", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" id="use-glass-morphism-forms" name="use_glass_morphism_forms" value="true">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <span class="color-primary tiny-text"><?php esc_html_e( "Make forms look like glass and to create depth and contrast between form and background image.", "material-dashboard" ); ?></span>
                    <div class="h-10"></div>
                    <div class="text-center">
                        <img src="<?php echo esc_url( AMD_IMG . "/glass-morphism-form.png" ); ?>" alt="" style="max-width:400px;height:auto">
                    </div>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <input type="hidden" id="all-forms-image" value="">
                    <img src="" alt="" id="all-forms-bg" style="display:none" data-default="<?php echo esc_url( $bg_placeholder ); ?>">
                    <button type="button" class="amd-admin-button --primary w-normal" id="pick-all-bg" data-link="all-forms-bg" data-media="all-forms-image" data-library="image"><?php echo esc_html_x( "Change all", "Admin", "material-dashboard" ); ?></button>
                </div>
                <div class="-sub-item">
                    <button type="button" class="amd-admin-button --red --text w-normal" id="remove-all-bg"><?php echo esc_html_x( "Remove all", "Admin", "material-dashboard" ); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    /**
     * After forms tab settings
     * @since 1.0.5
     */
    do_action( "amd_settings_tab_forms_after" );
?>

<script>
    (function () {
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)

        const $login_input = $("#login-form-image"), $login_img = $("#login-form-bg");
        const $register_input = $("#register-form-image"), $register_img = $("#register-form-bg");
        const $pr_input = $("#pr-form-image"), $pr_img = $("#pr-form-bg");
        const $others_input = $("#other-forms-image"), $others_img = $("#other-forms-bg");
        const $all_input = $("#all-forms-image"), $all_img = $("#all-forms-bg"), $remove_all = $("#remove-all-bg");
        const default_img = $all_img.attr("data-default");
        function set_all(atc_id, image_url){
            $login_input.val(atc_id);
            $register_input.val(atc_id);
            $pr_input.val(atc_id);
            $others_input.val(atc_id);
            $login_img.attr("src", image_url);
            $register_img.attr("src", image_url);
            $pr_img.attr("src", image_url);
            $others_img.attr("src", image_url);
        }
        $all_img.on("change", () => set_all($all_input.val(), $all_img.attr("src")));
        $remove_all.click(() => set_all("", default_img));
        $("#clear-login-bg").click(() => $login_input.val("") && $login_img.attr("src", default_img));
        $("#clear-register-bg").click(() => $register_input.val("") && $register_img.attr("src", default_img));
        $("#clear-pr-bg").click(() => $pr_input.val("") && $pr_img.attr("src", default_img));
        $("#clear-others-bg").click(() => $others_input.val("") && $others_img.attr("src", default_img));

        $amd.addEvent("on_settings_saved", () => {
            let $pt_login = $("#form-title-login"), $pt_register = $("#form-title-register"), $pt_fp = $("#form-title-fp");
            let $dashLogo = $('#dash-logo'), $customDashLogo = $("#custom-dash-logo");
            let dashLogo = $dashLogo.val();
            if($customDashLogo.val().length > 0) dashLogo = $customDashLogo.val();
            return {
                "login_page_title": $pt_login.val(),
                "register_page_title": $pt_register.val(),
                "reset_password_page_title": $pt_fp.val(),
                "dash_logo": dashLogo,
                "login_form_bg": $login_input.val(),
                "register_form_bg": $register_input.val(),
                "pr_form_bg": $pr_input.val(),
                "other_forms_bg": $others_input.val(),
                "use_glass_morphism_forms": $("#use-glass-morphism-forms").is(":checked") ? "true" : "false"
            };
        });
    }());
</script>

<style>
    .-small-img {
        display: flex;
        max-width: 200px;
        width: 100%;
        height: auto;
        aspect-ratio: 16/9;
        object-fit: cover;
        margin: 8px auto;
        border-radius: 8px;
    }
</style>