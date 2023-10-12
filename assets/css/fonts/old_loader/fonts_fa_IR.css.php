<?php
/**
 * Persian digit fonts
 */

# Destroy the execution if it isn't loaded from this plugin
defined( 'AMD_PATH' ) OR die();

if( isset( $_GET["screen"] ) AND $_GET["screen"] == "admin" ){
    require_once __DIR__ . "/fonts_en_US.css.php";
    return;
}

# Fonts base URL
$baseURL = AMD_CSS . "/fonts";

?>/**
 * Kalameh font is considered a proprietary software.
 * To gain information about the laws regarding the use of these fonts, please visit fontiran.com
 * This set of fonts are used in this project under the license: 342VUY
 */

/* Shabnam Persian digits */
@font-face {
    font-family: 'shabnam';
    font-weight: normal;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/shabnam/fa_normal/shabnam_fa_normal.woff2") format("woff2"),
         url("<?php echo $baseURL; ?>/shabnam/fa_normal/shabnam_fa_normal.woff") format("woff"),
         url("<?php echo $baseURL; ?>/shabnam/fa_normal/shabnam_fa_normal.ttf") format("truetype");
}
@font-face {
    font-family: 'shabnam';
    font-weight: bold;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/shabnam/fa_bold/shabnam_fa_bold.woff2") format("woff2"),
         url("<?php echo $baseURL; ?>/shabnam/fa_bold/shabnam_fa_bold.woff") format("woff"),
         url("<?php echo $baseURL; ?>/shabnam/fa_bold/shabnam_fa_bold.ttf") format("truetype");
}

/* kalameh Persian digits */
@font-face {
    font-family: 'kalameh';
    font-weight: normal;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/kalameh_old/fa_normal/kalameh_fa_normal.woff2") format("woff2"),
         url("<?php echo $baseURL; ?>/kalameh_old/fa_normal/kalameh_fa_normal.woff") format("woff"),
         url("<?php echo $baseURL; ?>/kalameh_old/fa_normal/kalameh_fa_normal.ttf") format("truetype");
}
@font-face {
    font-family: 'kalameh';
    font-weight: bold;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/kalameh_old/fa_bold/kalameh_fa_bold.woff2") format("woff2"),
         url("<?php echo $baseURL; ?>/kalameh_old/fa_bold/kalameh_fa_bold.woff") format("woff"),
         url("<?php echo $baseURL; ?>/kalameh_old/fa_bold/kalameh_fa_bold.ttf") format("truetype");
}