<?php

/**
 * Handle AJAX requests
 * @param array $r
 * Request data
 * @return void
 * @since 1.1.0
 */
function amd_ajax_target_search_engine( $r ){

    if( isset( $r["fetch_index"] ) ){

        $use_cache = ( $r["use_cache"] ?: "" ) == "true";
        $cache_path = AMD_CORE . "/AMDSearchEngine/cache";
        $cache_file = $cache_path . "/" . sanitize_file_name( str_replace( ".", "", AMD_VER ) . ".json" );

        $cache = null;
        $fetch = null;
        if( $use_cache ){
            if( !file_exists( $cache_path ) )
                mkdir( $cache_path );
            else if( file_exists( $cache_file ) )
                $cache = file_get_contents( $cache_file );
            $fetch = "cache";
        }

        if( !$cache ) {
            $result = wp_remote_post( AMD_SEARCH_ENGINE_URL, array( "body" => array( "token" => AMD_PATCH_HASH, "fetch_index" => "latest" ), "timeout" => 30000 ) );
            if( !is_wp_error( $result ) ) {
                $cache = wp_remote_retrieve_body( $result );
                $fetch = "remote";
            }
        }

        if( $cache ) {
            $data = str_replace( [ "{ADMIN_URL}" ], [ admin_url( "admin.php" ) ], $cache );
            $data = @json_decode( $data );
            if( $data ) {
                if( $fetch == "remote" )
                    file_put_contents( $cache_file, wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "data" => $data ] );
            }
        }

    }

    if( amd_is_admin() ){

        if( !empty( $r["search_users"] ) ){
            $query = $r["search_users"];
            $users = [];

            if( is_string( $query ) AND strpos( $query, "," ) !== false )
                $query = explode( ",", $query );

            global $amdSearch;

            if( is_iterable( $query ) ){
                foreach( $query as $q ){
                    $u = $amdSearch->search_user( $q );
                    if( $u AND $u instanceof AMDUser AND method_exists( $u, "export" ) )
                        $users[$u->ID] = $u->export();
                }
            }
            else if( is_string( $query ) OR is_numeric( $query ) ){
                $u = $amdSearch->search_user( $query );
                if( $u AND $u instanceof AMDUser AND method_exists( $u, "export" ) )
                    $users[$u->ID] = $u->export();
            }

            wp_send_json_success( ["msg" => __( "Success", "material-dashboard" ), "users" => $users] );

        }
        else if( !empty( $r["export_users_by_id"] ) ){
            $query = $r["export_users_by_id"];
            $users = [];

            if( is_string( $query ) AND strpos( $query, "," ) !== false )
                $query = explode( ",", $query );

            if( is_iterable( $query ) ){
                foreach( $query as $q ){
                    $u = amd_get_user_by( "id", $q );
                    if( $u AND $u instanceof AMDUser AND method_exists( $u, "export" ) )
                        $users[$u->ID] = $u->export();
                }
            }
            else if( is_string( $query ) OR is_numeric( $query ) ){
                $u = amd_get_user_by( "id", $query );
                if( $u AND $u instanceof AMDUser AND method_exists( $u, "export" ) )
                    $users[$u->ID] = $u->export();
            }

            wp_send_json_success( ["msg" => __( "Success", "material-dashboard" ), "users" => $users] );

        }

    }

    wp_send_json_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

}