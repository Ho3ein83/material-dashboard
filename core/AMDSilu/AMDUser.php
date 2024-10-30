<?php

class AMDUser{

	/**
	 * WP User object
	 * @var WP_User
	 * @since 1.0.0
	 */
	public $wpUser;

	/**
	 * User ID
	 * @var int
	 * @since 1.0.0
	 */
	public $ID;

	/**
	 * Username
	 * @var string
	 * @since 1.0.0
	 */
	public $username;

	/**
	 * User email
	 * @var string
	 * @since 1.0.0
	 */
	public $email;

	/**
	 * User firstname
	 * @var string
	 * @since 1.0.0
	 */
	public $firstname;

	/**
	 * User lastname
	 * @var string
	 * @since 1.0.0
	 */
	public $lastname;

	/**
	 * User fullname (appended firstname and lastname)
	 * @var string
	 * @since 1.0.0
	 */
	public $fullname;

	/**
	 * User avatar picture URL
	 * @var string
	 * @since 1.0.0
	 */
	public $profile;

	/**
	 * User avatar key
	 * @var string
	 * @since 1.0.0
	 */
	public $profileKey;

	/**
	 * User phone number
	 * @var string
	 * @since 1.0.0
	 */
	public $phone;

	/**
	 * User role
	 * @var string
	 * @since 1.0.0
	 */
	public $role;

	/**
	 * User gender
	 * @var string
	 * @since 1.0.0
	 */
	public $gender;

	/**
	 * User secret key
	 * @var string
	 * @since 1.0.0
	 */
	public $secretKey;

	/**
	 * User serial number for personal data encryption
	 * @var string
	 * @since 1.0.0
	 */
	public $serial;

	/**
	 * Whether user is valid or not
	 * @var bool
	 * @since 1.0.0
	 */
	public $isValid;

	/**
	 * User extra information
	 * @var array
	 * @since 1.0.6
	 */
	public $extra;

	/**
	 * Simple user object
	 */
	function __construct(){
		$this->wpUser = null;
		$this->ID = 0;
		$this->username = "";
		$this->email = "";
		$this->firstname = "";
		$this->lastname = "";
		$this->fullname = "";
		$this->profile = "";
		$this->phone = "";
		$this->role = "subscriber";
		$this->gender = "unknown";
		$this->secretKey = "";
		$this->serial = "";
		$this->isValid = false;
	}

	/**
	 * Initialize user
	 * @return void
	 * @since 1.0.6
	 */
	public function init(){

		/**
		 * Get user extra information
		 * @since 1.0.6
		 */
		$this->extra = apply_filters( "amd_simple_user_extra", [], $this );

	}

	/**
	 * Check if user is admin
	 * @return bool
	 * @since 1.0.0
	 */
	public function isAdmin(){

		return $this->role == "administrator";

	}

	/**
	 * Check if secret code is valid or not
	 *
	 * @param string|null $secret
	 * Secret code, pass null to check this user secret code
	 *
	 * @return bool
	 * Whether secret key is valid or not
	 * @since 1.0.0
	 */
	public function validateSecret( $secret = null ){

		if( $secret == null )
			$secret = $this->secretKey;

		return amd_validate( $secret, "/^[A-Z][J|FMASOND][a-z][0-9]{4}[a-zA-Z]{4}$/" );

	}

	/**
	 * Get registration time and date difference
	 * @return array
	 * Data array
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function getRegistration(){

		$registered = $this->getRegistrationTime();

		return array(
			"time" => $registered,
			"diff" => amd_get_date_diff( $registered ),
			"pass" => amd_get_time_pass_str( $registered ),
			"pass_ago" => amd_get_time_pass_str( $registered, true ),
		);

	}

	/**
	 * Get user registration time
	 * @return false|int
	 * @since 1.0.0
	 */
	public function getRegistrationTime(){

		return strtotime( $this->wpUser->user_registered );

	}

	/**
	 * Get user last seen time
	 * @return array|false[]
	 * @since 1.0.0
	 */
	public function getLastSeen(){

		$time = $this->getLastSeenTime();

		if( empty( $time ) )
			return array(
				"time" => false,
				"diff" => false,
				"pass" => false,
				"pass_ago" => false,
			);

		$ago = amd_get_time_pass_str( $time, true );
		$diff = amd_get_date_diff( $time );
		$pass = time() - $time > 86400 ? $ago : esc_html__( "Today", "material-dashboard" ) . " " . amd_true_date( "H:i", $time );
		$str = $diff > 0 ? amd_true_date( "j F Y", $time ) . " ($pass)" : $pass;

		return array(
			"time" => $time,
			"diff" => $diff,
			"pass" => amd_get_time_pass_str( $time ),
			"pass_ago" => $pass,
			"str" => $str,
		);

	}

	/**
	 * Get user last seen time from database
	 * @return string
	 * @since 1.0.0
	 */
	public function getLastSeenTime(){

		return amd_get_user_meta( $this->ID, "last_seen" );

	}

	/**
	 * Get user avatar image
	 * @return string
	 * @since 1.0.0
	 */
	public function getProfileURL(){

		if( get_current_user_id() == $this->ID )
			$edit_link = get_edit_profile_url( $this->ID );
		else
			$edit_link = add_query_arg( 'user_id', $this->ID, self_admin_url( 'user-edit.php' ) );

		return $edit_link;

	}

	/**
	 * Check if user is online or not
	 * @return bool
	 * @since 1.0.0
	 */
	public function isOnline(){
		$temp = amd_get_temp( "checkin_" . $this->ID );

		return (bool) $temp;
	}

	/**
	 * Check if specified role(s) is included in user roles
	 * @param string|array $role_s
	 * Single role string or array listed roles items
	 *
	 * @return bool
	 * @since 1.0.6
	 */
	public function compareRoles( $role_s ){

		if( is_string( $role_s ) )
			$role_s = [$role_s];

		if( !is_array( $role_s ) )
			return false;

		foreach( $role_s as $role ){
			if( in_array( $role, $this->wpUser->roles ) )
				return true;
		}

		return false;

	}

	/**
	 * Check if this object belongs to current user or not
	 * @return bool
	 * @since 1.1.2
	 */
	public function isCurrentUser(){

		return $this->ID == get_current_user_id();

	}

	/**
	 * Get user role text
	 * @param string|null $role
	 * User role or pass null to use this user role
	 *
	 * @return string
	 * Translated role name
	 * @since 1.0.8
	 */
	public function getRoleName( $role=null ){

		if( $role === null )
			$role = $this->role;

		$role = _x( ucfirst( $role ), "User role" );

		$role = _x( ucfirst( $role ), "User role", "material-dashboard-pro" );

		return $role;

	}

}