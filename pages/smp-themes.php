<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
	return;

global $amdLoader;

$themes = $amdLoader->getThemes();

$currentTheme = amd_get_site_option( "theme" );
$theme_counter = 0;

$shots = [];

?>
<script>
    let network = new AMDNetwork();
    network.setAction(amd_conf.ajax.private);
</script>
<div class="h-20"></div>
<h1 class="color-primary">
    <?php echo esc_html_x( "Themes", "Admin", "material-dashboard" ); ?>
    <a href="https://amatris.ir/material-dashboard/?void=demo-themes" class="small-text-im" target="_blank" rel="nofollow"><?php esc_html_e( "See demo", "material-dashboard" ); ?></a>
</h1>
<div class="cards-container">
    <div class="-cards">
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
            $url = $data["url"] ?? "";
            $thumb = $info["thumbnail"] ?? "";
            $author = $info["author"] ?? "";
            $requirements = $info["requirements"] ?? [];
            $admin_url = $data["admin_url"] ?? null;
            $admin_url_label = $data["admin_url_label"] ?? null;
            $is_active = $data["is_active"] ?? false;
            $is_premium = $data["is_premium"] ?? false;
            $theme_counter++;
            $path = $data["path"] ?? "";
            if( $url AND $path AND file_exists( $path ) ){
                $_shots = [];
                foreach( glob( "$path/{$id}_shot_*.{png,jpg}", GLOB_BRACE ) as $f )
                    $_shots[] = "$url/" . basename( $f );
                $shots[$name] = $_shots;
            }
            ?>
            <div class="__card <?php echo esc_attr( $usable ? '' : 'waiting' ); ?>">
                <div class="amd-thumb-card --theme-card" id="<?php echo esc_attr( "card-$id" ); ?>" style="--this-bg:url('<?php echo esc_url( $thumb ); ?>')">
                    <div class="--overlay"></div>
                    <div class="--content">
                        <label class="hb-switch">
                            <input type="radio" role="switch" name="theme"
                                   value="<?php echo esc_attr( $id ); ?>"<?php echo ( $is_active AND $usable ) ? ' checked="true"' : ''; ?>>
                            <span></span>
                        </label>
                        <h3 class="--title" id="<?php echo esc_attr( "theme-name-$id" ); ?>">
                            <?php echo esc_html( $name ); ?>
                            <?php if( !empty( $admin_url ) ): ?>
                                <a href="<?php echo esc_url( admin_url( "admin.php$admin_url" ) ); ?>" class="amd-admin-button --sm"><?php echo esc_html( $admin_url_label ? _x( $admin_url_label, "label", "material-dashboard-pro" ) : __( "Settings", "material-dashboard" ) ); ?></a>
                            <?php endif; ?>
                        </h3>
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
                        <div class="h-10"></div>
                        <div class="--badges">
                            <?php if( amd_theme_support( "night_mode", false, $data["id"] ) ): ?>
                                <span class="_item bg-primary-low">
                                    <?php _amd_icon( "dark_mode" ); ?>
                                    <?php echo strtolower( esc_html__( "Night mode", "material-dashboard" ) ); ?>
                                </span>
                            <?php endif; ?>
                            <?php if( amd_theme_support( "multilingual", false, $data["id"] ) ): ?>
                                <span class="_item bg-primary-low">
                                    <?php _amd_icon( "translate" ); ?>
                                    <?php echo strtolower( esc_html__( "Multilingual", "material-dashboard" ) ); ?>
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
        <?php if( $theme_counter <= 0 ): ?>
            <div class="col">
                <h2><?php echo esc_html_x( "There is no registered themes located", "Admin", "material-dashboard" ); ?></h2>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="h-20"></div>
<h1 class="color-primary">
    <?php esc_html_e( "Screenshots", "material-dashboard" ); ?>
    <button type="button" onclick="$(this).fadeOut(0);$('#themes-screenshots').fadeIn();" class="amd-admin-button --sm --text --primary w-normal"><?php esc_html_e( "Show screenshots", "material-dashboard" ); ?></button>
</h1>
<div class="h-20"></div>
<div id="themes-screenshots" style="display:none">
    <?php foreach( $shots as $name => $group ): ?>
    <h3><?php echo esc_html( $name ); ?></h3>
    <div class="_shot">
    <?php foreach( $group as $shot ): ?>
        <img src="<?php echo esc_url( $shot ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
    <?php endforeach; ?>
    </div>
    <div class="h-20"></div>
    <?php endforeach; ?>
</div>
<style>
    ._shot,
    ._shot > span {
        display: flex;
        align-items: center;
        justify-content: start;
    }
    ._shot {
        position: relative;
        flex-direction: row;
        flex-wrap: nowrap;
        width: 100%;
        border-radius: 0;
        overflow: auto;
        gap: 16px;
    }
    ._shot > img {
        display: block;
        border-radius: 16px;
    }
</style>
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

<!-- @formatter off -->
<style>.amd-thumb-card,.amd-thumb-card .--badges,.amd-thumb-card .--badges>._item{position:relative;display:flex;align-items:center;justify-content:center;flex-direction:row}.amd-card-columns .__card{display:block;width:auto}.amd-thumb-card{color:#fff;background:var(--amd-wrapper-bg);background-image:var(--this-bg);background-size:cover;background-position:center;padding:8px;border-radius:8px;overflow:hidden;min-height:250px;max-width:500px;flex:1}.amd-thumb-card>.--overlay{position:absolute;background:linear-gradient(0,rgba(0,0,0,.9) 0,rgba(0,0,0,.7) 100%);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);left:0;top:0;width:100%;height:100%;z-index:0}.amd-thumb-card>.--content{padding:8px;width:100%;height:100%;z-index:1}.amd-thumb-card .color-red{color:red}.amd-thumb-card .--title{color:currentColor}.amd-thumb-card>.--thumb{display:none}.amd-thumb-card .--badges{justify-content:start;flex-wrap:wrap;width:100%;margin:8px;gap:8px;z-index:2}.amd-thumb-card .--badges>._item{background:var(--amd-primary);border-radius:30px;padding:4px 8px;min-width:80px;height:20px;gap:4px}.amd-thumb-card .--badges>._item ._icon_{width:16px;height:auto}.cards-container>.-cards{display:flex;flex-wrap:wrap;align-items:center;justify-content:start;gap:16px}.cards-container .hb-switch>input:not(:checked){background-color:rgba(255, 255, 255,.5) !important}</style>
<!-- @formatter on -->