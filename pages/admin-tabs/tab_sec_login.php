<?php

$hideLogin = amd_get_site_option( "hide_login", "false" );
$login_attempts = amd_get_site_option( "login_attempts", "0" );
$login_attempts_time = amd_get_site_option( "login_attempts_time", "0" );

$defaultSettings = array(
	"hide_login" => $hideLogin,
	"login_attempts" => $login_attempts,
	"login_attempts_time" => $login_attempts_time,
);

?>

<!-- Login -->
<div class="amd-admin-card --setting-card">
	<h3 class="--title"><?php echo esc_html_x( "Login page", "Admin", "material-dashboard" ); ?></h3>
	<div class="--content">
		<div class="__option_grid">
			<div class="-item">
				<div class="-sub-item">
					<label for="hide-login">
						<?php echo esc_html_x( "Hide login page", "Admin", "material-dashboard" ); ?>
					</label>
				</div>
				<div class="-sub-item">
					<label class="hb-switch">
						<input type="checkbox" role="switch" name="hide_login" value="true" id="hide-login">
						<span></span>
					</label>
				</div>
				<div class="-sub-item --full">
					<p class="color-primary text-justify"><?php echo esc_html_x( "You can disable admin login page and use plugin login page instead", "Admin", "material-dashboard" ); ?>.</p>
				</div>
			</div>
            <div class="-item">
				<div class="-sub-item">
					<label for="login-attempts">
						<?php echo esc_html_x( "Login attempts", "Admin", "material-dashboard" ); ?>
					</label>
				</div>
				<div class="-sub-item">
                    <input type="number" id="login-attempts" class="amd-admin-input" min="0" value="<?php echo $login_attempts; ?>">
				</div>
				<div class="-sub-item --full">
					<p class="color-primary text-justify"><?php echo esc_html_x( "Specify how many times users can enter wrong password, after that times reached it will prevent them to login again for a few minutes. Set it to 0 for disabled", "Admin", "material-dashboard" ); ?>.</p>
				</div>
			</div>
            <div class="-item _show_on_login_attempts_">
				<div class="-sub-item">
					<label for="login-attempts-time">
						<?php echo esc_html_x( "Login attempts blocking time (minutes)", "Admin", "material-dashboard" ); ?>
					</label>
				</div>
				<div class="-sub-item">
                    <input type="number" id="login-attempts-time" class="amd-admin-input" min="0" value="<?php echo esc_attr( $login_attempts_time ); ?>">
				</div>
				<div class="-sub-item --full">
					<p class="color-primary text-justify"><?php echo esc_html_x( "Users will be blocked after too many attempts for login and they can login again after a while. Set it to 0 for default", "Admin", "material-dashboard" ); ?>.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    (function () {
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
        let $login_attempts = $("#login-attempts");
        $login_attempts.on("change input keydown", function(){
           if($(this).val() > 0)
               $("._show_on_login_attempts_").setWaiting(false);
           else
               $("._show_on_login_attempts_").setWaiting();
        });
        $login_attempts.trigger("change");
        $amd.addEvent("on_settings_saved", () => {
            let $hide_login = $("#hide-login");
            let $login_attempts_time = $("#login-attempts-time");
            let login_attempts = parseInt($login_attempts.val());
            let login_attempts_time = parseInt($login_attempts_time.val());
            if(isNaN(login_attempts)){
                login_attempts = 0;
                $login_attempts.val(0);
            }
            if(isNaN(login_attempts_time)){
                login_attempts_time = 0;
                $login_attempts_time.val(0);
            }
            return {
                "hide_login": $hide_login.is(":checked") ? "true" : "false",
                "login_attempts": login_attempts,
                "login_attempts_time": login_attempts_time
            }
        });
    }());
</script>
