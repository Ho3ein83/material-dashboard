<?php

class AMD_Todo {

	/**
	 * To-do ID
	 * @var int
	 * @since 1.0.0
	 */
	public $id;

	/**
	 * To-do salt key for decryption
	 * @var string
	 * @since 1.0.0
	 */
	public $salt;

	/**
	 * To-do key
	 * @var string
	 * @since 1.0.0
	 */
	public $key;

	/**
	 * Not decrypted text
	 * @var string
	 * @since 1.0.0
	 */
	public $_text;

	/**
	 * Decrypted To-do text
	 * @var string
	 * @since 1.0.0
	 */
	public $text;

	/**
	 * To-do status
	 * @var string
	 * @since 1.0.0
	 */
	public $status;

	/**
	 * To-do priority
	 * @var int
	 * @since 1.0.5
	 */
	public $priority;

	/**
	 * To-do meta-data
	 * @var array
	 * @since 1.0.0
	 */
	public $meta;

	/**
	 * To-do items object
	 */
	public function __construct(){

		if( is_user_logged_in() )
			$this->salt = amd_get_current_user()->secretKey;

		$this->reset_data();

	}

	/**
	 * Reset To-do data to default
	 * @return void
	 * @since 1.0.0
	 */
	public function reset_data(){
		$this->id = 0;
		$this->key = "";
		$this->_text = "";
		$this->text = "";
		$this->status = "";
		$this->priority = 0;
		$this->meta = [];
	}

	/**
	 * Change To-do salt key
	 * @param string $salt
	 * New salt value
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function set_salt( $salt ){
		$this->salt = $salt;
	}

	/**
	 * Set To-do data
	 *
	 * @param string $key
	 * To-do key
	 * @param string $_text
	 * Not decrypted text
	 * @param string $text
	 * Decrypted text
	 * @param string $status
	 * To-do status
	 * @param array $meta
	 * To-do meta-data
	 * @param int $priority
	 * To-do priority
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function set_data( $key, $_text, $text, $status, $meta, $priority=0 ){
		$this->key = $key;
		$this->_text = $_text;
		$this->text = $text;
		$this->status = $status;
		$this->priority = $priority;
		$this->meta = $meta;
	}

	/**
	 * Load To-do from ID and change object properties
	 * @param int $id
	 * To-do ID in database
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function load( $id ){

		$list = amd_get_todo_list( ["id" => $id] );

		$this->reset_data();
		if( count( $list ) ){
			$l = $list[0];
			$this->id = $l->id;
			$this->key = $l->todo_key;
			$this->_text = $l->todo_value;
			$this->text = amd_decrypt_aes( $this->_text, $this->salt );
			$this->status = $l->status;
			$this->meta = unserialize( $l->meta );
		}

	}

	/**
	 * Get complete To-do list by key
	 * @param string $key
	 * To-do list key
	 * @param string $salt
	 * Salt key for decryption
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function load_list( $key, $salt=null ){

		$list = amd_get_todo_list( ["todo_key" => $key] );

		global $amdDB;

		$lists = [];
		if( count( $list ) ){
			foreach( $list as $item ){
				$l = $item;
				$id = $l->id;
				$todo = new AMD_Todo();
				if( empty( $salt ) ) $salt = $todo->salt;
				$priority = intval( $amdDB->getTodoMeta( $id, "priority", "0" ) );
				$todo->set_data(
					$l->todo_key,
					$l->todo_value,
					amd_decrypt_aes( json_decode( $l->todo_value ), $salt ),
					$l->status,
					unserialize( $l->meta ),
					$priority
				);
				$lists[$id] = $todo;
			}
		}

		return $lists;

	}

	/**
	 * Insert to-do item into database
	 * @param string $key
	 * To-do key
	 * @param string $value
	 * To-do text
	 * @param string $status
	 * To-do status
	 * @param array $meta
	 * Meta-data
	 *
	 * @return false|int
	 * @since 1.0.0
	 */
	public function insert( $key, $value, $status, $meta=[] ){

		return amd_add_todo( $key, $value, $status, $this->salt, $meta );

	}

}