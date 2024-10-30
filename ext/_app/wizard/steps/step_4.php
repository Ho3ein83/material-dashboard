<?php

$iconPack = amd_get_site_option( "icon_pack", amd_get_default( "icon_pack" ) );

global /** @var AMDIcon $amdIcon */
$amdIcon;

$icons = $amdIcon->getIcons();

?><h1 class="--title"><?php _ex( "Icons", "Admin", "material-dashboard" ); ?></h1>
<div>
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
        <div class="row _m4">
            <div class="col-lg-6 text-justify">
                <label class="hb-switch">
                    <input type="radio" role="switch" id="<?php echo esc_attr( "pack-$id" ); ?>" name="icon-pack"
                           value="<?php echo esc_attr( $id ); ?>" data-icon-type="<?php echo esc_attr( $type ); ?>">
                    <span class="small-text"><?php echo wp_kses( "$badge $name", amd_allowed_tags_with_attr( "br,span,a,p" ) ); ?></span>
                </label>
                <p class="color-title small-text"><?php echo esc_html( $desc ); ?></p>
                <a href="<?php echo esc_url( $url ); ?>" class="color-low" target="_blank">
                    <p class="size-sm margin-0"><?php echo esc_html( $owner ); ?></p>
                </a>
                <br>
            </div>
            <div class="col-lg-6">
	            <?php if( !empty( $thumb ) ): ?>
                    <label for="<?php echo esc_attr( "pack-$id" ); ?>" class="clickable">
                        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $name ); ?>"
                             style="border-radius:8px">
                    </label>
	            <?php endif; ?>
            </div>
        </div>
	<?php endforeach; ?>
</div>
<script>
    function _step_4(done) {
        let $iconPack = $('input[name="icon-pack"]:checked');
        let icon = $iconPack.val();
        if(!icon){
            done(false);
            return _t("control_fields");
        }
        network.clean();
        network.put("save_options", {
            "icon_pack": icon
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
    (function(){
        $(`input[name="icon-pack"][value="<?php echo esc_attr( $iconPack ); ?>"]`).prop("checked", true);
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
    }());
</script>

<!-- @formatter off -->
<style>._m4{width:60%;background:rgba(var(--amd-primary-rgb),.2);padding:16px;border-radius:10px;margin:16px auto;text-align:center;direction:ltr;}.i_text-badge{background:rgba(var(--amd-primary-rgb),.1);font-size:13px;padding:0 8px;border-radius:30px;color:var(--amd-primary)}</style>
<!-- @formatter on -->