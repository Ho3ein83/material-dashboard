<?php

# Destroy request if WordPress is not loaded
defined( 'ABSPATH' ) OR die();

# Handle file upload
if( !empty( $_GET["_accept_upload"] ) ){

    # Custom upload ket
    $key = sanitize_text_field( $_GET["_accept_upload"] );

    # Handle upload process with action
    do_action( "amd_handle_file_upload_$key", amd_sanitize_files( $_FILES ) );

    # Destroy execution after request completed
	exit();

}

# Sanitized $_GET parameters
$get = amd_sanitize_get_fields( $_GET );

# Sanitized $_POST values
$post = amd_sanitize_post_fields( $_POST );

# Accept request with either $_GET and $_POST methods
$r = array_merge( $get, $post );

# Override active theme, you can use another installed theme to get assets from that theme
if( !empty( $r["_theme"] ) ){

    global /** @var AMDLoader $amdLoader */
	$amdLoader;

    $amdLoader->overrideTheme( $r["_theme"] );

}

# Handle other requests
do_action( "amd_api_init", $r );

# Init frontend strings and translation
do_action( "amd_init_translation" );

# Load assets
if( !empty( $r["stylesheets"] ) ){

    # Get comma separated stylesheets string
    $_styles = $r["stylesheets"];

    # Use minified or default stylesheet
    $minify = ( $r["minify"] ?? "false" ) == "true";

    # Convert comma separated string to array
    $styles = explode( ",", $_styles );

    # Default plugin CSS directory path
    $cssPath = AMD_PATH . '/assets/css';

    # Send CSS content-type header
    header( "Content-Type: text/css; charset=UTF-8" );

    # Prevent to access top directories with ../
    if( strpos( $_styles, ".." ) !== false )
        exit();

    # For each separated stylesheet
	foreach( $styles as $style ){

        # since 1.1.0
        if( has_action( "amd_custom_api_stylesheet_$style" ) ){
            do_action( "amd_custom_api_stylesheet_$style" );
            continue;
        }

        # Variables stylesheet
        if( $style == "_vars" ){

            global /** @var AMDCore $amdCore */
			$amdCore;

            # Get current theme variables, if you already used theme override that theme will be used
			$themes = $amdCore->getThemeColors();

            # Use fallback variables stylesheet if theme is not loaded
            if( empty( $themes ) ){

                # Use minified code if exists, otherwise use normal (not minified) stylesheet
                foreach( [AMD_ASSETS_PATH . "/css/vars" . ( $minify ? ".min.css" : ".css" ), AMD_ASSETS_PATH . "/css/vars.css"] as $v ){
                    if( file_exists( $v ) ){
	                    echo file_get_contents( $v );
						break;
                    }
                }

                # Skip loop
                continue;
            }

            # Get current theme mode (light or dark)
			$currentTheme = amd_get_current_theme_mode();

            # Print light and dark variables for used theme (active theme or overridden theme if exists)
			foreach( $themes as $theme => $data ){
				if( amd_starts_with( $theme, "light" ) OR amd_starts_with( $theme, "dark" ) ){

                    if( $theme == "light_default" )
						echo "body:not(.dark)";
					else if( $theme == "dark_default" )
						echo "body.dark";

                    echo "{";
                    if( !empty( $data ) and is_array( $data ) ){
                        foreach( $data as $key => $value )
		                    echo "$key:" . str_replace( ", ", ",", $value ) . ";\n";
                    }
                    echo "}";
				}
			}
		}

        # Dashboard stylesheet
        else if( $style == "_dashboard" ){

            global $amdLoader;

            # Get dashboard stylesheet CSS file URL if is set in theme info
	        $dashboard_style = $amdLoader->getThemeProperty( "dashboard_style", null, "" );

            # Active theme stylesheet
            if( file_exists( $dashboard_style ) )
                $f = $dashboard_style;
            # Default theme stylesheet (minified)
            else if( $minify AND file_exists( AMD_ASSETS_PATH . "/css/dashboard.min.css" ) )
                $f = AMD_ASSETS_PATH . "/css/dashboard.min.css";
            # Default theme stylesheet (not minified)
            else if( file_exists( AMD_ASSETS_PATH . "/css/dashboard.css" ) )
                $f = AMD_ASSETS_PATH . "/css/dashboard.css";
            # Skip dashboard stylesheet if not exists
            else
	            $f = null;
            # Get selected stylesheet
            if( file_exists( $f ) )
                echo file_get_contents( $f );
            # Send failed message comment
            else
	            printf( "\n/* Failed: Cannot load theme dashboard style (dashboard[.min].css) from %s path */\n\n", $f ?? "unknown" );

            # Custom stylesheet
            do_action( "amd_api_dashboard_stylesheet" );
        }

        # wp-admin stylesheet
        else if( $style == "_admin" ){

            global $amdLoader;

	        # Get admin stylesheet CSS file URL if is set in theme info
            $dashboard_style = $amdLoader->getThemeProperty( "admin_style", null, "" );

	        # Active theme stylesheet
            if( file_exists( $dashboard_style ) )
                $f = $dashboard_style;
            # Default theme stylesheet (minified)
            else if( $minify AND file_exists( AMD_ASSETS_PATH . "/css/admin.min.css" ) )
                $f = AMD_ASSETS_PATH . "/css/admin.min.css";
            # Default theme stylesheet (not minified)
            else if( file_exists( AMD_ASSETS_PATH . "/css/admin.css" ) )
                $f = AMD_ASSETS_PATH . "/css/admin.css";
            # Skip admin stylesheet if not exists
            else
	            $f = null;
	        # Get selected stylesheet
            if( file_exists( $f ) )
                echo file_get_contents( $f );
            # Send failed message comment
            else
	            printf( "\n/* Failed: Cannot load theme admin style (admin[.min].css) from %s path */\n\n", $f ?? "unknown" );
            do_action( "amd_api_admin_stylesheet" );
        }

        # Dashboard components
        else if( $style == "_components" ){

            global $amdLoader;

	        # Get components stylesheet CSS file URL if is set in theme info
            $dashboard_style = $amdLoader->getThemeProperty( "components_style", null, "" );

	        # Active theme stylesheet
            if( file_exists( $dashboard_style ) )
                $f = $dashboard_style;
            # Default theme stylesheet (minified)
            else if( $minify AND file_exists( AMD_ASSETS_PATH . "/css/components.min.css" ) )
                $f = AMD_ASSETS_PATH . "/css/components.min.css";
            # Default theme stylesheet (not minified)
            else if( file_exists( AMD_ASSETS_PATH . "/css/components.css" ) )
                $f = AMD_ASSETS_PATH . "/css/components.css";
            # Skip components stylesheet if not exists
            else
                $f = null;
	        # Get selected stylesheet
            if( $f AND file_exists( $f ) )
                echo file_get_contents( $f );
            # Send failed message comment
            else
                printf( "\n/* Failed: Cannot load theme components style (components[.min].css) from %s path */\n\n", $f ?? "unknown" );
            do_action( "amd_api_components_stylesheet" );
        }

        # Fonts
        else if( $style == "_fonts" ){

            # Get selected language or English as default
            $locale = $r["locale"] ?? "en_US";

            # Try older font loader and new loader as fallback
            foreach( [ "old_loader/fonts_$locale", "loader/fonts_$locale", "fonts" ] as $filename ){
				if( file_exists( "$cssPath/fonts/$filename.css.php" ) ){
					require_once( "$cssPath/fonts/$filename.css.php" );
                    break;
				}
			}
		}

        # Search for CSS stylesheet file
		else{

			if( $minify AND file_exists( "$cssPath/$style.min.css" ) ){
				echo file_get_contents( "$cssPath/$style.min.css" );
				echo "\n";
			}
			else if( file_exists( "$cssPath/$style.css" ) ){
				echo file_get_contents( "$cssPath/$style.css" );
				echo "\n";
			}

		}
	}
	exit();
}
else if( !empty( $r["scripts"] ) ){

	# Get comma separated scripts string
    $_scripts = $r["scripts"];

	# Use minified or default script
	$minify = ( $r["minify"] ?? "false" ) == "true";

	# Convert comma separated string to array
    $scripts = explode( ",", $r["scripts"] );

	# Default plugin JS directory path
    $jsPath = AMD_PATH . '/assets/js';

	# Send JavaScript content-type header
    header( "Content-Type: application/javascript; charset=UTF-8" );

	# Prevent to access top directories with ../
	if( strpos( $_scripts, ".." ) !== false )
		exit();

	# For each separated script
	foreach( $scripts as $script ){

        # since 1.1.0
        if( has_action( "amd_custom_api_script_$script" ) ){
            do_action( "amd_custom_api_script_$script", $minify, $r );
            continue;
        }

        # Main script (is )
		if( $script == "_main" ){
			echo "";
		}
		else if( $script == "_config" ){
			require_once( AMD_ASSETS_PATH . "/js/config.js.php" );
		}
		else{
			if( file_exists( "$jsPath/$script.min.js" ) ){
				echo file_get_contents( "$jsPath/$script.min.js" );
				echo "\n";
			}
			else if( file_exists( "$jsPath/$script.js" ) ){
				echo file_get_contents( "$jsPath/$script.js" );
				echo "\n";
			}
		}
	}
	exit();
}
else if( !empty( $r["_avatar"] ) ){

    # Requested avatar key
    $avatar = $r["_avatar"];

	if( amd_starts_with( $avatar, "http" ) ){
		wp_redirect( $avatar );
		exit();
	}

    # Get user by avatar key
    $user = amd_get_user_by_meta( "secret", $avatar );

    # Placeholder avatar image
	$ph_path = apply_filters( "amd_placeholder_avatar_path", AMD_ASSETS_PATH . "/images/avatars/placeholder.png" );

	$avatar_file = "";

	# Check if avatar key belongs to a user
    if( $user ){
        $avatars_path = amd_get_avatars_path();
	    # [AVATAR_IMAGE_FORMAT]
        $avatar_file = $user->secretKey . ".png";
    }
    else if( strpos( $avatar, ":" ) !== false ){
	    list( $path_id, $avatar_file ) = explode( ":", $avatar );

	    if( empty( $path_id ) )
		    $path_id = "amd_default";

        # Registered avatar presets
	    $avatars = apply_filters( "amd_get_avatars", [] );

	    $avatars_path = $avatars[$path_id] ?? "";
    }

	$avatar_path = "";

	if( !empty( $avatars_path ) AND file_exists( $avatars_path ) )
		$avatar_path = is_dir( $avatars_path ) ? "$avatars_path/$avatar_file" : $avatar_file;

    $extension = "png";

    if( $avatar == "placeholder" OR !file_exists( $avatar_path ) )
	    $avatar_path = $ph_path;

	$extension = pathinfo( $avatar_path, PATHINFO_EXTENSION );

    # Set mime-type for avatar image header
	$content_type = null;
    switch( $extension ){
        case "png":
            $content_type = "image/png";
            break;
        case "jpg":
        case "jpeg":
            $content_type = "image/jpeg";
            break;
        case "svg":
            $content_type = "image/svg+xml";
            break;
    }

    # If avatar image exists
	if( file_exists( $avatar_path ) AND $avatar !== "_EMPTY_" ){

        # Set avatar cache expiration (86400 seconds / 1 day)
        $cache_expire = apply_filters( "amd_assets_cache_expire", 86400 );
		header( "Pragma: public" );
		header( "Cache-Control: max-age=$cache_expire" );
		header( "Expires: " . gmdate( "D, d M Y H:i:s \G\M\T", time() + $cache_expire ) );
		header( "Content-Type: $content_type" );
		echo file_get_contents( $avatar_path );
	}
	else{
        # Send fallback avatar image
		header( "Content-Type: image/svg+xml" );
		header("Content-Disposition: inline; filename=empty.svg");
		echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1" style="width:512px;height:512px"><rect x="0" y="0" width="10" height="10" style="fill:#201573"/></svg>';
	}

	exit();

}

global /** @var AMDFirewall $amdWall */
$amdWall;

# Use registered API handlers
$amdWall->handleAPI();
