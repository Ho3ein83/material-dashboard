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

# since 1.2.0
do_action( "amd_admin_extension_page" );

?>
<script>
    let network = new AMDNetwork();
    network.setAction(amd_conf.ajax.private);
</script>
<div class="h-20"></div>
<h1 class="color-primary"><?php echo esc_html_x( "Extensions", "Admin", "material-dashboard" ); ?></h1>
<div class="cards-container">
    <div class="-cards">
        <?php foreach( $extensions as $id => $data ): ?>
            <?php
            $private = $data["private"] ?? false;
            $deprecated = $data["deprecated"] ?? false;
            $compatible = $data["compatible"] ?? null;
            $compatible_premium = $data["compatible_premium"] ?? null;
            $usable = false;
            $is_compatible = true;
            $is_premium_compatible = true;
            if( !empty( $data["is_usable"] ) AND is_callable( $data["is_usable"] ) )
                $usable = call_user_func( $data["is_usable"] );
            if( !empty( $compatible ) ){
                $v = trim( preg_replace( "/[<=>]/", "", $compatible ) );
                $e = trim( str_replace( $v, "", $compatible ) );
                $e = empty( $e ) ? ">=" : $e;
                if( !version_compare( AMD_VER, $v, $e ) ) {
                    $usable = false;
                    $is_compatible = false;
                }
            }
            if( !empty( $compatible_premium ) ){
                if( function_exists( "adp_plugin" ) ) {
                    $v = trim( preg_replace( "/[<=>]/", "", $compatible_premium ) );
                    $e = trim( str_replace( $v, "", $compatible_premium ) );
                    $e = empty( $e ) ? ">=" : $e;
                    if( !version_compare( adp_plugin()["Version"] ?? "", $v, $e ) ) {
                        $usable = false;
                        $is_premium_compatible = false;
                    }
                }
            }
            if( $private or $deprecated )
                continue;
            $info = $data["info"] ?? null;
            $name = $info["name"] ?? "";
            $description = $info["description"] ?? "";
            $version = $info["version"] ?? "";
            $url = $info["url"] ?? "";
            $thumb = $info["thumbnail"] ?? "";
            $author = $info["author"] ?? "";
            $since = $data["since"] ?? null;
            $requirements = $info["requirements"] ?? [];
            $admin_url = $data["admin_url"] ?? null;
            $admin_url_label = $data["admin_url_label"] ?? null;
            $is_enabled = in_array( $id, $enabled_exp );
            $is_premium = $data["is_premium"] ?? false;
            $text_domain = $data["text_domain"] ?? "material-dashboard";

            $ext_counter++;
            if( !is_iterable( $requirements ) )
                $requirements = [];
            ?>
            <div class="__card">
                <div class="amd-thumb-card --extension-card" id="<?php echo esc_attr( "card-$id" ); ?>" style="--this-bg:url('<?php echo esc_url( $thumb ); ?>')">
                    <div class="--overlay"></div>
                    <div class="--content">
                        <label class="hb-switch" <?php echo $usable ? '' : 'style="display:none"'; ?>>
                            <input type="checkbox" role="switch" name="extension"
                                   value="<?php echo esc_attr( $id ); ?>"<?php echo ( $is_enabled and $usable ) ? ' checked="true"' : ''; ?>>
                            <span></span>
                        </label>
                        <h3 class="--title" id="<?php echo esc_attr( "ext-name-$id" ); ?>">
                            <?php echo esc_html( $name ); ?>
                            <?php if( !empty( $admin_url ) && $is_enabled ): ?>
                                <a href="<?php echo esc_url( admin_url( "admin.php$admin_url" ) ); ?>" class="amd-admin-button --sm"><?php echo esc_html( $admin_url_label ? _x( $admin_url_label, "label", "material-dashboard-pro" ) : __( "Settings", "material-dashboard" ) ); ?></a>
                            <?php endif; ?>
                        </h3>
                        <p class="margin-0"><?php echo esc_html( $description ); ?></p>
                        <?php if( !$usable ): ?>
                            <h4 class="color-red" style="margin:12px 0 0"><?php echo esc_html_x( "Requirements: ", "Admin", "material-dashboard" ); ?></h4>
                            <ul class="mbt-10">
                                <?php if( !$is_compatible ): ?>
                                <li><?php echo amd_version_requirement( $compatible, esc_html__( "Material Dashboard", "material-dashboard" ) ); ?></li>
                                <?php endif; ?>
                                <?php if( !$is_premium_compatible ): ?>
                                <li><?php echo amd_version_requirement( $compatible_premium, esc_html__( "Material Dashboard Pro", "material-dashboard" ) ); ?></li>
                                <?php endif; ?>
                                <?php foreach( $requirements as $name => $requirement ): ?>
                                    <?php if( amd_check_requirements( $requirement ) ) continue; ?>
                                    <li><?php echo esc_html( $name ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="h-10"></div>
                        <div class="--badges">
                            <?php if( $since and $since == AMD_VER ): ?>
                            <span class="_item bg-none-im">
                                <?php _amd_icon( "heart" ); ?>
                                <?php esc_html_e( "New", "material-dashboard" ); ?>!
                            </span>
                            <?php endif; ?>
                            <?php if( $is_premium ): ?>
                            <span class="_item bg-primary-low">
                                <?php _amd_icon( "star" ); ?>
                                <?php esc_html_e( "Premium", "material-dashboard" ); ?>
                            </span>
                            <?php endif; ?>
                            <?php if( $version ): ?>
                                <span class="_item bg-green-low"><?php echo esc_html( $version ); ?></span>
                            <?php endif; ?>
                            <span class="_item bg-blue-low"><?php echo esc_html( $author ); ?></span>
                        </div>
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

<!-- @formatter off -->
<style>.amd-thumb-card,.amd-thumb-card .--badges,.amd-thumb-card .--badges>._item{position:relative;display:flex;align-items:center;justify-content:center;flex-direction:row}.amd-card-columns .__card{display:block;width:auto}.amd-thumb-card{color:#fff;background:var(--amd-wrapper-bg);background-image:var(--this-bg);background-size:cover;background-position:center;padding:8px;border-radius:8px;overflow:hidden;min-height:250px;max-width:500px;flex:1}.amd-thumb-card>.--overlay{position:absolute;background:linear-gradient(0,rgba(0,0,0,.9) 0,rgba(0,0,0,.7) 100%);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);left:0;top:0;width:100%;height:100%;z-index:0}.amd-thumb-card>.--content{padding:8px;width:100%;height:100%;z-index:1}.amd-thumb-card .color-red{color:red}.amd-thumb-card .--title{color:currentColor}.amd-thumb-card>.--thumb{display:none}.amd-thumb-card .--badges{justify-content:start;flex-wrap:wrap;width:100%;margin:8px;gap:8px;z-index:2}.amd-thumb-card .--badges>._item{background:var(--amd-primary);border-radius:30px;padding:4px 8px;min-width:80px;height:20px;gap:4px}.amd-thumb-card .--badges>._item ._icon_{width:16px;height:auto}.cards-container>.-cards{display:flex;flex-wrap:wrap;align-items:center;justify-content:start;gap:16px}.cards-container .hb-switch>input:not(:checked){background-color:rgba(255, 255, 255,.5) !important}</style>
<!-- @formatter on -->
