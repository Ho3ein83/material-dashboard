<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
	return;

global /** @var AMDLoader $amdLoader */
$amdLoader;

$extensions = $amdLoader->getExtensions();

$enabled = amd_get_site_option( "extensions" );
$enabled_exp = explode( ",", $enabled );
$ext_counter = 0;

?>

<script>
    let network = new AMDNetwork();
    network.setAction(amd_conf.ajax.private);
</script>
<div class="h-20"></div>
<div class="amd-card-columns c5">
	<?php foreach( $extensions as $id => $data ): ?>
		<?php
		$private = $data["private"] ?? false;
		$deprecated = $data["deprecated"] ?? false;
		$usable = false;
		if( !empty( $data["is_usable"] ) AND is_callable( $data["is_usable"] ) )
			$usable = call_user_func( $data["is_usable"] );
		if( $private OR $deprecated )
			continue;
		$info = $data["info"] ?? null;
		$name = $info["name"] ?? "";
		$description = $info["description"] ?? "";
		$version = $info["version"] ?? "";
		$url = $info["url"] ?? "";
		$thumb = $info["thumbnail"] ?? "";
		$author = $info["author"] ?? "";
		$requirements = $info["requirements"] ?? [];
		$is_enabled = in_array( $id, $enabled_exp );
		$is_premium = $data["is_premium"] ?? false;
		$ext_counter++;
		?>
        <div class="__card <?php echo $usable ? '' : 'waiting'; ?>">
            <div class="amd-oc-card --extension-card" id="card-<?php echo $id; ?>">
                <div class="--thumb">
					<?php if( !empty( $thumb ) ): ?>
                        <img src="<?php echo esc_url( $thumb ); ?>" alt="">
					<?php endif; ?>
                    <div class="--badges">
						<?php if( $version ): ?>
                            <span class="_item"><?php echo esc_html( $version ); ?></span>
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
                        <input type="checkbox" role="switch" name="extension"
                               value="<?php echo esc_attr( $id ); ?>"<?php echo ( $is_enabled AND $usable ) ? ' checked="true"' : ''; ?>>
                        <span></span>
                    </label>
                    <h3 class="--title" id="<?php echo esc_attr( "ext-name-$id" ); ?>"><?php echo esc_html( $name ); ?></h3>
                    <p class="margin-0"><?php echo esc_html( $description ); ?></p>
					<?php if( !$usable ): ?>
                        <h4 class="color-red" style="margin:12px 0 0"><?php echo esc_html_x( "Requirements: ", "Admin", "material-dashboard" ); ?></h4>
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
	<?php if( $ext_counter <= 0 ): ?>
        <div class="col">
            <h2><?php echo esc_html_x( "No extension located", "Admin", "material-dashboard" ); ?></h2>
        </div>
	<?php endif; ?>
</div>
<script>
    (function () {
        function createChanger(id, enable) {
            network.clean();
            network.put((enable === true ? "enable" : "disable") + "_extension", id);
            return network;
        }

        $('input[name="extension"]').on("change", function () {
            let $el = $(this), id = $el.val();
            let $card = $(`#card-${id}`), enabled = $el.is(":checked");
            let $cards = $(".--extension-card");
            let $name = $(`#ext-name-${id}`);
            $el.blur();
            $cards.cardLoader();
            let failed = t => {
                $cards.cardLoader(false);
                $el.prop("checked", !enabled);
                if (t) {
                    $amd.alert(_t("s_extension").replace("%s", $name.html()), t, {
                        icon: "error"
                    });
                }
            }
            let n = createChanger(id, enabled);
            n.on.end = (resp, error) => {
                if (!error) {
                    if (resp.success) {
                        $amd.toast(resp.data.msg);
                        location.reload();
                    }
                    else {
                        failed(resp.data.msg);
                    }
                } else {
                    failed(_t("error"));
                }
            }
            n.post();
        });
    }());
</script>

<style>.amd-card-columns .__card{display:block;width:auto}</style>