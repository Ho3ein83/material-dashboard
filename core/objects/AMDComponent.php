<?php

class AMDComponent {

	/**
	 * Component ID
	 * @var int
	 * @since 1.0.5
	 */
	protected $id;

	/**
	 * Component key
	 * @var string
	 * @since 1.0.5
	 */
	protected $key;

	/**
	 * Component type
	 * @var string
	 * @since 1.0.5
	 */
	protected $type;

	/**
	 * Component data
	 * @var mixed
	 * @since 1.0.5
	 */
	protected $data;

	/**
	 * Component registration time
	 * @var int
	 * @since 1.0.5
	 */
	protected $time;

	/**
	 * Component meta-data
	 * @var array
	 * @since 1.0.5
	 */
	protected $meta;

	/**
	 * Component object
	 * @since 1.0.5
	 */
	public function __construct(){

		$this->id = 0;

		$this->key = "";

		$this->type = "";

		$this->data = null;

		$this->time = 0;

		$this->meta = [];

    }

	/**
	 * @return int
	 * @since 1.0.5
	 */
	public function get_id(){

		return $this->id;

	}

	/**
	 * @param int $id
	 * @since 1.0.5
	 */
	public function set_id( $id ){

		$this->id = $id;

	}

	/**
	 * @return string
	 * @since 1.0.5
	 */
	public function get_key(){

		return $this->key;

	}

	/**
	 * @param string $key
	 * @since 1.0.5
	 */
	public function set_key( $key ){

		$this->key = $key;

	}

	/**
	 * @return string
	 * @since 1.0.5
	 */
	public function get_type(){

		return $this->type;

	}

	/**
	 * @param string $type
	 * @since 1.0.5
	 */
	public function set_type( $type ){

		$this->type = $type;

	}

	/**
	 * @return mixed|null
	 * @since 1.0.5
	 */
	public function get_data(){

		return $this->data;

	}

	/**
	 * @return mixed
	 * @since 1.0.5
	 */
	public function get_decoded_data(){

        return @json_decode( $this->data, true );

	}

	/**
	 * @param mixed|null $data
	 * @since 1.0.5
	 */
	public function set_data( $data ){

		$this->data = $data;

	}

	/**
	 * @return int
	 * @since 1.0.5
	 */
	public function get_time(){

		return $this->time;

	}

	/**
	 * @param int $time
	 * @since 1.0.5
	 */
	public function set_time( $time ){

		$this->time = $time;

	}

	/**
	 * @param bool $item
	 * Meta name to get, pass empty value to return full meta items
	 * @param mixed $default
	 * Get this default item if meta item doesn't exist
	 *
	 * @return mixed
	 * @since 1.0.5
	 */
	public function get_meta( $item=false, $default="" ){

		if( $item )
			return $this->meta[$item] ?? $default;

		return $this->meta;

	}

	/**
	 * @param array $meta
	 * @since 1.0.5
	 */
	public function set_meta( $meta ){

		$this->meta = $meta;

	}

    /**
     * Export current object values to array
     * @return array
     * @since 1.1.2
     */
    public function export() {
        return array(
            "id" => $this->get_id(),
            "key" => $this->get_key(),
            "data" => $this->get_decoded_data(),
            "type" => $this->get_type(),
            "time" => $this->get_time(),
            "meta" => $this->get_meta()
        );
    }

}