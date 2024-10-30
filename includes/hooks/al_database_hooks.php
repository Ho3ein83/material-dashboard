<?php

function amd_database_language_collations( $items ){
    $items[] = array(
        "name" => __( "Persian", "material-dashboard" ),
        "collation" => "utf8mb4_persian_ci",
        "charset" => "utf8mb4"
    );
    $items[] = array(
        "name" => __( "Default", "material-dashboard" ),
        "collation" => "utf8mb4_unicode_520_ci",
        "charset" => "utf8mb4"
    );
    return $items;
}
add_filter( "amd_database_language_collations", "amd_database_language_collations" );