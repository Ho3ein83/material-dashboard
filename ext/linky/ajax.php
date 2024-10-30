<?php

/**
 * Handle ajax request (private)
 * @param array $r
 * @param array $unfiltered_r
 * @return void
 * @since 1.2.0
 */
function amd_ajax_target_ext_linky_private( $r, $unfiltered_r ){

    if( isset( $r["get_friends_list"] ) ){
        $limit = intval( $r["limit"] ?? -1 );
        $target_user = null;
        if( amd_is_admin() AND !empty( $r["target_user"] ) )
            $target_user = $r["target_user"];
        wp_send_json_success( ["msg" => __( "Success", "material-dashboard" ), "friends" => amd_ext_linky_get_friends_list( true, $target_user, $limit )] );
    }

}

/**
 * Handle ajax request (public)
 * @param array $r
 * @param array $unfiltered_r
 * @return void
 * @since 1.2.0
 */
function amd_ajax_target_ext_linky_public( $r, $unfiltered_r ){

    if( !empty( $r["check_referral"] ) ){
        $ref = $r["check_referral"];
        $user = amd_ext_linky_get_user_by_referral( $ref );
        if( $user )
            wp_send_json_success( ["msg" => __( "Referral code is valid", "material-dashboard" ), "user_name" => $user->fullname] );
        wp_send_json_error( ["msg" => _x( "Referral code is not valid or doesn't exist", "linky", "material-dashboard" )] );
    }

}
