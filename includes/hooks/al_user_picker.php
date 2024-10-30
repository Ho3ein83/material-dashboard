<?php

/**
 * User picker scripts
 * @return void
 * @since 1.2.0
 */
function amd_user_picker_script(){

    require_once( AMD_SCRIPTS_PATH . "/user_picker.js.php" );

}
add_action( "amd_custom_api_script_user_picker", "amd_user_picker_script" );

/**
 * User library stylesheet
 * @return void
 * @since 1.2.0
 */
function amd_user_picker_stylesheet(){

    require_once( AMD_SCRIPTS_PATH . "/user_picker.css.php" );

}
add_action( "amd_custom_api_stylesheet_user_picker", "amd_user_picker_stylesheet" );

/**
 * User picker in admin header
 * @return void
 * @since 1.2.0
 */
function amd_admin_head_user_picker(){

    global /** @var AMDIcon $amdIcon */
    $amdIcon;

    $pack_id = $amdIcon->getCurrentIconPack();
    $icons = (array) $amdIcon->getIconPack( $pack_id )["icons"] ?? [];
    ksort( $icons );

    ?>
    <div class="amd-user-picker" id="amd-user-picker-root" style="display:none">
        <div class="--overlay"></div>
        <div class="--content">
            <div class="-title">
                <div class="_close clickable text-start">
                    <button type="button" class="amd-admin-button --red --text _close_picker_"><?php esc_html_e( "Close", "material-dashboard" ); ?></button>
                </div>
                <h3 class="margin-0 text-center">
                    <?php esc_html_e( "Choose from your site users", "material-dashboard" ); ?>
                    (<span class="_selected_users_">0</span>)
                </h3>
                <div class="text-end">
                    <button type="button" class="amd-admin-button _pick_selected_users_"><?php esc_html_e( "Select users", "material-dashboard" ); ?></button>
                </div>
            </div>
            <div class="-content">
                <div class="text-center">
                    <input type="text" class="amd-admin-input _input" placeholder="<?php esc_attr_e( "User ID, username, email, etc.", "material-dashboard" ); ?>" style="width:250px;margin:0 0 8px;" aria-label="">
                    <br>
                    <button type="button" class="amd-admin-button _add_btn"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
                    <button type="button" class="amd-admin-button _search_btn --primary --text"><?php esc_html_e( "Search", "material-dashboard" ); ?></button>
                    <button type="button" class="amd-admin-button _continue_btn --blue --text"><?php esc_html_e( "Continue", "material-dashboard" ); ?></button>
                    <div class="h-10"></div>
                    <p class="tiny-text _list_log"><?php esc_html_e( "Click on items to remove", "material-dashboard" ); ?></p>
                    <div class="_items_list" style="display:flex;align-items:center;justify-content:center;gap:8px"></div>
                    <div class="h-10"></div>
                    <progress class="hb-progress-circular _progress"></progress>
                    <table class="adp-admin-table _table" style="width:100%;background:var(--amd-primary-x-low);border-radius:12px">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( "User ID", "material-dashboard" ); ?></th>
                            <th><?php esc_html_e( "Fullname", "material-dashboard" ); ?></th>
                            <th><?php esc_html_e( "Select", "material-dashboard-pro" ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="_table_items"></tbody>
                    </table>
                    <table class="adp-admin-table _selection_table" style="width:100%;background:var(--amd-primary-x-low);border-radius:12px">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( "User ID", "material-dashboard" ); ?></th>
                            <th><?php esc_html_e( "Fullname", "material-dashboard" ); ?></th>
                            <th><?php esc_html_e( "Select", "material-dashboard-pro" ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="_selection_table_items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action( "amd_before_admin_head", "amd_admin_head_user_picker" );