<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
	return;

global /** @var AMDLoader $amdLoader */
$amdLoader;

$themes = $amdLoader->getThemes();

$currentTheme = amd_get_site_option( "theme" );
$theme_counter = 0;
?>
<script>
    let network = new AMDNetwork();
    network.setAction(amd_conf.ajax.private);
</script>
<div class="h-20"></div>
<div class="amd-card-columns c5">
	<?php foreach( $themes as $id => $data ): ?>
		<?php
		$deprecated = $data["deprecated"] ?? false;
		$usable = false;
		if( !empty( $data["is_usable"] ) AND is_callable( $data["is_usable"] ) )
			$usable = call_user_func( $data["is_usable"] );
		if( $deprecated )
			continue;
		$info = $data["info"] ?? null;
		$name = $info["name"] ?? "";
		$description = $info["description"] ?? "";
		$version = $info["version"] ?? "";
		$url = $info["url"] ?? "";
		$thumb = $info["thumbnail"] ?? "";
		$author = $info["author"] ?? "";
		$requirements = $info["requirements"] ?? [];
		$is_active = $data["is_active"] ?? false;
		$is_premium = $data["is_premium"] ?? false;
		$theme_counter++;
		?>
        <div class="__card <?php echo esc_attr( $usable ? '' : 'waiting' ); ?>">
            <div class="amd-oc-card --theme-card" id="<?php echo esc_attr( "card-$id" ); ?>">
                <div class="--thumb">
					<?php if( !empty( $thumb ) ): ?>
                        <img src="<?php echo esc_url( $thumb ); ?>" alt="">
					<?php endif; ?>
                    <div class="--badges">
						<?php if( $version ): ?>
                            <span class="_item"><?php echo esc_html( $version ); ?></span>
						<?php endif; ?>
                        <?php if( amd_theme_support( "night_mode", false, $data["id"] ) ): ?>
                            <span class="_item">
                                <?php _amd_icon( "dark_mode" ); ?>
                                <?php echo strtolower( esc_html__( "Night mode", "material-dashboard" ) ); ?>
                            </span>
						<?php endif; ?>
	                    <?php if( $is_premium ): ?>
                            <span class="_item">
                                <?php _amd_icon( "star" ); ?>
                                <?php esc_html_e( "Premium", "material-dashboard" ); ?>
                            </span>
	                    <?php endif; ?>
                    </div>
                </div>
                <div class="--content">
                    <label class="hb-switch">
                        <input type="radio" role="switch" name="theme"
                               value="<?php echo esc_attr( $id ); ?>"<?php echo ( $is_active AND $usable ) ? ' checked="true"' : ''; ?>>
                        <span></span>
                    </label>
                    <h3 class="--title" id="<?php echo esc_attr( "theme-name-$id" ); ?>"><?php echo esc_html( $name ); ?></h3>
                    <p class="margin-0"><?php echo esc_html( $description ); ?></p>
					<?php if( !$usable ): ?>
                        <h4 class="color-red"
                            style="margin:12px 0 0"><?php _ex( "Requirements: ", "Admin", "material-dashboard" ); ?></h4>
                        <ul class="mbt-10">
							<?php foreach( $requirements as $name => $requirement ): ?>
                                <?php if( amd_check_requirements( $requirement ) ) continue; ?>
                                <li><?php echo esc_html( $name ); ?></li>
							<?php endforeach; ?>
                        </ul>
					<?php endif; ?>
                    <p><i class="tiny-text color-low">- <?php echo esc_html( $author ); ?></i></p>
                </div>
            </div>
        </div>
	<?php endforeach; ?>
	<?php if( $theme_counter <= 0 ): ?>
        <div class="col">
            <h2><?php echo esc_html_x( "There is no registered themes located", "Admin", "material-dashboard" ); ?></h2>
        </div>
	<?php endif; ?>
</div>
<script>
    (function() {
        $('input[name="theme"]').on("change", function() {
            let $el = $(this), id = $el.val();
            if(!$el.is(":checked")) return;
            let $card = $(`#card-${id}`);
            let $cards = $(".--theme-card");
            let $name = $(`#theme-name-${id}`);
            $el.blur();
            $cards.cardLoader();
            let failed = t => {
                $el.prop("checked", !enabled);
                if(t) {
                    $amd.alert($name.html(), t, {
                        icon: "error"
                    });
                }
            }
            network.clean();
            network.put("switch_theme", id);
            network.on.end = (resp, error) => {
                $cards.cardLoader(false);
                if(!error) {
                    if(resp.success)
                        location.reload()
                    else
                        failed(resp.data.msg);
                }
                else {
                    failed(_t("error"));
                }
            }
            network.post();
        });
    }());
</script>