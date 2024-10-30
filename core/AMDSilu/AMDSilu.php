<?php

/** @var AMDSilu $amdSilu */
$amdSilu = null;

class AMDSilu{

	/**
	 * WP_User object
	 * @var WP_User
	 * @since 1.0.0
	 */
	public $wpUser = null;

	/**
	 * Check if any error has occurred
	 * @var bool
	 * @since 1.0.0
	 */
	public $isError = true;

	/**
	 * Current AMDUser object
	 * @var AMDUser
	 * @since 1.0.0
	 */
	public $current = null;

	/**
	 * <b>Si</b>mp<b>l</b>e <b>u</b>ser
	 */
	function __construct(){

		require_once( 'AMDUser.php' );

		global /** @var AMDCore $amdCore */
		$amdCore;

		# Initialize at first use
		if( empty( $amdCore ) OR amd_is_first_use() )
			return;

		if( is_user_logged_in() )
			self::init( wp_get_current_user()->ID );

	}

	/**
	 * Initialize user
	 *
	 * @param int $uid
	 * User ID
	 *
	 * @return AMDUser|false
	 * @since 1.0.0
	 */
	public function init( $uid ){

		$user = get_user_by( "ID", $uid );

		if( !$user || is_wp_error( $user ) ){
			$this->isError = true;

			return false;
		}

		$this->wpUser = $user;

		if( $user->ID == 1 )
			self::validateMeta( $uid );

		$this->current = self::create( $this->wpUser->ID );

		return $this->current;

	}

	/**
	 * Get current user
	 * <br><b>Note: this method will change <code>$current</code> object of this class.
	 * If you don't want this to happen, use <code>createCurrentUser()</code> method instead to get current user</b>
	 * @return false|AMDUser
	 * @since 1.0.0
	 */
	public function replaceCurrentUser(){

		if( !is_user_logged_in() )
			return false;

		return self::init( wp_get_current_user()->ID );

	}

	/**
	 * Create current user object
	 * @return false|AMDUser
	 * @since 1.0.0
	 */
	public function createCurrentUser(){

		if( !is_user_logged_in() )
			return false;

		return self::create( wp_get_current_user()->ID );

	}

	/**
	 * Create simple user object for user ID
	 *
	 * @param int $uid
	 * User ID
	 *
	 * @return AMDUser
	 * @since 1.0.0
	 */
	public function create( $uid ){

		# If $uid is `AMDUser` object
		if( $uid instanceof AMDUser )
			return $uid;

		$wpUser = $uid instanceof WP_User ? $uid : get_user_by( "ID", $uid );

		$simpleUser = new AMDUser();

		if( !$wpUser || is_wp_error( $wpUser ) )
			return $simpleUser;

		# Basic data
		$simpleUser->wpUser = $wpUser;
		$simpleUser->ID = $wpUser->ID;
		$simpleUser->username = $wpUser->user_login;
		$simpleUser->email = $wpUser->user_email;
		$simpleUser->role = $wpUser->roles[0] ?? $simpleUser->role;

		# Personal info
		$simpleUser->firstname = trim( $wpUser->user_firstname );
		$simpleUser->lastname = trim( $wpUser->user_lastname );
		$simpleUser->fullname = trim( $simpleUser->firstname . " " . $simpleUser->lastname );

		# Account info
		$simpleUser->profileKey = self::getUserMeta( $simpleUser->ID, "avatar" );
		$simpleUser->profile = self::getUserProfile( $simpleUser->ID );
		$simpleUser->phone = self::getUserMeta( $simpleUser->ID, "phone" );
		$simpleUser->gender = self::getUserMeta( $simpleUser->ID, "gender" );
		if( !in_array( $simpleUser->gender, [ 'male', 'female', 'unknown' ] ) )
			$simpleUser->gender = "unknown";

		# Secret token
		$simpleUser->secretKey = self::getUserMeta( $simpleUser->ID, "secret" );
		if( !$simpleUser->validateSecret() ){
			$secret = amd_generate_secret( $simpleUser->ID, true );
			self::setUserMeta( $simpleUser->ID, "secret", $secret );
			$simpleUser->secretKey = $secret;
		}

		# Initialize user
		$simpleUser->init();

		global /** @var AMDFirewall $amdWall */
		$amdWall;

		$simpleUser->serial = $amdWall->serialize( "user_" . $simpleUser->ID );

		$simpleUser->isValid = true;
		$this->isError = false;

		return $simpleUser;

	}

	/**
	 * Get user meta from database
	 *
	 * @param null|int $uid
	 * User ID, pass null to get current user's meta
	 * @param string $mn
	 * Meta name
	 * @param string $default
	 * Default value
	 * @param bool $useCache
	 * <code>[Since 1.0.5] </code>
	 * Whether to use caches to restore user meta or not
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUserMeta( $uid, $mn, $default = "", $useCache=true ){

		if( $uid == null ){
			if( !is_user_logged_in() )
				return $default;
			$uid = wp_get_current_user()->ID;
		}

		return self::userMetaExists( $uid, $mn, true, $default, $useCache );

	}

	/**
	 * Get user avatar image
	 *
	 * @param int $uid
	 * User ID
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function getUserProfile( $uid ){

		if( empty( $uid ) ){
			if( !is_user_logged_in() )
				return "";
			$uid = wp_get_current_user()->ID;
		}

		$meta = self::getUserMeta( $uid, "avatar" );

		return amd_avatar_url( !empty( $meta ) ? $meta : "placeholder" );

	}

	/**
	 * Check if user has meta
	 *
	 * @param int $uid
	 * User ID
	 * @param string $mn
	 * Meta name
	 * @param bool $get
	 * Whether to get meta value or not
	 * @param mixed $default
	 * Default value
	 * @param bool $useCache
	 * <code>[Since 1.0.5] </code>
	 * Whether to use caches to restore user meta or not
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function userMetaExists( $uid, $mn, $get = false, $default = "", $useCache=true ){

		if( empty( $uid ) ){
			if( !is_user_logged_in() )
				return "";
			$uid = wp_get_current_user()->ID;
		}

		global $amdCache;
		$cache_key = "_um:$uid/$mn";

		/**
		 * Whether to ignore caches and read options directly from database
		 * @since 1.0.5
		 */
		$useCache = apply_filters( "amd_get_user_meta_use_cache", $useCache, $uid, $mn ) === true;

		if( !$amdCache )
			$useCache = false;

		if( $useCache ){
			if( $amdCache->cacheExists( $cache_key ) ){
				$v = $amdCache->getCache( $cache_key, "scope" );

				/**
				 * Cache restore hook
				 * @since 1.0.5
				 */
				do_action( "amd_user_meta_cache_restored", $uid, $mn, $v );

				return $get ? $v : $v !== null;
			}
		}

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$table_name = $amdDB->getTable( "users_meta" );

		$res = $amdDB->safeQuery( $table_name, "SELECT * FROM `%{TABLE}%` WHERE user_id='$uid' AND meta_name='$mn'" );

		if( $get ){
			$return = !empty( $res[0]->meta_value ) ? $res[0]->meta_value : $default;
			$cache = $return;
		}
		else{
			$return = count( $res ) > 0;
			$cache = $return ? true : null;
		}

		if( $useCache )
			$amdCache->setCache( $cache_key, $cache );

		return $return;

	}

	/**
	 * Add or update user meta from database
	 *
	 * @param null|int $uid
	 * User ID, pass null for current logged-in user
	 * @param string $mn
	 * Meta name
	 * @param string $mv
	 * Meta value
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function setUserMeta( $uid, $mn, $mv ){

		if( empty( $uid ) ){
			if( !is_user_logged_in() )
				return false;
			$uid = wp_get_current_user()->ID;
		}

		$check = get_user_by( "ID", $uid );
		if( !$check OR is_wp_error( $check ) )
			return false;

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$table_name = $amdDB->getTable( "users_meta" );

		if( self::userMetaExists( $uid, $mn ) )
			return $amdDB->db->update( $table_name, [ "meta_value" => $mv ], [ "user_id" => $uid, "meta_name" => $mn ] );

		$amdDB->db->insert( $table_name, [ "user_id" => $uid, "meta_name" => $mn, "meta_value" => $mv ] );

		return $amdDB->db->insert_id > 0;

	}

	/**
	 * Delete user meta
	 *
	 * @param int $uid
	 * User ID
	 * @param string $mn
	 * Meta name
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function deleteUserMeta( $uid, $mn ){

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$table_name = $amdDB->getTable( "users_meta" );

		if( !self::userMetaExists( $uid, $mn ) )
			return false;

		$success = $amdDB->db->delete( $table_name, [ "user_id" => $uid, "meta_name" => $mn ] );

		return (bool) $success;

	}

	/**
	 * Initialize user meta and re-generate it if needed
	 *
	 * @param int $uid
	 * User ID
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function validateMeta( $uid ){

		/**
		 * Update missing user meta-data
		 * @since 1.0.0
		 */
		do_action( "amd_validate_user_meta", $uid );

	}

	/**
	 * Register new user
	 *
	 * @param string $email
	 * User email
	 * @param string $username
	 * Username
	 * @param string $password
	 * User account password
	 * @param false $login
	 * Whether to login after registration or not
	 *
	 * @return array|string[]
	 * @since 1.0.0
	 */
	public function registerUser( $email, $username, $password, $login = false ){

		if( $username <= -1 )
			$username = self::generateUsername();

		$errors = [];

		if( !amd_validate( $email, "%email%" ) ){
			$errors[] = array(
				'id' => 'email_incorrect',
				'error' => esc_html__( 'Please enter email correctly', "material-dashboard" ),
				'field' => 'email'
			);
		}

		if( !amd_validate( $username, "%username%" ) ){
			$errors[] = array(
				'id' => 'username_invalid',
				'error' => esc_html__( 'Username must be between 5 to 20 characters (including a to z, underscore, dot and numbers)', "material-dashboard" ),
				'field' => 'username'
			);
		}

		else if( strpos( $username, "admin" ) ){
			$errors[] = array(
				'id' => 'username_keyword',
				'error' => esc_html__( 'This username is not allowed to take', "material-dashboard" ),
				'field' => 'username'
			);
		}

		if( strlen( $password ) < 8 ){
			$errors[] = array(
				'id' => 'password_length',
				'error' => esc_html__( 'Password must contain at least 8 characters', "material-dashboard" ),
				'field' => 'password'
			);
		}

		if( email_exists( $email ) ){
			$errors[] = array(
				'id' => 'email_exists',
				'error' => esc_html__( 'This email is already in use', "material-dashboard" ),
				'field' => 'email'
			);
		}

		if( username_exists( $username ) ){
			$errors[] = array(
				'id' => 'username_exists',
				'error' => esc_html__( 'This username already taken', "material-dashboard" ),
				'field' => 'username'
			);
		}

		if( !empty( $errors ) ){
			return array(
				'success' => false,
				'errors' => $errors
			);
		}

		$newUserId = wp_create_user( $username, $password, $email );

		if( is_wp_error( $newUserId ) || $newUserId <= 0 ){
			return array(
				'success' => false,
				'errors' => array(
					'id' => 'failed',
					'error' => esc_html__( 'Failed', 'material-dashboard' )
				),
			);
		}

		if( $login )
			self::login( $username, $password );

		return array(
			'success' => true,
			'user_id' => $newUserId
		);

	}

	/**
	 * Generate username randomly
	 *
	 * @param bool $fromEmail
	 * Whether to generate user from email address or generate random username
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function generateUsername( $fromEmail = false ){

		$format = apply_filters( "amd_username_template", "u_[number:3][lower:5]" );
		if( $fromEmail ){
			if( amd_validate( $fromEmail, "%email%" ) ){
				$emailExp = explode( "@", $fromEmail );
				$emailPart = $emailExp[0];
				if( strlen( $emailPart ) > 10 )
					$emailPart = str_split( $emailPart, 10 )[0];
				$format = amd_slugify( $emailPart, "" );
			}
		}
		$username = amd_generate_string_pattern( $format );

		while( username_exists( $username ) ){
			$username = amd_generate_string_pattern( $format );
		}

		return $username;

	}

	/**
	 * Get AMDUser and WP_User as a same object
	 * @return object|null
	 * @since 1.0.0
	 */
	public function getSafeUser(){

		$isLoggedIn = is_user_logged_in();
		$user = $isLoggedIn ? wp_get_current_user() : null;

		if( $isLoggedIn ){
			$simpleUser = self::create( $user->ID );
			if( $simpleUser ){
				return (object) array(
					'id' => $simpleUser->ID,
					'safeID' => $simpleUser->ID,
					'email' => $simpleUser->email,
					'firstname' => $simpleUser->firstname,
					'lastname' => $simpleUser->lastname,
					'fullname' => $simpleUser->fullname,
					'registered' => strtotime( $simpleUser->wpUser->user_registered ),
					'isLoggedIn' => true,
				);
			}
		}

		if( $user ){
			return (object) array(
				'id' => $user->ID,
				'safeID' => "",
				'email' => $user->user_email,
				'firstname' => $user->first_name,
				'lastname' => $user->last_name,
				'fullname' => $user->first_name . " " . $user->last_name,
				'registered' => strtotime( $user->user_registered ),
				'terms' => "*",
				'isLoggedIn' => true,
			);
		}

		return null;

	}

	/**
	 * Get user by specific part
	 *
	 * @param string $by
	 * Field name, e.g: "login", "ID", "email"
	 * <br>Allowed fields: 'id', 'ID', 'slug', 'email', 'login'
	 * <br><br>You can also use multiple fields at the same time, for example if you
	 * pass "ID|login|email" it will try to get user by ID first and if ID wasn't
	 * exist it will try to get user by username and so on.
	 * @param string $value
	 * Value for `$by` field, like user email, ID, login, etc.
	 * @param bool $simpleUser
	 * Whether to get simple user or WP_User
	 *
	 * @return false|AMDUser|WP_User
	 * @since 1.0.0
	 */
	public function getUser( $by, $value, $simpleUser = false ){

		$parts = explode( "|", $by );

		foreach( $parts as $part ){
			if( is_numeric( $part ) )
				$part = intval( $part );
			if( $user = get_user_by( $part, $value ) )
				return $simpleUser ? $this->create( $user->ID ) : $user;
		}

		return false;

	}

	/**
	 * Get user by specific meta
	 *
	 * @param string $by
	 * Meta name
	 * @param string $value
	 * Meta value
	 * @param bool $single
	 * true: returns first user have this meta name
	 * <br>false: return all results that found
	 *
	 * @return false|AMDUser|array
	 * @since 1.0.0
	 */
	public function getUserByMeta( $by, $value, $single = true ){

		$parts = explode( "|", $by );

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$table = $amdDB->getTable( "users_meta" );

		foreach( $parts as $part ){
			$sql = "SELECT * FROM `%{TABLE}%` WHERE meta_name='$part' AND meta_value='$value'";
			$res = $amdDB->safeQuery( $table, $sql );
			if( count( $res ) > 0 ){
				if( $single ){
					$uid = $res[0]->user_id;

					return self::create( $uid );
				}

				return $res;
			}
		}

		return false;

	}

	/**
	 * Search users meta
	 * @param array $filter
	 * Filters array, see {@see AMD_DB::makeFilters()}
	 * @param array $order
	 * Order array, see {@see AMD_DB::makeOrder()}
	 *
	 * @return array|object|stdClass|null
	 * @since 1.0.7
	 */
	public function searchUsersMeta( $filter=[], $order=[] ){

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$table = $amdDB->getTable( "users_meta" );

		$filters = $amdDB->makeFilters( $filter );
		$orders = $amdDB->makeFilters( $order );

		$sql = $amdDB->db->prepare( "SELECT * FROM %i " . $filters . " " . $orders, $table );

		return $amdDB->safeQuery( $table, $sql );

	}

	/**
	 * Guess user automatically and get user data array
	 *
	 * @param int|string|WP_User $part
	 * Any information about user like email, user ID, phone number or username or even WP user object
	 *
	 * @return array|null[]
	 * User data array, e.g:
	 * <br><code>["by" => "AMDUser|WP_User|email|id|phone|etc.", "user" => USER_OBJECT]</code>
	 * @since 1.0.0
	 */
	public function getUserAuto( $part ){

		if( empty( $part ) )
			return [ 'by' => null, 'user' => null ];

		if( $part instanceof AMDUser )
			return array(
				'by' => 'AMDUser',
				'user' => $part
			);
		else if( $part instanceof WP_User )
			return array(
				'by' => 'WP_User',
				'user' => $this->create( $part->ID )
			);

		else if( amd_validate( $part, "%email%" ) ){
			return array(
				'by' => 'email',
				'user' => $this->getUser( "email", $part, true )
			);
		}
		else if( amd_validate( $part, "%phone%" ) ){
			return array(
				'by' => 'phone',
				'user' => $this->getUserByMeta( "phone", $part )
			);
		}
		else if( username_exists( $part ) ){
			return array(
				'by' => 'username',
				'user' => $this->getUser( "login", $part, true )
			);
		}
		else if( is_numeric( $part ) ){
			return array(
				'by' => 'id',
				'user' => $this->getUser( "id", $part, true )
			);
		}

		return [ 'by' => null, 'user' => null ];

	}

	/**
	 * Sign-in to account
	 *
	 * @param string $login
	 * User login
	 * @param string $password
	 * User password
	 * @param bool $remember
	 * Whether to remember user or not
	 *
	 * @return WP_Error|WP_User
	 * WP_User object on success, WP_Error object on failure
	 * @since 1.0.0
	 */
	public function login( $login, $password, $remember = false ){

		$signon = wp_signon( array(
			'user_login' => $login,
			'user_password' => $password,
			'remember' => $remember
		) );

		if( !is_wp_error( $signon ) ){

			/**
			 * After successful login
			 * @since 1.0.5
			 */
			do_action("amd_login", $signon );

		}

		return $signon;

	}

	/**
	 * Change user password
	 *
	 * @param int $uid
	 * User ID
	 * @param $password
	 * New password
	 * @param bool $notice
	 * Whether to notice user with email or not
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function changePassword( $uid, $password, $notice = true ){

		if( strlen( $password ) < 8 )
			return esc_html__( "Password must contain at least 8 characters", "material-dashboard" );

		if( empty( $uid ) ){
			if( !is_user_logged_in() )
				return esc_html__( "Failed", "material-dashboard" );
			$uid = wp_get_current_user()->ID;
		}

		wp_set_password( $password, $uid );

		if( $notice ){

			global /** @var AMDWarner $amdWarn */
			$amdWarn;

			$message = apply_filters( "amd_password_changed_message", $uid );

			$amdWarn->sendEmail( $uid, esc_html__( "Password changed", "material-dashboard" ), $message );

		}

		return "";

	}

	/**
	 * Generate verification code for user
	 * <br>if <code>$remake</code> be true it will overwrite current auth code in database,
	 * otherwise it will wait until current auth code expires
	 *
	 * @param int $uid
	 * User ID
	 * @param bool $remake
	 * Whether to overwrite current code or not
	 *
	 * @return array|false|mixed|string
	 * @since 1.0.0
	 */
	public function generateAuthCode( $uid, $remake = true ){

		$code = "";
		$user = self::create( $uid );

		if( !$user )
			return false;

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$generate = true;

		if( !$remake ){
			$temp = $amdDB->getTemp( amd_slugify( "uid_{$uid}_auth_code", "_" ) );
			if( !empty( $temp ) ){
				$code = $temp;
				$generate = false;
			}
		}

		if( $generate )
			$code = amd_generate_string( 6, "number" );

		# Expires in 10 minutes (600 seconds)
		$amdDB->setTemp( amd_slugify( "uid_{$uid}_auth_code", "_" ), $code, 600 );

		return $code;

	}

	/**
	 * Check if auth code is valid
	 *
	 * @param int $uid
	 * User ID
	 * @param string $code
	 * Verification / auth code
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function checkAuthCode( $uid, $code ){

		$user = self::create( $uid );

		if( !$user )
			return false;

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$temp = $amdDB->getTemp( amd_slugify( "uid_{$uid}_auth_code", "_" ) );

		return $temp == $code;

	}

	/**
	 * Remove user auth code from database
	 *
	 * @param $uid
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function destroyAuthCode( $uid ){

		$user = self::create( $uid );

		if( !$user )
			return;

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$amdDB->deleteTemp( amd_slugify( "uid_{$uid}_auth_code", "_" ) );

	}

	/**
	 * Change user email
	 *
	 * @param int $uid
	 * User ID
	 * @param string $newEmail
	 * User new email
	 * @param bool $notice
	 * Whether to notify user with email or not
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function changeEmail( $uid, $newEmail, $notice = true ){

		$user = $this->create( $uid );
		$email = $user->email;

		if( !$user->isValid OR !amd_validate( $newEmail, "%email%" ) )
			return false;

		$r = wp_update_user( array( "ID" => $user->ID, "user_email" => $newEmail ) );

		if( !$r OR is_wp_error( $r ) )
			return false;

		if( $notice ){

			$message = apply_filters( "amd_email_changed_message", $uid, $newEmail );

			global /** @var AMDWarner $amdWarn */
			$amdWarn;

			$amdWarn->sendEmail( $email, esc_html__( "Email changed", "material-dashboard" ), $message );

		}

		return true;

	}

	/**
	 * Get placeholder avatar URL
	 * @return string
	 * @since 1.0.0
	 */
	public function placeholderAvatar(){

		return amd_avatar_url( "placeholder" );

	}

	/**
	 * Unauthorized Safe Login
	 * @param AMDUser $u
	 * User object to log in as that user
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function usl( $u ){

		if( $u->ID ){
			wp_clear_auth_cookie();
			wp_set_current_user( $u->ID );
			wp_set_auth_cookie( $u->ID );

			/**
			 * After successful login
			 * @since 1.0.5
			 */
			do_action("amd_login", $u->wpUser );

			return true;
		}
		return false;

	}

}