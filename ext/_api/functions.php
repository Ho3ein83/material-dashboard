<?php

/**
 * Handle AJAX request
 * @param $r
 * Request data
 *
 * @return void
 * @since 1.0.0
 */
function amd_ext__api_handler_all( $r ){

	if( is_user_logged_in() ){

		# Current user
		$_current_user = amd_get_current_user();

		# Current user. Note: this object can be changed by admins by passing user ID with _user parameter!
		$_thisuser = $_current_user;

		if( amd_is_admin() AND !empty( $r["_user"] ) )
			$_thisuser = amd_get_user_by( "ID|email|login", $r["_user"] );

		if( !$_thisuser )
			amd_send_api_error( array(
				"error_code" => "invalid_user",
				"msg" => _x( "User not found", "Admin", "material-dashboard" )
			) );

		if( isset( $r["logout"] ) ){

			wp_logout();

			amd_send_api_success( array(
				"msg" => "",
				"url" => amd_get_login_page()
			) );

		}

		else if( !empty( $r["edit_account"] ) ){

			$user = $_thisuser;

			$data = $r["edit_account"];
			$toasts = [];

			$lastname_field = amd_get_site_option( "lastname_field" ) == "true";

			# Firstname
			if( !empty( $data["first_name"] ) )
				update_user_meta( $user->ID, "first_name", $data["first_name"] );

			# Lastname
			if( $lastname_field ){
				if( !empty( $data["last_name"] ) )
					update_user_meta( $user->ID, "last_name", $data["last_name"] );
			}
			else{
				update_user_meta( $user->ID, "last_name", "" );
			}

			$email_pending = false;
			$change_email_allowed = apply_filters( "amd_change_email_allowed", false );
			if( !empty( $data["email"] ) ){
				$target_email = sanitize_email( $data["email"] );
				if( $target_email != $user->email ){
					if( !amd_validate( $target_email, "%email%" ) ){
						$toasts[] = array(
							"text" => __( "Entered email is invalid", "material-dashboard" ),
							"timeout" => 4000
						);
					}
					else if( !$change_email_allowed ){
						$toasts[] = array(
							"text" => __( "You can't change your email address", "material-dashboard" ),
							"timeout" => 4000
						);
					}
					else if( email_exists( $target_email ) ){
						$toasts[] = array(
							"text" => __( "This email is already in use", "material-dashboard" ),
							"timeout" => 4000
						);
					}
					else{

						# Remove any email change request from temps
						amd_clean_temps_keys( "change_email:($user->ID)_(.*){8}" );

						# Create new action request and add it to temps (expires after 3600 seconds / 1 hour )
						$action_url = amd_make_action_url( "change_email", $user->ID . "_" . amd_generate_string_pattern( "[all:8]" ), $target_email );

						global $amdWarn;

						$message = apply_filters( "amd_change_email_message", $user->ID, $target_email, sanitize_url( $action_url ) );

						# Send confirmation email to user
						$sent = $amdWarn->sendEmail( $user->email, __( "Change your email", "material-dashboard" ), $message );

						if( $sent ){
							$email_pending = $target_email;
							$toasts[] = array(
								"text" => __( "Email change confirmation sent to your email", "material-dashboard" ),
								"timeout" => 5000
							);
						}

					}
				}
			}

			amd_send_api_success( array(
				"msg" => __( "Success", "material-dashboard" ),
				"toasts" => $toasts,
				"email_pending" => $email_pending
			) );

		}

		else if( !empty( $r["save_user_fields"] ) ){

			$uid = get_current_user_id();

			$custom_fields = $_POST["save_user_fields"] ?? [];
			$custom_fields = amd_sanitize_array_fields( $custom_fields );

			if( empty( $custom_fields ) )
				wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

			/**
			 * Validate fields before continue the registration
			 * @since 1.1.1
			 */
			do_action( "amd_register_validate_custom_fields", $custom_fields );

			/**
			 * Save user custom fields
			 * @since 1.0.5
			 */
			do_action( "amd_save_user_custom_fields", $uid, $custom_fields );

			wp_send_json_success( [ "msg" => __( "Success", "material-dashboard" ) ] );

		}

		else if( isset( $r["cancel_email_pending"] ) ){

			$user = $_thisuser;

			# Remove any email change request from temps
			amd_clean_temps_keys( "change_email:($user->ID)_(.*){8}" );

			amd_send_api_success( [ "msg" => "" ] );

		}

		else if( isset( $r["resend_email_pending"] ) ){

			$user = $_thisuser;

			# Get email change request from temps
			$temps = amd_find_temp( "temp_key", "change_email:($user->ID)_(.*){8}", true );
			$temp = $temps[0] ?? null;

			if( empty( $temp->temp_value ) )
				amd_send_api_error( ["msg" => __( "No email change request found", "material-dashboard" )] );

			# Create new action request and add it to temps with (expires after 3600 seconds / 1 hour )
			$action_url = amd_make_action_url_without_temp( "change_email", explode( ":", $temp->temp_key )[1] ?? "" );

			global $amdWarn;

			$message = apply_filters( "amd_change_email_message", $user->ID, $temp->temp_value, $action_url );

			# Send confirmation email to user
			$sent = $amdWarn->sendEmail( $user->email, __( "Change your email", "material-dashboard" ), $message );

            $toasts = [];
			if( $sent )
				$toasts[] = array(
					"text" => __( "Email change confirmation sent to your email", "material-dashboard" ),
					"timeout" => 5000
				);

			amd_send_api_success( array(
				"msg" => __( "Success", "material-dashboard" ),
				"toasts" => $toasts
			) );

		}

		else if( !empty( $r["upload_avatar"] ) ){

			$base64 = $r["upload_avatar"];

			$user = $_thisuser;

			list( $mime, $image ) = explode( ";", $base64 );
			list( , $image ) = explode( ",", $image );

			$mime = str_replace( "data:", "", $mime );

			# [AVATAR_IMAGE_FORMAT]
			$allowed_formats = ["image/png"];

			$extension = amd_guess_extension_from_mime_type( $mime );

			if( empty( $extension ) OR !in_array( $mime, $allowed_formats ) AND !in_array( $extension, $allowed_formats ) )
				amd_send_api_error( ["msg" => __( "Image format is not allowed", "material-dashboard" )] );

			$image_data = base64_decode( $image );
			$size = intval( floor( strlen( rtrim( $image, "=" ) ) * 0.75 ) );
			$max_size = apply_filters( "amd_max_avatar_upload_size", 1024*1024*2 );

			if( $size > $max_size )
				amd_send_api_error( ["msg" => sprintf( __( "Uploaded image is too large. Max size is %s", "material-dashboard" ), size_format( $max_size ) )] );

			$avatars_path = amd_get_avatars_path();
			if( !file_exists( $avatars_path ) )
				mkdir( $avatars_path, 0777, true );
			$image_path = $avatars_path . "/$user->secretKey.$extension";

			$bytes = file_put_contents( $image_path, $image_data );

			if( $bytes ){
				amd_set_user_meta( $user->ID, "avatar", $user->secretKey );

                # since 1.1.1
                do_action( "amd_avatar_updated", $user );

				amd_send_api_success( ["msg" => __( "Avatar image changed", "material-dashboard" ), "url" => amd_merge_url_query( amd_avatar_url( $user->secretKey ), "cache=" . time() ) ] );
			}

			amd_send_api_error( ["msg" => __( "Failed", "material-dashboard" )] );

		}

		else if( !empty( $r["change_avatar"] ) ){

			$user = $_thisuser;

			$avatar = $r["change_avatar"];

            if( preg_match( "/^https?:\/\/.*/", $avatar ) ){

                # since 1.2.0
                if( apply_filters( "amd_is_avatar_url_allowed", false, $avatar ) ){

                    amd_set_user_meta( $user->ID, "avatar", $avatar );

                    # since 1.1.1
                    do_action( "amd_avatar_updated", $user );

                    amd_send_api_success( ["msg" => __( "Avatar image changed", "material-dashboard" ), "url" => $avatar] );

                }
                else{
                    wp_send_json_error( ["msg" => __( "This avatar is not available or something went wrong", "material-dashboard" )] );
                }

            }

			if( in_array( $avatar, ["_EMPTY_", "placeholder", $user->secretKey] ) ){

				amd_set_user_meta( $user->ID, "avatar", $avatar );

                # since 1.1.1
                do_action( "amd_avatar_updated", $user );

				amd_send_api_success( ["msg" => __( "Avatar image changed", "material-dashboard" ), "url" => amd_merge_url_query( amd_avatar_url( $avatar ), "cache=" . time() ) ] );

			}

			list( $path_id, $avatar_file ) = explode( ":", $avatar );

			if( empty( $path_id ) )
				$path_id = "amd_default";

			$avatars = apply_filters( "amd_get_avatars", [] );
			$avatars_path = $avatars[$path_id] ?? "";

			$avatar_path = "";
			if( !empty( $avatars_path ) AND file_exists( $avatars_path ) )
				$avatar_path = is_dir( $avatars_path ) ? "$avatars_path/$avatar_file" : $avatar_file;

			if( !file_exists( $avatar_path ) )
				wp_send_json_error( ["msg" => __( "This avatar is not available or something went wrong", "material-dashboard" )] );

			amd_set_user_meta( $user->ID, "avatar", "$path_id:$avatar_file" );

            # since 1.1.1
            do_action( "amd_avatar_updated", $user );

			amd_send_api_success( ["msg" => __( "Avatar image changed", "material-dashboard" ), "url" => sanitize_url( amd_merge_url_query( amd_avatar_url( "$path_id:$avatar_file" ), "cache=" . time() ) ) ] );

		}

		else if( !empty( $r["change_password"] ) ){

            $data = $r["change_password"];
			$current_password = $data["current_password"] ?? "";
			$new_password = $data["new_password"] ?? "";

            if( strlen( $current_password ) < 8 || strlen( $new_password ) < 8 )
                wp_send_json_error( ["msg" => __( "Password must be at least 8 characters", "material-dashboard" )] );

            global $amdSilu;

            if( !$amdSilu->checkPassword( null, $current_password ) )
                wp_send_json_error( ["msg" => __( "Entered password doesn't match your current password", "material-dashboard" )] );

			$user = $_thisuser;


			$error = $amdSilu->changePassword( $user->ID, $new_password );

			if( empty( $error ) )
				wp_send_json_success( ["msg" => __( "Your password has changed successfully", "material-dashboard" )] );

			wp_send_json_error( ["msg" => $error] );

		}

		else if( !empty( $r["change_phone"] ) ){

			/**
			 * Allow users change phone number
			 * @since 1.0.4
			 */
			$allow_change_phone_number = apply_filters( "amd_allow_change_phone_number", true );

			if( !$allow_change_phone_number )
				wp_send_json_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

			$phone = $r["change_phone"];
			$phone = str_replace( " ", "", $phone );

			$phone_field = amd_get_site_option( "phone_field" ) == "true";
			$single_phone = amd_get_site_option( "single_phone" ) == "true";

			if( !$phone_field OR empty( $phone ) )
				wp_send_json_error( ["msg" => esc_html__( "Failed", "material-dashboard" )] );

			$formatted_phone = amd_apply_phone_format( $phone );

			if( $single_phone AND amd_phone_exists( $formatted_phone ) )
				wp_send_json_error( [ "msg" => esc_html__( "This phone number is already in use", "material-dashboard" )] );

			if( !amd_validate_phone_number( $phone ) )
				wp_send_json_error( [ "msg" => esc_html__( "Please enter your phone number correctly", "material-dashboard" )] );

			$success = amd_set_user_meta( null, "phone", $formatted_phone );

			if( $success )
				do_action( "on_phone_number_updated", $formatted_phone );

			wp_send_json_success( ["msg" => esc_html__( "Success", "material-dashboard" )] );

		}

        else if( !empty( $r["__validate"] ) ){

            $data = $r["__validate"];
            $user = amd_get_current_user();

            if( !empty( $data["phone"] ) ){
                $phone = amd_apply_phone_format( $data["phone"] );
                if( amd_validate_phone_number( $phone ) ){
                    if( amd_get_user_by_meta( "phone", $phone ) instanceof AMDUser )
                        wp_send_json_error( ["msg" => __( "This phone number is already in use", "material-dashboard" )] );
                    amd_set_user_meta( $user->ID, "phone", $phone );
                    $is_phone_verified = intval( amd_get_temp( "phone_verified_$phone" ) );
                    if( $is_phone_verified AND $is_phone_verified >= time() )
                        amd_set_user_meta( $user->ID, "phone_verified", time() );
                    wp_send_json_success( ["msg" => __( "Success", "material-dashboard" )] );
                }
            }

            /**
             * Validate the user information
             * @since 1.2.0
             */
            do_action( "amd_validate_user_information", $user, $data );

            wp_send_json_error( ["msg" => __( "Failed", "material-dashboard" )] );

        }

        else if( !empty( $r["search_product"] ) ){

            if( !class_exists( "WooCommerce" ) )
                wp_send_json_error( ["msg" => __( "WooCommerce plugin is not available", "material-dashboard" )] );

            $query = $r["search_product"];
            $keywords = explode( " ", $query );

            /**
             * Search limit
             * @sicne 1.2.0
             */
            $limit = apply_filters( "amd_product_search_limits", $r["search_limit"] ?? 20 );

            $posts = get_posts( array(
                "post_type" => "product",
                "numberposts" => 50
            ) );

            if( empty( $posts ) )
                wp_send_json_error( ["msg" => _x( "No results found", "Search results", "material-dashboard" )] );

            $results = [];
            $counter = 0;
            foreach( $posts as $post ){
                if( $counter >= $limit )
                    break;
                if( $post instanceof WP_Post ) {
                    $title = $post->post_title;
                    $content = wp_kses( $post->post_content, [] );
                    $excerpt = wp_kses( $post->post_excerpt, [] );
                    $add = false;
                    foreach( $keywords as $keyword ){
                        if( strpos( "$title\n$content\n$excerpt", $keyword ) !== false ){
                            $add = true;
                            break;
                        }
                    }
                    if( $add ){
                        if( $data = amd_export_post_data( $post ) ) {
                            $results[$post->ID] = $data;
                            $counter++;
                        }
                    }
                }
            }

            wp_send_json_success( ["msg" => __( "Success", "material-dashboard" ), "results" => $results] );

        }

        else if( !empty( $r["get_posts"] ) ){

            if( !class_exists( "WooCommerce" ) )
                wp_send_json_error( ["msg" => __( "WooCommerce plugin is not available", "material-dashboard" )] );

            $products = explode( ",", $r["get_posts"] );

            $post_type = $r["type"] ?? null;

            $posts = [];
            foreach( $products as $product_id ){
                $p = get_post( $product_id );
                if( $p instanceof WP_Post ) {
                    if( $post_type AND $post_type != $p->post_type )
                        continue;
                    $posts[] = $p;
                }
            }

            if( empty( $posts ) )
                wp_send_json_error( ["msg" => _x( "No results found", "Search results", "material-dashboard" )] );

            $results = [];
            foreach( $posts as $post ){
                if( $post instanceof WP_Post ) {
                    if( $data = amd_export_post_data( $post ) )
                        $results[$post->ID] = $data;
                }
            }

            wp_send_json_success( ["msg" => __( "Success", "material-dashboard" ), "results" => $results] );

        }

	}

}