<?php

/**
 * Print settings card for admin page
 * @return void
 * @sicne 1.2.1
 */
function amd_ext_todo_add_settings_section(){

    $todo_reminder = amd_get_site_option( "todo_reminder_enabled", "true" );
    $todo_reminder_use_email = amd_get_site_option( "todo_reminder_use_email", "true" );
    $todo_reminder_use_sms = amd_get_site_option( "todo_reminder_use_sms", "false" );

    ?>
    <!-- To-do list -->
    <div class="amd-admin-card --setting-card" data-ts="todo">
        <?php amd_dump_admin_card_keywords( ["todo", "to-do", "لیست وظایف", "یاد اور", "یاداور"] ); ?>
        <h3 class="--title"><?php echo esc_html_x( "Todo list", "todo", "material-dashboard" ); ?></h3>
        <div class="--content">
            <?php
            /** @since 1.2.1 */
            do_action( "amd_settings_before_todo_content" );
            ?>
            <div class="__option_grid">
                <?php
                /** @since 1.2.1 */
                do_action( "amd_settings_before_todo_items" );
                ?>
                <div class="-item">
                    <div class="-sub-item">
                        <label for="enable-todo-reminder">
                            <?php echo esc_html_x( "Enable to-do list reminder", "Admin", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" role="switch" name="todo_reminder_enabled" value="true" id="enable-todo-reminder" <?php echo $todo_reminder == "true" ? 'checked' : ''; ?>>
                            <span></span>
                        </label>
                    </div>
                    <div class="-sub-item --full">
                        <p class="color-blue"><?php echo esc_html_x( "You can enable reminders so users can set a reminder for their tasks and get notified when it needs to be reminded.", "Admin", "material-dashboard" ); ?></p>
                    </div>
                </div>
            </div>
            <div class="__option_grid _show_on_todo_reminder_">
                <div class="-item">
                    <div class="-sub-item">
                        <label for="todo-reminder-use-email">
                            <?php echo esc_html_x( "Send reminders via email", "Admin", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" role="switch" name="todo_reminder_use_email" value="true" id="todo-reminder-use-email" <?php echo $todo_reminder_use_email == "true" ? 'checked' : ''; ?>>
                            <span></span>
                        </label>
                    </div>
                </div>
                <div class="-item">
                    <div class="-sub-item">
                        <label for="todo-reminder-use-sms">
                            <?php echo esc_html_x( "Send reminders via SMS", "Admin", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" role="switch" name="todo_reminder_use_sms" value="true" id="todo-reminder-use-sms" <?php echo $todo_reminder_use_sms == "true" ? 'checked' : ''; ?>>
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="__option_grid">
                <?php
                /** @since 1.2.1 */
                do_action( "amd_settings_after_todo_items" );
                ?>
            </div>
            <?php
            /** @since 1.2.1 */
            do_action( "amd_settings_after_todo_content" );
            ?>
        </div>
    </div>
    <script>
        (function(){
            const $enable = $("#enable-todo-reminder");
            $enable.change(function(){$("._show_on_todo_reminder_")[$(this).is(":checked")?"fadeIn":"fadeOut"]();}).trigger("change");
            $amd.addEvent("on_settings_saved", () => {
                return {
                    "todo_reminder_enabled": $enable.is(":checked") ? "true" : "false",
                    "todo_reminder_use_email": $("#todo-reminder-use-email").is(":checked") ? "true" : "false",
                    "todo_reminder_use_sms": $("#todo-reminder-use-sms").is(":checked") ? "true" : "false",
                }
            });
        }());
    </script>
    <?php
}
add_action( "amd_settings_after_all_cards", "amd_ext_todo_add_settings_section" );

/**
 * User messages templates
 * @since 1.2.1
 */
add_filter( "amd_user_message_templates", function( $list ){

    $list["pending_todo"] = array(
        "title" => __( "Uncompleted to-do list", "material-dashboard" ),
        "template" => sprintf( __( "Hello dear %s, you have an uncompleted task in your to-do list waiting to get done.", "material-dashboard" ), "%FIRSTNAME%" ),
        "scopes" => ["email", "sms"]
    );

    return $list;

} );

add_filter( "amd_task_run", function( $executed, $task, $data ){
    $todo_reminder = amd_get_site_option( "todo_reminder_enabled", "true" );

    # Skip the execution if reminder is disabled in settings
    if( !amd_get_logical_value( $todo_reminder ) )
        return $executed;

    if( !$executed AND $task instanceof AMDTasks_Object AND is_array( $data ) ){

        $action = $data["action"] ?? "";
        if( $action == "remind_todo" ) {

            $todo_reminder_use_email = amd_get_site_option( "todo_reminder_use_email", "true" );
            $todo_reminder_use_sms = amd_get_site_option( "todo_reminder_use_sms", "false" );

            $methods = [];
            if( $todo_reminder_use_email )
                $methods[] = "email";
            if( $todo_reminder_use_sms )
                $methods[] = "sms";

            if( empty( $methods ) )
                return $executed;

            $info = $data["data"] ?? [];
            if( !empty( $info ) ) {
                $user_id = $info["user_id"] ?? "";
                if( !empty( $user_id ) ){
                    $user = amd_get_user( $user_id );
                    if( $user ){
                        $todo_id = $info["todo"] ?? "";
                        $todo = amd_get_todo_list( ["id" => $todo_id] );
                        if( !empty( $todo ) AND !empty( $todo[0]->status ) ){
                            if( $todo[0]->status == "pending" || $todo[0]->status == "undone" ){
                                global $amdWarn;

                                # MARKME: Send by pattern (done)
                                $message_id = "pending_todo";
                                $message = amd_get_user_message_template( $message_id, $user );
                                $title = __( "Uncompleted to-do list", "material-dashboard" );

                                # Send message by pattern if pattern exist, otherwise send it normally
                                if( in_array( "sms", $methods ) ){
                                    $pattern_send = amd_send_message_by_pattern( $user->phone, $message_id, ["firstname" => $user->getSafeName()], true );
                                    if( $pattern_send === false || $pattern_send > 0 )
                                        unset( $methods[array_search( "sms", $methods )] );
                                }
                                $amdWarn->sendMessage( ["email" => $user->email, "subject" => $title, "message" => $message, "phone" => $user->phone, "emailBreakLine" => true], implode( ",", $methods ), true );
                            }
                        }
                    }
                }
            }
            return true;
        }
    }
    return $executed;
}, 10, 3 );