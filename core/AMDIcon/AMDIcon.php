<?php

/** @var AMDIcon $amdIcon */
$amdIcon = null;

class AMDIcon{

	/**
	 * Icons pack list
	 * @since 1.0.0
	 * @var array
	 */
	protected $icons;

	/**
	 * Default packs directory path
	 * @since 1.0.0
	 * @var string
	 */
	public $pack_path;

	/**
	 * Current icon pack
	 * @since 1.0.0
	 * @var string
	 */
	protected $current_pack;

	/**
	 * Icon pack handler
	 */
	public function __construct(){

		$this->pack_path = __DIR__ . "/packs";

		self::setIconPack( amd_get_default( "icon_pack" ) );

		$this->icons = [];

		add_action( "amd_init_icons", function( $amdIcon ){

			/** @var AMDIcon $amdIcon */
			$amdIcon->registerPack( $amdIcon->pack_path );

		} );

		# Overwrite default icon pack value
		add_action( "init", [ $this, "initIconPack" ] );
		add_action( "amd_dashboard_init", [ $this, "initIconPack" ] );

		# Load icons
		do_action( "amd_init_icons", $this );

		# If you need to add/replace icons use this hook
		do_action( "amd_modify_icons", $this );

	}

	/**
	 * Get icon packs data with path
	 *
	 * @param string $path
	 * Icon pack path (that contains json files)
	 *
	 * @return bool|array
	 * Icon pack array on success, integer result number on failure
	 * @since 1.0.0
	 */
	public function init( $path = null ){

		if( empty( $path ) )
			$path = $this->pack_path;

		if( !file_exists( $path ) )
			return false;

		$icons = [];

		$packs = glob( "$path/*.json", GLOB_BRACE );

		if( empty( $packs ) )
			return false;

		foreach( $packs as $pack ){

			$json = file_get_contents( $pack );

			try{
				$data = json_decode( $json );
				$id = $data->id ?? null;
				if( empty( $id ) )
					continue;
				if( !empty( $data->merge ) AND $data->merge ){
					$i = $data->icons ?? null;
					if( empty( $i ) )
						continue;
					self::modifyIcons( $id, $i );
					continue;
				}
				foreach( $data as $property => $value ){
					$icons[$id][$property] = $value;
				}
			} catch( Exception $e ){
			}

		}

		return $icons;

	}

	/**
	 * Register new icon pack
	 *
	 * @param $path
	 * Icon pack path (that contains json files)
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function registerPack( $path ){

		$pack = self::init( $path );

		if( is_array( $pack ) ){
			$this->icons = array_merge( $this->icons, $pack );
			return true;
		}

		return false;

	}

	/**
	 * Initialize icon pack
	 * @return void
	 * @since 1.0.0
	 */
	public function initIconPack(){

		$iconPack = amd_get_site_option( "icon_pack", amd_get_default( "icon_pack" ) );

		$this->setIconPack( $iconPack );

	}

	/**
	 * Change current icon pack
	 *
	 * @param string $pack_id
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function setIconPack( $pack_id ){

		$this->current_pack = $pack_id;

		self::requireIconPack( $pack_id );

	}

	/**
	 * Add new icons or edit registered ones
	 * @param string $pack_id
	 * Icon pack ID
	 * @param array $icons
	 * Icons array
	 *
	 * @return bool
	 * True on successfully icons changed, false otherwise
	 * @since 1.0.0
	 */
	public function modifyIcons( $pack_id, $icons ){

		$pack = self::getIconPack( $pack_id );

		if( empty( $pack ) )
			return false;

		foreach( $icons as $icon_id => $icon )
			$this->icons[$pack_id]["icons"]->{$icon_id} = $icon;

		return true;

	}

	/**
	 * Add icon pack stylesheet to HTML header
	 *
	 * @param string $pack_id
	 * Icon pack ID
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function requireIconPack( $pack_id ){

		global /** @var AMDCache $amdCache */
		$amdCache;

		$pack = self::getIconPack( $pack_id );

		if( !empty( $pack ) ){
			if( !empty( $pack["style"] ) ){
				$url = amd_replace_constants( $pack["style"] );
				$amdCache->addStyle( "icon:$pack_id", $url );
			}
			if( !empty( $pack["script"] ) ){
				$url = amd_replace_constants( $pack["script"] );
				$amdCache->addScript( "icon:$pack_id", $url );
			}
		}

	}

	/**
	 * Get current icon pack
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getCurrentIconPack(){

		return $this->current_pack;

	}

	/**
	 * Get icon pack by ID
	 *
	 * @param string $pack_id
	 * Icon pack ID
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function getIconPack( $pack_id ){

		$icons = $this->icons;

		if( empty( $pack_id ) OR empty( $icons[$pack_id] ) )
			return null;

		return $icons[$pack_id];

	}

	/**
	 * Get icon
	 *
	 * @param string $pack_id
	 * Icon pack ID, e.g: "material-icons"
	 * @param string $icon_id
	 * Icon ID, e.g: "settings"
	 * @param array $styles
	 * Custom css, for example <code>["color" => "#000"]</code> applies <code>style="color:#000;"</code> to icon element
	 * @param array $options
	 * Custom options,
	 * you can replace any keyword with format "{KEYWORD}" in your icon pack HTML template.
	 * For example <code>["color" => "#000"]</code> will replace any <code>{COLOR}</code> with <code>#000</code>
	 * <b>Note:</b> case is ignored, "cOLoR" still will replace "{COLOR}" properties
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getIcon( $pack_id, $icon_id, $styles = [], $options = [] ){

		if( $pack_id == null )
			$pack_id = $this->current_pack;

		$pack_id = trim( $pack_id );
		$icon_id = trim( $icon_id );

		if( empty( $this->icons[$pack_id] ) )
			return "";

		$pack = $this->icons[$pack_id];
		if( empty( $pack ) )
			return "";

		$icons = $pack["icons"];
		$template = $pack["html_template"];
		if( empty( $template ) OR empty( $icons ) OR empty( $icons->{$icon_id} ) )
			return "";

		$icon = $icons->{$icon_id};

		if( !is_string( $icon ) )
			return "";

		$icon = preg_replace( "/\{ICON}/", $icon, $template );

		if( !empty( $styles ) ){
			$css = "";
			foreach( $styles as $property => $value ){
				if( empty( $property ) OR empty( $value ) )
					continue;
				$css .= "$property:$value;";
			}
			$icon = preg_replace( "/\[STYLE]/", " style=\"$css\"", $icon );
		}

		$options = array_merge( array(
			"size" => "24",
			"color" => "currentColor"
		), $options );

		foreach( $options as $option => $value ){
			$option = strtoupper( $option );
			if( in_array( $option, [ "icon" ] ) )
				continue;
			if( $option == "SIZE" ){
				$icon = preg_replace( "/\[SIZE]/", " width=\"$value\" height=\"$value\"", $icon );
			}
			else if( $option == "CLASS" ){
				$icon = preg_replace( "/\[CLASS]/", " $value", $icon );
			}
			else{
				$icon = preg_replace( "/\{$option}/", $value, $icon );
			}
		}

		$icon = preg_replace( "/\[STYLE]|\[CLASS]/", "", $icon );

		if( !is_string( $icon ) )
			return "";

		return $icon;

	}

	/**
	 * Check if icon pack is hidden or not (it can be set in icon json file)
	 *
	 * @param string $pack_id
	 * Icon pack ID
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function isPackHidden( $pack_id ){

		$pack = self::getIconPack( $pack_id );

		if( empty( $pack ) )
			return false;

		$hidden = $pack["hidden"] ?? false;

		return (bool) $hidden;

	}

	/**
	 * Get icon packs
	 * @return array
	 * Icon pack array
	 * @since 1.0.0
	 */
	public function getIcons(){

		return $this->icons;
	}

}