<?php

/**
 * Load main core for this extension
 * @return void
 * @since 1.2.0
 */
function amd_ext_linky_require_core(){

    global $amdLinkyCore;

    require_once AMD_EXT_LINKY_CORE . "/AMD_EXT_Linky.php";

    $amdLinkyCore = new AMD_EXT_Linky();

}

/**
 * Load all required cores
 * @return void
 * @since 1.2.0
 */
function amd_ext_linky_require_all(){

    amd_ext_linky_require_core();

}