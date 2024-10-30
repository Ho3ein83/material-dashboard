<?php

/** @var AMDWarner $amdWarn */
$amdWarn = null;

class AMDWarner{

	/**
	 * Configuration array
	 * @var array
	 * @since 1.0.0
	 */
	protected $config;

	/**
	 * Errors decoded data
	 * @var object
	 * @since 1.0.0
	 */
	protected $errors;

	/**
	 * Changelogs decoded data
	 * @var object
	 * @since 1.0.0
	 */
	protected $changelogs;

	/**
	 * Warnings, alerts and messages handler
	 */
	public function __construct(){

		# Initialize configuration
		$this->config = array(
			"changelog_file" => AMD_PATH . "/changelog.json",
			"error_codes_file" => AMD_PATH . "/error_codes.json"
		);

		# Initialize
		self::init();

	}

	/**
	 * Initialize
	 * @return void
	 * @since 1.0.0
	 */
	public function init(){

		# Load errors code
		self::bufferErrors();

		# Load changelog
		self::bufferChangelog();

	}

	/**
	 * Store errors code into <code>$errors</code> variable
	 * @return void
	 * @since 1.0.0
	 */
	public function bufferErrors(){

		if( file_exists( $this->config["error_codes_file"] ) )
			$this->errors = (array) json_decode( file_get_contents( $this->config["error_codes_file"] ) );

	}

	/**
	 * Store changelogs into <code>$changelogs</code> variable
	 * @return void
	 * @since 1.0.0
	 */
	public function bufferChangelog(){

		if( file_exists( $this->config["changelog_file"] ) )
			$this->changelogs = json_decode( file_get_contents( $this->config["changelog_file"] ) );

	}

	/**
     * Get errors
	 * @return object
	 * @since 1.0.0
	 */
	public function getErrors(){
        return (object) $this->errors;
    }

	/**
	 * Get error code from <code>error_codes.json</code> file
	 *
	 * @param $id
	 * Error ID
	 * @param $format
	 * The format of returned value, e.g: "Error %s"
     * <br>If you use <code>%s</code> in your string it will use <code>sprintf</code> function
     * for replacement that means you can only use one %s in <code>$format</code>
     * if you want to replace multiple parts use '%e' instead, e.g: <code>"Error: %e - search for error code %e"</code>
	 *
	 * @return string
     * @since 1.0.0
	 */
	public function getErrorCode( $id, $format = "" ){

		foreach( self::getErrors() as $code => $data ){
			$_id = $data->id ?? "";
			if( $id == $_id ){
				if( strpos( $format, "%s" ) !== false )
					return sprintf( $format, $code );
                else if( strpos( $format, "%e" ) !== false )
					return str_replace( "%e", $code, $format );

				return $code;
			}
		}

		return "";

    }

	/**
	 * Get 900 error code alert box data
	 * @return array
	 * @since 1.0.0
	 */
	public function handle_900(){

		return array(
			"text" => sprintf( esc_html_x( "Registration is not allowed on this site, if you want to let users to sign-up please enable 'Anyone can register' option in %ssettings%s", "Admin", "material-dashboard" ), '<a href="' . admin_url( "options-general.php" ) . '">', "</a>" ),
			"type" => "primary",
			"icon" => "person",
			"size" => "lg",
			"closable" => false
		);

	}

	/**
	 * Show HTML alert box
	 *
	 * @param array $data
     * Data array, some accepted properties:
     * <br><ul>
     * <li>`id`: Box ID</li>
     * <li>`text`: Box text</li>
     * <li>`type`: Box type, e.g: "info", "success", "error"</li>
     * <li>`size`: Box size, e.g: "sm", "md", "lg"</li>
     * <li>`icon`: Box icon ID, e.g: "star"</li>
     * <li>`align`: Text align, e.g: "left", "right", "auto"</li>
     * <li>`is_front`: Whether the box is in dashboard page or not, if you are using it in dashboard pages set it to true</li>
     * <li>`closable`: Whether the box is closable or not</li>
     * </ul>
	 *
	 * @return void
     * @since 1.0.0
	 */
	public function box( $data ){

		$box = $data;

		if( is_numeric( $data ) ){

			$code = $data;

			if( empty( $this->errors[$code] ) )
				return;

			if( method_exists( $this, "handle_$code" ) )
				$box = call_user_func( [ $this, "handle_$code" ] );

		}

		if( empty( $box ) )
			return;

		$id = $box["id"] ?? amd_generate_string_pattern( "[all:6]" );
		$text = $box["text"] ?? "";
		$type = $box["type"] ?? "info";
        if( amd_starts_with( $type, "color:" ) ){
            $type = str_replace( ["red", "green", "blue", "orange", "default"], ["error", "success", "info", "warning", "primary"], str_replace( "color:", "", $type ) );
        }
		$size = $box["size"] ?? "auto";
		$icon = $box["icon"] ?? "";
		$align = $box["align"] ?? "auto";
		$is_front = $box["is_front"] ?? false;
		$closable = $box["closable"] ?? false;

        $class = $is_front ? "amd-alert" : "amd-admin-alert";

		?>
        <div class="<?php echo esc_attr( $class ); ?> <?php echo esc_attr( "$type size-$size align-$align" ); ?>" id="<?php echo esc_attr( "amd-box-$id" ); ?>">
			<?php echo amd_icon( $icon ); ?>
            <div class="--content"><p><?php echo wp_kses( $text, amd_allowed_tags_with_attr( "br,span,a,p,button" ) ); ?></p></div>
			<?php if( $closable ): ?>
                <span class="--close" data-close-box="<?php echo esc_attr( $id ); ?>"><?php _amd_icon( "close" ); ?></span>
			<?php endif; ?>
        </div>
		<?php

	}

	/**
     * Send email to specific user(s) using WordPress `wp_mail` function
	 * @param string|array $to
     * Email receptors
	 * @param string $subject
     * Email subject
	 * @param string $message
     * Email message
	 * @param string $headers
     * Headers
	 * @param array $attachments
     * Attachments
	 *
	 * @return bool
     * Whether the email was sent successfully.
	 * @since 1.0.0
	 */
	public function sendEmail( $to, $subject, $message, $headers="", $attachments=[] ){

        if( is_numeric( $to ) ){
            $user = amd_get_user( $to );
            if( !$user ) return false;
            $to = $user->email;
        }

		$head = apply_filters( "amd_email_head", $to, $subject, $message );
		$message = apply_filters( "amd_email_content", $to, $subject, $message );
		$foot = apply_filters( "amd_email_foot", $to, $subject, $message );

        /* If you are using this plugin on localhost you can enable email-debug and see outgoing emails
         * in an HTML file named email.html and placed in WordPress installation directory.
         * Enable it using this code in your functions.php file:
         * add_filter( "amd_email_debug", "__return_true" );
         */
        if( amd_is_localhost() AND apply_filters( "amd_email_debug", false )  )
            file_put_contents( ABSPATH . "/email.html", $head . $message . $foot );

        wp_mail( $to, $subject, $head . $message . $foot, $headers, $attachments );

        return true;

    }

	public function getChangelogs(){

        return (array) $this->changelogs;

    }

	/**
	 * Send message to
	 *
	 * @param array $data
	 * Message data array, these are traditional options:
     * <ul>
     * <li><code>email [STRING]</code> (only required for sending emails) <br><b>Target user email</b></li>
     * <li><code>subject [STRING]</code> (only required for sending emails) <br><b>Message title</b></li>
     * <li><code>message [STRING]</code> (required) <br><b>Message text</b></li>
     * <li><code>phone [STRING]</code> (only required for sending SMS) <br><b>Target user phone number</b></li>
     * <li><code>emailBreakLine [BOOL]</code> (optional, only available in emails) <br><b>Whether to replace break lines (\n) with HTML `br` tag</b></li>
     * <li><code>user [AMDUser] `since 1.2.0`</code> (optional) <br><b>you can pass user object to retrieve user phone and email automatically</b></li>
     * </ul>
	 * @param string $methods
	 * Message sending method(s) (e.g: "email", "sms", "email,sms")
	 * @param bool|int $schedule
     * Pass a number to schedule message to send it, or pass false to send it immediately.
     * <br>The number is the number of seconds that you want to wait until sending message, e.g: 3600 equals to 1 hour
     * <br>If you pass true, it will send the message as soon as possible after someone checked-in
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function sendMessage( $data, $methods="email,sms", $schedule=false ){

        if( !empty( $data["user"] ) ){
            $u = $data["user"];
            if( $u instanceof AMDUser ){
                $data["email"] = $u->email;
                if( !empty( $u->phone ) )
                    $data["phone"] = $u->phone;
            }
        }

        if( $schedule === true ){

            /**
	         * Schedule time for messages
             * @since 1.0.8
	         */
	        $schedule = apply_filters( "amd_message_schedule_time", 0 );

        }

        # Schedule messenger task to send message in background
        if( $schedule !== false AND is_int( $schedule ) ){
            $schedule_data = array(
                "action" => "send_message",
                "data" => $data,
                "args" => [$methods]
            );
	        return amd_add_task( null, null, esc_html_x( "Send email and/or SMS to user", "Task title", "material-dashboard" ), $schedule_data );
        }

        if( strpos( $methods, "," ) !== false ){
            $success = false;
            foreach( explode( ",", $methods ) as $m ){
                if( self::sendMessage( $data, $m ) )
                    $success = true;
            }
            return $success;
        }

        if( $methods == "email" ){

            $to = $data["email"] ?? ( $data["to"] ?? "" );
            $subject = $data["subject"] ?? null;
            $message = $data["message"] ?? null;

            if( !$to OR !$subject OR !$message )
                return false;

            $bl = $data["emailBreakLine"] ?? false;

            if( $bl )
                $message = str_replace( "\n", "<br>", $message );

            $headers = $data["headers"] ?? "";
            $attachments = $data["attachments"] ?? [];

            return self::sendEmail( $to, $subject, $message, $headers, $attachments );

        }
        else if( $methods == "sms" ){
	        /**
	         * Handle SMS method
	         * @since 1.0.6
	         */
            $result = apply_filters( "amd_send_sms", $data );

            return $result === true;

        }

		/**
		 * Handle other methods
         * @since 1.0.5
		 */
        $success = apply_filters( "amd_send_message_with_method", $data, $methods );

        if( $success === true )
            return true;

        return false;

	}

}