<?php

class AMDTasks_Object {

	/**
	 * Task ID
	 * @var int
	 * @since 1.0.8
	 */
	private $id;

	/**
	 * Task user ID
	 * @var int
	 * @since 1.0.8
	 */
	private $user_id;

	/**
	 * Task user object
	 * @var AMDUser|null
	 * @since 1.0.8
	 */
	private $user;

	/**
	 * Task key
	 * @var string
	 * @since 1.0.8
	 */
	private $key;

    /**
	 * Task title
	 * @var string
	 * @since 1.1.0
	 */
	private $title;

	/**
	 * Task data string
	 * @var string
	 * @since 1.0.8
	 */
	private $task;

	/**
	 * Task repeat times
	 * @var int
	 * @since 1.0.8
	 */
	private $repeat;

	/**
	 * Task repeat times period
	 * @var int
	 * @since 1.0.8
	 */
	private $period;

	/**
	 * Task execution time
	 * @var int
	 * @since 1.0.8
	 */
	private $time;

	/**
	 * Task meta
	 * @var array
	 * @since 1.0.8
	 */
	private $meta;

	/**
	 * Task object
	 * @since 1.0.8
	 */
	public function __construct(){

	    self::set_id( 0 );
	    self::set_user_id( 0 );
	    self::set_key( "" );
	    self::set_task( json_encode( null ) );
	    self::set_repeat( 0 );
	    self::set_period( 0 );
	    self::set_time( 0 );
	    self::set_meta( [] );

    }

	/**
	 * Get ID
	 * @return int
	 * @since 1.0.8
	 */
	public function get_id(){
		return $this->id;
	}

	/**
	 * Set ID
	 * @param int $id
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_id( $id ){
		$this->id = $id;
	}

	/**
	 * Get task user ID
	 * @return int
	 * @since 1.0.8
	 */
	public function get_user_id(){
		return $this->user_id;
	}

	/**
	 * Set task user ID
	 * @param int $user_id
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_user_id( $user_id ){
		$this->user_id = $user_id;
		$this->user = $user_id ? amd_get_user( $user_id ) : null;
	}

	/**
	 * Get user object
	 * @return AMDUser|null
	 * @since 1.0.8
	 */
	public function get_user(){
		return $this->user;
	}

	/**
	 * Get key
	 * @return string
	 * @since 1.0.8
	 */
	public function get_key(){
		return $this->key;
	}

	/**
	 * Set key
	 * @param string $key
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_key( $key ){
		$this->key = $key;
	}

    /**
     * Get task title
     * @return string
     * @since 1.1.0
     */
    public function get_title(){

        return $this->title;

    }

    /**
     * Set task title
     * @param string $title
     * @since 1.1.0
     */
    public function set_title( $title ){

        $this->title = $title;

    }

	/**
	 * Get task data string
	 *
	 * @param bool $decrypt
	 * Whether to decrypt data or return encrypted task
	 *
	 * @return string
	 * @since 1.0.8
	 */
	public function get_task( $decrypt=true ){

		return $decrypt ? amd_decrypt_aes( $this->task, $this->key ) : $this->task;

	}

	/**
	 * Set task data string
	 * @param string $task
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_task( $task ){
		$this->task = $task;
	}

	/**
	 * Get repeat times
	 * @return int
	 * @since 1.0.8
	 */
	public function get_repeat(){
		return $this->repeat;
	}

	/**
	 * Set repeat times
	 * @param int $repeat
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_repeat( $repeat ){
		$this->repeat = $repeat;
	}

	/**
	 * Set task repeat period
	 * @return int
	 * @since 1.0.8
	 */
	public function get_period(){
		return $this->period;
	}

	/**
	 * Set task repeat period
	 * @param int $period
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_period( $period ){
		$this->period = $period;
	}

	/**
	 * Get task execution time
	 * @return int
	 * @since 1.0.8
	 */
	public function get_time(){
		return $this->time;
	}

	/**
	 * Set task execution time
	 * @param int $time
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_time( $time ){
		$this->time = $time;
	}

    /**
     * Get task meta
     *
     * @param string|null $element
     * Whether to get specific element from meta items or get the whole meta
     * @param mixed $default
     * Default value
     * @return mixed
     * @since 1.0.8
     */
	public function get_meta( $element=null, $default=null ){
		return $element ? ( $this->meta[$element] ?? $default ) : $this->meta;
	}

	/**
	 * Set meta
	 * @param array|mixed $meta
	 * Pass array data if you want to change meta value completely, or pass mixed data to change specific element
	 * @param string|null $element
	 * Pass null to set meta value completely, or pass meta element key to change specific element
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function set_meta( $meta, $element=null ){
		if( $element )
			$this->meta[$element] = $meta;
		else
			$this->meta = $meta;
	}

	/**
	 * Run task
	 * @param bool $timeCheck
	 * Whether to check task execution time or just run it
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function run( $timeCheck=true ){

		if( $timeCheck AND $this->get_time() > time() )
			return false;

		$json = @json_decode( $this->get_task(), true );

		if( $json ){

			/**
			 * Hook for running task item
			 * @since 1.0.8
			 */
			do_action( "amd_run_task", $this );

			/**
			 * Run task and get the result
			 * @since 1.0.8
			 */
			$finished = apply_filters( "amd_task_run", false, $this, $json );

			if( $finished )
				return $this->fire( $finished );
			else
				$this->renew();

		}

		return false;

	}

	/**
	 * Delete this task from database
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function delete(){

		global $amdTasks;

		return $amdTasks->deleteTaskByID( $this->id );

	}

	/**
	 * Complete task after running
	 * @param mixed $data
	 * Task result data
	 *
	 * @return bool
	 * @since 1.0.8
	 */
	private function fire( $data=null ){

		/**
		 * Fire task hook
		 * @since 1.0.8
		 */
		do_action( "amd_task_fire", $this, $data );

		if( $this->repeat <= 1 )
			return self::delete();

		global $amdTasks;

		return $amdTasks->updateTask( $this->id, array(
			"task_repeat" => $this->get_repeat() - 1,
			"task_time" => time() + $this->get_period()
		) );

	}

	/**
	 * Renew task execution
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.8
	 */
	public function renew(){

		global $amdTasks;

		return $amdTasks->updateTask( $this->id, array(
			"task_time" => time() + $this->get_period()
		) );

	}

    /**
     * Check if task has specific attribute
     * @param string $attribute
     * Attribute name, e.g: "visible", "editable", "executable"
     * @param bool $default
     * Default value, default is false
     * @return bool
     * @since 1.1.0
     */
    public function is( $attribute, $default=false ) {

        $attrs = $this->get_meta( "attributes" );

        return $attrs[$attribute] ?? $default;

    }

    /**
     * Export data for JSON encryption
     * @return array
     * @since 1.1.0
     */
    public function export() {

        $user = $this->get_user();

        return array(
            "id" => $this->get_id(),
            "key" => $this->get_key(),
            "title" => $this->get_title(),
            "user_id" => $this->get_user_id(),
            "user_fullname" => $user ? $user->fullname : "",
            "user_username" => $user ? $user->username : "",
            "user_email" => $user ? $user->email : "",
            "repeats" => $this->get_repeat(),
            "period" => $this->get_period(),
            "period_str" => $this->get_period() > 0 ? amd_convert_time_to_text( $this->get_period() ) : _x( "No period", "Task manager", "material-dashboard" ),
            "time" => $this->get_time(),
            "execution_time" => $this->get_time() - time(),
            "is_visible" => $this->is( "visible", true ),
            "is_deletable" => $this->is( "deletable", true ),
            "is_executable" => $this->is( "executable", true ),
            "attributes" => $this->get_meta( "attributes", [] ),
            "extra" => apply_filters( "amd_task_export_extra", null, $this )
        );

    }


}