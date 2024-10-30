<?php

$login_page_title = amd_get_site_option( "login_page_title" );
$register_page_title = amd_get_site_option( "register_page_title" );
$reset_password_page_title = amd_get_site_option( "reset_password_page_title" );

$dash = amd_get_dash_logo();
$dashLogo = $dash["logo"];
$dashLogoURL = $dash["url"];
$dashLogoVal = $dash["id"];

$defaultSettings = array(
	"dash_logo" => $dashLogo,
    "login_page_title" => $login_page_title,
    "register_page_title" => $register_page_title,
    "reset_password_page_title" => $reset_password_page_title
);

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
                    <input type="text" id="form-title-login" class="amd-admin-input" name="login_page_title" placeholder="<?php _e( "default", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="form-title-register">
                        <?php echo esc_html_x( "registration page", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="form-title-register" class="amd-admin-input" name="register_page_title" placeholder="<?php _e( "default", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="form-title-fp">
                        <?php echo esc_html_x( "Reset password page", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="form-title-fp" class="amd-admin-input" name="reset_password_page_title" placeholder="<?php _e( "default", "material-dashboard" ); ?>">
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
						       placeholder="<?php _e( "Custom URL", "material-dashboard" ); ?>">
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
            };
        });
    }());
</script>