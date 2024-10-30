<?php

/**
 * OTP verification code message
 * @since 1.2.0
 */
add_filter( "amd_otp_verification_code_message", function( $uid, $code ){
    $user = amd_get_user( $uid );
    if( !$user )
        return "";

    # Switch to user language
    $user->rollLocale();

    # [USER_TEMPLATE][MSG_TEMPLATE]
    $message = amd_get_user_message_template( "otp_verification", $user, $code );

    # Rollback language to current session language
    $user->rollbackLocale();

    return $message;

}, 10, 3 );