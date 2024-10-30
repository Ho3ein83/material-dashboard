<?php

global /** @var AMDCore $amdCore */
$amdCore;

$main_themes = $amdCore->getThemes();
$themes = $amdCore->getThemeColors();

$_themes = [];
foreach( $main_themes as $id => $data ){
	$_id = $data["id"];
	$scope = $data["scope"];
	$_themes[$_id]["id"] = $_id;
	$_themes[$_id]["name"] = $data["name"];
	$_themes[$_id][$scope] = $data["data"];
}

$primaryColor = amd_get_site_option( "primary_color", "purple" );
$_primaryColor = amd__validate_color_code( $primaryColor );

?><h1 class="--title"><?php echo esc_html_x( "Primary color", "Admin", "material-dashboard" ); ?></h1>

<div style="width:60%;margin:auto">
    <div class="__option_grid">
        <div class="-item">
            <div class="-sub-item">
                <label for="cp-purple" class="clickable">
                    <span class="color-badge" style="background:var(--amd-color-purple)"></span>
					<?php echo esc_html_x( "Purple", "Admin", "material-dashboard" ); ?>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="cp-purple" name="primary_color"
                           value="purple" <?php echo ( $primaryColor == "purple" OR empty( $primaryColor ) ) ? "checked" : "" ?>>
                    <span></span>
                </label>
            </div>
        </div>
        <div class="-item">
            <div class="-sub-item">
                <label for="cp-red" class="clickable">
                    <span class="color-badge" style="background:var(--amd-color-red)"></span>
					<?php echo esc_html_x( "Red", "Admin", "material-dashboard" ); ?>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="cp-red" name="primary_color"
                           value="red" <?php echo $primaryColor == "red" ? "checked" : "" ?>>
                    <span></span>
                </label>
            </div>
        </div>
        <div class="-item">
            <div class="-sub-item">
                <label for="cp-green" class="clickable">
                    <span class="color-badge" style="background:var(--amd-color-green)"></span>
					<?php echo esc_html_x( "Green", "Admin", "material-dashboard" ); ?>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="cp-green" name="primary_color"
                           value="green" <?php echo $primaryColor == "green" ? "checked" : "" ?>>
                    <span></span>
                </label>
            </div>
        </div>
        <div class="-item">
            <div class="-sub-item">
                <label for="cp-blue" class="clickable">
                    <span class="color-badge" style="background:var(--amd-color-blue)"></span>
					<?php echo esc_html_x( "Blue", "Admin", "material-dashboard" ); ?>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="cp-blue" name="primary_color"
                           value="blue" <?php echo $primaryColor == "blue" ? "checked" : "" ?>>
                    <span></span>
                </label>
            </div>
        </div>
        <div class="-item">
            <div class="-sub-item">
                <label for="cp-orange" class="clickable">
                    <span class="color-badge" style="background:var(--amd-color-orange)"></span>
					<?php echo esc_html_x( "Orange", "Admin", "material-dashboard" ); ?>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="cp-orange" name="primary_color"
                           value="orange" <?php echo $primaryColor == "orange" ? "checked" : "" ?>>
                    <span></span>
                </label>
            </div>
        </div>
        <div class="-item">
            <div class="-sub-item">
                <label for="cp-custom" class="clickable">
                    <span class="color-badge"
                          style="<?php echo $_primaryColor ? "background:" . $_primaryColor : ''; ?>"
                          id="custom-color-badge"></span>
					<?php echo esc_html_x( "Custom", "Admin", "material-dashboard" ); ?>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="cp-custom" name="primary_color"
                           value="custom" <?php echo amd__validate_color_code( $primaryColor ) ? "checked" : "" ?>>
                    <span></span>
                </label>
            </div>
        </div>
    </div>
    <div style="margin:4px auto 16px;width:max-content" dir="auto" id="custom-picker">
        <label>
            <input type="text" id="color-picker-custom" value="<?php echo esc_attr( $_primaryColor ); ?>" data-coloris>
            <span></span>
        </label>
    </div>
</div>
<!-- @formatter off -->
<style>input[data-coloris]{width:120px;padding:2px 12px;margin:0 38px;outline:none;border:none;background-color:rgba(var(--amd-primary-rgb),.2);color:var(--amd-text-color);border-radius:6px;height:30px}</style>
<!-- @formatter on -->
<script>
    function _step_5(done){
        var $cp = $("#color-picker-custom");
        let color = $('input[name="primary_color"]:checked').val();
        let c = $cp.val();
        network.clean();
        network.put("save_options", {
            "primary_color": color === "custom" ? c : color
        });
        network.on.end = (resp, error) => {
            if(!error){
                if(resp.success){done();}
                else{done(false);$amd.toast(resp.data.msg);}
            }
            else{
                done(false);
                $amd.toast(_t("error"));
            }
        }
        network.post();
    }
    $(document).ready(function() {
        (function() {
            Coloris({
                el: '.coloris',
                swatches: [
                    '#067bc2',
                    '#84bcda',
                    '#80e377',
                    '#ecc30b',
                    '#f37748',
                    '#d56062'
                ],
                format: "hex",
                alpha: false
            });
            let $badge = $("#custom-color-badge");
            $cp = $("#color-picker-custom");
            $cp.on("change", function() {
                $badge.css("background", $(this).val());
            });
        }());
        $(document).on("change", 'input[name="primary_color"]', function() {
            let $cont = $("#custom-picker");
            if($(this).val() === "custom") $cont.fadeIn();
            else $cont.fadeOut();
        });
        $('input[name="primary_color"]').each(function() {
            if($(this).is(":checked")) $(this).trigger("change");
        });
    });
</script>