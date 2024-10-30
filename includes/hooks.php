<?php

/**
 * Autoload hooks scripts
 * @return void
 * @since 1.2.0
 */
function amd_hooks_load_all(){

    # Search keys (look inside 'hooks' directory)
    # al_menu_hooks.php, al_icon_library_hooks.php

    foreach( glob( AMD_INCLUDES . "/hooks/*.php", GLOB_BRACE ) as $file ){
        if( file_exists( $file ) AND preg_match( "/\/al_(.*)\.php$/", $file ) )
            require_once( $file );
    }

}

/**
 * Fix database tables collation
 *
 * @param int $db_version
 * Database version number
 *
 * @return void
 * @since 1.0.5
 */
function amd_db_update_fix_database_collation( $db_version ){

	global $amdDB;

	$amdDB->repairTables();

	$amdDB->init( true );

}
add_action( "amd_update_db", "amd_db_update_fix_database_collation" );

/**
 * Alter database structure
 *
 * @param int $db_version
 * Database version number
 *
 * @return void
 * @since 1.2.1
 */
function amd_db_update_database_structures( $db_version ){

	global $amdDB;

	$amdDB->updateStructures( $db_version );

}
add_action( "amd_update_db", "amd_db_update_database_structures" );

/**
 * Whether to use glass morphism style in forms
 * @since 1.2.0
 */
add_filter( "amd_use_glass_morphism_form", function(){
    return amd_get_site_option( "use_glass_morphism_forms", "false" ) === "true";
} );

/**
 * Allow 'rgb' and 'rgba' expressions for inline styles
 * @since 1.2.0
 */
add_filter( "safecss_filter_attr_allow_css", function( $allow_css, $css_test_string ){
    $css_test_string = preg_replace(
        '/\b(?:rgb|rgba)(\((?:[^()]|(?1))*\))/',
        '',
        $css_test_string
    );
    return !preg_match( '%[\\\(&=}]|/\*%', $css_test_string );
}, 10, 2 );

/**
 * High priority pages
 * @since 1.2.0
 */
add_filter( "amd_high_priority_pages", function( $list ){
    $list[] = "__account_validation";
    return $list;
} );