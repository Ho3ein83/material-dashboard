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

		$r = amd_sanitize_post_fields( $_POST );

		if( !empty( $r["_ajax_target"] ) ){

			$target = $r["_ajax_target"];
			$callbacks = [ "amd_ajax_target_$target", "amd_ajax_target_{$target}_public" ];

			foreach( $callbacks as $callback ){
				if( function_exists( $callback ) )
					call_user_func( $callback, $r );

			}
			wp_send_json_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ), "_msg" => "400 Bad Request" ] );
		}

		$_get = !empty( $r["_get"] ) ? str_replace( "?", "", $r["_get"] ) : [];
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
					wp_send_json_error( [ "msg" => esc_html__( "Too many attempts, please try again later", "material-dashboard" ) ] );

				do_action( "amd_login_before_login", $r );

				$data = $r["login"];
				$user_login = !empty( $data["user"] ) ? $data["user"] : "";
				$password = !empty( $data["password"] ) ? $data["password"] : "";
				$remember = !empty( $data["remember"] ) ? $data["remember"] : false;
				if( strlen( $user_login ) < 3 )
					wp_send_json_error( [ "msg" => esc_html__( "Please enter your username correctly", "material-dashboard" ) ] );
				else if( strlen( $password ) < 8 )
					wp_send_json_error( [ "msg" => esc_html__( "Password must contain at least 8 characters", "material-dashboard" ) ] );

				global /** @var AMDSilu $amdSilu */
				$amdSilu;

				$userData = $amdSilu->getUserAuto( $user_login );

				$isPhone = false;
				$user = null;
				if( !empty( $userData["user"] ) and $userData["user"] ){
					$user = $userData["user"];
					$isPhone = ( $userData["by"] == "phone" );
				}

				do_action( "amd_login_before_authenticate", $r );

				$auth = wp_authenticate( $user->username ?? "", $password );
				if( !$user OR !$auth OR is_wp_error( $auth ) ){
					if( $isPhone )
						wp_send_json_error( [ "msg" => esc_html__( "Your phone number or password is incorrect", "material-dashboard" ) ] );
					else
						wp_send_json_error( [ "msg" => esc_html__( "Your username or password is incorrect", "material-dashboard" ) ] );
				}

				do_action( "amd_login_after_authenticate", $r );

				$login = $amdSilu->login( $user->username, $password, $remember );

				if( is_wp_error( $login ) )
					wp_send_json_error( [ "msg" => esc_html__( "Your login information is incorrect or an error has occurred. Please try again", "material-dashboard" ) ] );

				if( !empty( $_SESSION["redirect_pending"] ) ){
					$redirectURL = sanitize_url( $_SESSION["redirect_pending"] );
					$_SESSION["redirect_pending"] = null;
					unset( $_SESSION["redirect_pending"] );
				}
				else{
					$redirectURL = amd_get_dashboard_page();
				}

				wp_send_json_success( [ "msg" => esc_html__( "Logging in", "material-dashboard" ), "url" => $redirectURL ] );

			}

			else if( !empty( $r["register"] ) ){

				$data = $r["register"];

				$firstname = sanitize_text_field( $data["firstname"] ?? "" );
				$lastname = sanitize_text_field( $data["lastname"] ?? "" );
				$email = sanitize_email( $data["email"] ?? "" );
				$username = sanitize_text_field( $data["username"] ?? "" );
				$password = sanitize_text_field( $data["password"] ?? "" );
				$phone = sanitize_text_field( $data["phone"] ?? "" );
				$phone = amd_apply_phone_format( $phone );
				$login_after_registration = (bool) ( $data["login_after_registration"] ?? false );

				global /** @var AMDSilu $amdSilu */
				$amdSilu;

				$phone_field = amd_get_site_option( "phone_field" ) == "true";
				$phone_field_required = amd_get_site_option( "phone_field_required" ) == "true";
				$single_phone = amd_get_site_option( "single_phone" ) == "true";
				$lastname_field = amd_get_site_option( "lastname_field" ) == "true";
				$username_field = amd_get_site_option( "username_field" ) == "true";

				if( $lastname_field AND empty( $lastname ) )
					wp_send_json_error( [
						"msg" => esc_html__( "Failed", "material-dashboard" ),
						"errors" => [
							[
								"id" => "lastname_required",
								"error" => esc_html__( "Please enter you last name correctly", "material-dashboard" ),
								"field" => "lastname"
							]
						]
					] );

				if( $phone_field ){
					if( $phone_field_required OR !empty( $phone ) ){
						if( !amd_validate_phone_number( $phone ) ){
							wp_send_json_error( [
								"msg" => esc_html__( "Failed", "material-dashboard" ),
								"errors" => [
									[
										"id" => "phone_incorrect",
										"error" => esc_html__( "Please enter your phone number correctly", "material-dashboard" ),
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
							"msg" => esc_html__( "Failed", "material-dashboard" ),
							"errors" => [
								[
									"id" => "phone_exists",
									"error" => esc_html__( "This phone number is already in use", "material-dashboard" ),
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
						wp_send_json_error( [ "msg" => esc_html__( "Please enter a valid username", "material-dashboard" ) ] );
					}
				}

				$result = $amdSilu->registerUser( $email, $username, $password, $login_after_registration );

				$success = $result["success"];
				$errors = $result["errors"];

				if( !$success )
					wp_send_json_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ), "errors" => $errors ] );

				$uid = $result["user_id"];

				$locale = get_locale();

				// Set user meta (WordPress meta)
				update_user_meta( $uid, "first_name", $firstname );
				update_user_meta( $uid, "last_name", $lastname );
				update_user_meta( $uid, "locale", $locale );

				// Set user meta (AMD plugin meta)
				if( $phone_field )
					amd_set_user_meta( $uid, "phone", $phone );
				amd_set_user_meta( $uid, "registration", time() );
				amd_set_user_meta( $uid, "_init", "*" );
				amd_set_user_meta( $uid, "signup_method", "direct" );

				if( apply_filters( "amd_welcome_message_enabled", true ) )
					amd_set_user_meta( $uid, "welcome_pending", "true" );

				do_action( "amd_signup_complete", $uid );

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "url" => "", "query" => "auth=intro" ] );

			}

			else if( !empty( $r["reset_password"] ) ){

				$data = $r["reset_password"];
				$email = $data["email"] ?? "";
				$vCode = $data["vCode"] ?? "";
				$new_password = $data["new_password"];

				if( !empty( $email ) ){
					$user = get_user_by( "email", $email );
					if( !$user OR is_wp_error( $user ) )
						wp_send_json_error( [ "msg" => esc_html__( "Email not found", "material-dashboard" ) ] );
					if( !empty( $vCode ) ){
						if( !empty( $new_password ) ){
							if( strlen( $new_password ) < 8 )
								wp_send_json_error( [ "msg" => esc_html__( "Password must contain at least 8 characters", "material-dashboard" ) ] );
							$success = amd_change_password( $user->ID, $new_password, apply_filters( "amd_notify_password_change", true ) );
							if( $success )
								wp_send_json_success( [ "msg" => esc_html__( "Your password has been changed", "material-dashboard" ) ] );
							else
								wp_send_json_success( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );
						}
						else{
							$valid = amd_is_verification_code_valid( $user->ID, $vCode );
							if( !$valid )
								wp_send_json_error( [ "msg" => esc_html__( "Entered verification code is not correct or has been expired", "material-dashboard" ) ] );
							wp_send_json_success( [ "msg" => esc_html__( "Please enter your new password", "material-dashboard" ) ] );
						}
					}
					else{
						$vCode = amd_regenerate_verification_code( $user->ID );
						if( !$vCode )
							wp_send_json_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

						global /** @var AMDWarner $amdWarn */
						$amdWarn;

						$message = apply_filters( "amd_new_verification_code_message", $user->ID, $vCode );
						$sent = $amdWarn->sendEmail( $user->user_email, esc_html__( "Reset password", "material-dashboard" ), $message );

						if( !$sent )
							wp_send_json_error( [ "msg" => esc_html__( "An error has occurred while sending email", "material-dashboard" ) ] );

						wp_send_json_success( [ "msg" => esc_html__( "Verification code has been sent to your email", "material-dashboard" ) ] );
					}
				}

				wp_send_json_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

			}

		}

		wp_send_json_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

	}

	/**
	 * Ajax handler for logged-in users
	 * @return void
	 * @since 1.0.0
	 */
	public function ajaxHandler(){

		# Request parameters
		$r = amd_sanitize_post_fields( $_POST );

		if( !empty( $r["_ajax_target"] ) ){

			$target = $r["_ajax_target"];
			$callbacks = [ "amd_ajax_target_$target", "amd_ajax_target_{$target}_private" ];

			foreach( $callbacks as $callback ){
				if( function_exists( $callback ) )
					call_user_func( $callback, $r );

			}
			wp_send_json_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ), "_msg" => "400 Bad Request" ] );
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

		else if( isset( $r["_checkin"] ) ){

			do_action( "amd_checkin", $r );

			wp_send_json_success( [ "msg" => "" ] );

		}

		if( $isAdmin ){

			do_action( "amd_handle_admin_ajax", $r );

			if( isset( $r["repair_db"] ) ){
				global /** @var AMD_DB $amdDB */
				$amdDB;
				$amdDB->init( true );
				$amdDB->repairData( amd_get_default_options() );
				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["make_page"] ) ){
				$page = $r["make_page"];

				$confirm = !empty( $r["_confirm"] ) ? $r["_confirm"] : false;
				$confirm_replace = !empty( $r["_confirm_replace"] ) ? $r["_confirm_replace"] : false;

				$result = amd_make_pages( $page, $confirm, $confirm_replace );
				$success = $result["success"];
				$data = $result["data"] ?? [ "msg" => esc_html__( "Success", "material-dashboard" ) ];

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

					$action = null;
					if( !empty( $allowedOptions[$key] ) )
						$action = $allowedOptions[$key];
					if( amd_is_option_allowed( $key, $value ) ){
						$allowed = true;
						if( is_array( $action ) ){
							$type = $action["type"] ?? null;
							$filter = $action["filter"] ?? null;
							if( !empty( $filter ) and is_callable( $filter ) )
								$value = call_user_func( $filter, $value );
							$allowed = amd_compile_data_type( $type, $value );
						}
						if( $allowed )
							amd_set_site_option( $key, $value );
					}

				}

				wp_send_json_success( [ 'msg' => esc_html__( 'Success', "material-dashboard" ) ] );

			}

			else if( !empty( $r["enable_registration"] ) ){
				$en = $r["enable_registration"];
				if( $en == "false" )
					$en = 0;
				else
					$en = (bool) $en;
				update_site_option( "users_can_register", $en );
				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
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
					wp_send_json_error( !empty( $data ) ? $data : [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

				wp_send_json_success( !empty( $data ) ? $data : [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );

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
						wp_send_json_error( [ "msg" => esc_html__( "The uploaded file exceeds the 'upload_max_filesize' directive in 'php.ini'", "material-dashboard" ) ] );
					else
						wp_send_json_error( [ "msg" => esc_html__( "An error has occurred while uploading", "material-dashboard" ) ] );
				}

				if( !in_array( $file_type, [ "application/zip", "application/json" ] ) )
					wp_send_json_error( [ "msg" => esc_html__( "File format is not allowed", "material-dashboard" ) ] );

				if( $file_type == "application/json" ){

					$json = file_get_contents( $file["tmp_name"] );

					global /** @var AMD_DB $amdDB */
					$amdDB;

					$result = $amdDB->importJSON( "auto", $json, $overwrite );

					$success = $result["success"] ?? false;
					$data = $result["data"] ?? null;

					if( !$success )
						wp_send_json_error( !empty( $data ) ? $data : [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

					wp_send_json_success( !empty( $data ) ? $data : [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
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

				# Check if 'bundle.backup' file exists, this is the signature of backup file contains backup UTC timestamp
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
				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "html" => $html ] );

			}

			else if( isset( $r["_export"] ) ){

				$variants = $r["_export"];

				if( empty( $variants ) )
					wp_send_json_error( [ "msg" => esc_html__( "You have to select at least one variant for backup data", "material-dashboard" ) ] );

				global /** @var AMD_DB $amdDB */
				$amdDB;

				$data = $amdDB->exportJSON( $variants );

				if( empty( $data ) )
					wp_send_json_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

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
						"msg" => esc_html__( "Success", "material-dashboard" ),
						"url" => sanitize_url( $backup_url ),
						"filename" => $filename,
						"size" => size_format( $size )
					] );

				wp_send_json_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

			}

			else if( isset( $r["_cleanup"] ) ){

				$variants = $r["_cleanup"];

				if( empty( $variants ) )
					wp_send_json_error( [ "msg" => esc_html__( "You have to select at least one variant for cleanup data", "material-dashboard" ) ] );

				$variants = explode( ",", $variants );

				global /** @var AMDCore $amdCore */
				$amdCore;

				$data = $amdCore->cleanup( $variants );

				$html = "";
				foreach( $data as $item )
					$html .= "<p class=\"color-green\">&bull; $item</p>";

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "html" => $html ] );

			}

			else if( isset( $r["_dismiss_wizard"] ) ){

				amd_set_site_option( "wizard_dismissed", "true" );

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );

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

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ), "files" => $files ] );

			}

			else if( !empty( $r["remove_backup"] ) ){

				$file = $r["remove_backup"];

				global /** @var AMDExplorer $amdExp */
				$amdExp;

				$success = $amdExp->removeBackup( $file );

				if( $success )
					wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );

				wp_send_json_error( [ "msg" => esc_html__( "Failed", "material-dashboard" ) ] );

			}

			else if( !empty( $r["enable_extension"] ) ){
				$id = $r["enable_extension"];

				global /** @var AMDLoader $amdLoader */
				$amdLoader;

				$amdLoader->enableExtension( $id );

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["disable_extension"] ) ){
				$id = $r["disable_extension"];

				global /** @var AMDLoader $amdLoader */
				$amdLoader;

				$amdLoader->disableExtension( $id );

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["switch_theme"] ) ){
				$id = $r["switch_theme"];

				global /** @var AMDLoader $amdLoader */
				$amdLoader;

				$amdLoader->enableTheme( $id );

				wp_send_json_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );
			}

			else if( !empty( $r["get_atc_url"] ) ){

				$id = $r["get_atc_url"];

				$url = wp_get_attachment_url( $id );

				if( $url )
					wp_send_json_success( [ "msg" => "", "url" => $url ] );

				wp_send_json_success( [ "msg" => esc_html__( "Failed", "material-dashboard" ), "url" => "" ] );

			}

			else if( isset( $r["save_admin_note"] ) ){

				$note = amd_unescape_json( $r["save_admin_note"], false );

				$admin_note = amd_get_todo_list( [ "todo_key" => "admin_note" ] );
				if( count( $admin_note ) > 0 )
					$success = amd_update_todo( [ "todo_value" => $note ], [ "todo_key" => "admin_note" ], AMD_DIRECTORY );
				else
					$success = amd_add_todo( "admin_note", $note, "pending", AMD_DIRECTORY );

				if( $success )
					wp_send_json_success( [ "msg" => esc_html__( "Saved", "material-dashboard" ) ] );

				wp_send_json_error( [ "msg" => esc_html__( "Not saved", "material-dashboard" ) ] );

			}

		}

		wp_send_json_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ), "html" => "" ] );

	}

	/**
	 * Handle dashboard AJAX requests
	 * @return void
	 * @since 1.0.0
	 */
	public function dashAjaxHandler(){

		$r = !empty( $_POST ) ? amd_sanitize_post_fields( $_POST ) : [];

		global /** @var AMDDashboard $amdDashboard */
		$amdDashboard;

		$amdDashboard->ajax( $r );

	}

}