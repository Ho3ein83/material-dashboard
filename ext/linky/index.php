<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Extension constants
define( 'AMD_EXT_LINKY_PATH', __DIR__ );
define( 'AMD_EXT_LINKY_URL', AMD_EXT_URL . "/linky" );
define( 'AMD_EXT_LINKY_VIEW', AMD_EXT_LINKY_PATH . '/view' );
define( 'AMD_EXT_LINKY_CORE', AMD_EXT_LINKY_PATH . '/core' );
define( 'AMD_EXT_LINKY_TEMPLATES', AMD_EXT_LINKY_PATH . '/templates' );

require_once __DIR__ . "/ajax.php";

/**
 * Just for translations and extension availability check
 * @return void
 * @since 1.3.0
 */
function amd_ext_linky(){
    _x( "Amatris", "linky", "material-dashboard" );
    _x( "Linky", "linky", "material-dashboard" );
    _x( "Allow users to invite their friends and build up their network", "linky", "material-dashboard" );
}

/**
 * Print admin menu content
 * @return void
 * @since 1.2.0
 */
function amd_ext_linky_admin_page(){
    require_once AMD_EXT_LINKY_VIEW . "/admin.php";
}

/**
 * Get user friends list
 * @param bool $export_users
 * Whether to export the users or {@see AMDUser::export() export} them
 * @param int|null $user_id
 * User ID or null for current user
 * @param int $limit
 * The maximum number of items you want to have in the list, pass 0 for no limit. Default is 0
 *
 * @return AMDUser[]|array[]|array
 * Empty user if no friends found, otherwise user object list or
 * exported user array list based on $export_users parameter
 * @since 1.2.0
 */
function amd_ext_linky_get_friends_list( $export_users = false, $user_id = null, $limit = 0 ) {
    global $amdLinkyCore;
    return $amdLinkyCore->get_friends_list( $export_users, $user_id, $limit );
}

/**
 * Get the number of invited users by specific user
 * @param int|null $user_id
 * User ID or null for current user
 * @param bool $use_cache
 * Whether to use cache or get the invitation list directly from database
 *
 * @return int
 * The number of invited users
 * @since 1.2.0
 */
function amd_get_user_invited_users_count( $user_id = null, $use_cache = true ){
    global $amdCache;
    if( $use_cache AND $v = $amdCache->cacheExists( "ext_linky_invites" ) )
        return intval( $v );
    $invites = count( amd_ext_linky_get_friends_list( false, $user_id ) );
    if( $use_cache )
        $amdCache->setCache( "ext_linky_invites", $invites );
    return $invites;
}

/**
 * Check if a referral code is taken by a user or is free
 * @param string $referral_code
 * Referral code to search for
 *
 * @return false|int
 * False if referral code is free, otherwise the user ID of the referral code owner
 * @since 1.2.0
 */
function amd_ext_linky_referral_exist( $referral_code ){
    global $amdLinkyCore;
    return $amdLinkyCore->referral_exist( $referral_code );
}

/**
 * Generate a referral key
 * @param bool $unique
 * Whether to regenerate referral key if it's already in use by a user
 *
 * @return string
 * Referral key
 * @sicne 1.2.0
 */
function amd_ext_linky_generate_referral( $unique = true ) {
    global $amdLinkyCore;
    return $amdLinkyCore->generate_referral( $unique );
}

/**
 * Get user referral code
 * @param int|null $user_id
 * User ID or null for current user
 * @param bool $use_cache
 * Whether to use cache for storing referral code
 *
 * @return string
 * The referral code of user
 * @since 1.2.0
 */
function amd_ext_linky_get_user_referral_code( $user_id = null, $use_cache = true ) {
    global $amdLinkyCore;
    return $amdLinkyCore->get_user_referral_code( $user_id, $use_cache );
}

/**
 * Get user object by referral code
 * @param string $referral_code
 * Referral code
 * @return AMDUser|false
 * User object on success, false on failure
 * @sicne 1.2.0
 */
function amd_ext_linky_get_user_by_referral( $referral_code ){
    global $amdLinkyCore;
    return $amdLinkyCore->get_user_by_referral( $referral_code );
}

/**
 * Check if referral code is valid or not
 * @param string $referral_code
 * Referral code
 * @param bool $required
 * If false, referral code can be empty string and still be valid,
 * otherwise it must match the referral code pattern, default is false
 * @param bool $check_for_user
 * Whether to check if the referral code belongs to a user, default is false
 *
 * @return bool
 * True if referral code is valid, otherwise false
 * @since 1.2.0
 */
function amd_ext_linky_validate_referral_code( $referral_code, $required = false, $check_for_user = false ){
    global $amdLinkyCore;
    return $amdLinkyCore->validate_referral( $referral_code, $required, $check_for_user );
}

/**
 * Register custom site options
 * @return void
 * @since 1.2.0
 */
function amd_ext_linky_allowed_options(){

    # Register setting tabs
    /*do_action( "amd_setting_tabs", array(
        "ext_linky" => array(
            "id" => "ext_linky",
            "title" => esc_html_x( "Invite", "linky menu title", "material-dashboard" ),
            "icon" => "people",
            "page" => AMD_EXT_LINKY_VIEW . '/admin_tab_settings.php',
            "priority" => 61
        )
    ) );*/

}
add_action( "init", "amd_ext_linky_allowed_options" );
add_action( "admin_init", "amd_ext_linky_allowed_options" );

add_action( "amd_dashboard_init", function(){

    # Register lazy page
    amd_register_lazy_page( "ext_linky", _x( "Invited friends", "linky", "material-dashboard" ), AMD_EXT_LINKY_VIEW . "/page_ext_linky.php", "people" );

    # Add dashboard cards (content cards)
    do_action( "amd_add_dashboard_card", array(
        "ext_linky" => array(
            "type" => "content_card",
            "page" => AMD_EXT_LINKY_VIEW . "/cards/my_friends.php",
            "priority" => 7
        )
    ) );

} );

add_action( "amd_init_sidebar_items", function() {

    # Add menu item
    do_action( "amd_add_dashboard_sidebar_menu", array(
        "ext_linky" => array(
            "text" => _x( "Friends", "linky", "material-dashboard" ),
            "icon" => "people",
            "void" => "ext_linky",
            "url" => "?void=ext_linky",
            "priority" => 15
        )
    ) );

});

# Add translation strings for frontend
add_action( "amd_init_translation", function(){

    do_action( "amd_add_front_string", array(
        "referral_code" => _x( "Referral code", "linky", "material-dashboard" ),
    ) );

} );

function amd_ext_linky_after_user_edit_profile_items( $u ){

    if( !amd_is_admin() )
        return;

    $invites = amd_get_user_invited_users_count();

    ?>
    <tr>
        <th><?php echo esc_html_x( "Invited users", "linky", "material-dashboard" ); ?></th>
        <td><?php echo esc_html( $invites ); ?></td>
    </tr>
    <?php

}
add_action( "amd_after_user_edit_profile_items", "amd_ext_linky_after_user_edit_profile_items" );

function amd_ext_linky_admin_users_statistics(){

    global $amdLinkyCore;

    $last = apply_filters( "amd_ext_linky_users_statistics_invites_duration", 2592000 ); # 1 month
    $invites = $amdLinkyCore->filter_invites( function( $row_data, $meta, $invited_user ) use ( $last ) {
        $invited_time = $meta["joined_$invited_user"] ?? 0;
        return $invited_time >= time() - $last;
    } );

    $invites_count = 0;
    foreach( $invites as $item )
        $invites_count += count( $item );

    ?>
    <div class="-item">
        <div class="-sub-item"><?php echo esc_html_x( "Invited users in this month", "linky", "material-dashboard" ); ?></div>
        <div class="-sub-item"><?php echo esc_html( $invites_count ); ?></div>
    </div>
    <?php
}
add_action( "amd_admin_between_users_statistics", "amd_ext_linky_admin_users_statistics" );

# Load hooks
require_once AMD_EXT_LINKY_PATH . "/hooks.php";

# Load cores
require_once AMD_EXT_LINKY_CORE . "/init.php";
amd_ext_linky_require_all();