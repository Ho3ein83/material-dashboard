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