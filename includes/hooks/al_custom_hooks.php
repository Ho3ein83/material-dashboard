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