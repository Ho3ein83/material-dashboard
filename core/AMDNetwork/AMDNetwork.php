<?php

/** @var AMDNetwork $amdNet */
$amdNet = null;

class AMDNetwork{

	/**
	 * Network and AJAX handlers
	 */
	function __construct(){

		$ajaxAction = "amd_ajax_handler";
		$publicAjaxAction = "public_amd_ajax_handler";
		$dashboardAction = "amd_dash_ajax_handler";

		# Ajax handler for clients

		// Dashboard API
		add_action( "wp_ajax_$dashboardAction", [ $this, 'dashAjaxHandler' ] );

		// Logged-in users
		add_action( "wp_ajax_$ajaxAction", [ $this, 'ajaxHandler' ] );

		// Logged-out users / Unauthorized
		add_action( "wp_ajax_nopriv_$publicAjaxAction", [ $this, 'publicAjaxHandler' ] );

		// Public
		add_action( "wp_ajax_$publicAjaxAction", [ $this, 'publicAjaxHandler' ] );

	}

	/**
	 * Ajax handler for all clients
	 * @return void
	 * @since 1.0.0
	 */
	public function publicAjaxHandler(){

		do_action( "amd_dashboard_init" );

		do_action( "amd_ajax_init", "public" );

		# Request parameters
		$r = amd_sanitize_post_fields( $_POST );
		$unfilteredRequest = $_POST;

		if( !empty( $r["_ajax_target"] ) ){

			$target = $r["_ajax_target"];
			$callbacks = [ "amd_ajax_target_$target", "amd_ajax_target_{$target}_public" ];

			foreach( $callbacks as $callback ){
                if( function_exists( $callback ) ) {

                    if( is_string( $callback ) ) {

                        /** @since 1.1.2 */
                        do_action( "amd_ajt_$callback", $r, $unfilteredRequest );

                    }

                    call_user_func( $callback, $r, $unfilteredRequest );

                }
			}
			wp_send_json_error( [ "msg" => __( "An error has occurred", "material-dashboard" ), "_msg" => "400 Bad Request" ] );
		}

		$_get = !empty( $r["_get"] ) ? str_replace( "?", "", $r["_get"] ) : "";
		$_get = explode( "&", $_get );
		foreach( $_get as $value ){
			$exp = explode( "=", $value );
			$k = urldecode( $exp[0] );
			$v = urldecode( $exp[1] ?? "" );
			$safe_key = sanitize_key( $k );
			$_GET[$safe_key] = sanitize_text_field( $v );
		}

		do_action( "amd_handle_public_ajax", $r );

		if( !empty( $r["switchTheme"] ) ){

			$mode = $r["switchTheme"];

			if( in_array( $mode, apply_filters( "amd_allowed_theme_modes", [ "light", "dark" ] ) ) ){

				global /** @var AMDCache $amdCache */
				$amdCache;

				$amdCache->setCache( "theme", $mode, AMDCache::STAMPS['month'] );

				wp_send_json_success( [ "msg" => "Theme mode has been changed" ] );

			}

		}

		if( !is_user_logged_in() ){

			if( !empty( $r["login"] ) ){

				if( amd_login_attempts_reached() )
					wp_send_json_error( [ "msg" => __( "Too many attempts, please try again later", "material-dashboard" ) ] );

				do_action( "amd_login_before_login", $r );

				$data = $r["login"];
				$user_login = !empty( $data["user"] ) ? $data["user"] : "";
				$phone = !empty( $data["phone"] ) ? $data["phone"] : "";
				$password = !empty( $data["password"] ) ? $data["password"] : "";
				$remember = !empty( $data["remember"] ) ? $data["remember"] : false;

				global $amdSilu;

				if( !empty( $phone ) ){
//					$user_phone = str_replace( " ", "", $phone );
					$user_phone = amd_apply_phone_format( $phone );
					$user = amd_get_user_by_meta( "phone", $user_phone );
					$isPhone = true;
				}
				else{
					if( strlen( $user_login ) < 3 )
						wp_send_json_error( [ "msg" => __( "Please enter your username correctly", "material-dashboard" ) ] );
					else if( strlen( $password ) < 8 )
						wp_send_json_error( [ "msg" => __( "Password must contain at least 8 characters", "material-dashboard" ) ] );

					$userData = $amdSilu->getUserAuto( $user_login );

					$isPhone = false;
					$user = null;
					if( !empty( $userData["user"] ) ){
						$user = $userData["user"];
						$isPhone = ( $userData["by"] == "phone" );
					}
				}

				do_action( "amd_login_before_authenticate", $r );

				if( !$user )
					$auth = false;
				else
					$auth = wp_authenticate( $user->username ?? "", $password );
				if( !$user OR !$auth OR is_wp_error( $auth ) ){
					if( $isPhone )
						wp_send_json_error( [ "msg" => __( "Your phone number or password is incorrect", "material-dashboard" ) ] );
					else
						wp_send_json_error( [ "msg" => __( "Your username or password is incorrect", "material-dashboard" ) ] );
				}

				do_action( "amd_login_after_authenticate", $r );

                $use_2fa = amd_user_required_2f( $user->ID );
                if( $use_2fa ){
                    $token = amd_generate_string( 24 );

                    /**
	                 * 2-factor authentication code length
                     * @since 1.0.5
	                 */
                    $code_len = apply_filters( "amd_2fa_code_length", 4 );
                    $code = amd_generate_string( intval( $code_len ), "number" );

                    amd_set_temp( "2fa_token_$token", $code . "," . $user->ID );

                    $url = amd_make_action_url( "user_2fa", $token, $user->ID );

                    if( !$url )
	                    wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );

	                /**
	                 * Send 2FA code to user
                     * @since 1.0.5
	                 */
                    do_action( "amd_send_2fa_code", $code, $user->ID );

	                wp_send_json_success( [ "msg" => __( "Please wait", "material-dashboard" ), "url" => $url ] );
                }

				$login = $amdSilu->login( $user->username, $password, $remember );

				if( is_wp_error( $login ) )
					wp_send_json_error( [ "msg" => __( "Your login information is incorrect or an error has occurred. Please try again", "material-dashboard" ) ] );

                $redirectURL = amd_get_login_redirect_url( amd_get_dashboard_url() );
                $redirectURL = amd_parse_url( $redirectURL );
                amd_reset_login_redirect_record();

				wp_send_json_success( [ "msg" => __( "Logging in", "material-dashboard" ), "url" => $redirectURL ] );

			}

			else if( !empty( $r["register"] ) ){

				$data = $r["register"];

				$firstname = trim( preg_replace( "/\s+/", " ",  $data["firstname"] ?? "" ) );
				$lastname = trim( preg_replace( "/\s+/", " ",  $data["lastname"] ?? "" ) );
				$email = sanitize_email( $data["email"] ?? "" );
				$username = $data["username"] ?? "";
				$password = $data["password"] ?? "";
				$vCode = $data["vCode"] ?? "";
				$phone = $data["phone"] ?? "";
				$phone = amd_apply_phone_format( $phone );
				$login_after_registration = (bool) ( $data["login_after_registration"] ?? false );

				$custom_fields = $data["custom_fields"] ?? [];
				$custom_fields = array_unique( $custom_fields );

				/**
				 * Validate fields before continue the registration
                 * @since 1.1.1
				 */
                do_action( "amd_register_validate_custom_fields", $custom_fields );

				global $amdSilu;

				$phone_field = amd_get_site_option( "phone_field" ) == "true";
				$phone_field_required = amd_get_site_option( "phone_field_required" ) == "true";
				$single_phone = amd_get_site_option( "single_phone" ) == "true";
				$lastname_field = amd_get_site_option( "lastname_field" ) == "true";
				$username_field = amd_get_site_option( "username_field" ) == "true";

				if( $lastname_field AND empty( $lastname ) )
					wp_send_json_error( [
						"msg" => __( "Failed", "material-dashboard" ),
						"errors" => [
							[
								"id" => "lastname_required",
								"error" => __( "Please enter you last name correctly", "material-dashboard" ),
								"field" => "lastname"
							]
						]
					] );

				if( $phone_field ){
					if( $phone_field_required OR !empty( $phone ) ){
						if( !amd_validate_phone_number( $phone ) ){
							wp_send_json_error( [
								"msg" => __( "Failed", "material-dashboard" ),
								"errors" => [
									[
										"id" => "phone_incorrect",
										"error" => __( "Please enter your phone number correctly", "material-dashboard" ),
										"field" => "phone_number"
									]
								]
							] );
						}
					}
				}

				if( !empty( $phone ) ){
					if( $single_phone AND amd_phone_exists( $phone ) ){
						wp_send_json_error( [
							"msg" => __( "Failed", "material-dashboard" ),
							"errors" => [
								[
									"id" => "phone_exists",
									"error" => __( "This phone number is already in use", "material-dashboard" ),
									"field" => "phone_number"
								]
							]
						] );
					}
				}

				if( !$username_field ){
					$username = -1;
				}
				else if( !empty( $username ) ){
					if( !amd_validate( $username, "%username%" ) OR !apply_filters( "validate_username", $username ) ){
						wp_send_json_error( [ "msg" => __( "Please enter a valid username", "material-dashboard" ) ] );
					}
				}

                $pause = apply_filters( "amd_pause_registration", false, array(
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "email" => $email,
                    "username" => $username,
                    "password" => $password,
                    "vCode" => $vCode,
                    "phone" => $phone,
                ) );

                if( !empty( $pause ) )
                    wp_send_json_error( array_merge( $pause, ["pause" => true] ) );

				$result = $amdSilu->registerUser( $email, $username, $password, $login_after_registration, $phone );

				$success = $result["success"];
				$errors = $result["errors"];

				if( !$success )
					wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ), "errors" => $errors ] );

				$uid = $result["user_id"];

				$locale = get_locale();

				// Set user meta (WordPress meta)
				update_user_meta( $uid, "first_name", $firstname );
				update_user_meta( $uid, "last_name", $lastname );
				update_user_meta( $uid, "locale", $locale );

				// Set user meta (AMD plugin meta)
				if( $phone_field ) {
                    amd_set_user_meta( $uid, "phone", $phone );
                    $is_phone_verified = intval( amd_get_temp( "phone_verified_$phone" ) );
                    if( $is_phone_verified AND $is_phone_verified >= time() )
                        amd_set_user_meta( $uid, "phone_verified", time() );
                }
				amd_set_user_meta( $uid, "registration", time() );
				amd_set_user_meta( $uid, "_init", "*" );
				amd_set_user_meta( $uid, "signup_method", "direct" );

                if( !empty( $custom_fields ) ){
	                /**
	                 * Save user custom fields
                     * @since 1.0.5
	                 */
                    do_action( "amd_save_user_custom_fields", $uid, $custom_fields );
                }

                $user = amd_get_user( $uid );
				if( apply_filters( "amd_welcome_message_enabled", true ) AND $user ) {

                    $title = apply_filters( "amd_welcome_alert_title", $user );
                    $text = apply_filters( "amd_welcome_alert_text", $user );

                    /**
                     * Welcome message methods
                     * @since 1.1.0
                     */
                    $methods = apply_filters( "amd_welcome_message_methods", "email,sms" );

                    global $amdWarn;

                    $amdWarn->sendMessage( array(
                        "email" => $user->email,
                        "phone" => $user->phone,
                        "message" => $text,
                        "subject" => $title
                    ), $methods, true );

                }

				do_action( "amd_signup_complete", $uid );

                if( !session_id() )
                    session_start();

                $redirectURL = "";
                if( !empty( $_SESSION["redirect_pending"] ) ){
                    $redirectURL = sanitize_url( $_SESSION["redirect_pending"] );
                    $_SESSION["redirect_pending"] = null;
                    unset( $_SESSION["redirect_pending"] );
                }
                else{
                    $redirectURL = amd_get_dashboard_page();
                }

                $redirectURL = amd_parse_url( $redirectURL );

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ), "url" => $redirectURL, "query" => "auth=intro" ] );

			}

			else if( !empty( $r["reset_password"] ) ){

				$data = $r["reset_password"];
				$email = $data["email"] ?? "";
				$phone = $data["phone"] ?? "";
                $phone = amd_apply_phone_format( $phone );
				$message_method = $data["message_method"] ?? "email";
				$vCode = $data["vcode"] ?? "";
				$new_password = $data["new_password"];

                $continue = false;
                $user = null;

                global $amdSilu;
				if( !empty( $email ) AND $message_method == "email" ){
                    $user = $amdSilu->getUser( "email", $email, true );
					if( !$user OR is_wp_error( $user ) )
						wp_send_json_error( [ "msg" => __( "Email not found", "material-dashboard" ) ] );
                    $continue = "email";
				}
                else if( !empty( $phone ) AND $message_method == "phone" ){
                    $user = $amdSilu->getUserByMeta( "phone", $phone );
                    if( empty( $user ) OR !$user instanceof AMDUser )
                        wp_send_json_error( [ "msg" => __( "No user found with this phone number", "material-dashboard" ) ] );
                    $continue = "phone";
                }

                if( $continue AND $user ){
                    if( !empty( $vCode ) ){
                        if( !empty( $new_password ) ){
                            if( strlen( $new_password ) < 8 )
                                wp_send_json_error( [ "msg" => __( "Password must contain at least 8 characters", "material-dashboard" ) ] );
                            $success = amd_change_password( $user->ID, $new_password, apply_filters( "amd_notify_password_change", true ) );
                            if( $success )
                                wp_send_json_success( [ "msg" => __( "Your password has been changed", "material-dashboard" ) ] );
                            else
                                wp_send_json_success( [ "msg" => __( "Failed", "material-dashboard" ) ] );
                        }
                        else{
                            $valid = amd_is_verification_code_valid( $user->ID, $vCode );
                            if( !$valid )
                                wp_send_json_error( [ "msg" => __( "Entered verification code is not correct or has been expired", "material-dashboard" ) ] );
                            wp_send_json_success( [ "msg" => __( "Please enter your new password", "material-dashboard" ) ] );
                        }
                    }
                    else{
                        $vCode = amd_regenerate_verification_code( $user->ID );
                        if( !$vCode )
                            wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );

                        global $amdWarn;

                        $message = apply_filters( "amd_new_verification_code_message", $user->ID, $vCode );

                        $sent = false;
                        $send_without_pattern = true;
                        if( $continue == "phone" ){
                            # Send message by pattern if pattern exist, otherwise send it normally
                            $pattern_send = amd_send_message_by_pattern( $user->phone, "password_reset_verification", ["firstname" => $user->getSafeName(), "code" => $vCode] );
                            $sent = $pattern_send > 0;
                            if( $pattern_send === false || $pattern_send > 0 )
                                $send_without_pattern = false;
                        }

                        if( $send_without_pattern ) {
                            $sent = $amdWarn->sendMessage( array(
                                "email" => $user->email,
                                "phone" => $user->phone,
                                "subject" => __( "Reset password", "material-dashboard" ),
                                "message" => $message
                            ), ( $continue === "phone" ) ? "sms" : "email" );
                        }

                        if( !$sent )
                            wp_send_json_error( [ "msg" => $continue === "phone" ? __( "An error has occurred while sending SMS", "material-dashboard" ) : __( "An error has occurred while sending email", "material-dashboard" ) ] );

                        wp_send_json_success( [ "msg" => $continue === "phone" ? __( "Verification code has been sent to your phone", "material-dashboard" ) : __( "Verification code has been sent to your email", "material-dashboard" ) ] );
                    }
                }

				wp_send_json_error( [ "msg" => __( "An error has occurred", "material-dashboard" ) ] );

			}

			else if( !empty( $r["resend_rp_code"] ) ){

				$email = $r["resend_rp_code"];

				$user = get_user_by( "email", $email );

				if( !$user OR is_wp_error( $user ) )
					wp_send_json_error( [ "msg" => __( "Email not found", "material-dashboard" ) ] );

				$vCode = amd_regenerate_verification_code( $user->ID );
				if( !$vCode )
					wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );

				global /** @var AMDWarner $amdWarn */
				$amdWarn;

				$message = apply_filters( "amd_new_verification_code_message", $user->ID, $vCode );
				$sent = $amdWarn->sendEmail( $user->user_email, __( "Reset password", "material-dashboard" ), $message );

				if( !$sent )
					wp_send_json_error( [ "msg" => __( "An error has occurred while sending email", "material-dashboard" ) ] );
				$temp_key = "user_{$user->ID}_rp_code_resend";
				$temp = amd_get_temp( $temp_key, false );
				$temp_val = intval( $temp->temp_value ?? 0 );
				$temp_expire = intval( $temp->expire ?? 0 );
				if( $temp_val ){
					$i = null;
					if( $temp_expire )
						$i = $temp_expire - time();
					wp_send_json_success( [ "msg" => __( "Try again in another minute", "material-dashboard" ), "resend_interval" => $i ] );
				}

				/**
				 * Password reset resend message interval (in seconds)
				 * @since 1.0.4
				 */
				$resend_interval = apply_filters( "amd_reset_password_resend_interval", 60 );

				amd_set_temp( $temp_key, $resend_interval, $resend_interval );

				wp_send_json_success( [ "msg" => __( "Verification code has been sent to your email", "material-dashboard" ), "resend_interval" => $resend_interval ] );

			}
            
            else if( !empty( $r["2fa_submit"] ) ){

                $use_login_otp = amd_get_site_option( "use_login_otp", "false" ) == "true";
                if( $use_login_otp )
                    wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );
                
                $code = $r["2fa_submit"];
                $token = $r["token"] ?? "";
                
                if( !empty( $token ) ){
	                $temp = amd_get_temp( "2fa_token_$token" );
                    $exp = explode( ",", $temp );
                    if( $exp[0] == $code ){
                        $u = amd_get_user_by( "id", $exp[1] ?? 0 );
                        if( $u ){
                            global $amdSilu;
                            $success = $amdSilu->usl( $u );
                            if( $success ){
	                            $redirectURL = amd_get_dashboard_page();
	                            if( !empty( $_SESSION["redirect_pending"] ) ){
		                            $redirectURL = sanitize_url( $_SESSION["redirect_pending"] );
		                            $_SESSION["redirect_pending"] = null;
		                            unset( $_SESSION["redirect_pending"] );
	                            }
                                $redirectURL = amd_parse_url( $redirectURL );
	                            wp_send_json_success( [
		                            "msg" => __( "Success", "material-dashboard" ),
		                            "url" => $redirectURL
	                            ] );
                            }
                        }
                    }
                }

	            wp_send_json_error( ["msg" => __( "Entered verification code is not correct or has been expired", "material-dashboard" )] );
                
            }

            else if( !empty( $r["2fa_resend"] ) ){

                $use_login_otp = amd_get_site_option( "use_login_otp", "false" ) == "true";
                if( $use_login_otp )
                    wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );

	            $token = $r["2fa_resend"];

	            $temp = amd_get_temp( "2fa_token_$token" );
	            $exp = explode( ",", $temp );
	            $u = amd_get_user_by( "id", $exp[1] ?? 0 );

                if( $u ){
	                $temp = amd_get_temp( "2fa_token_{$token}_resend", false );
	                $_expire = $temp->expire ?? 0;
	                $expire = $_expire > 0 ? $_expire - time() : 0;

                    if( $expire > 0 )
	                    wp_send_json_success( ["msg" => __( "Failed", "material-dashboard" ), "resend_interval" => $expire] );

	                /**
	                 * 2-factor authentication code length
	                 * @since 1.0.5
	                 */
	                $code_len = apply_filters( "amd_2fa_code_length", 4 );
	                $code = amd_generate_string( intval( $code_len ), "number" );

                    amd_set_temp( "2fa_token_$token", $code . "," . $u->ID );

	                /**
	                 * Send 2FA code to user
	                 * @since 1.0.5
	                 */
	                do_action( "amd_send_2fa_code", $code, $u->ID );

	                amd_set_temp( "2fa_token_{$token}_resend", "true", 120 );

	                wp_send_json_success( ["msg" => __( "Verification code has been sent to your email and/or phone number", "material-dashboard" ), "resend_interval" => 120] );

                }

	            wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

            }

            else if( !empty( $r["otp"] ) ){

                $use_login_otp = amd_get_site_option( "use_login_otp", "false" ) == "true";

                if( !$use_login_otp )
                    wp_send_json_error( [ "msg" => __( "OTP is disabled, try logging in with password", "material-dashboard" ) ] );

                if( amd_login_attempts_reached() )
                    wp_send_json_error( [ "msg" => __( "Too many attempts, please try again later", "material-dashboard" ) ] );

                $data = $r["otp"];

                $login = $data["login"] ?? "";
                $phone = amd_apply_phone_format( $data["phone"] ?? "" );
                $code = $data["code"] ?? "";
                $resend_request = $data["resend"] ?? false;
                $remember = $data["remember"] ?? false;

                if( empty( $login ) AND empty( $phone ) )
                    wp_send_json_error( ["msg" => __( "Please fill out all fields correctly", "material-dashboard" )] );

                $user = null;
                if( !empty( $phone ) ){
                    $user = amd_get_user_by_meta( "phone", $phone );
                    if( !$user OR !$user instanceof AMDUser )
                        wp_send_json_error( ["msg" => __( "Phone number you entered is not registered", "material-dashboard" )] );
                }
                else if( !empty( $email ) ){
                    $user = amd_get_user_by( "email", $email );
                    if( !$user OR !$user instanceof AMDUser )
                        wp_send_json_error( ["msg" => __( "Username or email does not exist", "material-dashboard" )] );
                }

                if( $user instanceof AMDUser ){

                    $otp = $user->get_otp( $code );
                    $otp_code = $otp["code"];
                    $otp_verified = $otp["verify"];
                    $otp_key = $otp["key"];
                    $otp_is_new = $otp["is_new"];
                    $otp_resend = $otp["resend"];
                    $resend_time_left = max( $otp_resend - time(), 0 );

                    if( empty( $code ) OR $resend_request ){
                        if( $otp_is_new ){
                            if( $user->send_otp( $otp_code ) )
                                wp_send_json_success( ["msg" => __( "Verification code has been sent", "material-dashboard" ), "resend" => $resend_time_left] );
                            else
                                amd_delete_temp( $otp_key );
                        }
                        else if( $resend_request OR $resend_time_left <= 0 ){
                            if( $resend_time_left > 0 )
                                wp_send_json_success( ["msg" => __( "Cannot send verification code, try again later", "material-dashboard" ), "resend" => $resend_time_left] );

                            if( $user->send_otp( $otp_code ) ) {
                                /**
                                 * The number of seconds that users can request OTP resend
                                 * @since 1.2.0
                                 */
                                $resend_time = apply_filters( "amd_otp_resend_interval", 60 );
                                amd_set_temp( $otp_key, time() + $resend_time );
                                wp_send_json_success( [
                                    "msg" => __( "Verification code has been sent", "material-dashboard" ),
                                    "resend" => $resend_time
                                ] );
                            }
                        }
                        else{
                            wp_send_json_success( ["msg" => __( "Verification code has been already sent", "material-dashboard" ), "resend" => $resend_time_left] );
                        }
                    }
                    else if( $otp_verified ){
                        global $amdSilu;
                        if( $amdSilu->usl( $user, $remember ) ){
                            $redirectURL = amd_get_login_redirect_url( amd_get_dashboard_url() );
                            $redirectURL = amd_parse_url( $redirectURL );
                            amd_reset_login_redirect_record();
                            wp_send_json_success( ["msg" => __( "Logging in", "material-dashboard" ), "url" => $redirectURL] );
                        }
                    }

                    if( $code )
                        wp_send_json_error( ["msg" => __( "Entered verification code is not correct or has been expired", "material-dashboard" )] );

                    wp_send_json_error( ["msg" => __( "Cannot send verification code, try again later", "material-dashboard" )] );

                }

            }

		}

		wp_send_json_error( [ "msg" => __( "An error has occurred", "material-dashboard" ) ] );

	}

	/**
	 * Ajax handler for logged-in users
	 * @return void
	 * @since 1.0.0
	 */
	public function ajaxHandler(){

		do_action( "amd_ajax_init", "private" );

		# Request parameters
		$r = amd_sanitize_post_fields( $_POST );
		$unfilteredRequest = $_POST;

		if( !empty( $r["_ajax_target"] ) ){

			$target = $r["_ajax_target"];
			$callbacks = [ "amd_ajax_target_$target", "amd_ajax_target_{$target}_private" ];

			foreach( $callbacks as $callback ){
				if( function_exists( $callback ) ) {

                    if( is_string( $callback ) ) {

                        /** @since 1.1.2 */
                        do_action( "amd_ajt_$callback", $r, $unfilteredRequest );

                    }

                    call_user_func( $callback, $r, $unfilteredRequest );

                }

			}
			wp_send_json_error( [ "msg" => __( "An error has occurred", "material-dashboard" ), "_msg" => "400 Bad Request" ] );
		}

		do_action( "amd_handle_private_ajax", $r );

		$isAdmin = amd_is_admin();

		if( isset( $r["logout"] ) ){

			do_action( "amd_checkout", $r );

			wp_logout();

			wp_send_json_success( [
				"msg" => "",
				"url" => sanitize_url( apply_filters( "amd_logout_redirect_url", amd_get_login_page() ) )
			] );
		}

		else if( isset( $r["close_other_sessions"] ) ){

			wp_destroy_other_sessions();

			wp_send_json_success( ["msg" => __( "Success", "material-dashboard" )] );

		}

		else if( !empty( $r["destroy_session_by_hash"] ) ){

			$hash = $r["destroy_session_by_hash"];

			$current_user = amd_get_current_user();

			$token = amd_decrypt_aes( $hash, $current_user->secretKey );

			if( class_exists( 'WP_Session_Tokens' ) ){

				$session = WP_Session_Tokens::get_instance( $current_user->ID );

				if( !$session->verify( $token ) )
					wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

				$session->destroy( $token );

				wp_send_json_success( ["msg" => __( "Success", "material-dashboard" )] );

			}

			wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

		}

		else if( !empty( $r["get_login_reports"] ) ){

			$page = intval( $r["get_login_reports"] ) - 1;

			$current_user = amd_get_current_user();

			/**
			 * Max items per page
			 * @since 1.0.5
			 */
			$max_in_page = apply_filters( "amd_login_reports_max_items_per_page", $r["per_page"] ?? 10 );

			$current_session = wp_get_session_token();

			$get_html = boolval( $r["get_html"] ?? false );

			global $amdDB;

			$_reports = $amdDB->readReports( "login", $current_user->ID );

			$chunks = array_chunk( $_reports, $max_in_page );
			$reports = $chunks[$page] ?? [];

			$unknown = __( "Unknown", "material-dashboard" );

			$hasMore = count( $chunks ) > ( $page + 1 );

			$data = [];
			$counter = 0;
			foreach( $reports as $report ){

				if( $counter >= $max_in_page )
					break;

				$id = $report->id ?? null;
				$value = $report->report_value ?? "null";
				$user_id = $report->report_user ?? null;
				$time = $report->report_time ?? null;

				if( !$id OR !$value OR !$user_id )
					continue;

				$meta = unserialize( $report->meta ?? serialize( [] ) );
				$ip = $meta["ip"] ?? $unknown;
				$identity = $meta["identity"] ?? [];
				$browser = $identity["browser"] ?? [];
				$platform = $identity["platform"] ?? $unknown;

				$data[] = array(
					"id" => $id,
					"value" => $value,
					"user_id" => $user_id,
					"time" => $time,
					"ip" => $ip,
					"browser" => $browser,
					"platform" => $platform,
					"jfy" => amd_true_date( "j F Y", $time ),
					"hi" => amd_true_date( "H:i", $time ),
				);

				$counter++;

			}

			$row_number = $page * $max_in_page + 1;
			if( $get_html ){
				ob_start();

				foreach( $data as $item ){

					$id = $item["id"];
					$value = $item["value"];
					$user_id = $item["user_id"];
					$time = $item["time"];
					$ip = $item["ip"];
					$browser = $item["browser"];
					$platform = $item["platform"];

					$token = $value == "null" ? null : amd_decrypt_aes( $value, $current_user->secretKey );
					$session = WP_Session_Tokens::get_instance( $user_id );

					?>
					<tr data-login-report="<?php echo esc_attr( $id ); ?>">
						<td><?php echo esc_attr( $row_number ); ?></td>
						<td class="_row_date">
							<span class="_item_date"><?php echo amd_true_date( "l j F Y", $time ); ?></span>
							<br>
							<span class="_item_time tiny-text color-low"><?php echo amd_true_date( "H:i", $time ); ?></span>
						</td>
						<td class="_row_platform"><?php echo esc_html( $platform ); ?></td>
						<td class="_row_browser">
							<span class="_item_browser_name"><?php echo esc_html( $browser["name"] ?? $unknown ); ?></span>
							<?php if( $version = ( $browser["version"] ?? "" ) ): ?>
								<br>
								<span class="_item_browser_version tiny-text color-low" style="display:none"><?php echo esc_html( sprintf( __( "%s version", "material-dashboard" ), $version ) ); ?></span>
							<?php endif; ?>
						</td>
						<td class="_row_ip">
							<span class="_item_ip"><?php echo esc_html( $ip ?? $unknown ); ?></span>
						</td>
						<td class="_row_session">
							<?php if( $token == $current_session ): ?>
								<p class="_item_current_session font-title color-primary mbt-5"><?php echo esc_html_x( "Current session", "Session status", "material-dashboard" ); ?></p>
							<?php else: ?>
								<?php if( $token !== null ): ?>
									<?php if( $session->verify( $token ) ): ?>
										<p class="_item_session_inactive font-title color-red mbt-5" data-status="inactive" style="display:none"><?php echo esc_html_x( "Inactive", "Session status", "material-dashboard" ); ?></p>
										<p class="_item_session_active font-title color-green mbt-5" data-status="active"><?php echo esc_html_x( "Active", "Session status", "material-dashboard" ); ?></p>
										<button type="button" class="btn btn-sm btn-text --red --low" data-id="<?php echo esc_attr( $id ); ?>" data-destroy-session="<?php echo esc_attr( $value ); ?>"><?php esc_html_e( "Destroy session", "material-dashboard" ); ?></button>
									<?php else: ?>
										<p class="_item_session_inactive font-title color-red mbt-5"><?php echo esc_html_x( "Inactive", "Session status", "material-dashboard" ); ?></p>
									<?php endif; ?>
								<?php else: ?>
									<p class="_item_session_unknown font-title mbt-5"><?php echo esc_html( $unknown ); ?></p>
								<?php endif; ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php
					$row_number++;

				}

				$html = ob_get_clean();
				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ), "html" => $html, "has_more" => $hasMore ] );
			}

			wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ), "results" => $data, "has_more" => $hasMore ] );

		}

		else if( isset( $r["_checkin"] ) ){

			do_action( "amd_checkin", $r );

			wp_send_json_success( [ "msg" => "" ] );

		}

		else if( isset( $r["switch_2fa"] ) ){

			$use_login_2fa = amd_get_site_option( "use_login_2fa", "false" ) == "true";
			$force_login_2fa = amd_get_site_option( "force_login_2fa", "false" ) == "true";

			if( !$use_login_2fa OR $force_login_2fa )
				wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

			amd_set_user_meta( get_current_user_id(), "use_2fa", $r["switch_2fa"] == "true" ? "true" : "false" );

			wp_send_json_success( ["msg" => __( "Success", "material-dashboard" )] );

		}

		if( $isAdmin ){

			do_action( "amd_handle_admin_ajax", $r, $unfilteredRequest );

			if( isset( $r["repair_db"] ) ){

                global /** @var AMD_DB $amdDB */
				$amdDB;

                $amdDB->init( true );

                $amdDB->repairTables();

                $amdDB->repairData( amd_get_default_options() );

				/**
				 * On database repair
                 * @since 1.0.5
				 */
                do_action( "amd_repair_database" );

                if( !empty( $r["fix_collation"] ) ){

                    global $amdDB;

                    $collation = $r["fix_collation"];

                    $success = false;
                    foreach( apply_filters( "amd_database_language_collations", [] ) as $item ){
                        if( !empty( $item["collation"] ) AND $collation == $item["collation"] ){
                            $charset = $item["charset"] ?? "utf8mb4";
                            foreach( $amdDB->getTables() as $table )
                                $amdDB->collateTableAndColumns( $table, $collation, $charset );
                            $success = true;
                        }
                    }

                    if( $success )
                        wp_send_json_success( ["msg" => __( "Database language changed successfully", "material-dashboard" )] );

                    wp_send_json_error( ["msg" => __( "Cannot change database language, please try again", "material-dashboard" )] );

                }

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["make_page"] ) ){
				$page = $r["make_page"];

				$confirm = !empty( $r["_confirm"] ) ? $r["_confirm"] : false;
				$confirm_replace = !empty( $r["_confirm_replace"] ) ? $r["_confirm_replace"] : false;

				$result = amd_make_pages( $page, $confirm, $confirm_replace );
				$success = $result["success"];
				$data = $result["data"] ?? [ "msg" => __( "Success", "material-dashboard" ) ];

				if( $success )
					wp_send_json_success( $data );
				else
					wp_send_json_error( $data );

			}

			else if( !empty( $r["save_options"] ) ){

				$options = $r['save_options'];

				$allowedOptions = apply_filters( "amd_get_allowed_options", [] );

				do_action( "amd_on_options_save", $options, $allowedOptions );

				foreach( $options as $key => $value ){

					/**
					 * Override site option save
                     * @since 1.0.5
					 */
                    $ignore = apply_filters( "amd_save_site_option", true, $key, $value );
                    if( $ignore === false )
                        continue;

					$action = null;
					if( !empty( $allowedOptions[$key] ) )
						$action = $allowedOptions[$key];
					if( amd_is_option_allowed( $key, $value ) ){
						$allowed = true;
						if( is_array( $action ) ){
							$type = $action["type"] ?? null;
							$filter = $action["filter"] ?? null;
							if( !empty( $filter ) AND is_callable( $filter ) )
								$value = call_user_func( $filter, $value );
							$allowed = amd_compile_data_type( $type, $value );
						}
						if( $allowed ) {
                            amd_set_site_option( $key, $value );

                            /**
                             * After option saved
                             * @since 1.1.0
                             */
                            do_action( "amd_after_option_saved", $key, $value );
                        }
					}

				}

				wp_send_json_success( [ 'msg' => __( 'Success', "material-dashboard" ) ] );

			}

			else if( !empty( $r["enable_registration"] ) ){
				$en = $r["enable_registration"];
				if( $en == "false" )
					$en = 0;
				else
					$en = (bool) $en;
				update_site_option( "users_can_register", $en );
				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );
			}

			else if( isset( $r["skip_survey"] ) ){
				amd_set_site_option( "survey_skipped", "true" );
				wp_send_json_success( ["msg" => ""] );
			}

			else if( !empty( $r["_import"] ) ){

				$mode = $r["_import"];
				$json = $r["data"] ?? null;
				$overwrite = $r["overwrite"] ?? true;

				global /** @var AMD_DB $amdDB */
				$amdDB;

				$result = $amdDB->importJSON( $mode, $json, $overwrite );

				$success = $result["success"] ?? false;
				$data = $result["data"] ?? null;

				if( !$success )
					wp_send_json_error( !empty( $data ) ? $data : [ "msg" => __( "Failed", "material-dashboard" ) ] );

				wp_send_json_success( !empty( $data ) ? $data : [ "msg" => __( "Success", "material-dashboard" ) ] );

			}

			else if( isset( $_FILES["_import_zip"] ) ){

				if( !class_exists( "ZipArchive" ) )
					wp_send_json_error( [ "msg" => sprintf( esc_html_x( "This option requires %s library and it seems like your server doesn't have it", "Admin", "material-dashboard" ), "ZipArchive" ) ] );

				$file = $_FILES["_import_zip"];
				$overwrite = $r["overwrite"] ?? true;

				$dir = AMD_UPLOAD_PATH . "/temp";
				$target_dir = "$dir/contents";
				$file_type = $file["type"];
				$file_name = sanitize_file_name( $file["name"] );
				$zip_file = "$dir/$file_name";

				$file_error = $file["error"] ?? UPLOAD_ERR_OK;
				if( $file_error != UPLOAD_ERR_OK ){
					if( $file_error == UPLOAD_ERR_INI_SIZE )
						wp_send_json_error( [ "msg" => __( "The uploaded file exceeds the 'upload_max_filesize' directive in 'php.ini'", "material-dashboard" ) ] );
					else
						wp_send_json_error( [ "msg" => __( "An error has occurred while uploading", "material-dashboard" ) ] );
				}

				if( !in_array( $file_type, [ "application/zip", "application/json" ] ) )
					wp_send_json_error( [ "msg" => __( "File format is not allowed", "material-dashboard" ) ] );

				if( $file_type == "application/json" ){

					$json = file_get_contents( $file["tmp_name"] );

					global /** @var AMD_DB $amdDB */
					$amdDB;

					$result = $amdDB->importJSON( "auto", $json, $overwrite );

					$success = $result["success"] ?? false;
					$data = $result["data"] ?? null;

					if( !$success )
						wp_send_json_error( !empty( $data ) ? $data : [ "msg" => __( "Failed", "material-dashboard" ) ] );

					wp_send_json_success( !empty( $data ) ? $data : [ "msg" => __( "Success", "material-dashboard" ) ] );
				}

				# Get current mask for umask rollback
				$old_mask = umask( 0 );

				# Delete previous 'temp' directory with its content if already exist
				amd_delete_directory( $dir, true );

				# Recreate 'temp' directory
				if( !file_exists( $dir ) )
					mkdir( $dir );

				# Create contents directory inside 'temp' directory
				if( !file_exists( $target_dir ) )
					mkdir( $target_dir );

				# Override upload path
				add_filter( "upload_dir", "amd_custom_temp_upload_dir" );

				# Move uploaded zip file to 'temp' directory itself
				$handle = wp_handle_upload( $file, [ 'test_form' => false ] );

				# Set upload path back to normal
				remove_filter( "upload_dir", "amd_custom_temp_upload_dir" );

				# Send error if moving files failed
				if( !$handle OR isset( $handle["error"] ) )
					wp_send_json_error( [ "msg" => $handle["error"] ] );

				# Create ZIP archive object
				$zip = new ZipArchive();

				# Open ZIP archive and send error if failed
				if( $zip->open( $zip_file, ZipArchive::CREATE ) !== true )
					wp_send_json_error( [ "msg" => esc_html_x( "Zip archive cannot be opened", "Admin", "material-dashboard" ) ] );

				# Extract uploaded zip file to 'contents' directory
				$zip->extractTo( $target_dir );

				# Check if 'bundle.backup' file exists, this is the signature of backup file containing backup UTC timestamp
				if( !file_exists( "$target_dir/bundle.backup" ) ){

					# Remove 'temp' directory with its content
					amd_delete_directory( $dir, true );

					# Remove 'temp' if previous function couldn't delete parent directory itself
					rmdir( $dir );

					# Rollback umask to previous mask
					umask( $old_mask );

					# Send failure message
					wp_send_json_error( [ "msg" => esc_html_x( "Backup file is not valid", "Admin", "material-dashboard" ) ] );

				}

				global $amdDB;

				# Try to import archive and get response messages
				list( $messages ) = $amdDB->importArchives( $target_dir, $overwrite );

				$html = "";
				foreach( $messages as $data ){
					$color = ( $data["success"] ?? false ) ? "green" : "red";
					$text = trim( $data["msg"] ?? "" );
					if( empty( $text ) )
						continue;
					$html .= "<p class=\"color-$color\">&bull; $text</p>";
				}

				# Remove 'temp' directory with its content
				amd_delete_directory( $dir, true );

				# Remove 'temp' if previous function couldn't delete it
				rmdir( $dir );

				# Rollback umask to previous mask
				umask( $old_mask );

				# Send success message
				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ), "html" => $html ] );

			}

			else if( isset( $r["_export"] ) ){

				$variants = $r["_export"];

				if( empty( $variants ) )
					wp_send_json_error( [ "msg" => __( "You have to select at least one variant for backup data", "material-dashboard" ) ] );

				global /** @var AMD_DB $amdDB */
				$amdDB;

				$data = $amdDB->exportJSON( $variants );

				if( empty( $data ) )
					wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );

				global /** @var AMDExplorer $amdExp */
				$amdExp;

				$backupsPath = $amdExp->getPath( "backup" );

				$file_id = "backup_" . date( "Ymd" );
				$filename = "$file_id.json";

				list( $size, $backup_url, $archive, $archive_path ) = $amdDB->exportArchives( $backupsPath, $variants, $data );

				if( $size <= 0 ){
					$size = $amdExp->makeBackup( $data, $filename );
					$backup_url = $amdExp->pathURL( "backup", $filename );
				}
				else{
					/** @var ZipArchive $archive */
					if( $archive->open( $archive_path, ZipArchive::CREATE ) === true ){
						$archive->addFromString( $filename, $data );
						$archive->close();
					}
					$filename = basename( $archive_path );
				}

				if( $size )
					wp_send_json_success( [
						"msg" => __( "Success", "material-dashboard" ),
						"url" => sanitize_url( $backup_url ),
						"filename" => $filename,
						"size" => size_format( $size )
					] );

				wp_send_json_error( [ "msg" => __( "Export data is empty or permission to save files is not granted, please check your WordPress uploads directory permission.", "material-dashboard" ) ] );

			}

			else if( isset( $r["_cleanup"] ) ){

				$variants = $r["_cleanup"];

				if( empty( $variants ) )
					wp_send_json_error( [ "msg" => __( "You have to select at least one variant for cleanup data", "material-dashboard" ) ] );

				$variants = explode( ",", $variants );

				global /** @var AMDCore $amdCore */
				$amdCore;

				$data = $amdCore->cleanup( $variants );

				$html = "";
				foreach( $data as $item )
					$html .= "<p class=\"color-green\">&bull; $item</p>";

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ), "html" => $html ] );

			}

			else if( isset( $r["_dismiss_wizard"] ) ){

				amd_set_site_option( "wizard_dismissed", "true" );

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );

			}

			else if( isset( $r["get_backups"] ) ){

				global /** @var AMDExplorer $amdExp */
				$amdExp;

				$backupsPath = $amdExp->getPath( "backup" );

				$_files = glob( "$backupsPath/*" );
				$files = [];
				foreach( $_files as $file ){
					$filename = pathinfo( $file, PATHINFO_BASENAME );
					$files[$filename] = array(
						"size" => size_format( filesize( $file ) ),
						"url" => $amdExp->pathURL( "backup", $filename )
					);
				}

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ), "files" => $files ] );

			}

			else if( !empty( $r["remove_backup"] ) ){

				$file = $r["remove_backup"];

				global /** @var AMDExplorer $amdExp */
				$amdExp;

				$success = $amdExp->removeBackup( $file );

				if( $success )
					wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );

				wp_send_json_error( [ "msg" => __( "Failed", "material-dashboard" ) ] );

			}

			else if( !empty( $r["enable_extension"] ) ){
				$id = $r["enable_extension"];

				global /** @var AMDLoader $amdLoader */
				$amdLoader;

				$amdLoader->enableExtension( $id );

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["disable_extension"] ) ){
				$id = $r["disable_extension"];

				global /** @var AMDLoader $amdLoader */
				$amdLoader;

				$amdLoader->disableExtension( $id );

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["switch_theme"] ) ){
				$id = $r["switch_theme"];

				global /** @var AMDLoader $amdLoader */
				$amdLoader;

				$amdLoader->enableTheme( $id );

				wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["get_atc_url"] ) ){

				$id = $r["get_atc_url"];

                if( strpos( $id, "," ) !== false ){
                    $ids = explode( ",", $id );
                    $urls = [];
                    foreach( $ids as $atc_id )
                        $urls[$atc_id] = wp_get_attachment_url( $atc_id );
                    wp_send_json_success( ["msg" => "", "url" => "", "urls" => $urls] );
                }

				$url = wp_get_attachment_url( $id );

				if( $url )
					wp_send_json_success( [ "msg" => "", "url" => $url, "urls" => [$id => $url] ] );

				wp_send_json_success( [ "msg" => __( "Failed", "material-dashboard" ), "url" => "", "urls" => [] ] );

			}

			else if( isset( $r["save_admin_note"] ) ){

				$note = amd_unescape_json( $r["save_admin_note"], false );

				$admin_note = amd_get_todo_list( [ "todo_key" => "admin_note" ] );
				if( count( $admin_note ) > 0 )
					$success = amd_update_todo( [ "todo_value" => $note ], [ "todo_key" => "admin_note" ], AMD_DIRECTORY );
				else
					$success = amd_add_todo( "admin_note", $note, "pending", AMD_DIRECTORY );

				if( $success )
					wp_send_json_success( [ "msg" => empty( $note ) ? __( "Note cleared", "material-dashboard" ) : __( "Saved", "material-dashboard" ) ] );

				wp_send_json_error( [ "msg" => __( "Not saved", "material-dashboard" ) ] );

			}

            else if( !empty( $r["add_custom_hook"] ) ){
                
                $data = $r["add_custom_hook"];
                $type = trim( $data["type"] ?? "" );
                $name = trim( $data["name"] ?? "" );
                $value = trim( $data["value"] ?? "" );
                $callback = trim( $data["callback"] ?? "" );
                
                if( !in_array( $type, ["filter", "action"] ) )
                    wp_send_json_error( ["msg" => __( "Hook type must be one either 'filter' or 'action'", "material-dashboard" )] );

                if( empty( $name ) )
                    wp_send_json_error( ["msg" => __( "Please fill out hook name", "material-dashboard" )] );

                /*if( empty( $value ) AND empty( $callback ) )
                    wp_send_json_error( ["msg" => __( "Please fill out hook callback or value", "material-dashboard" )] );*/

                global $amdDB;

                if( $type === "filter" )
                    $success = $amdDB->addComponent( "custom_hook_filter", json_encode( ["name" => $name, "value" => $value, "callback" => $callback] ) );
                else
                    $success = $amdDB->addComponent( "custom_hook_action", json_encode( ["name" => $name, "callback" => $callback] ) );

                if( $success )
                    wp_send_json_success( ["msg" => __( "Success", "material-dashboard" ), "data" => ["id" => $success]] );

                wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

            }

            else if( !empty( $r["delete_custom_hook"] ) ){

                $data = $r["delete_custom_hook"];
                $type = trim( $data["type"] ?? "" );
                $id = trim( $data["id"] ?? "" );

                if( !in_array( $type, ["filter", "action"] ) )
                    wp_send_json_error( ["msg" => __( "Hook type must be one either 'filter' or 'action'", "material-dashboard" )] );

                if( empty( $id ) )
                    wp_send_json_error( ["msg" => __( "Please enter hook ID", "material-dashboard" )] );

                global $amdDB;

                if( $amdDB->deleteComponent( $id ) )
                    wp_send_json_success( ["msg" => __( "Success", "material-dashboard" )] );

                wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

            }

            else if( !empty( $r["export_custom_hooks"] ) ){

                $data = $r["export_custom_hooks"];
                $type = trim( $data["type"] ?? "" );

                if( !in_array( $type, ["filter", "action"] ) )
                    wp_send_json_error( ["msg" => __( "Hook type must be one either 'filter' or 'action'", "material-dashboard" )] );

                $hooks = amd_get_custom_hooks();
                $custom = $hooks[$type === "filter" ? "filters" : "actions"];
                $export = [];
                foreach( $custom as $item ){
                    $data = $item["data"] ?? [];
                    if( !empty( $data ) )
                        $export[] = $data;
                }

                $content = amd_encrypt_aes( json_encode( $export ), $type );

                wp_send_json_success( ["msg" => __( "Success" ), "data" => $content] );

            }

            else if( !empty( $r["import_custom_hooks"] ) ){

                $data = $r["import_custom_hooks"];
                $type = trim( $data["type"] ?? "" );
                $content = trim( $data["content"] ?? "" );

                if( !in_array( $type, ["filter", "action"] ) )
                    wp_send_json_error( ["msg" => __( "Hook type must be one either 'filter' or 'action'", "material-dashboard" )] );

                if( $content ) {
                    $json = amd_decrypt_aes( $content, $type );
                    $json = @json_decode( $json );
                    if( $json ){
                        global $amdDB;
                        foreach( $json as $item ){
                            $name = $item->name ?? "";
                            if( !$name )
                                continue;
                            $value = $item->value ?? "";
                            $callback = $item->callback ?? "";
                            $amdDB->addComponent( "custom_hook_$type", json_encode( array_merge( ["name" => $name, "callback" => $callback], $type === "filter" ? ["value" => $value] : [] ) ) );
                        }
                        wp_send_json_success( ["msg" => __( "Data imported successfully", "material-dashboard" )] );
                    }
                }

                wp_send_json_error( ["msg" => __( "Data is invalid", "material-dashboard" )] );

            }
            
		}

		wp_send_json_error( [ "msg" => __( "An error has occurred", "material-dashboard" ), "html" => "" ] );

	}

	/**
	 * Handle dashboard AJAX requests
	 * @return void
	 * @since 1.0.0
	 */
	public function dashAjaxHandler(){

		do_action( "amd_ajax_init", "dashboard" );

		$r = !empty( $_POST ) ? amd_sanitize_post_fields( $_POST ) : [];

		global /** @var AMDDashboard $amdDashboard */
		$amdDashboard;

		$amdDashboard->ajax( $r );

	}

}