<?php

/** @var AMDTasks $amdTasks */
$amdTasks = null;

class AMDTasks {

	/**
	 * Tasks and cron jobs core
	 * @since 1.0.8
	 */
	public function __construct(){

		# Initialize hooks
		self::initHooks();

    }

	/**
	 * Initialize hooks
	 * @return void
	 * @since 1.0.8
	 */
	public function initHooks(){

		# Register database tables
		add_action( "amd_after_cores_init", function(){

			global $amdDB;

            $engine = $amdDB->sanitizeEngine();

			$amdDB->registerTable( "tasks", array(
				"id" => "BIGINT NOT NULL AUTO_INCREMENT",
				"task_user" => "INT(255) NOT NULL",
				"task_key" => "VARCHAR(64) NOT NULL",
				"task_title" => "VARCHAR(64) NOT NULL",
				"task_data" => "LONGTEXT NOT NULL",
				"task_repeat" => "INT(255) NOT NULL",
				"task_period" => "INT(255) NOT NULL",
				"task_time" => "INT(255) NOT NULL",
				"meta" => "LONGTEXT NOT NULL",
				"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = $engine;"
			) );

		} );

		# Run pending tasks on users checkin
		add_action( "amd_checkin", "amd_run_queued_tasks" );

		# Run pending tasks on users checkout
		add_action( "amd_checkout", "amd_run_queued_tasks" );

		# Run tasks
		add_filter( "amd_task_run", function( $executed, $task, $data ){

			if( !$executed AND $task instanceof AMDTasks_Object AND is_array( $data ) ){

				$action = $data["action"] ?? "";
				$_data = $data["data"] ?? [];
				$args = $data["args"] ?? [];
				$success = false;

				if( $action == "send_message" ){
					global $amdWarn;
					$methods = $args[0] ?? "";
					if( $methods ){
						$amdWarn->sendMessage( $_data, $methods );
						$success = true;
					}
				}
				else if( $action == "callback" ){
					if( is_string( $_data ) AND function_exists( $_data ) ){
						call_user_func( $_data, $args );
						$success = true;
					}
				}

				return $success;

			}

			return $executed;

		}, 10, 3 );

        /**
         * Register admin submenu page
         * @since 1.1.0
         */
        add_action( "admin_menu", function(){

            $title = esc_html_x( "Task manager", "Tasks title", "material-dashboard" );

            $capability = apply_filters( "amd_menu_items_capability", "manage_options" );

            add_submenu_page( "material-dashboard", $title, $title, $capability, "amd-tasks", "amd_core_tasks_submenu_page", 8 );

        } );

	}

	/**
	 * Run all queued tasks
	 * @return void
	 * @since 1.0.8
	 */
	public function runQueuedTasks(){

		self::runTasks( self::getTasks( ["CUSTOM" => "`task_time` <= " . time()] ) );

	}

	/**
	 * Create new task object
	 * @return AMDTasks_Object
	 * @since 1.0.8
	 */
	public function makeEmptyObject(){

		require_once( __DIR__ . "/AMDTasks_Object.php" );

		return new AMDTasks_Object();

	}

	/**
	 * Convert database result object to {@see AMDTasks_Object}
	 *
	 * @param mixed $result_s
	 * Database single result or multiple object
	 * @param bool $single
	 * Whether to return single object or complete results
	 *
	 * @return array|AMDTasks_Object
	 * Single {@see AMDTasks_Object} object if `$single` is true, otherwise returns listed objects in array
	 * @since 1.0.8
	 */
	public function makeObjects( $result_s, $single=false ){

		$results = !is_iterable( $result_s ) ? [ $result_s ] : $result_s;

		$out = [];

		foreach( $results as $result ){
			if( $result instanceof AMDTasks_Object ){
				$task = $result;
			}
			else{
				$task = self::makeEmptyObject();
				if( isset( $result->id ) )
					$task->set_id( $result->id );
				if( isset( $result->task_user ) )
					$task->set_user_id( $result->task_user );
				if( isset( $result->task_key ) )
					$task->set_key( $result->task_key );
                if( isset( $result->task_title ) )
					$task->set_title( $result->task_title );
				if( isset( $result->task_data ) )
					$task->set_task( $result->task_data );
				if( isset( $result->task_repeat ) )
					$task->set_repeat( $result->task_repeat );
				if( isset( $result->task_period ) )
					$task->set_period( $result->task_period );
				if( isset( $result->task_time ) )
					$task->set_time( $result->task_time );
				if( isset( $result->meta ) )
					$task->set_meta( unserialize( $result->meta ) );
			}
			if( $single )
				return $task;
			$out[] = $task;
		}

		return $out;

	}

	/**
	 * Insert new task into database
	 *
	 * @param int|null $user_id
	 * User ID or pass null to ignore
	 * @param string|null $key
	 * Task key or pass null to generate automatically
	 * @param string $title
	 * Task title for visual task manager
	 * @param mixed $data
	 * Task data, this data will be encoded with {@see json_encode} and it is up to you to handle it correctly
	 * @param int $repeat
	 * Times to repeat the task
	 * @param int $period
	 * Task execution period at each repeat, for running a task every hour for 3 times, you need to set `$repeat` to 3 and `$period` to 3600 (seconds)
	 * <br><b>Note: by setting period to 0, `$repeat` will change to 1 automatically to prevent executing task multiple times at once</b>
	 * @param array $meta
	 * Meta-data array
	 *
	 * @return false|int
	 * Inserted row ID on success, false on failure
	 * @since 1.0.8
	 */
	public function addTask( $user_id, $key, $title, $data, $repeat=1, $period=0, $meta = [] ){

		if( !$user_id )
			$user_id = 0;

		if( !$key )
			$key = amd_generate_string( 64 );

		if( $period == 0 )
			$repeat = 1;

		global $amdDB;

		$table = $amdDB->getTable( "tasks" );

		if( is_array( $meta ) )
			$meta["time"] = time();

        return $amdDB->safeInsert( $table, array(
			"task_user" => $user_id,
			"task_key" => $key,
			"task_title" => strval( $title ),
			"task_data" => amd_encrypt_aes( json_encode( $data ), $key ),
			"task_repeat" => $repeat,
			"task_period" => $period,
			"task_time" => time() + $period,
			"meta" => serialize( $meta )
		) );

	}

	/**
	 * Get tasks from database
	 * @param array $filter
	 * Filters array, see {@see AMD_DB::makeFilters()}
	 * @param bool $make
	 * Whether to make object or return database results
	 * @param bool $single
	 * Whether to return single object or complete results
	 * @param array $order
	 * Order array, see {@see AMD_DB::makeOrder()}
	 *
	 * @return AMDTasks_Object|array|mixed|object|stdClass
	 * Empty array if there is no result, otherwise:
	 * <ul>
	 * <li>`$make=true` and `$single=true`: single {@see AMDTasks_Object} object</li>
	 * <li>`$make=true` and `$single=false`: listed {@see AMDTasks_Object} objects array</li>
	 * <li>`$make=false` and `$single=true`: Single database result from {@see wpdb::query()}</li>
	 * <li>`$make=false` and `$single=false`: Listed database results from {@see wpdb::query()}</li>
	 * </ul>
	 * @since 1.0.8
	 */
	public function getTasks( $filter=[], $make=true, $single=false, $order=[] ){

		global $amdDB;

		$_filter = $amdDB->makeFilters( $filter );
		$_order = $amdDB->makeOrder( $order );

		$table = $amdDB->getTable( "tasks" );

		$sql = $amdDB->db->prepare( "SELECT * FROM %i $_filter $_order", $table );

		$results = $amdDB->safeQuery( $table, $sql );

		if( empty( $results ) )
			return [];

		if( $single AND !$make )
			return $results[0] ?? [];

		return $make ? self::makeObjects( $results, $single ) : $results;

	}

	/**
	 * Get task by ID
	 * @param int $id
	 * Task ID
	 * @param bool $make
	 * Whether to make task or return database result object
	 *
	 * @return AMDTasks_Object|array|mixed|object|stdClass
	 * See {@see AMDTasks::getTasks()} for single return
	 * @since 1.0.8
	 */
	public function getTaskByID( $id, $make=true ){

		return self::getTasks( ["id" => $id], $make, true );

	}

	/**
	 * Delete specific task(s) from database
	 * @param array $where
	 * Where clauses, see {@see wpdb::delete()}
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function deleteTasks( $where ){

		global $amdDB;

		$table = $amdDB->getTable( "tasks" );

		return $amdDB->db->delete( $table, $where ) !== false;

	}

	/**
	 * Delete specific task with task ID
	 * @param int $id
	 * Task ID in database
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function deleteTaskByID( $id ){

		return self::deleteTasks( ["id" => $id] );

	}

	/**
	 * Delete specific task(s) with task key
	 * @param string $key
	 * Task key
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function deleteTasksByKey( $key ){

		return self::deleteTasks( ["task_key" => $key] );

	}

	/**
	 * Update task item(s) from database
	 * @param array $data
	 * @param array $where
	 *
	 * @return bool
	 * True on success, false on failure
	 * @see wpdb::update()
	 * @since 1.0.8
	 */
	public function updateTasks( $data, $where ){

		global $amdDB;

		$table = $amdDB->getTable( "tasks" );

		return $amdDB->db->update( $table, $data, $where ) !== false;

	}

	/**
	 * Update specific task
	 * @param int $id
	 * Task ID
	 * @param array $data
	 * Task data to update
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function updateTask( $id, $data ){

		return self::updateTasks( $data, ["id" => $id] );

	}

	/**
	 * Run specific task(s)
	 * @param array $tasks
	 * Tasks array, this array elements must be either task integer ID or {@see AMDTasks_Object}
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function runTasks( $tasks ){

		if( $tasks instanceof AMDTasks_Object )
			$tasks = [$tasks];
		else if( is_scalar( $tasks ) )
			$tasks = ["$tasks"];

		$queue = [];
		foreach( $tasks as $task ){
			$t = $task;
			if( is_string( $task ) OR is_int( $task ) )
				$t = self::getTaskByID( $task );
			if( $t instanceof AMDTasks_Object )
				$queue[] = $t;
		}

		/** @var AMDTasks_Object $task_item */
		foreach( $queue as $task_item )
			$task_item->run();

	}

    /**
     * Print admin submenu page content
     * @return void
     * @since 1.1.0
     */
    public function printAdminSubmenu() {

        require_once( __DIR__ . "/view/admin_menu.php" );

    }

    /**
     * Export tasks group for JSON encryption
     * @param AMDTasks_Object[] $tasks
     * {@see AMDTasks_Object} object list
     * @return array
     * @since 1.1.0
     */
    public function export( $tasks ) {

        $out = [];

        if( is_iterable( $tasks ) ){
            foreach( $tasks as $task ){
                if( $task instanceof AMDTasks_Object )
                    $out[] = $task->export();
            }
        }

        return $out;

    }

}

/**
 * Tasks core submenu page
 * @return void
 * @since 1.1.0
 */
function amd_core_tasks_submenu_page(){

    global $amdTasks;

    $amdTasks->printAdminSubmenu();

}

function amd_ajax_target_task_manager( $r ){

    if( isset( $r["get_tasks"] ) ){

        $current_page = $r["current_page"] ?? 1;
        $per_page = $r["per_page"] ?? apply_filters( "amd_task_manager_max_in_page", 10 );

        global $amdTasks;

        $tasks = $amdTasks->getTasks();

        $chunk = array_chunk( $tasks, $per_page );
        $this_chunk = $chunk[$current_page-1] ?? [];

        $has_more = !empty( $chunk[$current_page] );

        wp_send_json_success( ["msg" => esc_html__( "Success", "material-dashboard" ), "tasks" => $amdTasks->export( $this_chunk ), "has_more" => $has_more] );

    }

    else if( isset( $r["task_action"] ) ){

        $action = $r["task_action"];


        $task_id = $r["task_id"] ?? "";

        if( !$task_id )
            wp_send_json_error( ["msg" => esc_html__( "Selected task is not available or doesn't exist", "material-dashboard" )] );

        global $amdTasks;
        $task = $amdTasks->getTaskByID( $task_id );

        if( !$task )
            wp_send_json_error( ["msg" => esc_html__( "Selected task is not available or doesn't exist", "material-dashboard" )] );

        if( $action == "run" ){
            $s = $task->run( false );
            if( $s )
                wp_send_json_success( ["msg" => esc_html__( "Success", "material-dashboard" )] );
        }
        else if( $action == "delete" ){
            $s = $task->delete();
            if( $s )
                wp_send_json_success( ["msg" => esc_html__( "Success", "material-dashboard" )] );
        }

        wp_send_json_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

    }

}