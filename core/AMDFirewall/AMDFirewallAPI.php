<?php

class AMDFirewallAPI {

	/**
	 * Current user object
	 * @var false|AMDUser
	 */
	protected $user;

	/**
	 * Last error ID
	 * @var string
	 */
	protected $last_error;

	/**
	 * Last request data
	 * @var string|callable
	 */
	protected $last_request;

	/**
	 * Last request response
	 * @var array
	 */
	protected $last_response;

	/**
	 * Availability check
	 * @var bool
	 */
	public $available;

	/**
	 * API handler
	 */
	public function __construct(){

		# Set current user object
	    # 'AMDUser' object if logged-in, otherwise 'false'
	    $this->user = amd_get_current_user();

		$this->last_error = "";

		$this->last_request = "";

		$this->last_response = [];

		$this->available = true;

    }

	/**
	 * Send JSON API error
	 * @param string $error_id
	 * Error ID
	 * @param string $msg
	 * Response message
	 * @param mixed $data
	 * Response data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function error( $error_id, $msg="", $data=[] ){

		amd_send_api_error( array(
			"error_id" => $error_id,
			"msg" => $msg,
			"data" => $data
		) );

	}

	/**
	 * Handle API requests
	 * @return void
	 * @since 1.0.0
	 */
	public function handleAPI(){

		$api_enabled = apply_filters( "amd_api_enabled", true );

		if( !$api_enabled ){
			$this->last_request = "";
			self::error( "api_disabled", esc_html_x( "Access denied", "API message", "material-dashboard" ) );
			return;
		}

		global /** @var AMDFirewall $amdWall */
		$amdWall;

		$handlers = $amdWall->getAPIHandler();

		if( !empty( $handlers ) AND is_countable( $handlers ) AND count( $handlers ) > 0 ){

			foreach( $handlers as $data ){

				# Get handler and allowed methods
				$handler = $data["handler"] ?? null;
				$allowed_method = strtolower( $data["allowed_method"] ?? "post" );

				# Skip item if handler is not valid or allowed methods object is missing
				if( empty( $handler ) OR !is_callable( $handler ) OR empty( $allowed_method ) )
					continue;

				# Sanitize every $_GET parameter and store it into $get variable
				$get = amd_sanitize_get_fields( $_GET );

				# Sanitize $_POST object and store it into $r object
				$post = amd_sanitize_post_fields( $_POST );

				# Sanitize every $_FILES item
				$files = amd_sanitize_files( $_FILES );

				# Request object
				$r = null;

				if( $allowed_method == "get" )
					$r = $get;
				else if( $allowed_method == "post" )
					$r = $post;
				else if( $allowed_method == "files" )
					$r = $files;
				else if( $allowed_method == "*" )
					$r = array_merge( $get, $post, $files );

                /**
                 * @since 1.1.2
                 */
                do_action( "amd_before_handlers", $r, $handler );

                if( is_string( $handler ) ) {
                    /**
                     * @since 1.1.2
                     */
                    do_action( "amd_before_handler_$handler", $r );
                }

				call_user_func( $handler, $r );

			}

		}

		self::error( "bad_request", esc_html_x( "Request is not executable", "API message", "material-dashboard" ) );

	}

	/**
	 * Get last API error
	 * @return string
	 * @since 1.0.0
	 */
	public function getLastError(){

		return $this->last_error;

	}

	/**
	 * Get last API response
	 * @return array
	 * @since 1.0.0
	 */
	public function getLastResponse(){

		return $this->last_response;

	}

	/**
	 * Get last API request callback
	 * @return string|callable
	 * @since 1.0.0
	 */
	public function getLastRequest(){

		return $this->last_request;

	}

}