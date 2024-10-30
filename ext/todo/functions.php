<?php

# Load to-do class
require_once( __DIR__ . "/AMD_Todo.php" );

/**
 * Add allowed status IDs
 * @since 1.0.4
 */
add_filter( "amd_ext_todo_allowed_status_ids", function( $items ){

	$items[] = "pending";
	$items[] = "undone";
	$items[] = "done";
	$items[] = "new";

	return $items;

} );

//add_filter( "amd_ext_todo_authorize_required_for_get_list", "__return_false" );

/**
 * Authorize current user with to-do item
 * @param mixed $default
 * Default value
 * @param int $id
 * To-do item ID
 *
 * @return bool
 * True if user is allowed to modify to-do item, otherwise false
 * @since 1.0.4
 */
function amd_ext_todo_authorize_for( $default, $id ){

	if( $default === true )
		return true;

	$tasks = amd_get_todo_list( ["id" => $id] );

	if( !empty( $tasks[0] ) AND ( $key = $tasks[0]->todo_key ?? "" ) ){
		$thisuser = amd_get_current_user();

		if( $thisuser AND $thisuser->serial == $key )
			return true;
		$check = apply_filters( "amd_ext_todo_authorize_for_edit_check", null, $tasks[0] );
		if( $check == true )
			return true;
	}

	return false;

}
add_filter( "amd_ext_todo_authorize_required_for_delete", "amd_ext_todo_authorize_for", 10, 2 );
add_filter( "amd_ext_todo_authorize_required_for_edit", "amd_ext_todo_authorize_for", 10, 2 );

/**
 * Handle AJAX request
 *
 * @param array $r
 * Sanitized and filtered request data
 * @param array $unfilteredRequest
 * Non-sanitized request data (<b>Note: this is only available in 1.0.5 version and above!</b>)
 *
 * @return void
 * @since 1.0.0
 */
function amd_ajax_target_ext_todo( $r, $unfilteredRequest ){

	# Current user
	$_current_user = amd_get_current_user();

	# Current user. Note: this object can be changed by admins by passing user ID with '_user' parameter!
	$_thisuser = $_current_user;

	# Restrict access
	$restricted = apply_filters( "amd_restrict_capability_todo", false );

	if( $restricted AND !adp_has_access_to_page( "todo", $_current_user->ID ) )
		amd_send_api_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

	if( amd_is_admin() AND !empty( $r["_user"] ) )
		$_thisuser = amd_get_user_by( "ID|email|login", $r["_user"] );

	if( !$_thisuser )
		amd_send_api_error( array(
			"error_code" => "invalid_user",
			"msg" => esc_html_x( "User not found", "Admin", "material-dashboard" )
		) );

	if( !empty( $r["add_todo"] ) ){

		$user = $_thisuser;

		$data = $r["add_todo"];
		$unfilteredData = $unfilteredRequest["add_todo"] ?? [];

//		$text = wp_kses( $unfilteredData["text"] ?? "", amd_allowed_tags_with_attr( "i,b,br" ) );
		$text = $unfilteredData["text"] ?? "";

		$text = str_replace( "\n", "<br>", $text );

		if( empty( $text ) )
			amd_send_api_success( ["msg" => esc_html__( "Please fill out all fields correctly", "material-dashboard" )] );

		$todo = new AMD_Todo();

		$success = $todo->insert( $user->serial, $text, "pending", [] );

		if( $success ){
			$priority = $data["priority"] ?? 0;
			global $amdDB;
			$amdDB->setTodoMeta( $success, "priority", $priority );
			amd_send_api_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "id" => $success, "formatted_text" => $amdDB->formatHtml( $text ) ] );
		}

		amd_send_api_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

	}
	else if( isset( $r["get_todo_list"] ) ){

		$user = $_thisuser;

		$serial = $r["get_todo_list"];
		$salt = $r["todo_salt"] ?? null;

		if( empty( $serial ) )
			$serial = $user->serial;

		/**
		 * Send error if current user is not allowed to modify this to-do list
		 * @since 1.0.4
		 */
		$authorize = apply_filters( "amd_ext_todo_authorize_required_for_get_list", $_thisuser, $r );

		if( $authorize === false )
			amd_send_api_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

		$data = amd_ext_todo_tasks( $serial, $salt );

		amd_send_api_success( ["msg" => esc_html__( "Success", "material-dashboard" ), "data" => (object) $data] );

	}
	else if( !empty( $r["delete_todo"] ) ){

		$id = $r["delete_todo"];

		/**
		 * Send error if current user is not allowed to modify this to-do list
		 * @since 1.0.4
		 */
		$authorize = apply_filters( "amd_ext_todo_authorize_required_for_delete", false, $id );

		if( $authorize === false )
			amd_send_api_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

		$success = amd_delete_todo_list( ["id" => $id] );

		if( $success )
			amd_send_api_success( ["msg" => esc_html__( "Success", "material-dashboard" )] );

		amd_send_api_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

	}
	else if( !empty( $r["edit_todo"] ) ){

		$id = $r["edit_todo"];
		$data = $r["data"] ?? [];
		$unfilteredData = $unfilteredRequest["data"] ?? [];

		/**
		 * Send error if current user is not allowed to modify this to-do item
		 * @since 1.0.4
		 */
		$authorize = apply_filters( "amd_ext_todo_authorize_required_for_edit", false, $id );

		if( $authorize === false )
			amd_send_api_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

		if( !empty( $data ) ){

			$formatted_text = "";
			if( !empty( $unfilteredData["todo_value"] ) ){
				global $amdDB;
				$data["todo_value"] = $unfilteredData["todo_value"];
				$formatted_text = $amdDB->formatHtml( $data["todo_value"] );
			}

			$success = amd_edit_todo( $id, $data, $_thisuser->secretKey );

			if( $success )
				amd_send_api_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "formatted_text" => $formatted_text ] );

		}

		amd_send_api_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

	}

	else if( !empty( $r["import_tasks_priority"] ) ){

		$data = $r["import_tasks_priority"] ?? [];
		$items = explode( ",", $data );
		$success = false;

		global $amdDB;

		foreach( $items as $item ){
			$exp = explode( ":", $item );
			$id = $exp[0];
			$priority = $exp[1] ?? null;
			if( !$id OR $priority === null )
				continue;
			/**
			 * Send error if current user is not allowed to modify this to-do item
			 * @since 1.0.4
			 */
			$authorize = apply_filters( "amd_ext_todo_authorize_required_for_edit", false, $id );

			if( $authorize === false )
				continue;

			$amdDB->setTodoMeta( $id, "priority", $priority );

			$success = true;
		}

		if( $success )
			amd_send_api_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
		else
			amd_send_api_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

	}

	amd_send_api_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

}

/**
 * Get to-do list tasks with serial
 * @param string $serial
 * To-do list key
 * @param string $salt
 * Salt key for decryption
 *
 * @return array
 * @see AMDUser::serial
 * @since 1.0.0
 */
function amd_ext_todo_tasks( $serial, $salt=null ){

	$todo = new AMD_Todo();

	$list = $todo->load_list( $serial, $salt );

	$data = [];
	/** @var AMD_Todo $item */
	foreach( $list as $id => $item ){
		$data[$id] = array(
			"id" => esc_html( $item->id ),
			"text" => str_replace( "\n", "<br>", $item->text ),
			"status" => $item->status,
			"priority" => $item->priority,
		);
	}

	return $data;
}

/**
 * Get my to-do list tasks
 * @return array
 * @since 1.0.0
 */
function amd_ext_todo_my_tasks(){

	if( !is_user_logged_in() )
		return [];

	return amd_ext_todo_tasks( amd_get_current_user()->serial );

}

/**
 * Get uncompleted tasks from to-do list
 * @return array
 * @since 1.0.0
 */
function amd_ext_todo_my_undone_tasks(){

	$tasks = amd_ext_todo_my_tasks();

	$data = [];

	foreach( $tasks as $id => $task ){
		$id = $task["id"] ?? "";
		$text = $task["text"] ?? "";
		$status = $task["status"] ?? "";
		if( $status != "done" )
			$data[$id] = array(
				"id" => $id,
				"text" => $text,
				"status" => $status
			);
	}

	return $data;

}

/**
 * Get to-do extension primary color, you can change it by `amd_ext_todo_primary_color` filter
 * @return mixed|null
 * @since 1.0.0
 */
function amd_ext_todo_get_primary_color(){

	return apply_filters( "amd_ext_todo_primary_color", "blue" );

}