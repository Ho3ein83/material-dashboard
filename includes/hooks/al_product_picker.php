<?php

/**
 * Product picker scripts
 * @return void
 * @since 1.2.0
 */
function amd_product_picker_script(){

    require_once( AMD_SCRIPTS_PATH . "/product_picker.js.php" );

}
add_action( "amd_custom_api_script_product_picker", "amd_product_picker_script" );

/**
 * Product picker stylesheet
 * @return void
 * @since 1.2.0
 */
function amd_product_picker_stylesheet(){

    require_once( AMD_SCRIPTS_PATH . "/product_picker.css.php" );

}
add_action( "amd_custom_api_stylesheet_product_picker", "amd_product_picker_stylesheet" );