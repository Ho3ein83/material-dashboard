<?php

add_filter( "amd_use_manual_raw_template", function(){
    return amd_get_logical_value( amd_get_site_option( "manual_raw_template", "false" ) );
} );

add_action( "admin_notices", function(){

    if( isset( $_GET["amd-enable-raw-template"] ) OR isset( $_GET["amd-disable-raw-template"] ) ){
        $enable = isset( $_GET["amd-enable-raw-template"] );
        amd_set_site_option( "manual_raw_template", $enable ? "true" : "false" );
        amd_delete_site_option( "amd_template_check" );
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html( $enable ? _x( "Raw template has been enabled.", "Admin", "material-dashboard" ) : _x( "Raw template has been disabled.", "Admin", "material-dashboard" ) ); ?></p>
        </div>
        <script>(()=>{let params=new URLSearchParams(location.href.split("?")[1]||"");params.delete("amd-enable-raw-template");params.delete("amd-disable-raw-template");window.history.replaceState({},"","?"+params.toString());})();</script>
        <?php
        return;
    }

    if( apply_filters( "manual_raw_template", false ) )
        return;

    $last_check = intval( amd_get_site_option( "amd_template_check", 0 ) );

    if( $last_check < time() ){
        $result = wp_remote_get( amd_get_api_url() );
        $body = is_wp_error( $result ) ? null : ( $result["body"] ?? null );
        $json = @json_decode( $body );
        if( $json !== null AND $json !== false ){
            # check again within 1 week
            amd_set_site_option( "amd_template_check", time() + 604800 );
            return;
        }
        ?>
        <div class="notice notice-warning is-dismissible">
            <h4><?php esc_html_e( "Material Dashboard", "material-dashboard" ); ?></h4>
            <p><?php printf( esc_html_x( "If you have a problem loading dashboard pages, you can %senable raw template%s as an alternative fix.", "Admin", "material-dashboard" ), '<a href="' . admin_url( "index.php?amd-enable-raw-template" ) . '">', '</a>' ); ?></p>
        </div>
        <?php
    }

} );