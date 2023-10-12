<?php

class AMD_Todo {

	/**
	 * _Todo ID
	 * @var int
	 * @since 1.0.0
	 */
	public $id;

	/**
	 * _Todo salt key for decryption
	 * @var string
	 * @since 1.0.0
	 */
	public $salt;

	/**
	 * _Todo key
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
	 * Decrypted _todo text
	 * @var string
	 * @since 1.0.0
	 */
	public $text;

	/**
	 * _Todo status
	 * @var string
	 * @since 1.0.0
	 */
	public $status;

	/**
	 * _Todo meta-data
	 * @var array
	 * @since 1.0.0
	 */
	public $meta;

	/**
	 * _Todo items object
	 */
	public function __construct(){

		if( is_user_logged_in() )
			$this->salt = amd_get_current_user()->secretKey;

		$this->reset_data();

	}

	/**
	 * Reset _todo data to default
	 * @return void
	 * @since 1.0.0
	 */
	public function reset_data(){
		$this->id = 0;
		$this->key = "";
		$this->_text = "";
		$this->text = "";
		$this->status = "";
		$this->meta = [];
	}

	/**
	 * Change _todo salt key
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
	 * Set _todo data
	 * @param string $key
	 * _Todo key
	 * @param string $_text
	 * Not decrypted text
	 * @param string $text
	 * Decrypted text
	 * @param string $status
	 * _Todo status
	 * @param array $meta
	 * _Todo meta-data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function set_data( $key, $_text, $text, $status, $meta ){
		$this->key = $key;
		$this->_text = $_text;
		$this->text = $text;
		$this->status = $status;
		$this->meta = $meta;
	}

	/**
	 * Load _todo from ID and change object properties
	 * @param int $id
	 * _Todo ID in database
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
	 * Get complete _todo list by key
	 * @param string $key
	 * _Todo list key
	 * @param string $salt
	 * Salt key for decryption
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function load_list( $key, $salt=null ){

		$list = amd_get_todo_list( ["todo_key" => $key] );

		$lists = [];
		if( count( $list ) ){
			foreach( $list as $item ){
				$l = $item;
				$id = $l->id;
				$todo = new AMD_Todo();
				if( empty( $salt ) ) $salt = $todo->salt;
				$todo->set_data( $l->todo_key, $l->todo_value, amd_decrypt_aes( json_decode( $l->todo_value ), $salt ), $l->status, unserialize( $l->meta ) );
				$lists[$id] = $todo;
			}
		}

		return $lists;

	}

	/**
	 * Insert _todo item into databse
	 * @param string $key
	 * _Todo ket
	 * @param string $value
	 * _Todo text
	 * @param string $status
	 * _Todo status
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