<?php

/**
 * Icon library scripts
 * @return void
 * @since 1.1.0
 */
function amd_icon_library_script(){

    require_once( AMD_SCRIPTS_PATH . "/icon_library.js.php" );

}
add_action( "amd_custom_api_script_icon_library", "amd_icon_library_script" );

/**
 * Icon library stylesheet
 * @return void
 * @since 1.1.0
 */
function amd_icon_library_stylesheet(){

    require_once( AMD_SCRIPTS_PATH . "/icon_library.css.php" );

}
add_action( "amd_custom_api_stylesheet_icon_library", "amd_icon_library_stylesheet" );

/**
 * Icon library in admin header
 * @return void
 * @since 1.1.0
 */
function amd_admin_head_icon_library(){

    global /** @var AMDIcon $amdIcon */
    $amdIcon;

    $pack_id = $amdIcon->getCurrentIconPack();
    $icons = (array) $amdIcon->getIconPack( $pack_id )["icons"] ?? [];
    ksort( $icons );

    ?>
    <div class="amd-icon-library --hidden" id="amd-icon-library" style="display:none">
        <div class="--library">
            <span class="-close"><?php _amd_icon( "close" ); ?></span>
            <div class="text-center">
                <h2><?php esc_html_e( "Pick your icon", "material-dashboard" ); ?></h2>
            </div>
            <div class="-icons">
                <div class="-icon bg-red-im color-white-im" data-pick-icon="">
                    <?php esc_html_e( "None", "material-dashboard" ); ?>
                </div>
                <?php foreach( $icons as $icon_id => $icon ): ?>
                    <?php
                    if( amd_starts_with( $icon_id, "_" ) )
                        continue;
                    ?>
                    <div class="-icon" data-pick-icon="<?php echo esc_attr( $icon_id ); ?>">
                        <?php _amd_icon( $icon_id ); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
add_action( "amd_before_admin_head", "amd_admin_head_icon_library" );