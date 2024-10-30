<?php

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