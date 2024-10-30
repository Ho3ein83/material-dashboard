<?php

global /** @var array $AMD_CORE_LOAD */
$AMD_CORE_LOAD;

$AMD_CORE_LOAD = [];

/**
 * Initialize Database core
 * @return void
 * @since 1.0.0
 */
function amd_require_db(){

	require_once( 'AMD_DB/AMD_DB.php' );

	global /** @var AMD_DB $amdDB */
	$amdDB;

	$amdDB = new AMD_DB();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["database"] = true;

}

/**
 * Initialize main core
 * @return void
 * @since 1.0.0
 */
function amd_require_core(){

	require_once( 'AMDCore/AMDCore.php' );

	global /** @var AMDCore $amdCore */
	$amdCore;

	$amdCore = new AMDCore();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["core"] = true;

}

/**
 * Initialize cache core
 * @return void
 * @since 1.0.0
 */
function amd_require_cache(){

	require_once( 'AMDCache/AMDCache.php' );

	global /** @var AMDCache $amdCache */
	$amdCache;

	$amdCache = new AMDCache();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["cache"] = true;

}

/**
 * Initialize network
 * @return void
 * @since 1.0.0
 */
function amd_require_network(){

	require_once( 'AMDNetwork/AMDNetwork.php' );

	global /** @var AMDNetwork $amdNet */
	$amdNet;

	$amdNet = new AMDNetwork();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["network"] = true;

}

/**
 * Initialize <b>Si</b>mp<b>l</b>e <b>U</b>ser-manager
 * @return void
 * @since 1.0.0
 */
function amd_require_silu(){

	require_once( 'AMDSilu/AMDSilu.php' );

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	$amdSilu = new AMDSilu();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["silu"] = true;

}

/**
 * Initialize calendar
 * @return void
 * @since 1.0.0
 */
function amd_require_calendar(){

	require_once( 'AMDCalendar/AMDCalendar.php' );

	global /** @var AMDCalendar $amdCal */
	$amdCal;

	$amdCal = new AMDCalendar();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["calendar"] = true;

}

/**
 * Initialize icon pack
 * @return void
 * @since 1.0.0
 */
function amd_require_icon_pack(){

	require_once( 'AMDIcon/AMDIcon.php' );

	global /** @var AMDIcon $amdIcon */
	$amdIcon;

	$amdIcon = new AMDIcon();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["icon"] = true;

}

/**
 * Initialize firewall
 * @return void
 * @since 1.0.0
 */
function amd_require_warner(){

	require_once( 'AMDWarner/AMDWarner.php' );

	global /** @var AMDWarner $amdWarn */
	$amdWarn;

	$amdWarn = new AMDWarner();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["warner"] = true;

}

/**
 * Initialize file explorer
 * @return void
 * @since 1.0.0
 */
function amd_require_explorer(){

	require_once( 'AMDExplorer/AMDExplorer.php' );

	global /** @var AMDExplorer $amdExp */
	$amdExp;

	$amdExp = new AMDExplorer();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["explorer"] = true;

}

/**
 * Initialize frontend dashboard core
 * @return void
 * @since 1.0.0
 */
function amd_require_dashboard(){

	require_once( 'AMDDashboard/AMDDashboard.php' );

	global /** @var AMDDashboard $amdDashboard */
	$amdDashboard;

	$amdDashboard = new AMDDashboard();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["dashboard"] = true;

}

/**
 * Initialize themes and extension loader core
 * @return void
 * @since 1.0.0
 */
function amd_require_loader(){

	require_once( 'AMDLoader/AMDLoader.php' );

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	$amdLoader = new AMDLoader();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["loader"] = true;

}

/**
 * Load firewall core
 * @return void
 * @since 1.0.0
 */
function amd_require_firewall(){

	require_once( 'AMDFirewall/AMDFirewall.php' );

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	$amdWall = new AMDFirewall();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["firewall"] = true;

}

/**
 * Load tasks core
 * @return void
 * @since 1.0.8
 */
function amd_require_tasks(){

	require_once( 'AMDTasks/AMDTasks.php' );

	global /** @var AMDTasks $amdTasks */
	$amdTasks;

	$amdTasks = new AMDTasks();

	global $AMD_CORE_LOAD;
	$AMD_CORE_LOAD["tasks"] = true;

}

/**
 * Check if core is loaded
 *
 * @param string $core
 * Core name: "firewall", "database", "cache", etc.
 *
 * @return bool
 * Whether core is loaded or not
 * @since 1.0.0
 */
function amd_core_loaded( $core ){

	global $AMD_CORE_LOAD;

	return (bool) ( $AMD_CORE_LOAD[$core] ?? "" );

}

/**
 * Initialize important cores
 * @return void
 * @since 1.0.0
 */
function amd_require_all(){

	do_action( "amd_before_cores_init" );

	amd_require_db();

	amd_require_core();

	amd_require_cache();

	amd_require_firewall();

	amd_require_network();

	amd_require_silu();

	amd_require_tasks();

	amd_require_calendar();

	amd_require_icon_pack();

	amd_require_warner();

	amd_require_explorer();

	amd_require_loader();

	amd_require_dashboard();

	do_action( "amd_after_cores_init" );

}