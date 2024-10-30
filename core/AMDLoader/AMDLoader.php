<?php

/** @var AMDLoader $amdLoader */
$amdLoader = null;

class AMDLoader{

	/**
	 * Existing extensions list
	 * @var array
	 * @since 1.0.0
	 */
	protected $extensions;

	/**
	 * Existing themes list
	 * @var array
	 * @since 1.0.0
	 */
	protected $themes;

	/**
	 * Default extensions
	 * @var array
	 * @since 1.0.0
	 */
	protected $defaultExtensions;

    /**
	 * Fallback theme to use in case of theme errors
	 * @var string
	 * @since 1.2.1
	 */
	protected $fallbackTheme;

	/**
	 * Default enabled extensions
	 * @var array
	 * @since 1.0.0
	 */
	protected $defaultEnabledExtensions;

	/**
	 * If theme can be loaded
	 * @var bool
	 * @since 1.0.0
	 */
	protected $canLoadTheme;

	/**
	 * Use another theme instead of active theme.
	 * @var string|null
	 */
	protected $override_theme;

	/**
	 * Default theme (will be used if active theme cannot be loaded)
	 * @var string
	 */
	const DEFAULT_THEME = "amatris";

	/**
	 * Manage your extensions and themes
	 */
	public function __construct(){

		# Registered extensions
		$this->extensions = [];

		# Loaded themes
		$this->themes = [];

		# Default and private extensions
		$this->defaultExtensions = [ "_app", "_api" ];

		# Default enabled extensions
		$this->defaultEnabledExtensions = [ "todo" ];

		# If theme is running just fine
		$this->canLoadTheme = false;

		# Use another theme instead of enabled theme
		$this->override_theme = null;

        # Set fallback theme
        $this->fallbackTheme = "amatris";

		# Initialize
		self::init();

	}

	/**
	 * Initialize and load default extensions and themes
	 * @return void
	 * @since 1.0.0
	 */
	public function init(){

		global /** @var AMDExplorer $amdExp */
		$amdExp;

		if( empty( $amdExp ) )
			return;

		do_action( "amd_before_register_themes", $this );

		self::registerExtensions( AMD_EXT );

		self::registerThemes( AMD_THEMES );

		self::loadExtensions();

		$this->canLoadTheme = self::loadActiveTheme();

		add_action( "amd_begin_dashboard", function(){

			global /** @var AMDLoader $amdLoader */
			$amdLoader;

			if( !$amdLoader->canLoadTheme() )
				amd_assert_error( "cannot_load_theme", sprintf( esc_html__( "There is no available theme for current page, your theme files may be removed or changed. Please check your theme in %ssettings%s to see what's wrong", "material-dashboard" ), "<a href=\"" . admin_url( "admin.php?page=amd-themes" ) . "\" target=\"_blank\">", "</a>" ) );

			do_action( "amd_init_sidebar_items" );

		} );

	}

	/**
	 * Register all extensions in a directory
	 *
	 * @param string $dir
	 * Extensions directory path
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function registerExtensions( $dir ){

		global $amdExp;

		if( $amdExp->sys->exists( $dir ) ){

			foreach( $amdExp->sys->dirlist( $dir, false ) as $ext_name => $ext_data ){
				$type = $ext_data["type"] ?? "";
				if( $type != "d" )
					continue;
				$ext = self::scanExtension( $dir . "/$ext_name" );

				$allowed = apply_filters( "amd_extension_index", $ext );
				if( !$allowed )
					continue;

				if( empty( $ext ) )
					continue;
				$this->extensions[$ext_name] = $ext;
				$this->extensions[$ext_name]["private"] = amd_starts_with( $ext_name, "_" );
			}

		}

	}

	/**
	 * Register all themes in a directory
	 *
	 * @param string $dir
	 * Themes directory path
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function registerThemes( $dir ){

		global /** @var AMDExplorer $amdExp */
		$amdExp;

		if( $amdExp->sys->exists( $dir ) ){

			foreach( $amdExp->sys->dirlist( $dir, false ) as $theme_name => $theme_data ){
				$type = $theme_data["type"] ?? "";
				if( $type != "d" )
					continue;
				$theme = self::scanTheme( $dir . "/$theme_name" );

				$allowed = apply_filters( "amd_theme_index", $theme );
				if( !$allowed )
					continue;

				if( empty( $theme ) )
					continue;
				$this->themes[$theme_name] = $theme;
			}

		}

	}

	/**
	 * Check if extension is active
	 *
	 * @param string $extension
	 * Extension ID (same as extension directory name)
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function isExtensionActive( $extension ){

		if( in_array( $extension, $this->defaultExtensions ) )
			return true;

		$extensions = amd_get_site_option( "extensions" );
		$exp = explode( ",", $extensions );

		return in_array( $extension, $exp );

	}

	/**
	 * Check if theme is activated
	 *
	 * @param string $theme
	 * Theme ID (same as theme directory name)
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function isThemeActive( $theme ){

		return self::getActiveTheme() == $theme;

	}

	/**
	 * Get active theme from database
	 *
	 * @param bool $ignoreOverride
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getActiveTheme( $ignoreOverride = false ){

		if( !$ignoreOverride AND !empty( $this->override_theme ) AND self::isThemeRegistered( $this->override_theme ) )
			return $this->override_theme;

		return amd_get_site_option( "theme" );

	}

	/**
	 * Get current selected theme
	 *
	 * @param bool $ignoreOverride
	 * Whether to return current active theme or overridden theme (if there is any)
	 *
	 * @return false|mixed
	 * @since 1.0.0
	 */
	public function getCurrentTheme( $ignoreOverride = false ){

		$theme = self::getActiveTheme( $ignoreOverride );

		return $this->themes[$theme] ?? $this->fallbackTheme;

	}

	/**
	 * Get current theme or another registered theme data
	 *
	 * @param null|string $theme
	 * Theme ID, or pass null to get current theme
	 *
	 * @return false|mixed
	 * @since 1.0.0
	 */
	public function getTheme( $theme = null ){

        if( $theme === null )
			return self::getCurrentTheme();

		return $this->themes[$theme] ?? false;

	}

	/**
	 * Get theme property from its configuration file
	 *
	 * @param string $property
	 * Property name
	 * @param string $theme
	 * Theme ID
	 * @param mixed $default
	 * Default value (returns if property doesn't exist)
	 * @param bool $fromJsonData
	 * Whether to look in theme `json` data that generated by theme scanner
	 *
	 * @return mixed|null
	 * @see AMDLoader::scanTheme()
	 * @since 1.0.0
	 */
	public function getThemeProperty( $property, $theme = null, $default = null, $fromJsonData = false ){

		$_theme = self::getTheme( $theme );

		if( $fromJsonData ){
			if( !$_theme OR !isset( $_theme["json"]->{$property} ) )
				return $default;

			return $_theme["json"]->{$property};
		}

		if( !$_theme )
			return $default;


		return $_theme["properties"]->{$property} ?? ( $_theme[$property] ?? $default );

	}

	/**
	 * Get extension info
	 *
	 * @param string $path
	 * Extension directory path
	 *
	 * @return array|null
	 * Extension data on success, null on failure
	 * @since 1.0.0
	 */
	public function scanExtension( $path ){

		if( !is_dir( $path ) )
			return null;

		$dir = pathinfo( $path, PATHINFO_BASENAME );

		$_json = "$path/index.json";
		$_php = "$path/index.php";
		$_functions = "$path/functions.php";

		$json = file_exists( $_json ) ? json_decode( file_get_contents( $_json ) ) : null;
		$index_php = file_exists( $_php ) ? $_php : null;
		$functions_php = file_exists( $_functions ) ? $_functions : null;

		$name = $json->name ?? "";
		$desc = $json->description ?? "";
		$ver = $json->version ?? "";
		$url = $json->url ?? "";
        $since = $json->since ?? null;
        $compatible = $json->compatible ?? null;
        $compatible_premium = $json->compatible_premium ?? null;
		$translateContext = $json->context ?? $dir;
		$deprecated = $json->deprecated ?? false;
		$requirements = $json->requirements ?? "";
		$admin_url = $json->admin_url ?? null;
        $admin_url_label = $json->admin_url_label ?? null;
		$text_domain = $json->text_domain ?? "material-dashboard";
		$extURL = plugins_url( $dir, $path );
		$url = amd_replace_constants( $url );
		$thumb = $json->thumbnail ?? "";
		$thumb = amd_replace_constants( $thumb );
		$thumb = preg_replace( "/\{EXT_PATH}/", $dir, $thumb );;
		$thumb = preg_replace( "/\{EXT_URL}/", $extURL, $thumb );;
		$author = $json->author ?? "";
		$isPremium = $json->is_premium ?? false;
		$isActive = self::isExtensionActive( $dir );

		$req = $requirements;
		if( in_array( $dir, $this->defaultExtensions ) )
			$req = [];

		if( is_object( $req ) )
			$req = (array) $req;

		return array(
			"directory" => $dir,
			"path" => $path, # since 1.2.0
			"url" => $extURL, # since 1.2.0
			"since" => $since, # since 1.2.0
			"compatible" => $compatible, # since 1.2.0
			"compatible_premium" => $compatible_premium, # since 1.2.0
			"admin_url" => $admin_url, # since 1.2.0
            "admin_url_label" => $admin_url_label, # since 1.2.0
			"info" => array(
				"name" => esc_html_x( $name, $translateContext, "$text_domain" ),
				"description" => esc_html_x( $desc, $translateContext, "$text_domain" ),
				"version" => $ver,
				"url" => $url,
				"thumbnail" => $thumb,
				"requirements" => $req,
				"text_domain" => $text_domain,
				"author" => esc_html_x( $author, $translateContext, "$text_domain" )
			),
			"json" => $json,
			"index" => $index_php,
			"is_premium" => $isPremium,
			"functions" => $functions_php,
			"is_usable" => function() use ( $req ){
				return amd_check_requirements( $req );
			},
			"deprecated" => $deprecated,
			"is_active" => $isActive
		);

	}

	/**
	 * Get theme info
	 *
	 * @param string $path
	 * Theme directory path
	 *
	 * @return array|null
	 * Theme data on success, null on failure
	 * @since 1.0.0
	 */
	public function scanTheme( $path ){

		if( !is_dir( $path ) )
			return null;

		$dir = pathinfo( $path, PATHINFO_BASENAME );

		$_json = "$path/index.json";
		$_php = "$path/index.php";
		$_functions = "$path/functions.php";

		$json = file_exists( $_json ) ? json_decode( file_get_contents( $_json ) ) : null;
		$index_php = file_exists( $_php ) ? $_php : null;
		$functions_php = file_exists( $_functions ) ? $_functions : null;

		$name = $json->name ?? "";
		$desc = $json->description ?? "";
		$ver = $json->version ?? "";
		$url = $json->url ?? "";
        $since = $json->since ?? null;
		$translateContext = $json->context ?? $dir;
		$translateDomain = $json->text_domain ?? "material-dashboard";
		$deprecated = $json->deprecated ?? false;
		$requirements = $json->requirements ?? "";
        $admin_url = $json->admin_url ?? null;
        $admin_url_label = $json->admin_url_label ?? null;
		$url = amd_replace_constants( $url );
		$themeURL = plugins_url( $dir, $path );
		$thumb = $json->thumbnail ?? "";
		$thumb = amd_replace_constants( $thumb );
		$thumb = preg_replace( "/\{THEME_PATH}/", $path, $thumb );;
		$thumb = preg_replace( "/\{THEME_URL}/", $themeURL, $thumb );;
		$thumb = preg_replace( "/\{LOCALE}/", get_locale(), $thumb );;
		$author = $json->author ?? "";
		$properties = $json->properties ?? (object) [];
		$isPremium = $json->is_premium ?? false;

		$dashboard_style = $json->dashboard_style ?? "";
		$dashboard_style = preg_replace( "/\{THEME_PATH}/", $path, $dashboard_style );;
		$components_style = $json->components_style ?? "";
		$components_style = preg_replace( "/\{THEME_PATH}/", $path, $components_style );;

		$isActive = self::isThemeActive( $dir );

		if( is_object( $requirements ) )
			$requirements = (array) $requirements;

        return array(
            "path" => $path,
            "id" => $dir,
            "directory" => $dir,
            "url" => $themeURL, # since 1.2.0
            "since" => $since, # since 1.2.0
            "admin_url" => $admin_url, # since 1.2.0
            "admin_url_label" => $admin_url_label, # since 1.2.0
            "info" => array(
                "name" => esc_html_x( $name, $translateContext, $translateDomain ),
                "description" => esc_html_x( $desc, $translateContext, $translateDomain ),
                "version" => $ver,
                "url" => $url,
                "thumbnail" => $thumb,
                "requirements" => $requirements,
                "author" => esc_html_x( $author, $translateContext, $translateDomain )
            ),
            "json" => $json,
            "dashboard_style" => $dashboard_style,
            "components_style" => $components_style,
            "theme_url" => $themeURL,
            "properties" => $properties,
            "index" => $index_php,
            "is_premium" => $isPremium,
            "functions" => $functions_php,
            "is_usable" => function() use ( $requirements ){
                return amd_check_requirements( $requirements );
            },
            "deprecated" => $deprecated,
            "is_active" => $isActive
        );

	}

	/**
	 * Run loaded extensions
	 * @return void
	 * @since 1.0.0
	 */
	protected function loadExtensions(){

		do_action( "amd_before_load_extensions" );

		foreach( self::getExtensions() as $dir => $ext ){
			$_index = $ext["index"] ?? "";
			$_functions = $ext["functions"] ?? "";

            $compatible = $ext["compatible"] ?? null;
            $compatible_premium = $ext["compatible_premium"] ?? null;
            $is_compatible = true;
            $is_premium_compatible = true;

			$usable = false;
			if( !empty( $ext["is_usable"] ) AND is_callable( $ext["is_usable"] ) )
				$usable = call_user_func( $ext["is_usable"] );
			$active = $ext["is_active"] ?? false;
            if( $active ){
                if( !empty( $compatible ) ){
                    $v = trim( preg_replace( "/[<=>]/", "", $compatible ) );
                    $e = trim( str_replace( $v, "", $compatible ) );
                    $e = empty( $e ) ? ">=" : $e;
                    if( !version_compare( AMD_VER, $v, $e ) ) {
                        $usable = false;
                        $is_compatible = false;
                    }
                }
                if( !empty( $compatible_premium ) ){
                    if( function_exists( "adp_plugin" ) ) {
                        $v = trim( preg_replace( "/[<=>]/", "", $compatible_premium ) );
                        $e = trim( str_replace( $v, "", $compatible_premium ) );
                        $e = empty( $e ) ? ">=" : $e;
                        if( !version_compare( adp_plugin()["Version"] ?? "", $v, $e ) ) {
                            $usable = false;
                            $is_premium_compatible = false;
                        }
                    }
                }
            }
			if( !$usable OR !$active )
				continue;
			$path = AMD_EXT . "/$dir";
			$index = "$path/$_index";
			$functions = "$path/$_functions";

			do_action( "amd_before_extension_load", $ext );
			do_action( "amd_before_extension_{$dir}_load", $path );

			if( file_exists( $_functions ) )
				require_once( $_functions );
			if( file_exists( $_index ) )
				require_once( $_index );

			do_action( "amd_after_extension_load", $ext );
			do_action( "amd_after_extension_{$dir}_load", $path );

		}

		do_action( "amd_after_load_extensions" );

	}

	/**
	 * Load current enabled theme
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	protected function loadActiveTheme(){

        /**
         * @since 1.1.2
         */
        do_action( "amd_before_load_theme", $this );

		$theme = self::getCurrentTheme();
		$id = $theme["id"] ?? "";

		# time() is only used for make 'index.php' not existing and print an error if theme path is not defined
		$path = $theme["path"] ?? time();

		do_action( "amd_before_theme_{$id}_load", $path );

		if( !file_exists( "$path/index.php" ) )
			return false;

		if( file_exists( "$path/functions.php" ) )
			require_once( "$path/functions.php" );

		require_once( "$path/index.php" );

		do_action( "amd_after_theme_{$id}_load", $path );

		return true;

	}

	/**
	 * Get extensions array data
	 * @return array
	 * @since 1.0.0
	 */
	public function getExtensions(){

		return $this->extensions;

	}

	/**
	 * Get themes array data
	 * @return array
	 * @since 1.0.0
	 */
	public function getThemes(){

		return $this->themes;

	}

	/**
	 * Get extension URL
	 *
	 * @param string $ext
	 * Extension ID (same as extension directory name)
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getExtensionURL( $ext ){

		return AMD_EXT_URL . "/$ext";

	}

	/**
	 * Get theme URL
	 *
	 * @param string $ext
	 * Theme ID (same as theme directory name)
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getThemeURL( $theme ){

		return AMD_THEMES_URL . "/$theme";

	}

	/**
	 * Enable extension
	 * <br><b>Note: if target extension doesn't exist, it will add it to database anyway! However, it doesn't affect your site performance and will be ignored</b>
	 *
	 * @param string $id
	 * Extension ID (same as extension directory name)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enableExtension( $id ){

		$_extensions = amd_get_site_option( "extensions" );

		$extensions = explode( ",", $_extensions );

		$extensions[] = $id;

		$extensions = array_unique( $extensions );

		amd_set_site_option( "extensions", trim( implode( ",", $extensions ), "," ) );

	}

	/**
	 * Enable extension
	 * <br><b>Note: if target theme doesn't exist, it will change your theme anyway! So please make sure your theme is already registered (by `isThemeRegistered` method) before using this method</b>
	 *
	 * @param string $id
	 * Extension ID (same as extension directory name)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enableTheme( $id ){

		amd_set_site_option( "theme", $id );

	}

	/**
	 * Override theme to use this theme instead of active theme
	 * <br>Note: overridden theme will be used in specific places, not everywhere
	 *
	 * @param string $id
	 * Theme ID
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function setTheme( $id ){

		$this->override_theme = $id;

	}

	/**
	 * Check if theme is registered
	 *
	 * @param string $id
	 * Theme ID (same as theme directory name)
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function isThemeRegistered( $id ){

		return !empty( $this->themes[$id] );

	}

	/**
	 * Disable extension
	 *
	 * @param string $id
	 * Extension ID (same as extension directory name)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function disableExtension( $id ){

		$_extensions = amd_get_site_option( "extensions" );

		$_extensions = str_replace( $id, "", $_extensions );

		$_extensions = str_replace( ",,", ",", $_extensions );

		$extensions = explode( ",", $_extensions );

		$extensions = array_unique( $extensions );

		amd_set_site_option( "extensions", trim( implode( ",", $extensions ), "," ) );

	}

	/**
	 * Get default extension that should be enabled at plugin first installation
	 * @return array|string[]
	 * @since 1.0.0
	 */
	public function getDefaultEnabledExtensions(){

		return $this->defaultEnabledExtensions;

	}

	/**
	 * Load page from theme 'dashboard' directory
	 *
	 * @param string $page
	 * Page directory name
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function dashboardPage( $page ){

		$theme = self::getCurrentTheme();

		if( !$theme )
			amd_assert_error( "cannot_load_theme", sprintf( esc_html__( "There is no available theme for current page, your theme files may be removed or changed. Please check your theme in %ssettings%s to see what's wrong", "material-dashboard" ), "<a href=\"" . admin_url( "admin.php?page=amd-themes" ) . "\" target=\"_blank\">", "</a>" ) );

		$page_path = $theme["path"] . "/dashboard/$page.php";
		if( !file_exists( $page_path ) )
			amd_assert_error( "template_error", sprintf( esc_html__( "You are trying to access '%s' template which doesn't exist.", "material-dashboard" ), "$page.php" ) );

		require_once( $page_path );

	}

	/**
	 * Load templates located in theme 'templates' directory
	 *
	 * @param string $template
	 * Template file name (without .php extension)
	 * @param mixed $extra
	 * Some extra data to pass by cache (with key: '_extra'). To get extra data use {@see AMDCache::getCache()} method
	 *
	 * @return bool
	 * True on template loaded, false on failure
	 * @since 1.0.0
	 */
	public function loadTemplate( $template, $extra = null ){

		$theme = self::getCurrentTheme();

		if( !$theme )
			return false;

		$page_path = $theme["path"] . "/dashboard/templates/$template.php";

		global /** @var AMDCache $amdCache */
		$amdCache;

		$amdCache->setCache( "_extra", $extra );

		if( file_exists( $page_path ) ){
			require( $page_path );

			return true;
		}

		if( amd_is_admin() )
			amd_dump_error( "template_error", sprintf( esc_html__( "You are trying to access '%s' template which doesn't exist.", "material-dashboard" ), esc_html( $template ) . ".php" ) );

		return false;

	}

	/**
	 * Check if current theme has template
	 *
	 * @param string $template
	 * Template file name (without .php extension)
	 *
	 * @return bool
	 * Whether template exist or not
	 * @since 1.0.0
	 */
	public function templateExists( $template ){

		$theme = self::getCurrentTheme();

		if( !$theme )
			return false;

		$page_path = $theme["path"] . "/dashboard/templates/" . esc_html( $template ) . ".php";

		return file_exists( $page_path );

	}

	/**
	 * Load theme files in theme 'parts' directory
	 *
	 * @param string $part
	 * Part file name (without .php extension)
	 * @param mixed $extra
	 * Some extra data to pass by cache (with key: '_extra'). To get extra data use {@see AMDCache::getCache()} method
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function loadPart( $part, $extra = null ){

		$part = sanitize_file_name( $part );

		$theme = self::getCurrentTheme();

		if( !$theme )
			return false;

		$page_path = $theme["path"] . "/dashboard/parts/$part.php";

		global /** @var AMDCache $amdCache */
		$amdCache;

		$amdCache->setCache( "_extra", $extra );

		/**
		 * Override part path
		 * @since 1.0.4
		 */
		$page_path = apply_filters( "amd_part_{$part}_path", $page_path );

		if( $page_path AND file_exists( $page_path ) ){
			require( $page_path );

			return true;
		}

		if( $page_path === false )
			return true;

		if( amd_is_admin() AND apply_filters( "amd_show_templates_part_error", false ) )
			amd_dump_error( "template_part_error", sprintf( esc_html__( "You are trying to access '%s' template which doesn't exist.", "material-dashboard" ), "$part.php" ) );

		return false;

	}

	/**
	 * Check if theme part is available
	 * @param string $part
	 * Part file name (without .php extension)
	 *
	 * @return bool
	 * @since 1.0.4
	 */
	public function partExist( $part ){

		$part = sanitize_file_name( $part );

		$theme = self::getCurrentTheme();

		if( !$theme )
			return false;

		$page_path = $theme["path"] . "/dashboard/parts/$part.php";

		/**
		 * Override part path
		 * @since 1.0.4
		 */
		$page_path = apply_filters( "amd_part_{$part}_path", $page_path );

		if( $page_path AND file_exists( $page_path ) )
			return true;

		return false;

	}

	/**
	 * Print card with theme template
	 *
	 * @param array $card
	 * Card data
	 * @param bool $dumpInvalids
	 * Whether to display expired and unavailable cards
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function dumpSingleCard( $card, $dumpInvalids = false ){
		$expired = $card["expired"] ?? false;
		$available = $card["available"] ?? true;
		if( $expired AND !$dumpInvalids )
			return;
		if( !$available AND !$dumpInvalids )
			return;

		$page = $card["page"] ?? "";
		if( file_exists( $page ) ){
			require( $page );
			return;
		}
		$type = $card["type"];
		if( !empty( $card["content"] ) ){
			$content = $card["content"];
			if( amd_starts_with( $content, "path:" ) AND amd_ends_with( $content, ".php" ) ){
				$path = str_replace( "path:", "", $content );
				if( file_exists( $path ) ){
					ob_start();
					require_once( $path );
					$card["content"] = ob_get_clean();
				}
			}
		}

		self::loadTemplate( "card_$type", $card );
	}

	/**
	 * Check if current theme can be loaded
	 * @return bool
	 * @since 1.0.0
	 */
	public function canLoadTheme(){

		return $this->canLoadTheme;

	}

	/**
	 * Override theme
	 *
	 * @param string $id
	 * Theme id
	 *
	 * @return void
	 * @see self::setTheme()
	 * @since 1.0.0
	 */
	public function overrideTheme( $id ){

		self::setTheme( $id );

	}

}