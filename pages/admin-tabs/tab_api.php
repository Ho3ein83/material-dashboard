<?php

$api_enabled = amd_get_site_option( "api_enabled" ) == "true";
$api_url = amd_get_api_url();

$defaultSettings = array(
	"api_enabled" => $api_enabled,
);

?>

<!-- API -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "API", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="api-enabled">
                        <?php echo esc_html_x( "Enable API", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" id="api-enabled" role="switch" name="api_enabled" value="true">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "Develop your own application and connect it with your dashboard", "Admin", "material-dashboard" ); ?>. <?php echo amd_doc_url( "", true, "(" . esc_html_x( "Documentation", "Admin", "material-dashboard" ) . ")" ) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API URL -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "API URL", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <code class="color-primary clickable ellipsis" id="api-url"><?php echo esc_url( $api_url ); ?></code>
                </div>
                <div class="-sub-item">
                    <button class="amd-admin-button --sm" id="copy-api-url"><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
                    <button class="amd-admin-button --sm --primary --text" id="open-api-url"><?php esc_html_e( "Open", "material-dashboard" ); ?></button>
                </div>
                <div class="-sub-item --full">
                    <p class="color-red _show_on_api_disabled_"><?php echo esc_html_x( "API is disabled and this URL may not work properly", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
        let $api_enabled = $("#api-enabled"), $url = $("#api-url");
        let url = $url.html();
        let recheck = () => {
            if($api_enabled.is(":checked")){
                $("._show_on_api_enabled_").fadeIn();
                $("._show_on_api_disabled_").fadeOut();
            }
            else{
                $("._show_on_api_enabled_").fadeOut();
                $("._show_on_api_disabled_").fadeIn();
            }
        }
        recheck();
        $api_enabled.on("change", recheck);
        $url.click(() => $amd.copy(url, true));
        $("#copy-api-url").click(() => $amd.copy(url, true));
        $("#open-api-url").click(() => window.open(url, "_blank"));
        $amd.addEvent("on_settings_saved", () => {
            return {
                "api_enabled": $api_enabled.is(":checked") ? "true" : "false"
            };
        });
    }());
</script>