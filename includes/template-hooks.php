<?php

/**
 * Message template scopes
 * @since 1.0.8
 */
add_filter( "amd_messages_templates_scopes", function( $scopes ){

	$scopes["email"] = array(
		"id" => "email",
		"title" => esc_html__( "Email", "material-dashboard" ),
		"desc" => esc_html_x( "Alert users with email", "Message scope", "material-dashboard" )
	);

	$scopes["sms"] = array(
		"id" => "sms",
		"title" => esc_html__( "SMS", "material-dashboard" ),
		"desc" => esc_html_x( "Alert users with SMS if they have registered phone number", "Message scope", "material-dashboard" )
	);
	
	return $scopes;

} );

/**
 * Get all groups
 * @since 1.0.8
 */
add_filter( "amd_messages_templates_groups", function( $groups ){

	$groups["users"] = array(
		"title" => esc_html__( "User messages", "material-dashboard" ),
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
		"title" => esc_html__( "Welcome", "material-dashboard" ),
		"template" => sprintf( esc_html__( "Hello dear %s! Thanks for subscribing to our site, we hope you enjoy using it.", "material-dashboard" ), "%FIRSTNAME%" ),
		"scopes" => ["email", "sms"]
	);

	$list["password_reset_verification"] = array(
		"title" => esc_html__( "Password reset verification code", "material-dashboard" ),
		"template" => sprintf( esc_html__( "Dear %s, your verification code for password reset is: %s", "material-dashboard" ), "%FIRSTNAME%", "%CODE%" ),
		"scopes" => ["email", "sms"],
		"dependencies" => ["code"],
	);

	$list["password_changed"] = array(
		"title" => esc_html__( "Password changed", "material-dashboard" ),
		"scopes" => ["email", "sms"],
		"template" => sprintf( esc_html__( "Dear %s, your password has been changed at %s.\nPlease let us know if you didn't change it.", "material-dashboard" ), "%FIRSTNAME%", "%DATE%" )
	);

	$list["change_email_confirm"] = array(
		"title" => esc_html__( "Change email confirmation", "material-dashboard" ),
		"scopes" => ["email"],
		"template" => sprintf( esc_html__( "Dear %s, you have requested for changing your email, if you want to change it to %s please click on the below link. %s", "material-dashboard" ), "%FIRSTNAME%", "%NEW_EMAIL%", "%URL%" ),
		"dependencies" => ["new_email", "url"],
	);

	$list["email_changed"] = array(
		"title" => esc_html__( "Email changed", "material-dashboard" ),
		"scopes" => ["email"],
		"template" => sprintf( esc_html__( "Dear %s, your email address has been changed successfully", "material-dashboard" ), "%FIRSTNAME%" ),
		"dependencies" => ["new_email"],
	);

	$list["2fa_code"] = array(
		"title" => esc_html__( "Two factor authentication code", "material-dashboard" ),
		"scopes" => ["email", "sms"],
		"template" => sprintf( esc_html__( "Dear %s, your 2FA code is: %s", "material-dashboard" ), '%FIRSTNAME%', "\n%CODE%" ),
		"dependencies" => ["code"],
	);

	return $list;

} );

/**
 * User message template variables
 * @since 1.0.8
 */
add_filter( "amd_user_message_template_variables", function( $vars ){

	// TODO: add shortcode for [amd-msg-template="XYZ"]

	$vars["firstname"] = array(
		"id" => "firstname",
		"title" => esc_html__( "Firstname", "material-dashboard" ),
		"desc" => esc_html__( "The name of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto", # %FIRSTNAME%
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
		"title" => esc_html__( "Lastname", "material-dashboard" ),
		"desc" => esc_html__( "The name of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto", # %LASTNAME%
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
		"title" => esc_html__( "Fullname", "material-dashboard" ),
		"desc" => esc_html__( "The name of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto", # ...
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
		"title" => esc_html__( "Email", "material-dashboard" ),
		"desc" => esc_html__( "The email of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
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
		"title" => esc_html__( "Username", "material-dashboard" ),
		"desc" => esc_html__( "The username of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
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
		"title" => esc_html__( "Phone", "material-dashboard" ),
		"desc" => esc_html__( "The phone number of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
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
		"title" => esc_html__( "National code", "material-dashboard" ),
		"desc" => esc_html__( "The nationcal code of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
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
		"title" => esc_html__( "User ID", "material-dashboard" ),
		"desc" => esc_html__( "The ID of related user to this message", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user ){
			/** @var AMDUser $user */
			if( $user )
				return $user->ID;
			return "";
		}
	);

	$vars["date"] = array(
		"id" => "date",
		"title" => esc_html__( "Date and time", "material-dashboard" ),
		"desc" => esc_html__( "Current date and time based on current user region like: 2023/01/01 12:00", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_true_date( "Y/m/d H:i" );
		}
	);

	$vars["single_date"] = array(
		"id" => "single_date",
		"title" => esc_html__( "Date", "material-dashboard" ),
		"desc" => esc_html__( "Current single date based on current user region like: 2023/01/01", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_true_date( "Y/m/d" );
		}
	);

	$vars["time"] = array(
		"id" => "time",
		"title" => esc_html__( "Time", "material-dashboard" ),
		"desc" => esc_html__( "Current time based on current user region like: 12:00", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_true_date( "H:i" );
		}
	);

	$vars["url"] = array(
		"id" => "url",
		"title" => esc_html__( "URL", "material-dashboard" ),
		"desc" => esc_html__( "Specified URL, for example if you are editing email confirmation message this URL is email confirmation URL", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
			$url = $args[1] ?? "-";
			return $url;
		}
	);

	$vars["code"] = array(
		"id" => "code",
		"title" => esc_html__( "Verification code", "material-dashboard" ),
		"desc" => esc_html__( "Specified verification code, for example if you are editing 2 factor authentication message this code is 2FA code", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
			$code = $args[0] ?? null;
			return $code ?: "-";
		}
	);

	$vars["new_email"] = array(
		"id" => "new_email",
		"title" => esc_html__( "New email", "material-dashboard" ),
		"desc" => esc_html__( "User new email, this is for messages like email change confirmation and it means what email user wants to change to", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,?",
		"callback" => function( $user, $args ){
			$new_email = $args[0] ?? "";
			return $new_email;
		}
	);

	$vars["site_domain"] = array(
		"id" => "site_domain",
		"title" => esc_html__( "Site domain", "material-dashboard" ),
		"desc" => esc_html__( "This variable is often used for watermarks, for example you can add your site name below the messages, like: example.com", "material-dashboard" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return amd_replace_url( "%domain%" );
		}
	);

	$vars["site_name"] = array(
		"id" => "site_name",
		"title" => esc_html__( "Site name", "material-dashboard" ),
		"desc" => esc_html__( "This variable is often used for watermarks, for example your site title is:", "material-dashboard" ) . "<br>" . get_bloginfo( "name" ),
		"bbcode" => "Auto",
		"independent" => "users,*",
		"callback" => function( $user, $args ){
			return get_bloginfo( "name" );
		}
	);

	$vars["site_url"] = array(
		"id" => "site_url",
		"title" => esc_html__( "Site url", "material-dashboard" ),
		"desc" => esc_html__( "This is your site full URL address:", "material-dashboard" ) . "<br>" . get_site_url(),
		"bbcode" => "Auto",
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
			$template = $templates[$msg_id] ?? [];
			return $template["template"] ?? "";
		}
	}

	/*if( $msg_id == "password_reset_verification" )
		return sprintf( esc_html__( "Dear %s, your verification code for password reset is: %s", "material-dashboard" ), "%FIRSTNAME%", "%CODE%" );*/

	/**
	 * User messages templates
	 * @since 1.0.8
	 */
	/*$templates = apply_filters( "amd_user_message_templates", [] );

	$template = $templates[$msg_id] ?? null;

	if( $template AND !empty( $template["template"] ) )
		return $template["template"];*/

	return $default;

}, 10, 2 );

/**
 * Preview
 * @since 1.2.1
 */
add_filter( "_adp_text_template_password_reset_verification_preview_args", function(){
	return ["1234"];
} );

/**
 * Preview
 * @since 1.2.1
 */
add_filter( "_adp_text_template_change_email_confirm_preview_args", function(){
	return [is_user_logged_in() ? amd_get_current_user()->email : "you@email.co", get_site_url()];
} );

/**
 * Preview
 * @since 1.2.1
 */
add_filter( "_adp_text_template_email_changed_preview_args", function(){
	return [is_user_logged_in() ? amd_get_current_user()->email : "you@email.co"];
} );