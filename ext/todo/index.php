<?php

# Stop it here if extension is not loaded from WordPress
defined( 'ABSPATH' ) OR die();

# Extension constants
define( 'AMD_EXT_TODO_PATH', __DIR__ );
define( 'AMD_EXT_TODO_VIEW', AMD_EXT_TODO_PATH . '/view' );

/**
 * Just for translations and extension availability check
 * @return void
 */
function amd_ext_todo(){
	_x( "Amatris", "todo", "material-dashboard" );
	_x( "Todo list", "todo", "material-dashboard" );
	_x( "A professional todo list with data encryption for users privacy", "todo", "material-dashboard" );
}

/**
 * Initialize registered hooks
 * @since 1.0.0
 */
add_action( "amd_dashboard_init", function(){

	# Restrict access
	$restricted = apply_filters( "amd_restrict_capability_todo", false );

	if( $restricted AND apply_filters( "amd_deep_restriction", false ) )
		return;

	# Register lazy-loading pages
	amd_register_lazy_page( "todo", esc_html_x( "Todo list", "todo", "material-dashboard" ), AMD_EXT_TODO_VIEW . '/page_todo.php', "todo" );

	if( !$restricted ){

		# Add dashboard cards (icon cards)
		$tasks_count = count( amd_ext_todo_my_tasks() );
		$undone_tasks_count = count( amd_ext_todo_my_undone_tasks() );
		do_action( "amd_add_dashboard_card", array(
			"ic_todo" => array(
				"type" => "icon_card",
				"title" => esc_html__( "Tasks", "material-dashboard" ),
				"text" => esc_html( sprintf( _nx( "%d task", "%d tasks", $tasks_count, "todo", "material-dashboard" ), $tasks_count ) ),
				"subtext" => esc_html( sprintf( _nx( "%d undone task", "%d undone tasks", $undone_tasks_count, "todo", "material-dashboard" ), $undone_tasks_count ) ),
				"footer" => "<a href=\"javascript:void(0)\" data-lazy-query=\"?void=todo\">" . esc_html__( "View", "material-dashboard" ) . "</a>",
				"icon" => "todo",
				"color" => "green",
				"priority" => 2
			)
		) );

		# Add dashboard cards (content cards)
		do_action( "amd_add_dashboard_card", array(
			"todo_list" => array(
				"type" => "content_card",
				"page" => AMD_EXT_TODO_VIEW . "/cards/todo_list.php",
				"priority" => 7
			)
		) );

	}

} );

add_action( "amd_init_sidebar_items", function(){

	# Add menu item
	do_action( "amd_add_dashboard_sidebar_menu", array(
		"todo" => array(
			"text" => esc_html_x( "Todo list", "todo", "material-dashboard" ),
			"icon" => "todo",
			"void" => "todo",
			"url" => "?void=todo",
			"priority" => 8
		)
	) );

} );

/**
 * @return void
 * @since 1.0.0
 */
function amd_ext_todo_init_hooks(){

	# Set default export variants
	do_action( "amd_export_variant", array(
		"extension_todo" => array(
			"title" => esc_html_x( "Todo list", "todo", "material-dashboard" ),
			"export_type" => "json",
			"requirements" => [ "Todo extension" => "function:amd_ext_todo" ],
			"export" => function(){
				$lists = amd_get_todo_list( [] );
				$data = [];

				foreach( $lists as $list ){
					$id = $list->id;
					$data[$id] = array(
						"todo_key" => $list->todo_key,
						"todo_value" => $list->todo_value,
						"status" => $list->status,
						"meta" => $list->meta
					);
				}

				return $data;
			},
			"import" => function( $list ){

				global $amdDB, $amdWall;

				$table = $amdDB->getTable( "todo" );
				$amdDB->safeQuery( $table, "TRUNCATE `%{TABLE}%`" );

				$key_cache = [];
				$progress = [];
				$missed = 0;
				foreach( $list as $data ){

					$key = $data->todo_key ?? "";
					$value = $data->todo_value ?? "";
					$status = $data->status ?? "pending";
					$_meta = $data->meta ?? "a:0:{}";
					$meta = unserialize( $_meta );

					if( $key == "admin_note" ){
						amd_add_todo( "admin_note", $value, $status, AMD_DIRECTORY, $meta, false );
						continue;
					}

					if( empty( $key_cache[$key] ) ){
						$serial = $amdWall->deserialize( $key );
						$uid = str_replace( "user_", "", $serial );
						$user = amd_get_user( $uid );
						$key_cache[$key] = $user;
					}
					else{
						$user = $key_cache[$key];
					}

					if( !$user ){
						$missed++;
						continue;
					}

					amd_add_todo( $amdWall->serialize( $serial ), $value, $status, $user->serial, $meta, false );
					$progress[$key] = true;

				}
				return [true, esc_html_x( "Todo lists successfully imported", "todo", "material-dashboard" ), $progress, $missed];

			}
		)
	) );

    # Register allowed options (since 1.2.1)
    do_action( "amd_allowed_options", array(
        "todo_reminder_enabled" => "BOOL",
        "todo_reminder_use_email" => "BOOL",
        "todo_reminder_use_sms" => "BOOL",
    ) );

}
add_action( "init", "amd_ext_todo_init_hooks" );
add_action( "admin_init", "amd_ext_todo_init_hooks" );