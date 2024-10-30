<?php

/**
 * Send message by pattern
 * @param string $to
 * Reception phone number
 * @param string $message_id
 * Message ID, e.g: "2fa_code"
 * @param array $args
 * The list of message arguments, e.g: ["John", "1452"]
 * @param bool|int $schedule
 * See {@see AMDWarner::sendMessage} $schedule argument
 * @return bool|int
 * True on success, false on failure, -1 if pattern is disabled or doesn't exist
 * @since 1.2.1
 */
function amd_send_message_by_pattern( $to, $message_id, $args, $schedule = false ){

    if( !has_filter( "amd_send_message_by_pattern" ) )
        return false;

    return apply_filters( "amd_send_message_by_pattern", $to, $message_id, $args, $schedule );

}