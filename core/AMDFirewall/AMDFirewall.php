<?php

/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpRedundantVariableDocTypeInspection */

/** @var AMDFirewall $amdWall */
$amdWall = null;

class AMDFirewall{

	/**
	 * API handler object
	 * @var AMDFirewallAPI
	 * @since 1.0.0
	 */
	protected $api_handler;

	/**
	 * Handle custom APIs
	 * @since 1.0.0
	 * @var array
	 */
	protected $api_handlers;

	/**
	 * Protect your data and API requests
	 * @return void
	 * @since 1.0.0
	 */
	function __constructor(){

		# Initialize hooks
		self::initHooks();

	}

	/**
	 * Initialize hooks
	 * @return void
	 * @since 1.0.5
	 */
	public function initHooks(){}

	/**
	 * Handle wp-login requests
	 * @return void
	 * @since 1.0.0
	 */
	public function loginInit(){

		# Redirect wp-login to dashboard login page
		$hideLogin = amd_get_site_option( "hide_login", "false" );
		if( $hideLogin == "true" ){
			if( amd_is_wp_login() ){
				wp_safe_redirect( amd_get_login_page() );
				exit();
			}
		}

	}

	/**
	 * Handle login failed attempt
	 *
	 * @param string $ip
	 * Client IP address
	 * @param string $username
	 * Username
	 * @param WP_Error $error
	 * Login error object
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function handleLoginAttempt( $ip, $username, $error ){

		$temp_key = "login_attempt_$ip";
		$t = amd_get_temp( $temp_key );

		$expire = apply_filters( "amd_login_too_many_attempts_expire", 10 ) * 60;

		if( $t )
			amd_set_temp( $temp_key, $t + 1, $expire );
		else
			amd_set_temp( $temp_key, 1, $expire );

	}

	/**
	 * Handle rest API endpoint
	 *
	 * @param $endpoints
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function handleRestEndpoints( $endpoints ){

		# Disable users JSON from rest API
		if( !amd_is_admin() ){
			if( isset( $endpoints["/wp/v2/users"] ) )
				unset( $endpoints['/wp/v2/users'] );
			if( isset( $endpoints["/wp/v2/users/(?P<id>[\d]+)"] ) )
				unset( $endpoints["/wp/v2/users/(?P<id>[\d]+)"] );
		}

		return $endpoints;

	}

	/**
	 * Give IP address some privileges.
	 * <br>Privileges are an access level for admin login and secured APIs
	 *
	 * @param $ip
	 * @param $expire
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function addIPPrivilege( $ip = null, $expire = 3600 ){

		if( empty( $ip ) )
			$ip = self::getActualIP();

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$amdDB->setTemp( "ip_privilege_$ip", $ip, $expire );

		return $ip;

	}

	/**
	 * Check if client IP address has privilege.
	 * <br>Privileges are an access level for admin login and secured APIs
	 *
	 * @param $ip
	 * Client IP
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function hasIPPrivilege( $ip = null ){

		if( empty( $ip ) )
			$ip = self::getActualIP();

		global /** @var AMD_DB $amdDB */
		$amdDB;

		$tempKey = "ip_privilege_$ip";
		$t = $amdDB->getTemp( $tempKey, false );
		if( !empty( $t ) ){
			$expire = $t->expire ?? 0;
			if( $expire > time() )
				return true;
			$amdDB->deleteTemp( $tempKey );
		}

		return false;

	}

	/**
	 * Get client real IP address, you can add IP finder APIs to get the actual IP address in local machines
	 * @return mixed
	 * @since 1.0.0
	 */
	public function getActualIP(){

		return apply_filters( "amd_firewall_remote_ip", sanitize_text_field( $_SERVER["REMOTE_ADDR"] ) );

	}

	/**
	 * Encrypt with firewall internal encrypting method
	 * <br><b>Note: this method won't hash your string, and it's not impossible to decode, it only meant to
	 * hide original string from normal users. For important data use <code>encryptAES</code> method instead.</b>
	 *
	 * @param string $str
	 * Input string
	 * @param string $eChar
	 * Separator character
	 *
	 * @return string
	 * Encoded string.
	 * <br>E.g: "hello" -> "d049ca49d849d849de49"
	 * @see AMDFirewall::decode()
	 * @since 1.0.0
	 */
	public function encode( $str, $eChar = null ){
		$str = strval( $str );
		$ec = !empty( $eChar ) ? $eChar : self::getSeperatorChar();
		$s = "";
		for( $i = 0; $i < strlen( $str ); $i++ )
			$s .= self::factorChar( $str[$i] ) . $ec;

		return $s;

	}

	/**
	 * Decrypt with firewall internal encrypting method
	 *
	 * @param string $str
	 * Input string
	 * @param string $eChar
	 * Separator character
	 *
	 * @return string
	 * Decoded string
	 * <br>E.g: "d049ca49d849d849de49" -> "hello"
	 *
	 * @see AMDFirewall::encode()
	 * @since 1.0.0
	 */
	public function decode( $str, $eChar = null ){
		$str = strval( $str );
		$ec = !empty( $eChar ) ? $eChar : self::getSeperatorChar();
		$s = "";
		$exp = explode( $ec, $str );
		foreach( $exp as $item )
			$s .= self::refactorChar( $item );

		return $s;
	}

	/**
	 * Replace strings with keymap value
	 *
	 * @param $str
	 * Input string
	 *
	 * @return string
	 * Serialized string.
	 * <br>"hello" -> "0pLD80Bg7Bg713A"
	 * @since 1.0.0
	 */
	public function serialize( $str ){

		$keymap = $this->getKeymap();

		$out = "";
		for( $i = 0; $i < strlen( $str ); $i++ ){
			$char = $str[$i];
			$out .= $keymap[$char] ?? $char;
		}

		return $out;

	}

	/**
	 * Replace keymap items with string
	 *
	 * @param $str
	 * Serialized string
	 *
	 * @return string
	 * Deserialized string
	 * <br>"0pLD80Bg7Bg713A" -> "hello"
	 * @since 1.0.0
	 */
	public function deserialize( $str ){

		$keymap = $this->getKeymap( true );

		$out = "";
		$split = str_split( $str, 3 );

		for( $i = 0; $i < count( $split ); $i++ )
			$out .= $keymap[$split[$i]] ?? $split[$i];

		return $out;

	}

	/**
	 * Encode character ascii code
	 *
	 * @param $d
	 * Character
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function factorChar( $d ){
		try{
			$n = ord( $d );
			$n = $n * 2;

			return dechex( $n );
		} catch( Exception $e ){
			return $d;
		}
	}

	/**
	 * Decode character ascii code
	 *
	 * @param $d
	 * Character
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function refactorChar( $d ){
		try{
			$n = hexdec( $d );
			$n = intval( $n / 2 );

			return chr( $n );
		} catch( Exception $e ){
			return $d;
		}
	}

	/**
	 * Get separator character
	 * @return int
	 * @since 1.0.0
	 */
	protected function getSeperatorChar(){
		return ord( 1 );
	}

	/**
	 * Compress string
	 *
	 * @param $string
	 * Input string
	 *
	 * @return string
	 * Compressed string on success, input string on failure
	 * <br>e.g: "Hello world. This is a test" -> "??q???p????"
	 * @see  AMDFirewall::extract()
	 *@since 1.0.0
	 * @uses gzdeflate()
	 */
	public function compress( $string ){

		if( !function_exists( 'gzdeflate' ) )
			return $string;

		$out = @gzdeflate( $string );

		$out = @gzdeflate( $out );

		return $out ?: $string;

	}

	/**
	 * Extract string
	 *
	 * @param $string
	 * Compressed string
	 *
	 * @return string
	 * Extracted string on success, input string on failure
	 * <br>e.g: "??q???p????" -> "Hello world. This is a test"
	 * @see  AMDFirewall::compress()
	 *@since 1.0.0
	 * @uses gzinflate()
	 */
	public function extract( $string ){

		if( !function_exists( 'gzinflate' ) )
			return $string;

		$out = @gzinflate( $string );

		$out = @gzinflate( $out );

		return $out ?: $string;

	}

	/**
	 * Encrypt string with AES-128-CTR method
	 *
	 * @param $string
	 * Input string
	 * @param $salt
	 * Encryption salt (can be any string)
     * @param null|string $iv
     * Custom initialization vector key, pass null to use default IV key
	 *
	 * @return false|string
	 * The encrypted string on success or false on failure.
	 * <br>e.g: "hello" string with key "123" -> "EmDIqeI="
	 * @see  AMDFirewall::decryptAES()
	 *@since 1.0.0
	 * @uses openssl_encrypt()
	 */
	public function encryptAES( $string, $salt, $iv=null ){

		$original_string = $string;
		$cipher_algo = "AES-128-CTR";
		# $iv_length = openssl_cipher_iv_length( $cipher_algo );
		$option = 0;
		$encrypt_iv = $iv === null ? self::getNonce() : $iv;
		$encrypt_key = $salt;

		return openssl_encrypt( $original_string, $cipher_algo, $encrypt_key, $option, $encrypt_iv );

	}

    /**
     * Decrypt encoded string with AES-128-CTR method
     *
     * @param $hash
     * Encrypted string
     * @param $key
     * Decryption key (salt in encryption)
     * @param null|string $iv
     * Custom initialization vector key, pass null to use default IV key
     *
     * @return false|string
     * The decrypted string on success or false on failure.
     * <br>e.g: "EmDIqeI=" hash with key "123" -> "hello"
     * @see  AMDFirewall::encryptAES()
     * @since 1.0.0
     * @uses openssl_decrypt()
     */
	public function decryptAES( $hash, $key, $iv=null ){

		$cipher_algo = "AES-128-CTR";
		$option = 0;
		$decrypt_iv = $iv === null ? self::getNonce() : $iv;
		$decrypt_key = $key;

		return openssl_decrypt( $hash, $cipher_algo, $decrypt_key, $option, $decrypt_iv );

	}

	/**
	 * Get AES encryption nonce
	 * @return string
	 */
	protected function getNonce(){

		/*
		 * NOTE: you can enable site-nonce by adding this line to your theme function or plugin:
		 *
		 * add_filter( "amd_use_site_nonce", "__return_true" );
		 *
		 * After that your site-nonce is a unique code, so your data encryption will be more secure.
		 *
		 * BUT IF YOU ENABLE SITE-NONCE your site nonce will be used for encryption, that means
		 * your data are only valid in your current site config, so if you change your site-nonce
		 * users data will be lost and BACKUPS WON'T WORK ANYMORE!
		 *
		 * HOWEVER you can copy your site nonce from old site to your new site by replacing this line
		 * in your 'wp-config.php' file to your new site config:
		 *
		 * define( 'NONCE_KEY', 'YOUR_NONCE_KEY_HERE' );
		 *
		 * After that your data decryption works just fine!
		 */

		if( defined( 'NONCE_SALT' ) and apply_filters( "amd_use_site_nonce", false ) )
			return str_split( NONCE_SALT, 16 )[0];

		return apply_filters( "amd_firewall_aes_nonce", "1=gVHpo{gIOixP@s" );

	}

	/**
	 * Keymap for encryption
	 *
	 * @param $rev
	 *
	 * @return array|string[]
	 * @since 1.0.0
	 */
	protected function getKeymap( $rev = false ){

		// @formatter:off
		$keymap = array(
			# Uppercase
			"A" => "H87",
			"B" => "e1p",
			"C" => "s43",
			"D" => "q4r",
			"E" => "T5r",
			"F" => "F9m",
			"G" => "D1e",
			"H" => "k1l",
			"I" => "Z4f",
			"J" => "N56",
			"K" => "B65",
			"L" => "Rt8",
			"M" => "of7",
			"N" => "dx4",
			"O" => "h56",
			"P" => "p09",
			"Q" => "a2q",
			"R" => "x3f",
			"S" => "f0k",
			"T" => "ml9",
			"U" => "q7d",
			"V" => "u85",
			"W" => "po9",
			"X" => "bh8",
			"Y" => "mn6",
			"Z" => "b3v",
			# Lowercase
			"a" => "10p",
			"b" => "gd6",
			"c" => "E43",
			"d" => "8u5",
			"e" => "D80",
			"f" => "i9U",
			"g" => "1qw",
			"h" => "0pL",
			"i" => "4fd",
			"j" => "5R4",
			"k" => "ej6",
			"l" => "Bg7",
			"m" => "ak7",
			"n" => "cR5",
			"o" => "13A",
			"p" => "gE7",
			"q" => "ve3",
			"r" => "01n",
			"s" => "di8",
			"t" => "64n",
			"u" => "hE6",
			"v" => "s8j",
			"w" => "qd2",
			"x" => "pq0",
			"y" => "aX2",
			"z" => "pL9",
			# Numbers
			"0" => "cEf",
			"1" => "Aer",
			"2" => "Hui",
			"3" => "pqT",
			"4" => "dYn",
			"5" => "Lpq",
			"6" => "Aom",
			"7" => "Duv",
			"8" => "MkO",
			"9" => "hEi",
			# Special characters
			"~" => "150",
			"!" => "151",
			"@" => "152",
			"#" => "153",
			"$" => "154",
			"%" => "155",
			"^" => "156",
			"&" => "157",
			"*" => "158",
			"(" => "159",
			")" => "160",
			"_" => "161",
			"-" => "162",
			"+" => "163",
			"=" => "164",
			"." => "165",
			";" => "166",
			":" => "167",
			"\"" => "168",
			"'" => "169",
			"[" => "170",
			"]" => "171",
			"{" => "172",
			"}" => "173",
			"," => "174"
		);
		// @formatter:on

		if( !$rev )
			return $keymap;

		$keymapRev = [];
		foreach( $keymap as $key => $value ){
			$keymapRev[$value] = $key;
		}

		return $keymapRev;

	}

	/**
	 * Escape characters ascii code with %
	 *
	 * @param $str
	 * Input string
	 *
	 * @return string
	 * Escaped string.
	 * <br>"hello" -> "%104%101%108%108%111"
	 * @since 1.0.0
	 */
	public function escape( $str ){

		$out = "";
		for( $i = 0; $i < strlen( $str ); $i++ ){
			$out .= "%" . ord( $str[$i] );
		}

		return $out;

	}

	/**
	 * Unescape string ascii code with %
	 *
	 * @param $str
	 * Input string
	 *
	 * @return string
	 * Unescaped string.
	 * <br>"%104%101%108%108%111" -> "hello"
	 * @since 1.0.0
	 */
	public function unescape( $str ){

		$out = "";
		$exp = explode( "%", trim( $str, "%" ) );
		foreach( $exp as $char ){
			$out .= chr( $char );
		}

		return $out;

	}

	/**
	 * Add new AJAX API handler
	 *
	 * @param $id
	 * @param $handler
	 * @param $allowed_method
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function addAPIHandler( $id, $handler, $allowed_method = "*" ){

		$this->api_handlers[$id] = array(
			"handler" => $handler,
			"allowed_method" => $allowed_method
		);

	}

	/**
	 * Get registered API handlers
	 * @return array
	 * @since 1.0.0
	 */
	public function getAPIHandler(){

		return $this->api_handlers;

	}

	/**
	 * Handle api.
	 * <br><b>Note: this method is already used in '_app' extension, you
	 * don't need to call it again. You can add your API handlers with hooks.
	 * See our documentation for more information</b>
	 * @return AMDFirewallAPI
	 * @since 1.0.0
	 */
	public function handleAPI(){

		require_once( "AMDFirewallAPI.php" );

		$api = new AMDFirewallAPI();

		$api->handleAPI();

		return $api;

	}

	/**
	 * Set '$api_handler' array
	 *
	 * @param AMDFirewallAPI $handler
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function setHandler( $handler ){

		$this->api_handler = $handler;

	}

	/**
	 * Parse user agent and determine user browser and device details
	 * @param string $user_agent
	 * User agent to parse, pass null to get current agent
	 *
	 * @return AMDFirewallBrowser
	 * Firewall browser object
	 * @since 1.0.5
	 */
	public function parseAgent( $user_agent=null ){

		if( $user_agent == null )
			$user_agent = sanitize_text_field( $_SERVER["HTTP_USER_AGENT"] ?? "" );

		require_once( __DIR__ . "/AMDFirewallBrowser.php" );

		return new AMDFirewallBrowser( $user_agent );

	}

    /**
     * Get current user agent if available
     * @return string
     * @since 1.2.0
     */
    public function getUserAgent() {
        return sanitize_text_field( $_SERVER["HTTP_USER_AGENT"] ?? "" );
    }

}