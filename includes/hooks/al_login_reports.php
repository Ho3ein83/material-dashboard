<?php

/**
 * new login notification message
 * @since 1.2.0
 */
add_filter( "amd_login_notification_message", function( $uid, $user_agent, $ip ){
    $user = amd_get_user( $uid );
    if( !$user )
        return "";

    # Switch to user language
    $user->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE]
    $message = amd_get_user_message_template( "new_login", $user, $user_agent, $ip );

    # Rollback language to current session language
    $user->rollbackLocale();

    return $message;

}, 10, 3 );

/**
 * Send login notification
 * @since 1.2.0
 */
add_action( "amd_login", function( $wp_user ){

    if( !$wp_user OR !$wp_user instanceof WP_User )
        return;

    global $amdSilu;
    $user = $amdSilu->create( $wp_user );

    if( empty( $user->phone ) )
        return;

    $email_enabled = amd_get_site_option( "enable_email_login_notification", "false" ) === "true";
    $email_enabled = apply_filters( "amd_notify_user_login_over_email", $email_enabled, $wp_user );
    $sms_enabled = amd_get_site_option( "enable_sms_login_notification", "false" ) === "true";
    $sms_enabled = apply_filters( "amd_notify_user_login_over_sms", $sms_enabled, $wp_user );
    if( $sms_enabled OR $email_enabled ){
        global $amdWall, $amdWarn;
        $message = apply_filters( "amd_login_notification_message", $wp_user->ID, $amdWall->getUserAgent(), $amdWall->getActualIP() );
        $methods = array_filter( [$email_enabled ? "email" : null, $sms_enabled ? "sms" : null] );

        $amdWarn->sendMessage( array(
            "phone" => $user->phone,
            "email" => $user->email,
            "subject" => __( "New login", "material-dashboard" ),
            "message" => $message,
            "emailBreakLine" => true,
        ), implode( ",", $methods ), true );
    }

} );