<?php

/** @var AMDCache $amdCache */
$amdCache = null;

class AMDCache{

	/**
	 * Time stamps
	 * @sicne 1.0.0
	 */
	const STAMPS = array(
		'minute' => 60,
		'hour' => 3600,
		'day' => 86400,
		'week' => 604800,
		'month' => 2592000
	);

	/**
	 * Cookies and sessions prefix
	 * @sicne 1.0.0
	 */
	const PREFIX = 'amd_';

	/**
	 * Variable scope
	 * @var stdClass
	 * @sicne 1.0.0
	 */
	public $scope;

	/**
	 * Public cache variables
	 * @var array
	 * @sicne 1.0.0
	 */
	public $cache;

	/**
	 * Default variables
	 * @var array
	 * @sicne 1.0.0
	 */
	protected $defaults;

	/**
	 * CSS stylesheets URL
	 * @var array
	 * @sicne 1.0.0
	 */
	protected $css;

	/**
	 * JavaScript scripts URL
	 * @var array
	 * @sicne 1.0.0
	 */
	protected $js;

	/**
	 * 24 hours -> 1 day
	 * @var int
	 * @sicne 1.0.0
	 */
	public $COOKIE_DAY = 24;

	/**
	 * 168 hours -> 7 days
	 * @var int
	 * @sicne 1.0.0
	 */
	public $COOKIE_WEEK = 168;

	/**
	 * 720 hours -> 30 days
	 * @var int
	 * @sicne 1.0.0
	 */
	public $COOKIE_MONTH = 720;

	/**
	 * Array for storing data with multiple groups
	 * @var array
	 * @sicne 1.0.0
	 */
	public $SCOPE_GROUP = [];

	/**
	 * Cache, sessions and cookies manager
	 */
	public function __construct(){

        # Clear scope values
		$this->scope = new stdClass();

        # Reset cache values
		$this->cache = [];

		# Get styles and scripts version
		$versions = AMD_VERSION_CODES;

		# Admin
		$this->addStyle( 'admin:admin', amd_get_api_url( '?stylesheets=_admin&ver=' . $versions["admin"] ?? "unset" ) );
		$this->addScript( 'admin:admin', AMD_JS . '/admin.js', $versions["admin"] ?? "unset" );

		# Admin: Coloris module
		$this->addStyle( 'admin:coloris', AMD_MOD . '/coloris/coloris.min.css', null );
		$this->addScript( 'admin:coloris', AMD_MOD . '/coloris/coloris.min.js', null );

		# Theme variables
		$this->addStyle( 'global:vars', amd_get_api_url( '?stylesheets=_vars&ver=' . $versions["vars"] ?? "unset" ), null );

		# Global
		$this->addStyle( 'global:structure', AMD_CSS . '/structure.css', $versions["structure"] ?? "unset" );
		$this->addScript( 'global:bundle', AMD_JS . '/bundle.js', $versions["bundle"] ?? "unset" );

		# Global: Hello popup module
		$this->addScript( 'global:hello', AMD_MOD . '/hello/hello.js', $versions["hello-pop"] ?? null );
		$this->addStyle( 'global:hello', AMD_MOD . '/hello/hello.css', $versions["hello-pop"] ?? null );

		# Dashboard: Cropper module
		$this->addStyle( 'dashboard:cropper', AMD_MOD . '/cropper/cropper.css', "1.5.11" );
		$this->addScript( 'dashboard:cropper', AMD_MOD . '/cropper/cropper.js', "1.5.11" );

        # Dashboard: Quill JS editor
		$this->addStyle( 'global:quill', AMD_MOD . '/quill-editor/quill.snow.css', "1.0.0" );
		$this->addScript( 'global:quill', AMD_MOD . '/quill-editor/quill.js', "1.0.0" );

	}

	/**
	 * Store data in multiple groups
	 *
	 * @param string $group
	 * Group ID
	 * @param string|array $id_s
	 * Group data: array for recursive list, string for single item
	 * @param mixed $value
	 * Item value: if `$id_s` is an array leave it empty
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function setScopeGroup( $group, $id_s, $value = "" ){
		if( is_array( $id_s ) ){
			foreach( $id_s as $id => $v )
				self::setScopeGroup( $group, $id, $v );
		}
		else if( is_scalar( $id_s ) ){
			$this->SCOPE_GROUP[$group][$id_s] = $value;
		}
	}

    /**
	 * Replace group data
	 *
	 * @param string $group
	 * Group ID
	 * @param mixed $new_value
     * New value to replace
	 *
	 * @return void
	 * @sicne 1.0.4
	 */
	public function updateScopeGroup( $group, $new_value ){
		if( isset( $this->SCOPE_GROUP[$group] ) )
            $this->SCOPE_GROUP[$group] = $new_value;
	}

	/**
	 * Get group items.
	 * if <code>$id</code> is null, it returns the whole group and returns empty array if group is not set<br>
	 * if <code>$id</code> is not null, returns items with specified id and returns empty array if id is not set
	 *
	 * @param string $group
	 * Group ID
	 * @param null|string $id
	 * Group id: leave it to null to get the whole group data
	 * @param bool $sort
	 * Whether to sort array or not
	 *
	 * @return array|mixed
	 * @sicne 1.0.0
	 */
	public function getScopeGroup( $group, $id = null, $sort = false ){
		if( empty( $id ) )
			$data = $this->SCOPE_GROUP[$group] ?? [];
		else
			$data = $this->SCOPE_GROUP[$group][$id] ?? [];

		if( $sort AND is_array( $data ) ){
			usort( $data, function( $a, $b ){
				return ( $a["priority"] ?? 10 ) - ( $b["priority"] ?? 10 );
			} );
		}

		return $data;
	}

	/**
     * Remove item groups item
	 *
	 * @param string $group
     * Group ID
	 * @param string|null $id
     * Item ID, pass null to remove all items
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function removeScopeGroup( $group, $id=null ){

        if( isset( $this->SCOPE_GROUP[$group] ) ){

            if( isset( $this->SCOPE_GROUP[$group][$id] ) )
                unset( $this->SCOPE_GROUP[$group][$id] );
            else
	            unset( $this->SCOPE_GROUP[$group] );

        }

	}

	/**
	 * Remove cache if exists.
	 * <br>Note: Cache contains sessions, cookies and scope variables
	 *
	 * @param string $key
     * Cache key (without prefix)
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function removeCache( $key ){

		$id = self::PREFIX . $key;

		# Remove cookie
		if( self::cookieExists( $key ) ){

			// Just making sure
			$_COOKIE[$id] = null;

			setcookie( $id, "", time() - 3600, "/" );

			unset( $_COOKIE[$id] );

		}

		# Remove session
		if( self::sessionExists( $key ) ){

			$_SESSION[$id] = null;

			unset( $_SESSION[$id] );

		}

		# remove cache
		if( self::cacheExists( $key ) ){

			$this->cache[$key] = null;

			unset( $this->cache[$key] );

		}

	}

	/**
	 * Remove cookie if exists
	 *
	 * @param string $key
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function removeCookie( $key ){

		$id = self::PREFIX . $key;

		// Remove cookie by changing the expiration time
		if( self::cookieExists( $key ) )
			setcookie( $id, "", time() - 3600, "/" );

	}

	/**
	 * Check if cookie exists and get it if <code>$get</code> is true
	 *
	 * @param string $key
	 * Cookie key (`PREFIX` constant will be added to the beginning)
	 * @param bool $get
	 * Whether to get cookie value (if exists) or not
	 *
	 * @return bool|mixed|string
	 * @sicne 1.0.0
	 */
	public function cookieExists( $key, $get = false ){

		$id = self::PREFIX . $key;

		return ( isset( $_COOKIE[$id] ) AND $_COOKIE[$id] != null ) ? ( $get ? $_COOKIE[$id] : true ) : ( $get ? "" : false );

	}

	/**
	 * Check if session exists and get it if <code>$get</code> is true
	 *
	 * @param string $key
	 * Session key (`PREFIX` constant will be added to the beginning)
	 * @param bool $get
	 * Whether to get session value (if exists) or not
	 *
	 * @return bool|mixed|string
	 * @sicne 1.0.0
	 */
	public function sessionExists( $key, $get = false ){

		$id = self::PREFIX . $key;

		return ( isset( $_SESSION[$id] ) AND $_SESSION[$id] != null ) ? ( $get ? $_SESSION[$id] : true ) : ( $get ? "" : false );

	}

	/**
	 * Check if `$cache` variable contains an element
	 *
	 * @param string $key
	 * Cache key
	 * @param false $get
	 * Whether to get cache (if exists) or not
	 * @param string $default
	 * Default value that return if cache doesn't exist
	 *
	 * @return bool|mixed|string
	 * @sicne 1.0.0
	 */
	public function cacheExists( $key, $get = false, $default = "" ){

		return !empty( $this->cache[$key] ) ? ( $get ? $this->cache[$key] : true ) : ( $get ? $default : false );

	}

	/**
	 * Get cache if exist
	 *
	 * @param string $key
	 * Cache key
	 * @param string $type
	 * Cache type: "cookie" | "session" | "scope"
	 *
	 * @return mixed|null
	 * @sicne 1.0.0
	 */
	public function getCache( $key, $type = "*" ){

		if( $cache = self::cookieExists( $key, true ) AND in_array( $type, [ "*", "cookie" ] ) )
			return $cache;

		if( $cache = self::sessionExists( $key, true ) AND in_array( $type, [ "*", "session" ] ) )
			return $cache;

		if( $cache = self::cacheExists( $key, true ) AND in_array( $type, [ "*", "scope" ] ) )
			return $cache;

		return null;

	}

	/**
	 * Set cache
	 * <br>Note: Cache contains sessions, cookies and scope variables
	 *
	 * @param string $key
	 * Cache key (`PREFIX` constant will be added to the beginning for cookies)
	 * @param string|mixed $value
	 * Cache value (for cookie only string is acceptable)
	 * @param null|true|int $cookie
	 * Number of days or true for 1 day or null to skip adding cookie
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function setCache( $key, $value, $cookie = null ){

		$this->cache[$key] = $value;

		$id = self::PREFIX . $key;

		if( $cookie )
			setcookie( $id, $value, time() + ( self::STAMPS['day'] * ( is_int( $cookie ) ? $cookie : 1 ) ), '/' );

	}

	/**
	 * Set cookie
	 *
	 * @param string $key
	 * Cookie key (`PREFIX` constant will be added to the beginning)
	 * @param string $value
	 * Cookie value
	 * @param bool|int $cookie
	 * Number of days or true for 1 day
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function setCookie( $key, $value, $cookie = false ){

		$id = self::PREFIX . $key;

		if( !headers_sent() )
			setcookie( $id, $value, time() + ( self::STAMPS['day'] * ( is_int( $cookie ) ? $cookie : 1 ) ), '/' );

	}

	/**
	 * Set session
	 *
	 * @param string $key
	 * Session key (`PREFIX` constant will be added to the beginning)
	 * @param string $value
	 * Session value
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function setSession( $key, $value ){

		$id = self::PREFIX . $key;

		$_SESSION[$id] = $value;

	}

	/**
	 * Get session if exists
	 *
	 * @param string $key
	 * Session key (`PREFIX` constant will be added to the beginning)
	 *
	 * @return mixed|null
	 * @sicne 1.0.0
	 */
	public function getSession( $key ){

		$id = self::PREFIX . $key;

		return $_SESSION[$id] ?? null;

	}

	/**
	 * Check if cache is set
	 * <br>Note: Cache contains sessions, cookies and scope variables
	 *
	 * @param string $key
	 * Cache key (`PREFIX` constant will be added to the beginning for sessions and cookies)
	 * @param bool $onlyCookie
	 * Whether to only check cookie or all cache methods
	 *
	 * @return bool
	 * @sicne 1.0.0
	 */
	public function cacheIsset( $key, $onlyCookie = false ){

		$cookieExists = self::cookieExists( $key );

		if( $onlyCookie )
			return $cookieExists;

		if( $cookieExists )
			return true;

		if( self::cacheExists( $key ) )
			return true;

		if( self::cacheExists( $key ) )
			return true;

		return false;

	}

	/**
	 * Add CSS stylesheet
	 *
	 * @param string $id
     * Stylesheet ID. You can specify stylesheet scope by adding ':' character to the beginning of ID. e.g: "dashboard:style_id"
	 * @param string $url
     * Stylesheet URL
	 * @param string $ver
     * Stylesheet version
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function addStyle( $id, $url, $ver = "unknown" ){

		$this->css[$id] = array(
			'id' => $id,
			'url' => $url,
			'ver' => $ver
		);

	}

	/**
	 * Add JS script
	 *
	 * @param string $id
	 * Script ID. You can specify script scope by adding ':' character to the beginning of ID. e.g: "dashboard:script_id"
	 * @param string $url
	 * Script URL
	 * @param string $ver
	 * Script version
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function addScript( $id, $url, $ver = "unknown" ){

		$this->js[$id] = array(
			'id' => $id,
			'url' => $url,
			'ver' => $ver
		);

	}

	/**
     * Print stylesheets
	 *
	 * @param string $scope
     * Stylesheet scope
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function dumpStyles( $scope = null ){

		if( !is_array( $this->css ) OR empty( $this->css ) )
			return;

		foreach( $this->css as $id => $data ){
			if( !empty( $scope ) AND !amd_starts_with( $id, "$scope:" ) )
				continue;
			if( empty( $scope ) AND strpos( $id, ":" ) !== false )
				continue;
			$v = $data['ver'];
			# @formatter off
?>
    <link rel="stylesheet" href="<?php echo esc_url( $data['url'] . ( !empty( $v ) ? '?ver=' . $v : '' ) ); ?>">
<?php
			# @formatter on
		}

	}

	/**
     * Print script
	 * @param string $scope
     * Script scope
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function dumpScript( $scope = null ){

		if( !is_array( $this->js ) or empty( $this->js ) )
			return;

		foreach( $this->js as $id => $data ){
			if( !empty( $scope ) AND !amd_starts_with( $id, "$scope:" ) )
				continue;
			if( empty( $scope ) AND strpos( $id, ":" ) !== false )
				continue;
			$v = $data['ver'];
			# @formatter off
?>
    <script src="<?php echo esc_url( $data['url'] . ( !empty( $v ) ? '?ver=' . $v : '' ) ); ?>"></script>
<?php
			# @formatter on
		}

	}

	/**
     * Remove stylesheet by ID
	 * @param string $id
     * Stylesheet ID
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function removeStyleById( $id ){

		if( isset( $this->css[$id] ) )
			unset( $this->css[$id] );

	}

    /**
     * Remove script by ID
	 * @param string $id
     * Script ID
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function removeScriptById( $id ){

		if( isset( $this->js[$id] ) )
			unset( $this->js[$id] );

	}

    /**
     * Remove stylesheet by scope
	 * @param string $scope
     * Scope name
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function removeStyleByScope( $scope ){

		if( !is_array( $this->css ) OR empty( $this->css ) )
			return;

		foreach( $this->css as $id => $data ){
			if( !empty( $scope ) AND !amd_starts_with( $id, "$scope:" ) )
				continue;
			if( empty( $scope ) AND strpos( $id, ":" ) !== false )
				continue;
            self::removeStyleById( $id );
		}

	}

    /**
     * Remove script by scope
	 * @param string $scope
     * Scope name
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function removeScriptByScope( $scope ){

		if( !is_array( $this->js ) OR empty( $this->js ) )
			return;

		foreach( $this->js as $id => $data ){
			if( !empty( $scope ) AND !amd_starts_with( $id, "$scope:" ) )
				continue;
			if( empty( $scope ) AND strpos( $id, ":" ) !== false )
				continue;
            self::removeScriptById( $id );
		}

	}

	/**
	 * Get defaults value
	 *
	 * @param string $key
     * Item key
	 * @param mixed $default
     * Return this value if item doesn't exist
	 *
	 * @return mixed|string
	 * @sicne 1.0.0
	 */
	public function getDefault( $key, $default = "" ){

		return isset( $this->defaults[$key] ) ? $this->defaults[$key] : $default;

	}

	/**
	 * Set defaults value
	 *
	 * @param string $key
     * Item key
	 * @param mixed $value
     * Item value
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function setDefault( $key, $value ){

		$this->defaults[$key] = $value;

	}

	/**
	 * Set default value only if not exist
	 *
	 * @param string $key
     * Item key
	 * @param mixed $value
     * Item value
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function addDefault( $key, $value ){

		if( !empty( $this->defaults[$key] ) )
			return;

		self::setDefault( $key, $value );

	}

	/**
	 * Set locale with adding user meta and cookie
	 *
	 * @param string $locale
	 * Locale code
	 * @param bool $switch
     * Whether to switch locale
	 *
	 * @return void
	 * @sicne 1.0.0
	 */
	public function setLocale( $locale, $switch=true ){

        if( $switch )
            amd_switch_locale( $locale, false );

		if( is_user_logged_in() )
			update_user_meta( get_current_user_id(), "locale", $locale );
		else
			self::setCookie( "locale", $locale, self::STAMPS["month"] );

	}

	public function isDashboard(){



    }

}