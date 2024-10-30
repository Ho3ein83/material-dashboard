<?php

/**
 * Load custom hooks
 * @return void
 * @since 1.1.2
 */
function amd_load_custom_hooks(){
    $hooks = amd_get_custom_hooks();
    $filters = $hooks["filters"] ?? [];
    $actions = $hooks["actions"] ?? [];

    foreach( $filters as $filter ){
        $data = $filter["data"];
        $name = $data["name"] ?? "";
        if( !$name )
            continue;
        $value = sanitize_text_field( $data["value"] ?? "" );
        $callback = sanitize_text_field( $data["callback"] ?? "" );

        if( $callback AND is_callable( $callback ) ){
            add_filter( $name, $callback );
            continue;
        }
        add_filter( $name, function() use ( $value ) {
            if( amd_starts_with( $value, "Explode:" ) )
                $value = explode( ",", str_replace( "Explode:", "", $value ) );
            else if( amd_starts_with( $value, "Number:" ) )
                $value = intval( str_replace( "Number:", "", $value ) );
            else if( amd_starts_with( $value, "String:" ) )
                $value = strval( str_replace( "String:", "", $value ) );
            else if( amd_starts_with( $value, "Bool:" ) )
                $value = boolval( str_replace( "Bool:", "", $value ) );
            else if( amd_starts_with( $value, "Float:" ) )
                $value = floatval( str_replace( "Float:", "", $value ) );
            else if( $value === "Null:" )
                $value = null;
            else if( $value === "True:" )
                $value = true;
            else if( $value === "False:" )
                $value = false;
            else if( $value === "Zero:" )
                $value = 0;
            else if( amd_starts_with( $value, "Json:" ) )
                $value = @json_decode( str_replace( "Json:", "", $value ) );
            else if( amd_starts_with( $value, "User:" ) )
                $value = amd_get_user( str_replace( "User:", "", $value ) );
            return apply_filters( "amd_format_custom_filter", $value );
        } );
    }

    foreach( $actions as $action ){
        $data = $action["data"];
        $name = $data["name"] ?? "";
        if( !$name )
            continue;
        $callback = sanitize_text_field( $data["callback"] ?? "" );

        if( $callback AND is_callable( $callback ) )
            add_action( $name, $callback );
    }

}
add_action( "amd_after_cores_init", "amd_load_custom_hooks" );

/**
 * Enable or disable default avatar pack
 * @since 1.3.0
 */
add_filter( "amd_enable_default_avatars", function( $default ){
    return amd_get_site_option( "pro_avatar_pack_prefer", "default" ) == "custom" ? false : $default;
} );

/**
 * Custom avatars
 * @since 1.3.0
 */
add_filter( "amd_custom_avatars", function( $list ){
    if( amd_get_site_option( "pro_avatar_pack_prefer", "default" ) == "custom" ){
        $pro_custom_avatar_pack = amd_get_site_option( "pro_custom_avatar_pack" );
        foreach( explode( ",", $pro_custom_avatar_pack ) as $item ){
            if( $url = wp_get_attachment_url( $item ) )
                $list[$item] = array(
                    "url" => $url,
                    "attachment" => $item
                );
        }
    }
    return $list;
} );

/**
 * Avatars whitelist
 * @since 1.3.0
 */
add_filter( "amd_is_avatar_url_allowed", function( $default, $url ){
    $pro_custom_avatar_pack = amd_get_site_option( "pro_custom_avatar_pack" );
    foreach( explode( ",", $pro_custom_avatar_pack ) as $item ){
        if( $atc_url = wp_get_attachment_url( $item ) ){
            if( amd_match_uri( $url, $atc_url ) )
                return true;
        }
    }
    return $default;
}, 10, 2 );

add_filter( "amd_placeholder_avatar_url", function( $default ){
    $pro_custom_placeholder_avatar = amd_get_site_option( "pro_custom_placeholder_avatar" );
    if( $pro_custom_placeholder_avatar && $url = wp_get_attachment_url( $pro_custom_placeholder_avatar ) )
        return $url;
    return $default;
} );

add_filter( "amd_placeholder_avatar_path", function( $default ){
    $pro_custom_placeholder_avatar = amd_get_site_option( "pro_custom_placeholder_avatar" );
    if( $pro_custom_placeholder_avatar && $file = get_attached_file( $pro_custom_placeholder_avatar ) )
        return $file;
    return $default;
} );