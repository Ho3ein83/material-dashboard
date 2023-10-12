<?php

$iconPack = amd_get_site_option( "icon_pack", amd_get_default( "icon_pack" ) );

$defaultSettings = array(
	"icon-pack" => $iconPack
);

global /** @var AMDIcon $amdIcon */
$amdIcon;

$icons = $amdIcon->getIcons();

?>
<?php foreach( $icons as $id => $icon ): ?>
	<?php
    if( $amdIcon->isPackHidden( $id ) )
        continue;
	$name = $icon["name"] ?? "";
	$desc = $icon["description"] ?? "";
	$owner = $icon["owner"] ?? "";
	$url = !empty( $icon["url"] ) ? $icon["url"] : "javascript: void(0)";
	$version = $icon["version"] ?? "";
	$type = $icon["icon_type"] ?? "";
	$badge = "";
	$thumb = $icon["thumbnail"] ?? "";
	$thumb = amd_replace_constants( $thumb );
    if( $type == "svg" )
        $badge = '<span class="i_text-badge">svg</span>';
	?>
    <div class="amd-admin-card --reverse --setting-card">
        <div class="--content">
            <div class="__option_grid">
                <div class="-item">
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="radio" role="switch" id="<?php echo esc_attr( "pack-$id" ); ?>" name="icon-pack"
                                   value="<?php echo esc_attr( $id ); ?>" data-icon-type="<?php echo esc_attr( $type ); ?>">
                            <span><?php echo "$badge $name"; ?></span>
                        </label>
                        <p class="color-title text-justify"><?php echo esc_html( $desc ); ?></p>
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank">
                            <p class="color-low size-sm margin-0"><?php echo esc_html( $owner ); ?></p>
                        </a>
                    </div>
                    <div class="-sub-item">
                        <label for="<?php echo esc_attr( "pack-$id" ); ?>">
							<?php if( !empty( $thumb ) ): ?>
                                <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $name ); ?>"
                                     style="border-radius:8px">
							<?php endif; ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    (function () {
        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
        let once = false;
        $(document).on("change", 'input[data-icon-type="svg"]', function(){
            if(once) return;
            if($(this).is(":checked")){
                let type = $(this).attr("data-icon-type");
                if(type === "svg"){
                    $amd.alert(_t("svg_icon"), _t("svg_icons_alert").replace("%s", '<span class="i_text-badge">svg</span>'));
                    once = true;
                }
            }
        });
        $amd.addEvent("on_appearance_saved", () => {
            let $iconPack = $('input[name="icon-pack"]:checked');
            return {
                "icon_pack": $iconPack.val()
            };
        });
    }());
</script>