<?php

$hide_users_rest_api = amd_get_site_option( "hide_users_rest_api", "true" );

$defaultSettings = array(
	"hide_users_rest_api" => $hide_users_rest_api
);

?>

<!-- Resp API -->
<div class="amd-admin-card --setting-card">
	<h3 class="--title"><?php echo esc_html_x( "Rest API", "Admin", "material-dashboard" ); ?></h3>
	<div class="--content">
		<div class="__option_grid">
			<div class="-item">
				<div class="-sub-item">
					<label for="hide-login">
						<?php echo esc_html_x( "Disable 'users' rest API", "Admin", "material-dashboard" ); ?>
					</label>
				</div>
				<div class="-sub-item">
					<label class="hb-switch">
						<input type="checkbox" role="switch" name="hide_users_rest_api" value="true" id="hide-rest">
						<span></span>
					</label>
				</div>
				<div class="-sub-item --full">
					<p class="color-primary text-justify">
                        <?php echo esc_html_x( "By enabling this item only admins can send request to users endpoints, it'll help your site to be protected from some brute force attacks", "Admin", "material-dashboard" ); ?>.
                        <?php echo amd_doc_url( "users_rest", true ); ?>
                    </p>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    (function () {
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
        $amd.addEvent("on_settings_saved", () => {
            let $hide_users_rest_api = $("#hide-rest");
            return {
                "hide_users_rest_api": $hide_users_rest_api.is(":checked") ? "true" : "false"
            }
        });
    }());
</script>
