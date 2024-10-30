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

?>
<!-- Primary color -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "Primary color", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="cp-purple">
						<?php echo esc_html_x( "Purple", "Admin", "material-dashboard" ); ?>
                        <span class="color-badge" style="background:var(--amd-color-purple)"></span>
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
                    <label for="cp-red">
						<?php echo esc_html_x( "Red", "Admin", "material-dashboard" ); ?>
                        <span class="color-badge" style="background:var(--amd-color-red)"></span>
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
                    <label for="cp-green">
						<?php echo esc_html_x( "Green", "Admin", "material-dashboard" ); ?>
                        <span class="color-badge" style="background:var(--amd-color-green)"></span>
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
                    <label for="cp-blue">
						<?php echo esc_html_x( "Blue", "Admin", "material-dashboard" ); ?>
                        <span class="color-badge" style="background:var(--amd-color-blue)"></span>
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
                    <label for="cp-orange">
						<?php echo esc_html_x( "Orange", "Admin", "material-dashboard" ); ?>
                        <span class="color-badge" style="background:var(--amd-color-orange)"></span>
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
                    <label for="cp-custom">
						<?php echo esc_html_x( "Custom", "Admin", "material-dashboard" ); ?>
                        <span class="color-badge"
                              style="<?php echo $_primaryColor ? "background:$_primaryColor" : ''; ?>"
                              id="custom-color-badge"></span>
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
</div>

<script>
    $(document).ready(function() {
        (function() {
            Coloris({
                el: '.coloris',
                swatches: [
                    '#067bc2',
                    '#84bcda',
                    '#80e377',
                    '#d6b109',
                    '#f37748',
                    '#d56062',
                    '#303030',
                    '#5142fc',
                ],
                format: "hex",
                alpha: false
            });
            let $badge = $("#custom-color-badge");
            $cp = $("#color-picker-custom");
            $cp.on("change", function() {
                $badge.css("background", $(this).val());
            });
            $amd.addEvent("on_appearance_saved", () => {
                let color = $('input[name="primary_color"]:checked').val();
                let c = $cp.val();
                $badge.css("background", c);
                return {
                    "primary_color": color === "custom" ? c : color
                };
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
<div class="hr"></div>
<div class="text-center">
    <h3><?php _ex( "Theme preview", "Admin", "material-dashboard" ); ?></h3>
    <select class="amd-admin-select" id="amd-theme">
        <option value="" selected style="display: none;"><?php echo esc_html_x( "Select one", "Admin", "material-dashboard" ); ?></option>
		<?php foreach( $_themes as $theme_id => $theme_data ): ?>
            <option value="<?php echo esc_attr( $theme_data['id'] ); ?>">
                <?php echo esc_html( $theme_data["name"] ); ?>
            </option>
		<?php endforeach; ?>
    </select>
    <div class="amd-admin-card --setting-card">
        <span class="margin-0 color-primary"><?php echo amd_icon( "light_mode" ) ?></span>
        <h3 class="--title"><?php echo esc_html_x( "Light mode", "Admin", "material-dashboard" ); ?></h3>
        <div class="--content">
            <div id="light-preview" dir="auto"></div>
        </div>
    </div>
    <div class="amd-admin-card --setting-card">
        <span class="margin-0 color-primary"><?php echo amd_icon( "dark_mode" ) ?></span>
        <h3 class="--title"><?php echo esc_html_x( "Dark mode", "Admin", "material-dashboard" ); ?></h3>
        <div class="--content">
            <div id="dark-preview" dir="auto"></div>
        </div>
    </div>
</div>
<div class="h-100"></div>
<!-- @formatter off -->
<style>.--color-box{display:flex;flex-wrap:wrap;align-items:center;justify-content:start}.--color-box>.--color{width:24px;height:24px;border-radius:4px;margin:4px;border:1px solid rgba(var(--amd-text-color-rgb),.2)}#light-preview,#dark-preview{background:var(--amd-wrapper-bg);color:var(--amd-text-color) !important;border-radius:10px;padding:8px 16px}#light-preview>*,#dark-preview>*{color:var(--amd-text-color);font-family:var(--amd-font-family) !important}</style>
<!-- @formatter on -->
<script>
    (function() {
        let $theme = $("#amd-theme");
        let theme_json = <?php echo json_encode( $_themes ); ?>;
        let previewHTML = `<h1>${_t("hello_world")}</h1>
            <p>${_t("lorem_ipsum")}</p>
			<div class="--color-box">
				<div class="--color" style="background:var(--amd-primary);"></div>
				<div class="--color" style="background:var(--amd-color-red);"></div>
				<div class="--color" style="background:var(--amd-color-green);"></div>
				<div class="--color" style="background:var(--amd-color-blue);"></div>
				<div class="--color" style="background:var(--amd-color-orange);"></div>
				<div class="--color" style="background:var(--amd-color-purple);"></div>
				<div class="--color" style="background:var(--amd-primary-x-low);"></div>
			</div>`;
        let _light = document.querySelector("#light-preview");
        _light.innerHTML = previewHTML;
        let _dark = document.querySelector("#dark-preview");
        _dark.innerHTML = previewHTML;

        function resetTheme() {
            let v = $theme.val();
            if(v.length <= 0) {
                $(_light).fadeOut(0);
                $(_dark).fadeOut(0);
                return;
            }
            let theme_light = theme_json[v].light || {}, theme_dark = theme_json[v].dark || {};
            for(let [key, value] of Object.entries(theme_light)) {
                _light.style.setProperty(key, value);
            }
            for(let [key, value] of Object.entries(theme_dark)) {
                _dark.style.setProperty(key, value);
            }
            $(_light).fadeIn();
            $(_dark).fadeIn();
        }

        resetTheme();
        $theme.on("change", () => resetTheme());
    }());
</script>