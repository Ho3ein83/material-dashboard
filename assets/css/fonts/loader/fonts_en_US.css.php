<?php
/**
 * English digit fonts
 */

# Destroy the execution if it isn't loaded from this plugin
defined( 'AMD_PATH' ) OR die();

# Fonts base URL
$baseURL = AMD_CSS . "/fonts";

?>/**
 * Kalameh font is considered a proprietary software.
 * To gain information about the laws regarding the use of these fonts, please visit fontiran.com
 * This set of fonts are used in this project under the license: 342VUY
 */

/* Shabnam English digits */
@font-face {
    font-family: 'shabnam';
    font-weight: normal;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/shabnam/normal/shabnam_normal.woff2") format("woff2"),
    url("<?php echo $baseURL; ?>/shabnam/normal/shabnam_normal.woff") format("woff"),
    url("<?php echo $baseURL; ?>/shabnam/normal/shabnam_normal.ttf") format("truetype");
}

@font-face {
    font-family: 'shabnam';
    font-weight: bold;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/shabnam/bold/shabnam_bold.woff2") format("woff2"),
    url("<?php echo $baseURL; ?>/shabnam/bold/shabnam_bold.woff") format("woff"),
    url("<?php echo $baseURL; ?>/shabnam/bold/shabnam_bold.ttf") format("truetype");
}

/* Kalameh English digits */
@font-face {
    font-family: 'kalameh';
    font-weight: normal;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/kalameh/normal/kalameh_normal.woff2") format("woff2"),
    url("<?php echo $baseURL; ?>/kalameh/normal/kalameh_normal.woff") format("woff"),
    url("<?php echo $baseURL; ?>/kalameh/normal/kalameh_normal.ttf") format("truetype");
}

@font-face {
    font-family: 'kalameh';
    font-weight: bold;
    font-display: swap;
    src: url("<?php echo $baseURL; ?>/kalameh/bold/kalameh_bold.woff2") format("woff2"),
    url("<?php echo $baseURL; ?>/kalameh/bold/kalameh_bold.woff") format("woff"),
    url("<?php echo $baseURL; ?>/kalameh/bold/kalameh_bold.ttf") format("truetype");
}