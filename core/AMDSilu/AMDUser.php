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
	 * User secret key (insensitive)
	 * @var string
	 * @since 1.0.0
	 */
	public $secretKey;

	/**
	 * User serial number for personal data encryption (sensitive)
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
	 * User locale code
	 * @var string
	 * @since 1.1.0
	 */
	public $locale;

    /**
	 * User locale code for locale
	 * @var string
	 * @since 1.1.0
	 */
	public $rollback_locale;

    /**
     * Whether the user is dummy user and has no real identity
     * @var bool
     * @sicne 1.2.0
     */
    public $is_dummy;

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
		$this->is_dummy = false;
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

        $this->rollback_locale = $this->locale;

	}

    /**
     * Get avtar image URL
     * @return mixed|null
     * @since 1.2.0
     */
    public function getProfile() {

        return apply_filters( "amd_user_avatar_url", $this->profile, $this->ID );

    }

    public function rollLocale( $locale=null ) {

        $this->rollback_locale = get_locale();

        if( $locale === null )
            $locale = $this->locale;

        if( $locale )
            switch_to_locale( $locale );

    }

    public function rollbackLocale() {

        if( $this->rollback_locale )
            switch_to_locale( $this->rollback_locale );

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
     * Get user safe name for SMS and messages
     * @param null|string $safe_name
     * Safe name string, or pass null to use default safe name (default is 'user' in English or 'کاربر' in Persian)
     * @return string
     * Safe name string
     * @since 1.2.1
     */
    public function getSafeName( $safe_name = null ) {
        if( !empty( trim( $this->firstname ) ) )
            return $this->firstname;
        return $safe_name === null ? _x( "user", "safe name", "material-dashboard" ) : $safe_name;
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
	 * @since 1.1.1
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

        return _x( ucfirst( $role ), "User role", "material-dashboard-pro" );

	}

    /**
     * Export user data for front-end
     * @return array
     * <pre>array(
     *    "id" => INT,
     *    "email" => STRING,
     *    "username" => STRING,
     *    "phone" => STRING,
     *    "first_name" => STRING,
     *    "last_name" => STRING,
     *    "fullname" => STRING,
     *    "role" => STRING,
     *    "role_name" => STRING,
     *    "ncode" => STRING
     *    "gender" => STRING,
     *    "avatar" => STRING,
     *    "profile_url" => STRING,
     *    "locale" => STRING,
     *    "registration" => INT,
     * )
     * </pre>
     * @since 1.2.0
     */
    public function export() {
        return array(
            "id" => $this->ID,
            "email" => $this->email,
            "username" => $this->username,
            "phone" => $this->phone,
            "first_name" => $this->firstname,
            "last_name" => $this->lastname,
            "fullname" => $this->fullname,
            "role" => $this->role,
            "role_name" => $this->getRoleName(),
            "ncode" => amd_get_user_meta( $this->ID, "ncode" ),
            "gender" => $this->gender,
            "avatar" => $this->profile,
            "profile_url" => $this->getProfileURL(),
            "locale" => $this->locale,
            "registration" => $this->getRegistrationTime(),
        );
    }

    /**
     * Get OTP data like resend duration and verifying the code
     * @param string|null $code
     * OTP code, pass null to generate a new one and assign it to the user
     *
     * @return array
     * Information array about given OTP code
     * @since 1.2.0
     */
    public function get_otp( $code = null ) {

        /**
         * One time password length
         * @sicne 1.2.0
         */
        $otp_length = apply_filters( "amd_otp_code_length", 6 );

        /**
         * The number of seconds that users can request OTP resend
         * @since 1.2.0
         */
        $resend_time = apply_filters( "amd_otp_resend_interval", 60 );

        /**
         * The number of seconds that OTP code will be expired
         * @since 1.2.0
         */
        $expire_time = apply_filters( "amd_otp_code_expire", 300 ); # 5 minutes

        global $amdWall;

        $temp = amd_find_temp( "temp_key", "otp_code_{$this->ID}_*", true );

        $is_new = false;
        if( empty( $temp[0] ) ){
            $_code = amd_generate_string( $otp_length, "number" );
            $serial = $amdWall->serialize( $_code );
            $temp_key = "otp_code_" . $this->ID . "_$serial";
            $resend_expires = time() + $resend_time;
            $is_new = true;
            amd_set_temp( $temp_key, time() + $resend_time, $expire_time );
        }
        else{
            $t = $temp[0];
            $temp_key = $t->temp_key;
            $resend_expires = $t->temp_value;
            $_code = $amdWall->deserialize( str_replace( "otp_code_{$this->ID}_", "", $temp_key ) );
        }

        return array(
            "code" => $_code,
            "verify" => !empty( $code ) AND $_code == $code,
            "is_new" => $is_new,
            "key" => $temp_key,
            "resend" => $resend_expires
        );

    }

    /**
     * Send OTP code to the user, the message will be initiated with text templates
     * and message method can be changed from settings
     * @param string $otp_code
     * One-time-password code
     * @param bool $schedule
     * Whether to schedule message or send it immediately
     *
     * @return bool
     * True on successfully sending the message, false on failure
     * @since 1.2.0
     */
    public function send_otp( $otp_code, $schedule=false ) {

        if( empty( $otp_code ) )
            return false;

        global $amdWarn;

        $use_email_otp = amd_get_site_option( "use_email_otp", "false" ) == "true";

        $message = apply_filters( "amd_otp_verification_code_message", $this, $otp_code );

        $method = null;

        if( $use_email_otp )
            $method = "email";
        else if( !empty( $this->phone ) )
            $method = "sms";

        if( empty( $method ) )
            return false;

        if( $method == "sms" ){
            # Send message by pattern if pattern exist, otherwise send it normally
            $pattern_send = amd_send_message_by_pattern( $this->phone, "otp_verification", ["code" => $otp_code] );
            if( $pattern_send >= 0 )
                return boolval( $pattern_send );
        }

        return $amdWarn->sendMessage( array(
            "user" => $this,
            "title" => esc_html__( "Login verification code (OTP)", "material-dashboard" ),
            "message" => $message
        ), $method, $schedule );

    }

    /**
     * This only will return a value if Linky extension is enabled, otherwise it may be an empty string
     *
     * @return string
     * Referral code string
     * @since 1.2.0
     */
    public function get_referral_code() {
        if( function_exists( "amd_ext_linky" ) )
            return amd_ext_linky_get_user_referral_code( $this->ID );
        return "";
    }

    /**
     * This only will return a value if Linky extension is enabled, otherwise it may be an empty string
     *
     * @return string
     * Referral link string
     * @since 1.2.0
     */
    public function get_referral_link() {
        if( function_exists( "amd_ext_linky" ) ) {
            $referral_code = amd_ext_linky_get_user_referral_code( $this->ID );
            $referral_url = amd_merge_url_query( amd_get_login_page(), "auth=register&referral=" . urlencode( $referral_code ) );

            /**
             * Referral URL
             * @since 1.2.0
             */
            return apply_filters( "amd_ext_linky_referral_url", $referral_url, $referral_code );
        }
        return "";
    }

}