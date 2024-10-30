<?php

/**
 * Message template scopes
 * @since 1.0.8
 */
add_filter( "amd_messages_templates_scopes", function( $scopes ){

	$scopes["email"] = array(
		"id" => "email",
		"title" => __( "Email", "material-dashboard" ),
		"desc" => _x( "Alert users with email", "Message scope", "material-dashboard" )
	);

	$scopes["sms"] = array(
		"id" => "sms",
		"title" => __( "SMS", "material-dashboard" ),
		"desc" => _x( "Alert users with SMS if they have registered phone number", "Message scope", "material-dashboard" )
	);
	
	return $scopes;

} );

/**
 * Get all groups
 * @since 1.0.8
 */
add_filter( "amd_messages_templates_groups", function( $groups ){

	$groups["sms_templates"] = array(
		"title" => _x( "SMS templates", "Admin text template", "material-dashboard" ),
		"list" => apply_filters( "amd_sms_message_templates", [] )
	);

    $groups["users"] = array(
		"title" => __( "User messages", "material-dashboard" ),
		"list" => apply_filters( "amd_user_message_templates", [] )
	);

	return $groups;

} );

/**
 * User messages templates
 * @since 1.0.8
 */
add_filter( "amd_user_message_templates", function( $list ){

	$list["welcome"] = array(
		"title" => __( "Welcome", "material-dashboard" ),
		"template" => sprintf( __( "Hello dear %s! Thanks for subscribing to our site, we hope you enjoy using it.", "material-dashboard" ), "%FIRSTNAME%" ),
		"scopes" => ["email", "sms"]
	);

	$list["password_reset_verification"] = array(
		"title" => __( "Password reset verification code", "material-dashboard" ),
		"template" => sprintf( __( "Dear %s, your verification code for password reset is: %s", "material-dashboard" ), "%FIRSTNAME%", "%CODE%" ),
		"scopes" => ["email", "sms"],
		"dependencies" => ["code"],
	);

	$list["password_changed"] = array(
		"title" => __( "Password changed", "material-dashboard" ),
		"scopes" => ["email", "sms"],
		"template" => sprintf( __( "Dear %s, your password has been changed at %s.\nPlease let us know if you didn't change it.", "material-dashboard" ), "%FIRSTNAME%", "%DATE%" )
	);

	$list["change_email_confirm"] = array(
		"title" => __( "Change email confirmation", "material-dashboard" ),
		"scopes" => ["email"],
		"template" => sprintf( __( "Dear %s, you have requested for changing your email, if you want to change it to %s please click on the below link. %s", "material-dashboard" ), "%FIRSTNAME%", "%NEW_EMAIL%", "%URL%" ),
		"dependencies" => ["new_email", "url"],
	);

	$list["email_changed"] = array(
		"title" => __( "Email changed", "material-dashboard" ),
		"scopes" => ["email"],
		"template" => sprintf( __( "Dear %s, your email address has been changed successfully", "material-dashboard" ), "%FIRSTNAME%" ),
		"dependencies" => ["new_email"],
	);

	$list["2fa_code"] = array(
		"title" => esc_html__( "Two factor authentication code", "material-dashboard" ),
		"scopes" => ["email", "sms"],
		"template" => sprintf( __( "Dear %s, your 2FA code is: %s", "material-dashboard" ), "%FIRSTNAME%", "%CODE%" ),
		"dependencies" => ["code"],
	);

    $list["new_login"] = array(
		"title" => __( "New login", "material-dashboard" ),
		"scopes" => ["email", "sms"],
		"template" => sprintf( __( "Dear %s, new device has been logged in to your account.\n%s\n%s", "material-dashboard" ), "%FIRSTNAME%", "%DATE%", "%DEVICE%" ),
		"dependencies" => ["device"],
	);

    $list["otp_verification"] = array(
		"title" => __( "OTP verification", "material-dashboard" ),
		"scopes" => ["email", "sms"],
		"template" => sprintf( __( "Your verification code for login is: %s", "material-dashboard" ), "%CODE%" ),
		"dependencies" => ["code"],
	);

	return $list;

} );

/**
 * User SMS templates
 * @since 1.2.0
 */
add_filter( "amd_sms_message_templates", function( $list ){

	$list["sms_footer"] = array(
		"title" => _x( "Footer template", "Admin text template", "material-dashboard" ),
		"template" => amd_replace_url( "%domain%" ),
		"scopes" => ["sms"],
        "allow_variables" => false,
        "allowed_html_tags" => [],
	);

	return $list;

} );

/**
 * Override SMS footer text
 * @since 1.2.0
 */
add_filter( "amd_sms_footer", function(){
    return amd_get_user_message_template( "sms_footer", amd_get_dummy_user() );
} );

/**
 * User message template variables
 * @since 1.0.8
 */
add_filter( "amd_user_message_template_variables", function( $vars ){

	// TODO: add shortcode for [amd-msg-template="XYZ"]

	$vars["firstname"] = array(
		"id" => "firstname",
		"title" => __( "Firstname", "material-dashboard" ),
		"desc" => __( "The name of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto", # %FIRSTNAME%
		"type" => "string",
		"independent" => "users,~",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->firstname;
			return "";
		}
	);

	$vars["lastname"] = array(
		"id" => "lastname",
		"title" => __( "Lastname", "material-dashboard" ),
		"desc" => __( "The name of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto", # %LASTNAME%
        "type" => "string",
		"independent" => "users,~",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->lastname;
			return "";
		}
	);

	$vars["fullname"] = array(
		"id" => "fullname",
		"title" => __( "Fullname", "material-dashboard" ),
		"desc" => __( "The name of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto", # ...
        "type" => "string",
		"independent" => "users,~",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->fullname;
			return "";
		}
	);

	$vars["email"] = array(
		"id" => "email",
		"title" => __( "Email", "material-dashboard" ),
		"desc" => __( "The email of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->email;
			return "";
		}
	);

	$vars["username"] = array(
		"id" => "username",
		"title" => __( "Username", "material-dashboard" ),
		"desc" => __( "The username of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->username;
			return "";
		}
	);

	$vars["phone"] = array(
		"id" => "phone",
		"title" => __( "Phone", "material-dashboard" ),
		"desc" => __( "The phone number of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,~",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->phone;
			return "";
		}
	);

	$vars["ncode"] = array(
		"id" => "ncode",
		"title" => __( "National code", "material-dashboard" ),
		"desc" => __( "The nationcal code of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,~",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return amd_get_user_meta( $user->ID, "ncode" );
			return "";
		}
	);

	$vars["uid"] = array(
		"id" => "uid",
		"title" => __( "User ID", "material-dashboard" ),
		"desc" => __( "The ID of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "integer",
		"independent" => "users,~",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->ID;
			return "";
		}
	);

	$vars["date"] = array(
		"id" => "date",
		"title" => __( "Date and time", "material-dashboard" ),
		"desc" => __( "Current date and time based on current user region like: 2023/01/01 12:00", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_true_date( "Y/m/d H:i" );
		}
	);

	$vars["single_date"] = array(
		"id" => "single_date",
		"title" => __( "Date", "material-dashboard" ),
		"desc" => __( "Current single date based on current user region like: 2023/01/01", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_true_date( "Y/m/d" );
		}
	);

	$vars["time"] = array(
		"id" => "time",
		"title" => __( "Time", "material-dashboard" ),
		"desc" => __( "Current time based on current user region like: 12:00", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_true_date( "H:i" );
		}
	);

	$vars["url"] = array(
		"id" => "url",
		"title" => __( "URL", "material-dashboard" ),
		"desc" => __( "Specified URL, for example if you are editing email confirmation message this URL is email confirmation URL", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
            return $args[1] ?? "-";
		}
	);

	$vars["code"] = array(
		"id" => "code",
		"title" => __( "Verification code", "material-dashboard" ),
		"desc" => __( "Specified verification code, for example if you are editing 2 factor authentication message this code is 2FA code", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "integer",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
			$code = $args[0] ?? null;
			return $code ?: "-";
		}
	);

    $vars["device"] = array(
		"id" => "device",
		"title" => __( "Device", "material-dashboard" ),
		"desc" => __( "Device info such as device name and IP address (whichever is available)", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
            $user_agent = $args[0] ?? null;
            $ip = $args[1] ?? null;
            if( empty( $user_agent ) OR !is_string( $user_agent ) )
                return "?";
            global $amdWall;
            $result = $amdWall->parseAgent( $user_agent );
            $out = [];
            if( $browser = $result->getBrowser() ) $out[] = $browser;
            if( $platform = $result->getPlatform() ) $out[] = $platform;
            if( $ip ) $out[] = $ip;
			return empty( $out ) ? "?" : implode( " - ", $out );
		}
	);

	$vars["new_email"] = array(
		"id" => "new_email",
		"title" => __( "New email", "material-dashboard" ),
		"desc" => __( "User new email, this is for messages like email change confirmation and it means what email user wants to change to", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
            return $args[0] ?? "";
		}
	);

	$vars["site_domain"] = array(
		"id" => "site_domain",
		"title" => __( "Site domain", "material-dashboard" ),
		"desc" => __( "This variable is often used for watermarks, for example you can add your site name below the messages, like: example.com", "material-dashboard" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_replace_url( "%domain%" );
		}
	);

	$vars["site_name"] = array(
		"id" => "site_name",
		"title" => __( "Site name", "material-dashboard" ),
		"desc" => __( "This variable is often used for watermarks, for example your site title is:", "material-dashboard" ) . "<br>" . get_bloginfo( "name" ),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return get_bloginfo( "name" );
		}
	);

	$vars["site_url"] = array(
		"id" => "site_url",
		"title" => __( "Site url", "material-dashboard" ),
		"desc" => __( "This is your site full URL address:", "material-dashboard" ) . "\n" . get_site_url(),
		"bbcode" => "Auto",
        "type" => "string",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return get_site_url();
		}
	);

	return $vars;

});

/**
 * Get user message template text
 * @since 1.0.8
 */
add_filter( "amd_user_message_template_text", function( $default, $msg_id ){

	/**
	 * Get all groups
	 * @since 1.2.1
	 */
	$groups = apply_filters( "amd_messages_templates_groups", [] );

	foreach( $groups as $group ){
		$templates = $group["list"] ?? [];
		if( !empty( $templates ) AND !empty( $templates[$msg_id] ) ){
			$template = $templates[$msg_id];
			return $template["template"] ?? "";
		}
	}

	return $default;

}, 10, 2 );