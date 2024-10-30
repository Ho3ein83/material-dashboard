<?php

# Load cores
add_action( "init", function(){

	// Load all required cores
	amd_require_all();

} );

/**
 * Extra components stylesheet
 * @since 1.0.4
 * @return void
 */
function amd_extra_components(){
	echo <<<CSS
        .btn.btn-icon-square{width:max-content !important;min-width:max-content !important;display:inline-flex !important;aspect-ratio:1;align-items:center;justify-content:center;padding:8px !important}
        .btn-icon-square.bis-sm{padding:2px !important}
        .btn-sm.btn-icon-square>._icon_{width:18px;height:auto}
        .btn.btn-icon-square.--block{display:flex !important}
        .btn.btn-full{width:100%}
        .hb-select-group>select{left:0}
        body.rtl .hb-select-group>select{text-align:right}
        body.ltr .hb-select-group>select{text-align:left}
    CSS;
}
add_action( "amd_api_components_stylesheet", "amd_extra_components" );

/**
 * Alternate for wp_head
 * @return void
 * @since 1.0.0
 */
function amd_wp_head_alternate(){

    do_action( "amd_wp_head_alternate" );

}

/**
 * Get icon
 *
 * @param string $icon
 * Icon ID, e.g: "material-icons"
 * @param string|null $pack
 * Icon pack ID, pass null to use current icon pack
 * @param array $styles
 * Custom css, for example <code>["color" => "#000"]</code> applies <code>style="color:#000;"</code> to icon element
 * @param array $options
 * Custom options,
 * you can replace any keyword with format "{KEYWORD}" in your icon pack HTML template.
 * For example <code>["color" => "#000"]</code> will replace any <code>{COLOR}</code> with <code>#000</code>
 * <b>Note:</b> case is ignored, "cOLoR" still will replace "{COLOR}" properties
 *
 * @return string
 * @see AMDIcon::getIcon()
 * @since 1.0.0
 */
function amd_icon( $icon, $pack = null, $styles = [], $options = [] ){

    if( empty( $icon ) )
        return "";

	global /** @var AMDIcon $amdIcon */
	$amdIcon;

	return $amdIcon->getIcon( $pack, $icon, $styles, $options );
}

/**
 * Echo icon
 *
 * @param string $icon
 * Icon ID
 * @param string|null $pack
 * Icon pack ID, pass null to use current icon pack
 * @param array $styles
 * Custom css, for example <code>["color" => "#000"]</code> applies <code>style="color:#000;"</code> to icon element
 * @param array $options
 * Custom options,
 * you can replace any keyword with format "{KEYWORD}" in your icon pack HTML template.
 * For example <code>["color" => "#000"]</code> will replace any <code>{COLOR}</code> with <code>#000</code>
 * <b>Note:</b> case is ignored, "cOLoR" still will replace "{COLOR}" options
 *
 * @return void
 * @see amd_icon()
 * @since 1.0.0
 */
function _amd_icon( $icon, $pack = null, $styles = [], $options = [] ){

	echo amd_icon( $icon, $pack, $styles, $options );

}

/**
 * Change current icon pack
 *
 * @param string $pack
 * Pack ID
 *
 * @return void
 * @see AMDIcon::setIconPack()
 * @since 1.0.0
 */
function amd_set_icon_pack( $pack ){

	global /** @var AMDIcon $amdIcon */
	$amdIcon;

	$amdIcon->setIconPack( $pack );

}

/**
 * Get current icon pack
 *
 * @return string
 * @see AMDIcon::getCurrentIconPack()
 * @since 1.0.0
 */
function amd_current_icon_pack(){

	global /** @var AMDIcon $amdIcon */
	$amdIcon;

	return $amdIcon->getCurrentIconPack();

}

/**
 * Add icon pack stylesheet to HTML header
 *
 * @param string $pack_id
 * Icon pack ID
 *
 * @return void
 * @see AMDIcon::requireIconPack()
 * @since 1.0.0
 */
function amd_icon_pack_require( $pack_id ){

	global /** @var AMDIcon $amdIcon */
	$amdIcon;

	$amdIcon->requireIconPack( $pack_id );

}

/**
 * Get current icon pack
 *
 * @return string
 * @see AMDIcon::getCurrentIconPack()
 * @since 1.0.0
 */
function amd_get_icon_pack(){

	global /** @var AMDIcon $amdIcon */
	$amdIcon;

	return $amdIcon->getCurrentIconPack();

}

/**
 * Check if current page is wp-login
 * @return mixed|null
 * @since 1.0.0
 */
function amd_is_wp_login(){

	return apply_filters( "amd_is_admin_login", !is_user_logged_in() AND ( $GLOBALS["pagenow"] ?? "" ) == "wp-login.php" );

}

/**
 * Replace constant name with its value
 *
 * @param string $str
 * Input string
 *
 * @return array|string|string[]|null
 * @since 1.0.0
 */
function amd_replace_constants( $str ){

	$str = preg_replace( "/\{AMD_PATH}/", AMD_PATH, $str );
	$str = preg_replace( "/\{AMD_DIRECTORY}/", AMD_DIRECTORY, $str );
	$str = preg_replace( "/\{AMD_CORE}/", AMD_CORE, $str );
	$str = preg_replace( "/\{AMD_ASSETS_PATH}/", AMD_ASSETS_PATH, $str );
	$str = preg_replace( "/\{AMD_ASSETS}/", AMD_ASSETS, $str );
	$str = preg_replace( "/\{AMD_CSS}/", AMD_CSS, $str );
	$str = preg_replace( "/\{AMD_JS}/", AMD_JS, $str );
	$str = preg_replace( "/\{AMD_IMG}/", AMD_IMG, $str );
	$str = preg_replace( "/\{AMD_MOD}/", AMD_MOD, $str );
	$str = preg_replace( "/\{AMD_PAGES}/", AMD_PAGES, $str );
	$str = preg_replace( "/\{AMD_DASHBOARD}/", AMD_DASHBOARD, $str );
	$str = preg_replace( "/\{AMD_AUTH_SITE}/", AMD_AUTH_SITE, $str );
	$str = preg_replace( "/\{DOC_URL}/", amd_doc_url( "" ), $str );

	/**
     * For custom properties
	 * @sicne 1.0.0
	 */
	return apply_filters( "amd_replace_constants", $str );

}

/**
 * Check if required classes and/or functions are exists. See documentation for more details
 *
 * @param $r
 * Requirements query, use <code>class(CLASS_NAME)</code> for class availability check,
 * and use <code>function(FUNCTION)NAME)</code> for check if function is exists.
 * <ul>
 * <li>Use "&&" or "AND" for logical 'and' operation</li>
 * <li>Use "||" or "OR" for logical 'or' operation</li>
 * </ul>
 * Examples:
 * <ul>
 * <li><code>class(WP_User) AND function(wp_get_current_user)</code></li>
 * <li><code>class(EDD_Requirements_Check) || class(WooCommerce)</code></li>
 * <li><code>function(amd_plugin)</code></li>
 * </ul>
 *
 * @return bool
 * @since 1.0.0
 */
function amd_check_requirements( $r ){

    # If requirements is empty
    if( empty( $r ) )
        return true;

    # If multiple requirements are needed
    if( is_iterable( $r ) ){

        $all = true;
        foreach( $r as $name => $item ){
            if( !is_string( $item ) OR !amd_check_requirements( $item ) )
                $all = false;
        }
        return $all;

    }
    else if( !is_string( $r ) )
        return false;

	$query = $r;

	# Split the query into separate search terms
	$search_terms = preg_split( '/\s+(AND|OR)\s+/', $query, -1, PREG_SPLIT_DELIM_CAPTURE );

	# Loop through the search terms and evaluate each one
	$results = array();
	$operator = 'AND';
	foreach( $search_terms as $term ){
		if( $term == 'AND' || $term == 'OR' ){
			$operator = $term;
		}
		else{
			list( $type, $name ) = explode( ':', $term );
			$exists = false;

            if( $type == 'function' )
				$exists = function_exists( $name );
            elseif( $type == 'class' )
	            $exists = class_exists( $name );

			if( $exists && $operator == 'OR' ){
				# If the operator is OR and the term exists, we can stop searching
				$results[] = true;
				break;
			}
            elseif( !$exists && $operator == 'AND' ){
				# If the operator is AND and the term does not exist, we can stop searching
				$results[] = false;
				break;
			}
			else{
				$results[] = $exists;
			}
		}
	}

	# If all search terms are true (for 'AND' queries) or at least one is true (for 'OR' queries), the overall result is true
	$available = ( $operator == 'AND' ? !in_array( false, $results ) : in_array( true, $results ) );

	return $available;

}

/**
 * Get current URL with options
 *
 * @param bool $removeGET
 * @param string $only
 *
 * @return mixed|string
 * @since 1.0.0
 */
function amd_current_url( $removeGET = true, $only = "" ){

	# If WordPress is loaded by websocket or browser headers missing
	if( empty( $_SERVER["HTTP_HOST"] ) )
		return "";

	$http_s = "http" . ( is_ssl() ? "s" : "" ) . "://";

	$hostname = sanitize_text_field( $_SERVER["HTTP_HOST"] );

	$URI = sanitize_text_field( $_SERVER["REQUEST_URI"] );

	$URI_without_GET = "";

	$GET = "";

	if( strpos( $URI, "?" ) !== false ){

		$URI_without_GET = trim( substr( $URI, 0, strpos( $URI, "?" ) ) );

		$GET = trim( substr( $URI, strlen( $URI_without_GET ), strlen( $URI ) ) );

	}

	if( !empty( $only ) ){

		if( $only == "host" )
			return $http_s . $hostname;
		else if( $only == "full_uri" )
			return $http_s . $hostname . $URI;
		else if( $only == "uri" )
			return $URI;
		else if( $only == "uri_without_get" )
			return $URI_without_GET;
		else if( $only == "get" )
			return $GET;
		else if( $only == "self" )
			return dirname( sanitize_text_field( $_SERVER['PHP_SELF'] ) );
		else if( $only == "exclude_query" )
			return explode( "?", $http_s . $hostname . $URI )[0];
		else if( $only == "self_uri" )
			return $http_s . $hostname . dirname( sanitize_text_field( $_SERVER['PHP_SELF'] ) );
		else if( $only == "domain" )
			return preg_replace( "/https?:\/\//", "", $hostname );
		else if( $only == "remove_get_query" )
			return $http_s . $hostname . $URI_without_GET;

	}

	$url = $http_s . $hostname . ( $removeGET ? $URI_without_GET : $URI );

    return $url;

}

/**
 * Check if two URLs are the same.
 * <ul>
 * <li>"http://site.com/example" and "http://www.site.com/example/" are the same</li>
 * <li>"www.google.com" and "http://google.com" are the same</li>
 * <li>"www.google.com" and "https://google.com" aren't the same</li>
 * <li>"http://site.com/example" and "https://site.com/example/" are the same if $ignoreSSL be true</li>
 * <li>"https://google.com" and "www.google.com/" are also the same if $ignoreSSL be true</li>
 * </ul>
 *
 * @param string $url1
 * URL 1
 * @param string $url2
 * URL 2
 * @param false $ignoreSSL
 * Whether to ignore https from the beginning or not
 *
 * @return bool
 * Whether URLs match or not
 * @since 1.0.0
 */
function amd_match_uri( $url1, $url2, $ignoreSSL = true ){

	if( $ignoreSSL ){
		# Ignore http / https
		$url1 = preg_replace( "/https?:\/\//", "", $url1 );
		$url2 = preg_replace( "/https?:\/\//", "", $url2 );
	}

	# Remove http prefix
	$url1 = preg_replace( "/http:\/\//", "", $url1 );
	$url2 = preg_replace( "/http:\/\//", "", $url2 );

	# Remove www
	$url1 = preg_replace( "/www\./", "", $url1 );
	$url2 = preg_replace( "/www\./", "", $url2 );

	# Remove query
	$url1 = explode( "?", $url1 )[0];
	$url2 = explode( "?", $url2 )[0];

	# Trim slash from end of the URL
	$url1 = trim( $url1, "/" );
	$url2 = trim( $url2, "/" );

	return $url1 == $url2;
}

/**
 * Convert URL query to array.
 * e.g:
 * <br>`?page=1`: <code>["page" => "1"]</code>
 * <br>`page=1&hello=world`: <code>["page" => "1", "hello" => "world"]</code>
 *
 * @param string $url
 * The full URL or query part with or without "?"
 *
 * @return array
 * Parameters array
 * @since 1.0.0
 */
function amd_query_to_array( $url ){

	# If there is no query in URL
	$flag = false;
	if( strpos( $url, "?" ) === false and strpos( $url, "&" ) === false and strpos( $url, "=" ) === false )
		return [];
	if( strpos( $url, "?" ) === false )
		$flag = true;

	# 1.Remove any other part of URL except query part
	# 2.Remove query mark (?) from beginning of URL
	$url = explode( "?", $url )[$flag ? 0 : 1];

	# Decode URL
	$url = urldecode( $url );

	# Explode each parameter
	$queries = [];
	foreach( explode( "&", $url ) as $item ){

		$exp = explode( "=", $item );
		$key = $exp[0];
		$value = $exp[1] ?? "";
		$queries[$key] = $value;

	}

	return $queries;

}

/**
 * Convert array to URL query
 * e.g:
 * <br><code>amd_array_to_query( ["page" => "1", "status" => "trash"], "?" )</code> -> "?page=1&status=trash"
 * <br><code>amd_array_to_query( ["page" => "1" ], "" )</code> -> "page=1"
 *
 * @param array $arr
 * Query array with key and value, like <code>["key" => "value"]</code>
 * @param string $prefix
 * Query prefix for URL, you can leave it empty to get query in "key=value" format
 * or pass character like "?" to get in "?key=value" format
 *
 * @return string
 * Query string
 * @since 1.0.0
 */
function amd_array_to_query( $arr, $prefix = "" ){

	$query = [];
	foreach( $arr as $key => $value ){
		if( empty( $key ) )
			continue;
		if( empty( $value ) )
			$query[] = $key;
		else
			$query[] = "$key=$value";
	}
	$out = trim( implode( "&", $query ), "&" );
	if( empty( $out ) )
		return "";

	return $prefix . $out;

}

/**
 * Merge URL with query.
 * e.g: <code>"http://example.com/?p=1"</code> merge with query <code>["key" => "value"]</code> returns:
 * <br><code>"http://example.com/?p=1&key=value"</code>
 *
 * @param string $url
 * Haystack: URL
 * @param string|array $query
 * Needle: queries array or string
 *
 * @return mixed|string
 * @since 1.0.0
 */
function amd_merge_url_query( $url, $query ){

	if( is_string( $query ) ){
		if( strpos( $url, "?" ) === false ){
			$url = explode( "?", $url )[0] . amd_array_to_query( amd_query_to_array( $query ), "?" );
		}
		else{
			$q = amd_query_to_array( $url );
			$q = array_merge( $q, amd_query_to_array( $query ) );
			$url = explode( "?", $url )[0] . amd_array_to_query( $q, "?" );
		}
	}
	else if( is_array( $query ) ){
		if( strpos( $url, "?" ) === false ){
			$url = explode( "?", $url )[0] . amd_array_to_query( $query, "?" );
		}
		else{
			$q = amd_query_to_array( $url );
			$q = array_merge( $q, $query );
			$url = explode( "?", $url )[0] . amd_array_to_query( $q, "?" );
		}
	}

	return $url;

}

/**
 * Remove queries from URL
 *
 * @param string $url
 * URL to remove query, you can use <code>amd_replace_url( "%full_uri%" )</code> to get current URL
 * @param string|array $query
 * Can be the array of query list like <code>["page", "status"]</code>, or a single query string like "page"
 *
 * @return mixed|string
 * @since 1.0.0
 */
function amd_url_remove_query( $url, $query ){

	$q = amd_query_to_array( $url );

	$remove = $query;

	# Convert numbers and string to array
	if( is_string( $remove ) OR is_numeric( $remove ) )
		$remove = [ "$remove" ];

	if( !is_array( $remove ) )
		return $url;

	foreach( $remove as $item ){
		if( !empty( $q[$item] ) )
			unset( $q[$item] );
	}

	$url_exp = explode( "?", $url );
	$url = trim( $url_exp[0], "/" );

	return "$url/" . amd_array_to_query( $q, "?" );

}

/**
 * Replace URL with specified parameters
 * <br><ul>
 * <li><b>%host%</b>: http://domain.com</li>
 * <li><b>%uri%</b>: /slug/file.php/?extra=text</li>
 * <li><b>%full_uri%</b>: http://domain.com/slug/file.php/?extra=text</li>
 * <li><b>%uri_without_get%</b>: /slug/file.php/</li>
 * <li><b>%get%</b>: ?extra=text<li>
 * <li><b>%self%</b>: /slug</li>
 * <li><b>%self_uri%</b>: http://domain.com/slug</li>
 * <li><b>%domain%</b>: domain.com</li>
 * <li><b>%remove_get_query%</b>: http://domain.com/parent/slug</li>
 * </ul>
 *
 * @param string $url
 * URL part
 *
 * @return string
 * Returned URL is sanitized
 * @since 1.0.0
 */
function amd_replace_url( $url ){

	$replaceArray = array(
		"%host%" => amd_current_url( false, "host" ),
		"%full_uri%" => amd_current_url( false, "full_uri" ),
		"%uri%" => amd_current_url( false, "uri" ),
		"%uri_without_get%" => amd_current_url( false, "uri_without_get" ),
		"%get%" => amd_current_url( false, "get" ),
		"%self%" => amd_current_url( false, "self" ),
		"%exclude_query%" => amd_current_url( false, "exclude_query" ),
		"%self_uri%" => amd_current_url( false, "self_uri" ),
		"%domain%" => amd_current_url( false, "domain" ),
		"%remove_get_query%" => amd_current_url( false, "remove_get_query" ),
	);

	foreach( $replaceArray as $key => $value )
		$url = str_replace( $key, $value, $url );

	return $url;

}

/**
 * Add or update user meta from database
 *
 * @param null|int $uid
 * User ID, pass null for current logged-in user
 * @param string $mn
 * Meta name
 * @param string $mv
 * Meta value
 *
 * @return bool
 * @since 1.0.0
 */
function amd_set_user_meta( $uid, $mn, $mv ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->setUserMeta( $uid, $mn, $mv );

}

/**
 * Get user meta
 *
 * @param int|null $uid
 * User ID, pass null to get current user
 * @param string $mn
 * Meta name
 * @param string $default
 * Default value
 *
 * @return bool|string
 * @since 1.0.0
 */
function amd_get_user_meta( $uid, $mn, $default = "" ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->getUserMeta( $uid, $mn, $default );

}

/**
 * Delete user meta
 *
 * @param int $uid
 * User ID
 * @param string $mn
 * Meta name
 *
 * @return bool
 * @since 1.0.0
 */
function amd_delete_user_meta( $uid, $mn ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->deleteUserMeta( $uid, $mn );

}

/**
 * Check if user has meta
 *
 * @param int $uid
 * User ID
 * @param string $mn
 * Meta name
 *
 * @return string
 * @since 1.0.0
 */
function amd_user_has_meta( $uid, $mn ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->userMetaExists( $uid, $mn );

}

/**
 * Set temporarily data in database
 *
 * @param string $name
 * Temp name
 * @param string $value
 * Temp value
 * @param int $expire
 * Temp expiration time in seconds, e.g: 3600 -> 1 hour
 *
 * @return bool|int|mysqli_result|resource|null
 * On update: the number of rows updated, or false on error.
 * <br>On insert: the number of rows inserted, or false on error.
 * @since 1.0.0
 */
function amd_set_temp( $name, $value, $expire = 3600 ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->setTemp( $name, $value, $expire );

}

/**
 * Get temporarily data from database
 *
 * @param string $name
 * Temp bool name
 * @param bool $single
 * Whether to return a single string of temp value or full row object.
 *
 * @return array|object|false|string
 * @since 1.0.0
 */
function amd_get_temp( $name, $single = true ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->getTemp( $name, $single );

}

/**
 * Search for temporarily data in database
 *
 * @param string $col
 * Column name, 'temp_key' or 'temp_value' or even 'expire' if needed.
 * @param string $regex
 * The regex to match with column value
 * @param bool $get
 * Whether to return rows count ($get=false) or get rows data ($get=true)
 *
 * @return bool|array
 * @since 1.0.0
 */
function amd_find_temp( $col, $regex, $get = false ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->findTemp( $col, $regex, $get );

}

/**
 * Check if temp key exists in database
 *
 * @param string $name
 * Temp key
 *
 * @return bool
 * True on row exist, otherwise false
 * @since 1.0.0
 */
function amd_temp_exists( $name ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->tempExists( $name );

}

/**
 * Delete temporarily data from database
 *
 * @param string|null $name
 * The name of temp data or leave it empty for expiration check
 * @param bool $timeCheck
 * If you pass $name you can let it check expiration time and keep it if not expired.
 * Otherwise, if you set it to false it will delete it anyway.
 *
 * @return array|object|stdClass
 * @since 1.0.0
 */
function amd_delete_temp( $name = null, $timeCheck = false ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->deleteTemp( $name, $timeCheck );

}

/**
 * Remove expired temps
 * @return void
 * @since 1.0.0
 */
function amd_clean_temps(){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	$amdDB->cleanExpiredTemps();

}

/**
 * Remove expired temps with temp_key regex
 *
 * @param string $regex
 * Regular expression
 *
 * @return void
 * @since 1.0.0
 */
function amd_clean_temps_keys( $regex ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	$amdDB->cleanExpiredTempsKeys( $regex );

}

/**
 * Generate random string
 * <br>Values for $key:
 * <br>[custom]: use any characters as string. e.g: 'abcABC123'
 * <br>'numbers' / 'number': 0-9
 * <br>'lower': a-z
 * <br>'upper': A-Z
 * <br>'string': a-z A-Z
 * <br>'hex': 0-9 a-f
 * <br>'all': 0-9 a-z A-Z
 * <br>'all_char': 0-9 a-z A-Z !$^()&amp;_#*+%@
 *
 * @param int $len
 * String length
 * @param string|int $key
 * Integer key code (e.g: 1, -2, 5) or string key (e.g: "lower", "number", "all") or custom string (e.g: "abc123")
 *
 * @return string
 * @since 1.0.0
 */
function amd_generate_string( $len = 10, $key = -1 ){

	if( empty( $len ) )
		$len = 10;

	switch( strtolower( $key ) ){
		case 'lower':
			$key = 1;
			break;
		case 'upper':
			$key = 2;
			break;
		case 'string':
			$key = 3;
			break;
		case 'numbers':
		case 'number':
			$key = 4;
			break;
		case 'hex':
			$key = 5;
			break;
		case 'char':
			$key = 6;
			break;
		case 'all':
			$key = -1;
			break;
		case 'all_char':
			$key = -2;
			break;
		default:
			$key = is_string( $key ) ? $key : -1;
	}

	if( is_numeric( $key ) ){

		switch( $key ){
			case -2:
				# Small and capital letters and numbers and characters
				$key = "BRxuPvymLbwCKThesXWUtjSNQOgkdrlpqAVMYcfGioznZJHaDIFE4765291380!$^(@_&#*+)%";
				break;
			case -1:
				# Small and capital letters and numbers
				$key = "BRxuPvymLbwCKThesXWUtjSNQOgkdrlpqAVMYcfGioznZJHaDIFE4765291380";
				break;
			case 1:
				# Just small letters
				$key = "ksmprzgbjcnltofydhiweaqvxu";
				break;
			case 2:
				# Just capital letters
				$key = "HYBWDXTURCAZPQOMFKGESJLVIN";
				break;
			case 3:
				# Small and capital letters
				$key = "BRxuPvymLbwCKThesXWUtjSNQOgkdrlpqAVMYcfGioznZJHaDIFE";
				break;
			case 4:
				# Just numbers
				$key = "4765291380";
				break;
			case 5:
				# Hex code
				$key = "Cc9728DEeBb43Ff5Aa160";
				break;
			case 6:
				# Characters
				$key = "!$^(@_&#*+)%";
				break;
		}

	}

	$string = '';

	$key = str_shuffle( str_shuffle( $key ) );

    # Set random seed
	srand( microtime( true ) * 1000000 );

	for( $i = 0; $i < $len; $i++ )
		$string .= $key[rand( 0, strlen( $key ) - 1 )];

	return $string;

}

/**
 * Unescape slash in JSON
 *
 * @param string $str
 * Input string
 * @param bool $decode
 * Whether to decode JSON or just escape it
 * @param int $tries
 *
 * @return mixed|null
 * @since 1.0.0
 */
function amd_unescape_json( $str, $decode = true, $tries=1 ){

    for( $i = 1; $i <= $tries; $i++ ){
	    $str = str_replace( "\\\"", "\"", $str );
	    $str = str_replace( "\'", "'", $str );
	    $str = str_replace( "\\n", "\n", $str );
	    $str = str_replace( "\\r", "\r", $str );
	    $str = str_replace( "\\t", "\t", $str );
    }

	return $decode ? json_decode( $str ) : $str;

}

/**
 * Generate secret key for user
 * <br>Format: <code>ABc1234abcd</code>
 *
 * @param int $uid
 * User ID
 * @param bool $addToDB
 * Whether to add it into database or not
 *
 * @return string|null
 * @since 1.0.0
 */
function amd_generate_secret( $uid, $unique = false, $addToDB = false ){

	$user = get_user_by( "ID", $uid );

	if( !$user || is_wp_error( $user ) )
		return null;

	$registered = $user->user_registered;

	$time = strtotime( $registered );

	$mm = date( "M", $time );

	$makeSecret = function() use ( $mm, $time ){
		return amd_generate_string( 1, 'upper' ) . $mm[0] . amd_generate_string( 1, 'lower' ) . substr( strval( $time ), -4 ) . amd_generate_string( 4, 'lower' );
	};
	$secret = call_user_func( $makeSecret );

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	if( $unique and !empty( $amdSilu ) ){
		while( $amdSilu->getUserByMeta( "secret", $secret ) ){
			$secret = call_user_func( $makeSecret );
		}
	}

	if( $addToDB )
		amd_set_user_meta( $uid, "secret", $secret );

	return $secret;

}

/**
 * Generate verification code for user
 *
 * @param int $uid
 * User ID
 * @param string $name
 * Custom verification ID. e.g: "reset_password", "verify_email"
 * @param string $pattern
 * Verification code pattern, see: <code>amd_generate_string_pattern</code>
 *
 * @return mixed
 * @see amd_generate_string_pattern()
 * @since 1.0.0
 */
function amd_regenerate_verification_code( $uid, $name = "verification", $pattern = "[number:6]" ){

	$code = amd_generate_string_pattern( $pattern );

	$success = (bool) amd_set_temp( "vc_{$uid}_$name", $code );

	return $success ? $code : false;

}

/**
 * Check if verification code belongs to user with ID $uid
 *
 * @param int $uid
 * User ID
 * @param string $vCode
 * Verification code
 * @param string $name
 * Verification code name
 *
 * @return bool
 * @since 1.0.0
 */
function amd_is_verification_code_valid( $uid, $vCode, $name = "verification" ){

	$temp_name = "vc_{$uid}_$name";

	return amd_get_temp( $temp_name ) == $vCode;

}

/**
 * Change user password
 *
 * @param int $uid
 * User ID
 * @param string $new_password
 * New password
 * @param bool $notify
 * Whether let user know about password change
 *
 * @return bool
 * @since 1.0.0
 */
function amd_change_password( $uid, $new_password, $notice = false ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->changePassword( $uid, $new_password, $notice );

}

/**
 * Get user automatically
 *
 * @param int|string|WP_User $part
 * Any information about user like email, user ID, phone number or username or even WP user object
 *
 * @return AMDUser|null
 * AMDUser on user found, otherwise null
 * @since 1.0.0
 */
function amd_get_user( $user ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->getUserAuto( $user )["user"] ?? null;

}

/**
 * Guess user automatically and get user data array
 *
 * @param string $part
 * Any information about user like email, user ID, phone number or username
 *
 * @return array|null[]
 * User data array, e.g:
 * <br><code>["by" => "AMDUser|WP_User|email|id|phone|etc.", "user" => USER_OBJECT]</code>
 * @since 1.0.0
 */
function amd_guess_user( $part ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->getUserAuto( $part );

}

/**
 * Get user by specific part
 *
 * @param string $by
 * Field name, e.g: "login", "ID", "email"
 * <br>Allowed fields: 'id', 'ID', 'slug', 'email', 'login'
 * <br><br>You can also use multiple fields at the same time, for example if you
 * pass "ID|login|email" it will try to get user by ID first and if ID wasn't
 * exist it will try to get user by username and so on.
 * @param string $value
 * Value for `$by` field, like user email, ID, login, etc.
 * @param bool $simpleUser
 * Whether to get simple user or WP_User
 *
 * @return false|AMDUser|WP_User
 * @since 1.0.0
 */
function amd_get_user_by( $by, $user, $simpleUser = true ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->getUser( $by, $user, $simpleUser );

}

/**
 * Get user by specific meta
 *
 * @param string $by
 * Meta name
 * @param string $value
 * Meta value
 * @param bool $single
 * true: returns first user have this meta name
 * <br>false: return all results that found
 *
 * @return false|AMDUser|array
 * @since 1.0.0
 */
function amd_get_user_by_meta( $by, $value, $single = true ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->getUserByMeta( $by, $value, $single );

}

/**
 * Get current user with simple user object {@see AMDUser}
 * @return false|AMDUser
 * AMDUser object on success, false on fail
 * @since 1.0.0
 */
function amd_get_current_user(){

	if( !is_user_logged_in() )
		return false;

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	$user = $amdSilu->create( wp_get_current_user()->ID );

	return $user->isValid ? $user : false;

}

/**
 * Get default / fallback locale
 * @return mixed|void
 * @since 1.0.0
 */
function amd_get_default_locale(){

	return apply_filters( "amd_get_default_locale", "fa_IR" );

}

/**
 * Get default site options as JSON string
 * @return string
 * @since 1.0.0
 */
function amd_get_default_options(){

	global /** @var AMDCache $amdCache */
	$amdCache;

	return $amdCache->getDefault( "options_data" );

}

/**
 * Add/Update site option
 *
 * @param string $name
 * Option name
 * @param string $value
 * Option value
 *
 * @return bool|int|mysqli_result|resource|null
 * On update: The number of rows updated, or false on error
 * <br>On insert: The number of rows inserted, or false on error
 * @since 1.0.0
 */
function amd_set_site_option( $name, $value ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->setSiteOption( $name, $value );

}

/**
 * Delete site option
 *
 * @param string $name
 * Option name
 *
 * @return bool
 * True on success, false on failure
 * @since 1.0.0
 */
function amd_delete_site_option( $name ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->deleteSiteOption( $name );

}

/**
 * Get site option from database
 *
 * @param string $name
 * Option name
 * @param string $default
 * Default value
 * @param bool $ignoreCaches
 * <code>[Since 1.0.5] </code>
 * Whether to ignore caches and get site option directly from database or get it from system caches,<br>
 * using caches can improve site performance but site options may be old
 *
 * @return string
 * @since 1.0.0
 */
function amd_get_site_option( $name, $default = "", $ignoreCaches=false ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->getSiteOption( $name, $default, $ignoreCaches );

}

/**
 * Get result of regex search inside site_options table
 *
 * @param string $regex
 * Search regex
 *
 * @return array|object|\stdClass|null
 * @since 1.0.0
 */
function amd_search_site_option( $regex ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->searchSiteOption( $regex );

}

/**
 * Check if site option exists in database
 *
 * @param string $name
 * Option name
 *
 * @return bool
 * @since 1.0.0
 */
function amd_site_option_exists( $name ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->siteOptionExists( $name );

}

/**
 * Check if current URL is using "https"
 * @return bool
 * @since 1.0.0
 */
function amd_using_https(){

	return amd_starts_with( amd_replace_url( "%host%" ), "https://" );

}

/**
 * Check if site options is allowed to set in plugin settings
 *
 * @param string $option
 * Option name
 * @param mixed|null $value
 * Value to be checked, pass null to skip value check
 *
 * @return bool
 * @since 1.0.0
 */
function amd_is_option_allowed( $option, $value=null ){

    if( apply_filters( "amd_is_option_allowed_$option", false ) )
        return true;

	$allowedOptions = apply_filters( "amd_get_allowed_options", [] );

    foreach( $allowedOptions as $key => $data ){

        if( strpos( $key, "[*]" ) !== false ){
            $pattern = str_replace( "[*]", "(.*)", $key );
            if( preg_match( "/^$pattern$/", $option ) ){
                if( $value === null )
                    return true;

	            if( is_string( $data ) ){
		            if( amd_compile_data_type( $data, $value ) )
			            return true;
	            }
	            else if( is_array( $data ) ){
		            $type = $data["type"] ?? null;
		            $filter = $data["filter"] ?? null;
		            if( !empty( $filter ) and is_callable( $filter ) )
			            $value = call_user_func( $filter, $value );
		            if( amd_compile_data_type( $type, $value ) )
			            return true;
	            }

            }
        }
        else if( $key == $option ){

	        if( is_string( $data ) ){
		        if( amd_compile_data_type( $data, $value ) )
			        return true;
	        }
	        else if( is_array( $data ) ){
		        $type = $data["type"] ?? null;
		        $filter = $data["filter"] ?? null;
		        if( !empty( $filter ) and is_callable( $filter ) )
			        $value = call_user_func( $filter, $value );
		        if( amd_compile_data_type( $type, $value ) )
			        return true;
	        }

        }

	}

	return false;

}

/**
 * Check if string or array starts with an element or string
 * <br>You can use <code>str_starts_with</code> built-in function for string comparison
 *
 * @param string|array $haystack
 * The string or array to search in
 * @param mixed $needle
 * The substring or object to search for in the $haystack.
 *
 * @return bool
 * @see str_starts_with
 * @since 1.0.0
 */
function amd_starts_with( $haystack, $needle ){

	if( is_array( $haystack ) ){
		$a = current( $haystack );

		return $needle == $a;
	}

	return substr( $haystack, 0, strlen( $needle ) ) === $needle;

}

/**
 * Check if string or array ends with an element or string
 *
 * @param string|array $haystack
 * The string or array to search in
 * @param mixed $needle
 * The substring or object to search for in the $haystack
 *
 * @return bool
 * @since 1.0.0
 */
function amd_ends_with( $haystack, $needle ){

	if( is_array( $haystack ) ){
		$a = $haystack;
		end( $a );

		return $needle == $a;
	}

	$length = strlen( $needle );
	if( !$length )
		return true;

	return substr( $haystack, -$length ) === $needle;
}

/**
 * Validate string with regex or template
 * <br>e.g: $string => "Hello World", $regex => "/[0-9]/", returns false
 * <br>e.g: $string => "12345", $regex => "/[0-9]/", returns true
 * <br>e.g: $string => "info@example.com", $regex => "%email%", returns true
 * <br>Templates: <ul>
 * <li>%phone% (phone number)</li>
 * <li>%tel% (telephone)</li>
 * <li>%email% (email)</li>
 * <li>%password% (password with at least 8 characters)</li>
 * <li>%username% (allowed characters: a-z, A-Z, 0-9, _)</li>
 * <li>%zipcode% (zip/postal code)</li>
 * </ul>
 *
 * @param string $string
 * Input string
 * @param string $regex
 * Validation key or regex string
 * @param bool $required
 * If option be required and input string be empty, returns false
 *
 * @return bool
 * @since 1.0.0
 */
function amd_validate( $string, $regex, $required = true ){

	if( !$required and empty( $string ) )
		return true;

	if( amd_starts_with( $regex, "%" ) AND amd_ends_with( $regex, "%" ) ){
		switch( trim( $regex, "%" ) ){
			case "phone":
				$regex = "/^0[0-9]{10,13}$/";
				break;
			case "tel":
				$regex = "/^[0-9]{10,12}$/";
				break;
			case "email":
				$regex = "/^[a-zA-Z0-9.!#$%&'*+\/\=\?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]{2,})*$/";
				break;
			case "password":
				$regex = "/.{8,}/";
				break;
			case "username":
				$regex = "/^[0-9a-zA-Z_.]{5,20}$/";
				break;
			case "zipcode":
				$regex = "/^[0-9]{5,}$/";
				break;
			default:
				return false;
		}
	}

    return (bool) @preg_match( $regex, $string );

}

/**
 * Slugify string
 * <br>e.g: <code>"Hello World" -> "hello_world"</code>
 *
 * @param string $text
 * Input text
 * @param string $sp
 * Separator character, for URLs it would be "/"
 * <br><code><b>DEPRECATED: this parameter is no longer available since 1.0.4 version, and it will be ignored.</b></code>
 *
 * @return string
 * @since 1.0.0
 */
function amd_slugify( $text, $sp = '/' ){

    return sanitize_title( $text );

}

/**
 * Parse BBCode from two characters
 *
 * @param string $bbcode
 * BBCode
 * @param callable $callback
 * BBCode callback
 * @param string $charStart
 * BBCode starting character
 * @param string $charEnd
 * BBCode ending character
 *
 * @return mixed
 * @since 1.0.0
 */
function amd_parse_char_between( $bbcode, $callback, $charStart = '[', $charEnd = ']' ){

	if( !is_callable( $callback ) )
		return $bbcode;

	$exp = explode( $charEnd, $bbcode );

	$out = $bbcode;
	foreach( $exp as $e ){
		$exp2 = explode( $charStart, $e );
		$e2 = $exp2[1] ?? "";
		if( is_callable( $callback ) ){
			$res = call_user_func( $callback, $e2 );
			$out = str_replace( $charStart . $e2 . $charEnd, $res, $out );
		}
	}

	return $out;
}

/**
 * Generate random string from command.
 * <br>Command format: <code>TYPE:LENGTH</code>
 * <br>e.g: <br><code>number:10</code><br><code>upper:6</code>
 *
 * @param string $cmd
 * Command string
 *
 * @return false|string
 * @see amd_generate_string()
 * @since 1.0.0
 */
function amd_generate_string_command( $cmd ){

	if( empty( $cmd ) )
		return false;

	$exp = explode( ":", $cmd );
	$key = $exp[0];
	$len = !empty( $exp[1] ) ? $exp[1] : "";
	$len = trim( $len );

	return amd_generate_string( $len, $key );

}

/**
 * Generate random string with BBCode
 *
 * @param string $code
 * Pattern string
 * <br>Format: <code>[MODE:LENGTH]</code>
 * <br>MODE: [number|hex|lower|upper|all|all_char|etc]
 * <br>e.g: <code>[number:6]</code>
 *
 * @return mixed
 * @see amd_generate_string()
 * @since 1.0.0
 */
function amd_generate_string_pattern( $code ){

	return amd_parse_char_between( $code, function( $cmd ){
		return amd_generate_string_command( $cmd );
	} );

}

/**
 * Get avatars base URL
 *
 * @param string $id
 * Image ID
 *
 * @return string
 * @since 1.0.0
 */
function amd_avatar_url( $id ){

	return amd_get_api_url( "?_avatar=$id" );

}

/**
 * Check if current user is admin
 *
 * @return bool
 * @since 1.0.0
 */
function amd_is_admin(){

	return apply_filters( "amd_is_admin", false );

}

/**
 * Print HTML content of cards
 *
 * @param string|callable $filter
 * Filter callable or card type string
 *
 * @return void
 * @since 1.0.0
 */
function amd_dump_cards( $filter = "" ){

	$cards = apply_filters( "amd_get_dashboard_cards", $filter );

	foreach( $cards as $id => $card ){
		$page = $card["page"] ?? "";
		$title = $card["title"] ?? "";
		$content = $card["content"] ?? "";
		if( !empty( $page ) ){
			if( !file_exists( $page ) )
				continue;
			require_once( $page );
		}
		else{
			$title_allowed_tags = amd_allowed_tags_with_attr( "br,span,a" );
			?>
            <div class="col-lg-5">
                <div class="amd-card" id="<?php echo esc_attr( "acc_card-$id" ); ?>">
                    <h3 class="--title-2"><?php echo wp_kses( $title, $title_allowed_tags ); ?></h3>
                    <div class="--content"><?php echo wp_kses( $content, wp_kses_allowed_html( "post" ) ); ?></div>
                </div>
            </div>
			<?php
		}
	}

}

/**
 * Get current theme mode (light, dark, etc.)
 * @return string
 * @since 1.0.0
 */
function amd_get_current_theme_mode(){

	global /** @var AMDCache $amdCache */
	$amdCache;

	$theme = is_user_logged_in() ? amd_get_user_meta( get_current_user_id(), "theme" ) : "";

	if( empty( $theme ) OR !in_array( $theme, apply_filters( "amd_allowed_theme_modes", [ "light", "dark" ] ) ) )
		$theme = $amdCache->getCache( "theme" );

	if( empty( $theme ) OR !in_array( $theme, apply_filters( "amd_allowed_theme_modes", [ "light", "dark" ] ) ) )
		$theme = "light";

	return $theme;

}

/**
 * Get API main URL
 * @return string
 * @since 1.0.0
 */
function amd_get_api_url( $query = "" ){

	$APIPage = intval( amd_get_site_option( "api_page" ) );

	return amd_merge_url_query( get_permalink( $APIPage ), $query );

}

/**
 * Get dashboard URL
 *
 * @param string $query
 * URL parameter query
 *
 * @return mixed|string
 * Dashboard URL
 */
function amd_get_dashboard_url( $query = "" ){

	return amd_merge_url_query( amd_get_dashboard_page(), $query );

}

/**
 * Main header
 * @return void
 * @since 1.0.0
 */
function amd_main_header(){
	do_action( "amd_admin_header" );
	?>
	<?php do_action( "amd_config_script" ); ?>
    <!-- @formatter off -->
    <style>.amd-progress-linear.--card-progress{position:absolute;top:0;left:0;width:100%}.amd-progress-linear{-webkit-appearance:none;-moz-appearance:none;appearance:none;border:0;width:160px;height:4px;vertical-align:middle;color:var(--amd-primary);background-color:rgba(var(--amd-primary-rgb),.12)}.amd-progress-linear::-webkit-progress-bar{background-color:transparent}.amd-progress-linear::-webkit-progress-value{background-color:currentColor;transition:all .2s}.amd-progress-linear::-moz-progress-bar{background-color:currentColor;transition:all .2s}.amd-progress-linear::-ms-fill{border:0;background-color:currentColor;transition:all .2s}.amd-progress-linear:indeterminate{background-size:200% 100%;background-image:linear-gradient(to right,currentColor 16%,transparent 16%),linear-gradient(to right,currentColor 16%,transparent 16%),linear-gradient(to right,currentColor 25%,transparent 25%);animation:matter-progress-linear 1.8s infinite linear}.amd-progress-linear:indeterminate::-webkit-progress-value{background-color:transparent}.amd-progress-linear:indeterminate::-moz-progress-bar{background-color:transparent}.amd-progress-linear:indeterminate::-ms-fill{animation-name:none}@keyframes matter-progress-linear{0%{background-position:32% 0,32% 0,50% 0}2%{background-position:32% 0,32% 0,50% 0}21%{background-position:32% 0,-18% 0,0 0}42%{background-position:32% 0,-68% 0,-27% 0}50%{background-position:32% 0,-93% 0,-46% 0}56%{background-position:32% 0,-118% 0,-68% 0}66%{background-position:-11% 0,-200% 0,-100% 0}71%{background-position:-32% 0,-200% 0,-100% 0}79%{background-position:-54% 0,-242% 0,-100% 0}86%{background-position:-68% 0,-268% 0,-100% 0}100%{background-position:-100% 0,-300% 0,-100% 0}}</style>
    <!-- @formatter on -->
	<?php
}

/**
 * Admin header
 * @return void
 * @since 1.0.0
 */
function amd_admin_head(){

	do_action( "amd_before_admin_head" );

	global /** @var AMDCache $amdCache */
	$amdCache;

	$API_URL = amd_get_api_url();
	$locale = get_locale();
	$amdCache->addStyle( 'admin:fonts', amd_merge_url_query( $API_URL, "stylesheets=_fonts&locale=$locale&screen=admin&ver=1.0.0" ), null );

	$amdCache->dumpStyles( "admin" );
	$amdCache->dumpScript( "admin" );

	$amdCache->dumpStyles( "icon" );
	$amdCache->dumpStyles( "global" );
	$amdCache->dumpScript( "icon" );
	$amdCache->dumpScript( "global" );

    amd_main_header();

	$wizard_dismissed = amd_get_site_option( "wizard_dismissed" ) == "true";
	$auto_generated_pages = amd_get_site_option( "auto_generated_pages" ) == "true";

    $_get = amd_sanitize_get_fields( $_GET );

    if( !empty( $_get["ref"] ) AND $_get["ref"] == "wizard" ){
		amd_set_site_option( "wizard_dismissed", "true" );
		$wizard_dismissed = true;
	}

	if( !$wizard_dismissed AND $auto_generated_pages ){
		$wizard_url = amd_get_wizard_url();
		amd_alert_box( array(
			"id" => "wizard-dismiss",
			"text" => sprintf(
                        esc_html_x( "Hi %s! Thanks for using our plugin, we hope you enjoy using it. You can setup your dashboard simply from %sWizard%s or admin panel.", "Admin", "material-dashboard" ),
                        amd_get_current_user()->firstname, "<a href=\"" . esc_url( $wizard_url ) . "\">", "</a>"
                      )
                      . '<button class="block mt-8-im" id="wizard-dismiss">'
                      . esc_html__( "No thanks", "material-dashboard") . '</button>',
			"type" => "primary",
			"size" => "lg"
		) );
		# @formatter off
        ?><script>$("#wizard-dismiss").click(function(){let $card=$("#amd-box-wizard-dismiss");network.clean();network.put("_dismiss_wizard","");network.on.start=()=>$card.setWaiting();network.on.end=(resp,error) =>{if(!error && resp.success){$card.removeSlow()} else{$card.setWaiting(false)}};network.post();});</script><?php
        # @formatter on
	}
}

/**
 * Match data with string data type(s)<br>
 * Allowed values for <code>$action</code>:
 * <ul>
 * <li>"Any specified value"</li>
 * <li>INT: Only integer, e.g: 93</li>
 * <li>NUMBER: Number in either string or integer forms, e.g: "90", 23</li>
 * <li>STRING: Any string, e.g: "Hello World!"</li>
 * <li>BOOL: Boolean in either string or boolean forms, e.g: true, "false"</li>
 * <li>ARRAY: Any array, e.g: ["a", "b", 3]</li>
 * <li>CALLABLE | FUNCTION: Any callable or function</li>
 * <li>NULL: Null</li>
 * </ul>
 * Example:<br>
 * <code>amd_compile_data_type( 'INT,"Hello, world"', $value )</code><br>
 * <code>$value</code>: 1 (true)<br>
 * <code>$value</code>: 90 (true)<br>
 * <code>$value</code>: "Hello, world" (true)<br>
 * <code>$value</code>: "Hello" (false)<br>
 * <code>$value</code>: true (false)<br>
 * <code>$value</code>: null (false)<br>
 *
 * @param string|callable $action
 * Allowed data type(s) string or callable
 * @param mixed $value
 * Haystack value
 *
 * @return bool|mixed
 * @since 1.0.0
 */
function amd_compile_data_type( $action, $value ){
	if( is_callable( $action ) ){
		return call_user_func( $action, $value );
	}
	else if( is_string( $action ) and strpos( $action, "," ) !== false ){
		$ret = false;
		$action = preg_replace_callback( "/['\"](.*)['\"]/", function( $i ) use ( &$ret, $value ){
			$ret = $i[1] == $value;

			return "";
		}, $action );
		if( $ret )
			return $ret;
		foreach( explode( ",", trim( $action, "," ) ) as $item ){
			$item = trim( $item );
			if( preg_match( "/([\"']).*([\"'])/", $item ) )
				if( trim( $item, "\"'" ) == $value )
					return true;
			if( amd_compile_data_type( $item, $value ) )
				return true;
		}
	}
	else{
		if( preg_match( "/\[((INT)|(NUMBER)|(STRING)|(BOOL)|(ARRAY)|(CALLABLE)|(FUNCTION)|(NULL))]/", $action ) ){
			switch( strtolower( trim( $action, "[]" ) ) ){
				case "int":
					if( is_int( $value ) )
						return true;
					break;
				case "number":
					if( is_numeric( $value ) )
						return true;
					break;
				case "string":
					if( is_string( $value ) )
						return true;
					break;
				case "bool":
					if( is_bool( $value ) or in_array( strtolower( $value ), [ "true", "false" ] ) )
						return true;
					break;
				case "array":
					if( is_array( $value ) )
						return true;
					break;
				case "callable":
				case "function":
					if( is_callable( $value ) )
						return true;
					break;
				case "null":
					if( $value == null )
						return true;
					break;
			}

		}
		else if( preg_match( "/^(['\"])?(INT|NUMBER|STRING|BOOL|ARRAY|CALLABLE|FUNCTION|NULL)(['\"])?$/", $action ) ){
			foreach( explode( ",", $action ) as $item ){
				$item = trim( $item );

				if( amd_compile_data_type( "[$item]", $value ) )
					return true;
			}
		}
		else{
			if( $action == $value )
				return true;
		}
	}

	return false;
}

/**
 * Check if current page is login page
 * @return bool
 * @since 1.0.0
 */
function amd_is_login_page( $auth = null ){

	if( is_page() ){
		$thisPage = get_post();
		if( !empty( $thisPage ) ){
			$loginPage = intval( amd_get_site_option( "login_page" ) );
			if( $loginPage == $thisPage->ID ){
                $_get = amd_sanitize_get_fields( $_GET );
				if( empty( $auth ) ){
					return true;
				}
				else{
					$_auth = $_get["auth"] ?? "";

					return $_auth == $auth;
				}
			}
		}
	}

	return false;

}

/**
 * Check if current page is dashboard page
 * @return bool
 * @since 1.0.0
 */
function amd_is_dashboard_page(){

	if( is_page() ){
		$thisPage = get_post();
		if( !empty( $thisPage ) ){
			$dashboardPage = intval( amd_get_site_option( "dashboard_page" ) );
			if( $dashboardPage == $thisPage->ID )
				return true;
		}
	}

	return false;

}

/**
 * Get login page URL
 * @param bool $safe
 * [Deprecated] this parameter is no longer available since 1.0.4 version
 * @return string
 * @since 1.0.0
 */
function amd_get_login_page( $safe = true ){

	$loginPage = amd_get_site_option( "login_page" );

	$url = get_permalink( $loginPage );

	return !$url ? "" : $url;

}

/**
 * Get dashboard page URL
 *
 * @param bool $safe
 * [Deprecated] this parameter is no longer available since 1.0.4 version
 *
 * @return string
 * @see AMDWarner::getErrors()
 * @since 1.0.0
 */
function amd_get_dashboard_page( $safe = true ){

	$dashboardPage = amd_get_site_option( "dashboard_page" );

	$url = get_permalink( $dashboardPage );

	return !$url ? "" : $url;

}

/**
 * Get custom page URL
 * @return string
 * @since 1.0.4
 */
function amd_get_page_url( $page ){

	$page_id = amd_get_site_option( "{$page}_page" );

	$url = get_permalink( $page_id );

	return !$url ? "" : $url;

}

/**
 * Register new page
 *
 * @param string $id
 * Page ID
 * @param string $title
 * Page title
 * @param string $path
 * Page PHP file path
 * @param string $icon
 * Page icon
 *
 * @return void
 * @see AMDDashboard::registerPage()
 * @sicne 1.0.0
 */
function amd_register_lazy_page( $id, $title, $path, $icon ){

	do_action( "amd_register_lazy_page_simple", $id, $title, $path, $icon );

}

/**
 * Get error from <code>error_codes.json</code> file
 * @return mixed|null
 * @since 1.0.0
 */
function amd_get_errors(){

	global /** @var AMDWarner $amdWarn */
	$amdWarn;

	return $amdWarn->getErrors();

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
 * @uses AMDWarner::getErrorCode()
 */
function amd_get_error_code( $id, $format = "" ){

	global /** @var AMDWarner $amdWarn */
	$amdWarn;

	return $amdWarn->getErrorCode( $id, $format );

}

/**
 * Dashboard page
 * @return void
 * @since 1.0.0
 */
function amd_dump_dashboard(){

	do_action( "amd_dashboard_init" );

	require_once( AMD_DASHBOARD . '/dashboard.php' );

}

/**
 * Login page
 * @return void
 * @since 1.0.0
 */
function amd_dump_login(){

	do_action( "amd_dashboard_init" );

    $_get = amd_sanitize_get_fields( $_GET );

	$auth = sanitize_text_field( $_get["auth"] ?? "" );
	if( empty( $auth ) )
		$auth = "login";

	if( has_action( "amd_auth_$auth" ) )
		do_action( "amd_auth_$auth" );
	else
		do_action( "amd_auth_login" );

}

/**
 * Load API page
 * @return void
 * @since 1.0.0
 */
function amd_load_api(){

	require_once( AMD_API_PATH . '/index.php' );

}

/**
 * Validate hex color code
 *
 * @param $v
 *
 * @return mixed|string
 * @since 1.0.0
 */
function amd__validate_color_code( $v, $def = "" ){
	return preg_match( "/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/", $v ) ? $v : $def;
}

/**
 * Check if site is running on localhost
 *
 * @param bool $ignoreNonDNS
 * Pass false to take non-dns sites (the sites that has no domain and uses server IP as URL) as localhost,
 * otherwise only on 'localhost' domain returns true
 *
 * @return bool|string
 * @since 1.0.0
 */
function amd_is_localhost( $ignoreNonDNS = false ){

	# If WordPress is loaded by websocket or browser headers missing
	if( empty( $_SERVER["HTTP_HOST"] ) )
		return "";

	if( amd_replace_url( "%domain%" ) == "localhost" )
		return true;
	else if( amd_validate( sanitize_text_field( $_SERVER['HTTP_HOST'] ), "/^(\d{1,3}.?){4}$/" ) AND !$ignoreNonDNS )
		return true;

	return false;

}

/**
 * Get date based on date-mode (e.g: Jalali, Gregorian)
 *
 * @param $format
 * Date format
 * @param $time
 * Unix timestamp
 *
 * @return array|false|string|string[]
 * @since 1.0.0
 */
function amd_true_date( $format, $time = null ){

	global /** @var AMDCalendar $amdCal */
	$amdCal;

	return $amdCal->realDate( $format, $time );

}

/**
 * Get simple user (AMDUser) object
 *
 * @param int $uid
 * User ID. Set it to null to get current user
 *
 * @return AMDUser
 * AMDUser on success
 * @since 1.0.0
 */
function amd_simple_user( $uid = null ){

	if( empty( $uid ) ){
		if( !is_user_logged_in() )
			return null;
		$uid = wp_get_current_user()->ID;
	}

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	return $amdSilu->create( $uid );

}

/**
 * Create plugin pages like "Dashboard" page and "API" page
 *
 * @param string $page
 * Page ID, e.g: "api", "dashboard", "login", etc.
 * @param bool $confirm
 * Confirm for create new page if page already exists
 * @param bool $confirmReplace
 * Confirm for use existing page if page already exists
 *
 * @return array
 * @since 1.0.0
 */
function amd_make_pages( $page, $confirm = false, $confirmReplace = false ){

    $allowed_pages = apply_filters( "amd_get_allowed_pages", [ "login", "dashboard", "api" ] );

	/**
	 * Change allowed pages with custom filter
     * @since 1.0.4
	 */
    $allowed_pages = apply_filters( "amd_custom_allowed_pages", $allowed_pages );

	if( !in_array( $page, $allowed_pages ) )
		return array(
			"success" => false,
			"data" => [ "msg" => esc_html__( "Failed", "material-dashboard" ) ]
		);

	if( !$confirm OR $confirmReplace ){
		$posts = get_posts( [ "post_type" => "page", "name" => $page ] );

		if( count( $posts ) > 0 ){

			$site_page = intval( amd_get_site_option( "{$page}_page" ) );

			if( empty( $site_page ) ){

				$postID = $posts[0]->ID ?? null;

				if( !empty( $postID ) AND $confirmReplace ){

					amd_set_site_option( "{$page}_page", $postID );

					$postURL = get_permalink( $postID );

					return array(
						"success" => true,
						"data" => [
							"msg" => esc_html__( "Page was already exists and new page has replaced", "material-dashboard" ),
							"page_id" => $postID,
							"page_title" => $page,
							"url" => $postURL
						]
					);

				}

			}

			return array(
				"success" => false,
				"data" => [
					"msg" => esc_html__( "This page already registered, do you want to make another page?", "material-dashboard" ),
					"_confirm" => ""
				]
			);

		}

	}

	$result = wp_insert_post( array(
		"post_title" => ucfirst( $page ),
		/**
		 * Get custom shortcode for page
         * @since 1.0.4
		 */
		"post_content" => apply_filters( "amd_custom_page_shortcode", "[amd-$page]", $page ),
		"post_status" => "publish",
		"post_type" => "page",
	) );

	if( !$result or is_wp_error( $result ) )
		return array(
			"success" => false,
			"data" => [ "msg" => esc_html__( "Failed", "material-dashboard" ) ]
		);

	update_post_meta( $result, "amd_post", "true" );

	amd_set_site_option( "{$page}_page", $result );

	$postURL = get_permalink( $result );

	return array(
		"success" => true,
		"data" => [
			"msg" => esc_html__( "Page has been created", "material-dashboard" ),
			"page_id" => $result,
			"page_title" => $page,
			"url" => sanitize_url( $postURL )
		]
	);

}

/**
 * Ask user to create required pages like API page if they don't exist
 * @return bool
 * @since 1.0.0
 */
function amd_api_page_required( $autoGenerate = true ){

    global /** @var AMD_DB $amdDB */
	$amdDB;

	$APIPage = intval( amd_get_site_option( "api_page" ) );
	$_apiPost = get_post( $APIPage );
	$API_PAGE_OK = !empty( $_apiPost ) ? $_apiPost->post_status == "publish" : null;

    if( !$API_PAGE_OK )
        amd_set_site_option( "api_page", "" );

	$dashboardPage = intval( amd_get_site_option( "dashboard_page" ) );
	$_dashboardPost = get_post( $dashboardPage );
	$DASHBOARD_PAGE_OK = !empty( $_dashboardPost ) ? $_dashboardPost->post_status == "publish" : null;

	$loginPage = intval( amd_get_site_option( "login_page" ) );
	$_loginPost = get_post( $loginPage );
	$LOGIN_PAGE_OK = !empty( $_loginPost ) ? $_loginPost->post_status == "publish" : null;

	# Create required pages
	if( $autoGenerate ){
		$pages = [];
		$auto_generated_pages = amd_get_site_option( "auto_generated_pages" ) == "true";
		if( !$API_PAGE_OK )
			$pages[] = "api";
		if( !$auto_generated_pages ){
			if( !$DASHBOARD_PAGE_OK )
				$pages[] = "dashboard";
			if( !$LOGIN_PAGE_OK )
				$pages[] = "login";
			amd_set_site_option( "auto_generated_pages", "true" );
		}
		if( !empty( $pages ) ){
			foreach( $pages as $page )
				amd_make_pages( $page, false, true );
			?>
            <p><?php esc_html_e( "Please wait", "material-dashboard" ); ?>...</p>
            <script>location.reload()</script>
            <?php
			exit();
		}
	}

	if( !$amdDB->isInstalled() ){
		?>
        <style>
            .hello-pop {
                --amd-font-family: 'shabnam', 'roboto', sans-serief;
                --amd-wrapper-bg: #efefef;
                --amd-text-color: #414141;
                --amd-text-color-rgb: 65, 65, 65;
                --amd-pointer: pointer;
            }
        </style>
        <script>
            Hello.fire({
                title: `<?php esc_html_e( "Database", "material-dashboard" ); ?>`,
                text: `<?php esc_html_e( "Database is not installed correctly or needs to be updated, do you want to do it now?", "material-dashboard" ); ?>`,
                confirmButton: `<?php esc_html_e( "Yes", "material-dashboard" ); ?>`,
                cancelButton: `<?php esc_html_e( "Not now", "material-dashboard" ); ?>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                onCancel: () => location.href = `<?php echo admin_url(); ?>`,
                onConfirm: () => {
                    Hello.closeAll();
                    Hello.fire({
                        title: `<?php esc_html_e( "Please wait", "material-dashboard" ); ?>`,
                        text: `<?php esc_html_e( "Updating database", "material-dashboard" ); ?>...`,
                        confirmButton: false,
                        cancelButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false
                    });
                    let failed = text => {
                        Hello.closeAll();
                        Hello.fire({
                            title: `<?php esc_html_e( "Failed", "material-dashboard" ); ?>`,
                            text: text,
                            confirmButton: `<?php esc_html_e( "Okay", "material-dashboard" ); ?>`,
                            cancelButton: false,
                        });
                    }
                    send_ajax({
                            action: amd_conf.ajax.private,
                            repair_db: true
                        },
                        resp => {
                            if(resp.success)
                                location.reload();
                            else
                                failed(resp.data.msg);
                        },
                        xhr => {
                            failed(_t("error"));
                            console.log(xhr)
                        }
                    );
                }
            });
        </script>
		<?php
		return false;
	}
	else if( empty( $APIPage ) OR empty( amd_get_api_url() ) OR !$API_PAGE_OK ){
		?>
        <style>
            .hello-pop {
                --amd-font-family: 'shabnam', 'roboto', sans-serief;
                --amd-wrapper-bg: #efefef;
                --amd-text-color: #414141;
                --amd-text-color-rgb: 65, 65, 65;
                --amd-pointer: pointer;
            }
        </style>
        <script>
            Hello.fire({
                title: `<?php esc_html_e( "API page", "material-dashboard" ); ?>`,
                text: `<?php esc_html_e( "API page is required for settings and styles to be loaded, do you allow to create it automatically?", "material-dashboard" ); ?>`,
                confirmButton: `<?php esc_html_e( "Yes", "material-dashboard" ); ?>`,
                cancelButton: `<?php esc_html_e( "Not now", "material-dashboard" ); ?>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                onCancel: () => location.href = `<?php echo admin_url(); ?>`,
                onConfirm: () => {
                    Hello.closeAll();
                    Hello.fire({
                        title: `<?php esc_html_e( "Please wait", "material-dashboard" ); ?>`,
                        text: `<?php esc_html_e( "Making API page", "material-dashboard" ); ?>...`,
                        confirmButton: false,
                        cancelButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false
                    });
                    let failed = text => {
                        Hello.closeAll();
                        Hello.fire({
                            title: `<?php esc_html_e( "Failed", "material-dashboard" ); ?>`,
                            text: text,
                            confirmButton: `<?php esc_html_e( "Okay", "material-dashboard" ); ?>`,
                            cancelButton: false,
                        });
                    }
                    send_ajax({
                            action: amd_conf.ajax.private,
                            make_page: "api",
                            _confirm_replace: true
                        },
                        resp => {
                            if(resp.success)
                                location.reload();
                            else
                                failed(resp.data.msg);
                        },
                        xhr => {
                            failed();
                            console.log(xhr)
                        }
                    );
                }
            });
        </script>
		<?php
		return false;
	}

	return true;

}

/**
 * Check if registration is allowed in this site
 * @return bool
 * @since 1.0.0
 */
function amd_can_users_register(){

	return get_option( "users_can_register" ) == 1;

}

/**
 * Make alert box
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
 * @see AMDWarner::box()
 * @since 1.0.0
 */
function amd_alert_box( $data ){

	global /** @var AMDWarner $amdWarn */
	$amdWarn;

	$amdWarn->box( $data );

}

/**
 * Convert HEX color code to RGB
 *
 * @param string $color
 * HEX color code with "#", e.g: "#fff", "#12a8dc"
 * @param null|string $format
 * Desired format for return value, "r" is for red color, "g" for green and "b" for blue,
 * e.g: "r, g, b" -> "65, 245, 80". Leave it empty to get RGB array
 *
 * @return array|mixed|string|string[]
 * @since 1.0.0
 */
function amd_hex_to_rgb( $color, $format = null ){

	$hex = str_replace( '#', '', $color );
	if( strlen( $hex ) == 3 ):
		$rgbArray['r'] = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$rgbArray['g'] = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$rgbArray['b'] = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	else:
		$rgbArray['r'] = hexdec( substr( $hex, 0, 2 ) );
		$rgbArray['g'] = hexdec( substr( $hex, 2, 2 ) );
		$rgbArray['b'] = hexdec( substr( $hex, 4, 2 ) );
	endif;

	if( empty( $format ) )
		return $rgbArray;

	$out = str_replace( "r", $rgbArray["r"], $format );
	$out = str_replace( "g", $rgbArray["g"], $out );
	$out = str_replace( "b", $rgbArray["b"], $out );

	return $out;

}

/**
 * Check if post exists and it's published
 *
 * @param int $postID
 * Post ID
 *
 * @return bool
 * @since 1.0.0
 */
function amd_post_available( $postID ){

	$post = get_post( $postID );

	if( !$post )
		return false;

	return $post->post_status == "publish";

}

/**
 * Set defaults value
 *
 * @param string $key
 * Item key
 * @param mixed $value
 * Item value
 *
 * @return void
 * @since 1.0.0
 */
function amd_set_default( $key, $value ){

	global /** @var AMDCache $amdCache */
	$amdCache;

	$amdCache->setDefault( $key, $value );

}

/**
 * Get defaults value
 *
 * @param string $key
 * Item key
 * @param mixed $default
 * Return this value if item doesn't exist
 *
 * @return mixed|string
 * @sicne 1.0.0
 */
function amd_get_default( $key, $default = "" ){

	global /** @var AMDCache $amdCache */
	$amdCache;

	return $amdCache->getDefault( $key, $default );

}

/**
 * Get logo directory or logo file
 *
 * @param string $name
 * Logo file name, leave it empty to get logo directory path
 *
 * @return string
 * @since 1.0.0
 */
function amd_get_logo( $name = "" ){
	return AMD_IMG . "/logo" . ( !empty( $name ) ? "/" . sanitize_file_name( $name ) : "" );
}

/**
 * Get login page dashboard logo
 * @return array
 * @since 1.0.0
 */
function amd_get_dash_logo(){
	$dashLogo = amd_get_site_option( "dash_logo", amd_get_default( "dash_logo" ) );
	$dashLogoURL = $dashLogo;
	$dashLogoVal = $dashLogo;
	if( is_numeric( $dashLogo ) ){
		$dashLogoURL = wp_get_attachment_url( $dashLogo );
		$dashLogoVal = $dashLogoURL;
	}

	return apply_filters( "amd_dashboard_logo", array(
		"url" => $dashLogoURL,
		"id" => $dashLogoVal,
		"logo" => $dashLogo
	) );
}

/**
 * Get regions and country codes
 * @return array
 * @since 1.0.0
 */
function amd_get_regions(){

	$country_codes = amd_get_site_option( "country_codes" );
	$country_codes = amd_unescape_json( $country_codes );
	$phoneRegions = apply_filters( "amd_country_codes", [] );

	if( !is_array( $country_codes ) AND !is_object( $country_codes ) )
		$country_codes = [];

	$selectedRegions = [];
	foreach( $country_codes as $key => $value ){
		$selectedRegions[$key] = true;
		if( empty( $phoneRegions[$key] ) )
			$phoneRegions[$key] = (array) $value;
	}


	$first_cc = "";
	$countries_cc_html = "";
	$cc_count = 0;
	$format = "";
    $out_regions = [];
	foreach( $phoneRegions as $cc => $data ){
		if( empty( $selectedRegions[$cc] ) )
			continue;
		$digit = sanitize_text_field( $data["digit"] ?? "" );
		$format = sanitize_text_field( $data["format"] ?? "" );
		if( empty( $digit ) )
			continue;
		if( empty( $first_cc ) )
			$first_cc = $digit;

		$region = sanitize_text_field( $data["name"] ?? "" );
		if( !empty( $region ) )
			$region = " - " . $region;
        ob_start();
        # @formatter off
		?>
<a href="javascript: void(0)" class="btn btn-text" data-pick-cc="<?php echo esc_attr( $digit ); ?>" dir="auto">
    <span>
        <span>+<?php echo esc_html( $digit . " " . $region ); ?></span>
    </span>
</a>
        <?php
        # @formatter on
		$countries_cc_html = ob_get_clean();
        $out_regions[$cc] = $data;
		$cc_count++;
	}

	return array(
		"all_regions" => $phoneRegions,
		"regions" => $out_regions,
		"count" => $cc_count,
		"first" => $first_cc,
		"format" => $format,
		"html" => $countries_cc_html
	);

}

/**
 * Validate phone number with specific format and country code
 *
 * @param string $phone
 * Phone number
 * @param string $cc
 * Country code digit, e.g: 98, 1
 * @param string $format
 * Format of phone number, e.g: "XXX-XXX-XXXX"
 *
 * @return bool
 * Whether the phone number is correct or not
 * @since 1.0.0
 */
function amd_validate_phone_format( $phone, $cc, $format ){

	$cc = str_replace( "+", "", $cc );

	$regex = $format;
	$regex = str_replace( "-", "\\s?", $regex );
	$regex = str_replace( "X", "[0-9]", $regex );
	$regex = "/^\+$cc\\s?" . "$regex\$/";

	return (bool) preg_match( $regex, $phone );

}

/**
 * Check if phone number is correct.
 * <br><b>Note: Only the phone numbers and country codes that are registered/enabled in settings
 * are known as a valid phone number, others will be detected as invalid ones</b>
 *
 * @param string $phone
 *
 * @return bool
 * @since 1.0.0
 */
function amd_validate_phone_number( $phone ){

	$phoneRegions = amd_get_regions()["regions"];

	foreach( $phoneRegions as $cc => $data ){

		$digit = $data["digit"] ?? "";
		$format = $data["format"] ?? "";

		if( $digit == "98" AND preg_match( "/^0[0-9]{10}$/", $phone ) )
			return true;

		if( amd_validate_phone_format( $phone, $digit, $format ) )
			return true;

	}

	return false;

}

/**
 * Format phone number for saving
 * @param string $phone
 * Phone number
 *
 * @return string
 * @since 1.0.1
 */
function amd_apply_phone_format( $phone ){

    $phone = str_replace( " ", "", $phone );

    return apply_filters( "amd_phone_format", $phone );

}

/**
 * Check if phone number is used by a user or not
 *
 * @param string $phone
 * Phone number
 *
 * @return bool
 * @since 1.0.0
 */
function amd_phone_exists( $phone ){

	global /** @var AMDSilu $amdSilu */
	$amdSilu;

	$res = $amdSilu->getUserByMeta( "phone", $phone, false );

	return is_countable( $res ) ? count( $res ) > 0 : false;

}

/**
 * Send JSON response
 *
 * @param mixed $data
 * Response data
 * @param bool $is_success
 * Whether the request was successful or not
 *
 * @return void
 * @since 1.0.0
 */
function amd_send_api_response( $data, $is_success ){

	$msg = $data["msg"] ?? [];
	$d = $data["data"] ?? [];

	header( "Content-Type: application/json; charset=UTF-8" );

	# Double-checking
	if( !is_bool( $is_success ) )
		$is_success = (bool) $is_success;

	$_d = array(
		"success" => $is_success,
		"error" => !$is_success,
		"msg" => !empty( $msg ) ? $msg : esc_html__( "An error has occurred", "material-dashboard" ),
		"data" => $data
	);

	if( !$is_success ){
		$err_id = $data["error_id"] ?? "error";
		$error = amd_get_error_code( $err_id );
		$_d = array_merge( $_d, array(
			"error_id" => $err_id,
			"error_code" => $error,
		) );
	}

	echo json_encode( $_d );

	exit();

}

/**
 * Send success JSON response
 *
 * @param mixed $data
 * Response data
 *
 * @return void
 * @since 1.0.0
 */
function amd_send_api_success( $data ){

	amd_send_api_response( $data, true );

}

/**
 * Send error JSON response
 *
 * @param mixed $data
 * Response data
 *
 * @return void
 * @since 1.0.0
 */
function amd_send_api_error( $data ){

	amd_send_api_response( $data, false );

}

/**
 * Generate action URL without adding temp
 *
 * @param string $name
 * Action name
 * @param string $scope
 * Action scope
 *
 * @return mixed|string
 * @since 1.0.0
 */
function amd_make_action_url_without_temp( $name, $scope = "" ){

	return amd_merge_url_query( amd_get_login_page(), "&auth=action&action=$name" . ( !empty( $scope ) ? "&scope=$scope" : "" ) );

}

/**
 * Generate action URL with adding temp
 *
 * @param string $name
 * Action name
 * @param string $scope
 * Action scope
 * @param string $action
 * Action
 * @param int $expire
 * Action expiration time (in seconds)
 *
 * @return mixed|string
 * @since 1.0.0
 */
function amd_make_action_url( $name, $scope, $action, $expire = 3600 ){

	amd_set_temp( $name . ( !empty( $scope ) ? ":$scope" : "" ), $action, $expire );

	return amd_make_action_url_without_temp( $name, $scope );

}

/**
 * Guess file extension from mime-type
 *
 * @param string $mime
 * Mime type, e.g: image/png, application/json
 *
 * @return false|string
 * @since 1.0.0
 */
function amd_guess_extension_from_mime_type( $mime ){

	$mime_map = apply_filters( "amd_mime_types", [] );

	return $mime_map[$mime] ?? false;
}

/**
 * Guess mime type from extension. e.g: "png" -> "image/png"
 *
 * @param string $extension
 * File extension
 *
 * @return false|int|string
 * @since 1.0.0
 */
function amd_guess_mime_type_from_extension( $extension ){

	# Ignore case
	$extension = strtolower( $extension );

	# 1: Remove redundant dots
	# 2: JPG & JPEG has the same mime type, but we can only have one item in `$mime_map` array
	$extension = str_replace( [ ".", "jpg" ], [ "", "jpeg" ], $extension );

	$mime_map = apply_filters( "amd_mime_types", [] );
	$mime_map_reverse = array_flip( $mime_map );

	return $mime_map_reverse[$extension] ?? false;
}

/**
 * Get WordPress allowed file extensions
 * @return array
 * Allowed file extensions array
 * @since 1.0.0
 */
function amd_get_wp_allowed_extensions(){

    $ext = [];

    foreach( get_allowed_mime_types() as $extensions => $mime )
	    $ext = array_merge( $ext, explode( "|", $extensions ) );

    return $ext;

}

/**
 * Allowed HTML tags with attributes for some dashboard contents
 *
 * @param string $tags
 * Needle tags. Separate with comma for multiple tags, e.g: "span,a"
 * <br>Available tags:
 * <ul>
 * <li>br</li>
 * <li>p</li>
 * <li>span</li>
 * <li>p</li>
 * <li>div</li>
 * <li>img</li>
 * <li>i</li>
 * <li>b</li>
 * <li>strong</li>
 * <li>a</li>
 * <li>u</li>
 * <li>small</li>
 * <li>em</li>
 * <li>button</li>
 * </ul>
 *
 * @return array
 * Data array for {@see wp_kses()}
 * @since 1.0.0
 */
function amd_allowed_tags_with_attr( $tags ){

    $attr_style_data = ["color", "background-color"];

    $default_attributes = array(
	    "data-*" => true,
	    "class" => true,
	    "id" => true,
	    "style" => $attr_style_data,
	    "title" => true
    );

    $tags_data = array(
	    "br" => array(),
        "span" => array(
		    "data-*" => true,
		    "class" => true,
		    "id" => true,
		    "dir" => true,
		    "style" => $attr_style_data,
		    "title" => true
	    ),
        "p" => array(
		    "data-*" => true,
		    "class" => true,
		    "id" => true,
		    "dir" => true,
		    "style" => $attr_style_data,
		    "title" => true
	    ),
        "div" => array(
		    "data-*" => true,
		    "class" => true,
		    "id" => true,
		    "dir" => true,
		    "style" => $attr_style_data,
		    "title" => true
	    ),
        "img" => array(
		    "data-*" => true,
		    "class" => true,
		    "id" => true,
		    "src" => true,
		    "alt" => true,
		    "loading" => true,
		    "style" => $attr_style_data,
		    "title" => true
	    ),
        "i" => $default_attributes,
        "b" => $default_attributes,
        "u" => $default_attributes,
        "strong" => $default_attributes,
        "small" => $default_attributes,
        "ul" => $default_attributes,
        "ol" => $default_attributes,
        "li" => $default_attributes,
        "em" => $default_attributes,
	    "a" => array(
		    "href" => true,
		    "rel" => true,
		    "rev" => true,
		    "name" => true,
		    "target" => true,
		    "data-*" => true,
		    "class" => true,
		    "id" => true,
		    "dir" => true,
		    "style" => $attr_style_data,
		    "title" => true
	    ),
        "button" => array(
		    "data-*" => true,
		    "type" => true,
		    "class" => true,
		    "id" => true,
		    "style" => $attr_style_data,
		    "title" => true
	    )
    );

    $default_tags = apply_filters( "amd_default_html_tags_with_attr", $tags_data );

    $data = [];

    foreach( explode( ",", $tags ) as $tag ){

        $tag = trim( $tag );

        if( isset( $default_tags[$tag] ) )
	        $data[$tag] = $default_tags[$tag];

    }

    return $data;

}

/**
 * Get avatars directory path
 * @return string
 * @since 1.0.0
 */
function amd_get_avatars_path(){

	$path = AMD_UPLOAD_PATH . "/avatars";

	if( !file_exists( $path ) )
		mkdir( $path );

	return $path;

}

/**
 * Delete directory with its content recursively
 *
 * @param string $dir
 * Directory name
 * @param bool $recursive
 * Whether to delete directory with its content or skip if directory contains other files and folders
 *
 * @return bool
 * True on success, false on failure
 * @since 1.0.0
 */
function amd_delete_directory( $dir, $recursive = false ){

	if( !file_exists( $dir ) )
		return false;

	if( !is_dir( $dir ) )
		return unlink( $dir );

	$old_mask = umask( 0 );
	if( $recursive ){
		foreach( glob( "$dir/*", GLOB_BRACE ) as $file ){
			if( is_dir( $file ) ){
				amd_delete_directory( $file, true );
				rmdir( $file );
			}
			else
				unlink( $file );
		}

		return true;
	}

	$s = rmdir( $dir );

	umask( $old_mask );

	return $s;

}

/**
 * Add To-do item
 *
 * @param string $key
 * To-do key. e.g: "user_1"
 * @param string $value
 * To-do text. e.g: "Contact customer #12"
 * @param string $status
 * To-do status. e.g: "pending", "done", "undone"
 * @param string $salt
 * To-do salt for encoding
 * @param array $meta
 * To-do meta-data. e.g: ["date" => "2023-01-02"]
 * @param bool $encode
 * Whether to encode $value or not.
 * <br><b>Note: You always have to use encoded value for to-do lists, but if your to-do text is already encoded you can pass false to skip encoding</b>
 *
 * @return false|int
 * Inserted row ID on success, false on failed
 * @since 1.0.0
 */
function amd_add_todo( $key, $value, $status, $salt, $meta = [], $encode = true ){

	if( empty( $meta ) )
		$meta = [ "time" => time() ];

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->addTodo( $key, $value, $status, $salt, $meta, $encode );

}

/**
 * Update to-do item/list
 *
 * @param array $data
 * Item or list data to update. e.g: ["todo_key" => "user_1", "todo_value" => "Hello world", ...]
 * @param array $where
 * Item or list selector. e.g: ["id" => 12]
 * @param string $salt
 * Salt for encryption
 *
 * @return bool
 * True on success, false on failure
 * @since 1.0.0
 */
function amd_update_todo( $data, $where, $salt = "" ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->updateTodo( $data, $where, $salt );

}

/**
 * Get to-do list
 *
 * @param array $filters
 * Filters array
 * @param bool $single
 * Whether to get single todo_value string or full row results
 *
 * @return array|object|stdClass|string|null
 * @see AMD_DB::makeFilters()
 */
function amd_get_todo_list( $filters, $single = false ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->getTodoList( $filters, $single );

}

/**
 * Delete to-do items or list
 *
 * @param array $where
 *
 * @return bool
 * @since 1.0.0
 */
function amd_delete_todo_list( $where ){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	return $amdDB->deleteTodoList( $where );

}

/**
 * Edit to-do item
 *
 * @param int $todo_id
 * To-do ID
 * @param array $data
 * To-do data to update
 * @param string $salt
 * Salt for encryption
 *
 * @return bool
 * @since 1.0.0
 */
function amd_edit_todo( $todo_id, $data, $salt="" ){

	if( !amd_is_todo_for_user( $todo_id, get_current_user_id() ) )
		return false;

	return amd_update_todo( $data, [ "id" => $todo_id ], $salt );

}

/**
 * Check if to-do item is owned by user
 *
 * @param int $todo_id
 * To-do ID
 * @param int $user_id
 * User ID
 *
 * @return bool
 * @since 1.0.0
 */
function amd_is_todo_for_user( $todo_id, $user_id ){

	$user = amd_get_user_by( "ID", $user_id );

	if( !$user )
		return false;

	$todo = amd_get_todo_list( [ "id" => $todo_id ] );

	if( !count( $todo ) )
		return false;

	/**
	 * Check if user is allowed to change to-do item
     * @since 1.0.4
	 */
	if( apply_filters( "amd_ext_todo_task_belongs_to", $todo_id, $user_id ) === true )
        return true;

	return $todo[0]->todo_key == $user->serial;

}

/**
 * Encrypt AES
 *
 * @param string $string
 * @param string $salt
 *
 * @return false|string
 * @since 1.0.0
 */
function amd_encrypt_aes( $string, $salt ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	return $amdWall->encryptAES( $string, $salt );

}

/**
 * Decrypt AES
 *
 * @param string $hash
 * @param string $salt
 *
 * @return false|string
 * @since 1.0.0
 */
function amd_decrypt_aes( $hash, $salt ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	return $amdWall->decryptAES( $hash, $salt );

}

/**
 * Serialize string
 *
 * @param string $string
 * Input string
 *
 * @return string
 * @since 1.0.1
 */
function amd_serialize( $string ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	return $amdWall->serialize( $string );

}

/**
 * Deserialize string
 *
 * @param string $string
 * Serialized string
 *
 * @return string
 * @since 1.0.1
 */
function amd_deserialize( $string ){

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	return $amdWall->deserialize( $string );

}

/**
 * Get all users (AMDUser object)
 * @return array
 * @since 1.0.0
 */
function amd_get_all_users(){

	global /** @var AMD_DB $amdDB */
	$amdDB;

	$table = $amdDB->getTable( "wp_users" );

	$res = $amdDB->safeQuery( $table, "SELECT * FROM `%{TABLE}%`" );

	$users = [];
	foreach( $res as $user ){

		$u = amd_get_user_by( "ID", $user->ID ?? 0 );

		if( $u )
			$users[$u->ID] = $u;

	}

	return $users;

}

/**
 * Get recently registered users (AMDUser object)
 * @return array
 * @since 1.0.0
 */
function amd_get_newest_users(){

	$users = amd_get_all_users();
	$offset = apply_filters( "amd_new_user_offset", 3 );

	$out = [];
	/** @var AMDUser $user */
	foreach( $users as $user ){
		$r = $user->getRegistration()["diff"];
		if( $r <= $offset )
			$out[$user->ID] = $user;
	}

	return $out;

}

/**
 * Get current online users
 * @return array
 * Array containing `AMDUser` object
 * @since 1.0.0
 */
function amd_get_online_users(){

	$users = amd_get_all_users();

	$out = [];
	/** @var AMDUser $user */
	foreach( $users as $user ){
		if( $user->isOnline() )
			$out[$user->ID] = $user;
	}

	return $out;

}

/**
 * Get date difference and passed days
 *
 * @param string $earlier
 * Earlier date
 * @param string $later
 * Later date
 *
 * @return int|null
 * Date difference number on success, null on failure
 * @since 1.0.0
 */
function amd_get_date_diff( $earlier, $later = 'now' ){

	if( is_numeric( $earlier ) )
		$earlier = date( "Y-m-d H:i:s", $earlier );
	if( is_numeric( $later ) )
		$later = date( "Y-m-d H:i:s", $later );

	try{
		$_later = new DateTime( $later );
		$_earlier = new DateTime( $earlier );

		return intval( abs( $_later->diff( $_earlier )->format( "%a" ) ) );
	} catch( Exception $e ){
		return null;
	}
}

/**
 * Check if login attempts reached
 *
 * @param bool $getData
 * Whether to get data or check if login attempts reached
 * <br>Data format:
 * <code>
 * array(
 *   "max_attempts" => (int),
 *   "attempts" => (int),
 *   "expire" => (int),
 *   "reached" => (bool),
 *   "ip_address" => (string)
 * )
 * </code>
 *
 * @return array|bool|int[]
 * @since 1.0.0
 */
function amd_login_attempts_reached( $getData = false ){

	$login_attempts = apply_filters( "amd_login_too_many_attempts_limit", 10 );

	global /** @var AMDFirewall $amdWall */
	$amdWall;

	$ip = $amdWall->getActualIP();

	if( $login_attempts <= 0 )
		return $getData ? array(
			"max_attempts" => 0,
			"attempts" => 0,
			"expire" => 0,
			"reached" => false,
			"ip_address" => $ip
		) : false;

	$t = amd_get_temp( "login_attempt_$ip", false );
	if( empty( $t ) ){
		$attempts = 0;
		$login_attempts = 0;
		$expire = 0;
	}
	else{
		$attempts = $t->temp_value ?? 0;
		$expire = $t->expire ?? 0;
	}

	$reached = false;
	if( $login_attempts > 0 )
		$reached = $attempts >= $login_attempts;

	return $getData ? array(
		"max_attempts" => $login_attempts,
		"attempts" => $attempts,
		"expire" => $expire,
		"reached" => $reached,
		"ip_address" => $ip
	) : $reached;

}

/**
 * Check if plugin is not used before and database is not initialized
 * @return bool
 * @since 1.0.0
 */
function amd_is_first_use(){

	$m = amd_get_site_option( "initialized" );

	return empty( $m );

}

/**
 * Get wizard page URL
 * @return string
 * @since 1.0.0
 */
function amd_get_wizard_url(){
	return (string) admin_url( "admin.php?page=amd-wizard" );
}

/**
 * Get elapsed time from $time to now
 *
 * @param string|int $time
 * Timestamps
 *
 * @return array
 * @throws Exception
 * @since 1.0.0
 */
function amd_get_time_pass( $time ){

	$passedTime = time() - $time;
	$passedDays = amd_get_date_diff( date( "Y-m-d", $time ) );

	$ii = $passedTime;
	$mm = floor( $ii / 60 );
	$hh = floor( $ii / 3600 );
	$dd = floor( $ii / 86400 );
	$m = floor( $ii / ( 86400 * 30 ) );
	$yy = floor( $ii / ( 86400 * 365 ) );

	$data = array(
		'each' => array(
			'seconds' => $ii,
			'minutes' => $mm,
			'hours' => $hh,
			'days' => $dd,
			'months' => $m,
			'years' => $yy
		),
		'passed' => array(
			'days' => $passedDays
		)
	);

	return $data;

}

/**
 * Get passed time with translation
 *
 * @param string|int $time
 * Timestamp
 * @param bool $ago
 * Whether to get ago format of date or not, e.g: "12 days ago"
 *
 * @return string|null
 * @since 1.0.0
 */
function amd_get_time_pass_str( $time, $ago = false ){

	$last = amd_get_time_pass( $time );

	$days = $last['passed']['days'];

	if( $days >= 365 ){
		// Year
		$yy = floor( $days / 365 );
		if( $ago )
			return sprintf( _n( "%s year ago", "%s years ago", $yy, "material-dashboard" ), $yy );
		else
			return sprintf( _n( "%s year", "%s years", $yy, "material-dashboard" ), $yy );
	}
	else if( $days < 365 && $days >= 30 ){
		// Month
		$mm = floor( $days / 30 );
		if( $ago )
			return sprintf( _n( "%s month ago", "%s months ago", $mm, "material-dashboard" ), $mm );
		else
			return sprintf( _n( "%s month", "%s months", $mm, "material-dashboard" ), $mm );
	}
	else if( $days < 30 && $days >= 7 ){
		// Week
		$ww = floor( $days / 7 );
		if( $ago )
			return sprintf( _n( "%s week ago", "%s weeks ago", $ww, "material-dashboard" ), $ww );
		else
			return sprintf( _n( "%s week", "%s weeks", $ww, "material-dashboard" ), $ww );
	}
	else if( $days < 7 && $days > 0 ){
		// Day
		$dd = floor( $days );
		if( $ago )
			return sprintf( _n( "%s day ago", "%s days ago", $dd, "material-dashboard" ), $dd );
		else
			return sprintf( _n( "%s day", "%s days", $dd, "material-dashboard" ), $dd );
	}
	else if( $days <= 0 ){
		return esc_html__( "Today", "material-dashboard" );
	}

	return "";

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
function amd_load_template( $template, $extra = null ){

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	return $amdLoader->loadTemplate( $template, $extra );

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
function amd_template_exists( $template ){

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	return $amdLoader->templateExists( $template );

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
function amd_load_part( $part, $extra = null ){

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	return $amdLoader->loadPart( $part, $extra );

}

/**
 * Check if theme part is available
 *
 * @param string $part
 * Part file name (without .php extension)
 *
 * @return bool
 * @since 1.0.4
 */
function amd_part_exist( $part ){

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	return $amdLoader->partExist( $part );

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
function amd_dump_single_card( $card, $dumpInvalids = false ){

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	$amdLoader->dumpSingleCard( $card, $dumpInvalids );

}

/**
 * Check users filter
 *
 * @param string $filters
 * Filters
 *
 * @return bool
 * @since 1.0.0
 */
function amd_check_user_filters( $filters ){

	if( is_string( $filters ) )
		$f = explode( ",", trim( $filters, "," ) );
	else if( is_array( $filters ) )
		$f = $filters;
	else
		return false;

	if( empty( $f ) OR in_array( "*", $f ) )
		return true;

	foreach( $f as $item ){

		if( apply_filters( "amd_user_filter_$item", false ) === true )
			return true;

		if( strpos( $item, ":" ) !== false ){
			$exp = explode( ":", $item );
			$context = $exp[0];
			$value = $exp[1] ?? "";
			if( apply_filters( "amd_user_filter_$context", $value ) === true )
				return true;
			if( apply_filters( "amd_user_filter_$context.$value", false ) === true )
				return true;
		}
		else{
			if( $u = amd_guess_user( $item )["user"] ?? null )
				return $u->ID == get_current_user_id();
		}

	}

	return false;

}

/**
 * Get element classes array
 *
 * @param $element
 *
 * @return array|mixed|null
 * @since 1.0.0
 */
function amd_get_element_class( $element ){

	$classes = apply_filters( "amd_get_element_class", $element );

	return $classes == $element ? [] : $classes;

}

/**
 * Add class for element
 *
 * @param string $element
 * Element name
 * @param array $classes
 * Comma separated string (e.g: "class_1,class_2") or classes array (e.g: ["class_1", "class_2"])
 *
 * @return void
 * @since 1.0.0
 */
function amd_add_element_class( $element, $classes ){

	do_action( "amd_add_element_class", $element, $classes );

}

/**
 * Remove element class
 *
 * @param string $element
 * Element name
 * @param array $classes
 * Comma separated string (e.g: "class_1,class_2") or classes array (e.g: ["class_1", "class_2"])
 *
 * @return void
 * @since 1.0.0
 */
function amd_remove_element_class( $element, $classes ){

	do_action( "amd_remove_element_class", $element, $classes );

}

/**
 * Get element classes array
 *
 * @param string $element
 * Element name
 *
 * @return array|mixed|null
 * @since 1.0.0
 */
function amd_get_element_classes( $element ){

	return amd_get_element_class( $element );

}

/**
 * Get element classes string
 *
 * @param string $element
 * Element name
 *
 * @return string
 * Separated classes string, e.g: "class_1 class_2 class_3"
 * @since 1.0.0
 */
function amd_element_classes( $element ){

	return implode( " ", amd_get_element_classes( $element ) );

}

/**
 * Get plugin URL in WordPress repository
 * @return string
 * @since 1.0.0
 */
function amd_get_plugin_repo_url(){

    # [WP_PLUGIN_URL]
	return "https://wordpress.org/plugins/material-dashboard";

}

/**
 * Convert standard locale code (e.g: "en") to WordPress locale code (e.g: "en_US")
 *
 * @param string $locale
 * @param string $default
 *
 * @return mixed|null
 * @since 1.0.0
 */
function amd_standard_locale_to_wp_locale( $locale, $default=null ){

	$locales = array(
		"fa" => array(
			"locale" => "fa_IR",
			"code" => "fa",
			"country" => "Iran"
		),
		"en" => array(
			"locale" => "en_US",
			"code" => "en",
			"country" => "USA"
		)
	);

	if( empty( $default ) )
		$default = $locale;

	$lookup = apply_filters( "amd_locales_lookup", $locales );

	return $lookup[$locale]["locale"] ?? $default;

}

/**
 * Require auth page
 * @return void
 * @since 1.0.0
 */
function amd_open_auth_page(){

	require_once( AMD_DASHBOARD . '/auth.php' );

}

/**
 * Dump admin pages tabs
 *
 * @param array $tabs
 * Tabs array
 *
 * @return array
 * @since 1.0.0
 */
function amd_dump_admin_tabs( $tabs ){

	$tabCounter = 0;
	$tabsJS = [];
	foreach( $tabs as $id => $tab ){
		$_id = $tab["id"] ?? null;
		$_text = $tab["title"] ?? "";
		$_icon = $tab["icon"] ?? "";
		$_page = $tab["page"] ?? "";
		if( !empty( $_id ) )
			$id = $_id;
		if( empty( $_text ) )
			continue;
        $safe_id = sanitize_key( $id );
        $safe_icon = sanitize_text_field( $_icon );
        $tabsJS[$safe_id] = array(
            "text" => sanitize_text_field( $_text ),
            "icon" => amd_icon( $safe_icon )
        );
		$tabCounter++;
		if( !empty( $_page ) AND file_exists( $_page ) ){
			?>
            <div data-amd-content="<?php echo esc_attr( $id ) ?>"><?php @require_once( $_page ); ?></div>
			<?php
		}
	}

	return $tabsJS;

}

/**
 * Validate every $_FILES item
 *
 * @param array $files
 *
 * @return array
 * $_FILES array with validated files item
 * @since 1.0.0
 */
function amd_sanitize_files( $files ){

	if( empty( $files ) )
        return [];

	$out = [];

	# WordPress allowed file extensions for upload
	$allowed_extensions = amd_get_wp_allowed_extensions();

	# Validate every item
	foreach( $files as $file ){

		# File upload error
		$file_error = $file["error"] ?? UPLOAD_ERR_OK;

		# If file is uploaded and no error occurred
		if( $file_error == UPLOAD_ERR_OK ){

			# Sanitize file name
			$filename = sanitize_file_name( $file["name"] );

			# Check file type
			$extension = pathinfo( $filename, PATHINFO_EXTENSION );
			if( in_array( $extension, $allowed_extensions ) )
				$out[$filename] = $file;
//				$out = array_merge( $out, $file );
		}
	}

	return $out;

}

/**
 * Validate every $_GET parameters
 *
 * @param array $get
 * $_GET data array
 *
 * @return array
 * @since 1.0.0
 */
function amd_sanitize_get_fields( $get ){

	$data = [];

    foreach( $get as $key => $value ){
        $safe_key = sanitize_key( $key );
	    $data[$safe_key] = sanitize_text_field( $value );
    }

    return $data;

}

/**
 * Sanitize array
 * @param array $arr
 * Input array
 *
 * @return array
 * Sanitized array
 * @since 1.0.5
 */
function amd_sanitize_array_fields( $arr ){

	$data = [];

    foreach( $arr as $key => $value ){
        $safe_key = sanitize_text_field( $key );
	    $data[$safe_key] = is_scalar( $value ) ? sanitize_text_field( $value ) : ( is_iterable( $value ) ? amd_sanitize_array_fields( $value ) : null );
    }

    return $data;

}

/**
 * Validate every $_POST parameters
 *
 * @param array $post
 * $_POST data array
 *
 * @return array
 * @since 1.0.1
 */
function amd_sanitize_post_fields( $post ){

    foreach( $post as $key => $value ){
        $safe_key = sanitize_key( $key );
	    $post[$safe_key] = is_array( $value ) ? amd_sanitize_post_fields( $value ) : sanitize_text_field( $value );
    }

    return $post;

}

/**
 * Die error for specific error ID
 *
 * @param string $error_id
 * Error ID
 * @param string $error_text
 * Error text
 * @param bool $headersCheck
 * Check if headers are already sent then use custom error box instead of `wp_die`
 *
 * @return void
 * @since 1.0.0
 */
function amd_assert_error( $error_id, $error_text = "", $headersCheck = true ){

	$error_code = amd_get_error_code( $error_id );

	if( !$error_text )
		$error = sprintf( esc_html__( "An error has occurred", "material-dashboard" ) . ". " . esc_html__( "Error code %s", "material-dashboard" ), $error_code );
	else
		$error = $error_text . "<br>" . "<a href=\"" . esc_url( amd_doc_url( $error_id ) ) . "\">" . sprintf( esc_html__( "Error code %s", "material-dashboard" ), $error_code ) . "</a>";
	if( $headersCheck AND headers_sent() ){
		ob_start();
		# @formatter off
		?>
        <style>body{font-family:"shabnam","roboto","arial",sans-serif;background:#f1f1f1}.amd_error{width:600px;max-width:90%;margin:32px auto;background:#fff;padding:8px 16px;border:1px solid #e1e1e1;font-size:16px}p{color:#414141;margin:8px;line-height:24px}a{color:#0073aa}a:hover,a:active{color:#006799}a:focus{color:#124964}</style>
        <div class="amd_error" dir="auto"><p><?php echo wp_kses( $error, amd_allowed_tags_with_attr( "br,span,a,p" ) ); ?></p></div>
		<?php
		# @formatter on
		die( ob_get_clean() );
	}

	wp_die( $error );

}

/**
 * Print custom error box
 *
 * @param string $error_id
 * Error ID
 * @param string $error_text
 * Error text
 *
 * @return void
 * @since 1.0.0
 */
function amd_dump_error( $error_id, $error_text = "" ){

	$error_code = amd_get_error_code( $error_id );

	if( !$error_text )
		$error = sprintf( esc_html__( "An error has occurred", "material-dashboard" ) . ". " . esc_html__( "Error code %s", "material-dashboard" ), $error_code );
	else
		$error = $error_text . "<br>" . "<a href=\"" . amd_doc_url( $error_id ) . "\">" . sprintf( esc_html__( "Error code %s", "material-dashboard" ), $error_code ) . "</a>";

	# @formatter off
	?>
    <style>.amd_error{width:600px;max-width:90%;margin:32px auto;background:#fff;padding:8px 16px;border:1px solid #e1e1e1;font-size:16px}.amd_error p{color:#414141;margin:8px;line-height:24px}</style>
    <div class="amd_error" dir="auto"><p><?php echo wp_kses( $error, amd_allowed_tags_with_attr( "br,span,a,p" ) ); ?></p></div>
	<?php
    # @formatter on

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
function amd_get_theme_property( $property, $theme = null, $default = null, $fromJsonData = false ){

	global /** @var AMDLoader $amdLoader */
	$amdLoader;

	return $amdLoader->getThemeProperty( $property, $theme, $default, $fromJsonData );

}

/**
 * Get theme URL
 *
 * @param string $theme
 * Theme ID
 * @param string $path
 * Extra path
 *
 * @return string
 * @since 1.0.0
 */
function amd_get_theme_url( $theme = null, $path = "" ){

	return amd_get_theme_property( "theme_url", $theme, "" ) . "/$path";

}

/**
 * Check if dashboard theme supports an option
 *
 * @param string $option
 * Option name. e.g: "night_mode", "multi_language"
 * @param bool $default
 * Default value that returns if option not found
 * @param null|string $theme
 * Theme name, pass null to select current active theme
 *
 * @return bool
 * True on property supports, otherwise false
 * @since 1.0.0
 */
function amd_theme_support( $option, $default = false, $theme = null ){

	$supports = amd_get_theme_property( "supports", $theme, (object) [] );

	return (bool) ( $supports->{$option} ?? $default );

}

/**
 * Check if dashboard is multilingual. You can manage your site language in dashboard admin settings
 *
 * @param bool $theme_check
 * Whether to check dashboard theme support or not
 *
 * @return bool
 * True on multi languages enabled, otherwise false
 */
function amd_is_multilingual( $theme_check = false ){

	$locales = amd_get_site_option( "locales" );

	$multilingual = strpos( trim( $locales, "," ), "," ) !== false;

	return $theme_check ? amd_theme_support( "multilingual", true ) AND $multilingual : $multilingual;

}

/**
 * Default allowed HTML tags
 * @return string[]
 * @since 1.0.0
 */
function amd_default_allowed_html_tags(){

	return [ "h1", "h2", "h3", "h4", "h5", "h6", "p", "a", "span", "button", "i", "strong", "b", "br", "hr", ];

}

/**
 * Check if lazy-loading is enabled
 * @return bool
 * @since 1.0.0
 */
function amd_is_lazy_loading_enabled(){

	return apply_filters( "amd_use_lazy_loading", amd_get_site_option( "use_lazy_loading", "true" ) == "true" );

}

/**
 * Override the default upload path (use it with `upload_dir` filter)
 *
 * @param array $dir
 * Directory path array data
 *
 * @return  array
 * @since 1.0.0
 */
function amd_custom_temp_upload_dir( $dir ){

    return array(
		       'path' => $dir['basedir'] . '/' . AMD_DIRECTORY . '/temp',
		       'url' => $dir['baseurl'] . '/' . AMD_DIRECTORY . '/temp',
		       'subdir' => '/' . AMD_DIRECTORY . '/temp',
	       ) + $dir;

}

/**
 * Strip HTML tags with their contents.
 * <br>e.g:<br>
 * <code>amd_destroy_html_tag( "<div>Hello <h1>world</h1></div>", "h1" )</code>
 * <br>Output: <code><div>Hello </div></code>
 *
 * @param string $html
 * HTML content
 * @param $tag_s
 * Single tag string or multiple tags array. e.g: "a", ["a", "span", "h1"]
 *
 * @return string
 * New HTML content
 * @since 1.0.0
 */
function amd_destroy_html_tag( $html, $tag_s ){

	if( is_array( $tag_s ) ){
		$out = $html;
		foreach( $tag_s as $tag )
			$out = amd_destroy_html_tag( $out, $tag );

		return $out;
	}

	return (string) preg_replace( "~<$tag_s(.*?)</$tag_s>~Usi", "", $html );

}

/**
 * Print WordPress core jQuery library
 * @return void
 * @since 1.0.0
 */
function amd_head_jquery(){

    # Print jQuery core scripts
    wp_print_scripts( ["jquery"] );

	# Print jQuery-ui core scripts
	wp_print_scripts( ["jquery-ui", "jquery-ui-core", "jquery-ui-draggable", "jquery-ui-droppable", "jquery-ui-resizable", "jquery-ui-sortable"] );

    # Ignore noConflict mode
	echo '<script>var $ = jQuery;</script>';

}
add_action( "amd_wp_head_alternate", "amd_head_jquery" );

/**
 * Add jQuery core to dashboard
 * @return void
 * @since 1.0.0
 */
function amd_enqueue_scripts(){

    wp_enqueue_script( "jquery" );

	# jQuery UI components
	wp_enqueue_stored_styles( "jquery-ui" );
	wp_enqueue_script( "jquery-ui-core" );
	wp_enqueue_script( "jquery-ui-draggable" );
	wp_enqueue_script( "jquery-ui-droppable" );
	wp_enqueue_script( "jquery-ui-resizable" );
	wp_enqueue_script( "jquery-ui-sortable" );

}
add_action( "wp_enqueue_scripts", "amd_enqueue_scripts" );
add_action( "admin_enqueue_scripts", "amd_enqueue_scripts" );

/**
 * Make jQuery global for dashboard settings pages in admin (ignore noConflict mode ONLY in that pages)
 * @return void
 * @since 1.0.0
 */
function amd_globalize_jquery(){

    echo '<script>var $ = jQuery;</script>';

}
add_action( "amd_before_admin_head", "amd_globalize_jquery" );

/**
 * Append blog name to page title in dashboard
 * @param string $title
 * Page title
 *
 * @return string
 * @since 1.0.0
 */
function amd_dashboard_pages_title( $title ){

	$blog_name = get_bloginfo( 'name' );

    return "$title | $blog_name";

}

# Login page title
add_filter( "amd_login_page_title", "amd_dashboard_pages_title" );

# Reset password page title
add_filter( "amd_reset_password_page_title", "amd_dashboard_pages_title" );

# Registration page title
add_filter( "amd_registration_page_title", "amd_dashboard_pages_title" );

/**
 * Edit user profile
 * @param $u
 *
 * @return void
 * @since 1.0.0
 */
function amd_edit_user_profile( $u ){

	global $amdCore;

	$amdCore->editUserFields( $u );

}
add_action( "edit_user_profile", "amd_edit_user_profile" );
add_action( "show_user_profile", "amd_edit_user_profile" );

/**
 * Check if SSL is forced or not
 * @return bool
 * @since 1.0.0
 */
function amd_is_ssl_forced(){

	return apply_filters( "amd_is_ssl_forced", amd_get_site_option( "force_ssl", "false" ) == "true" );

}

/**
 * Echo number format guides
 * @return void
 * @since 1.0.0
 */
function amd_dump_number_format_guide(){
	?>
    <p><?php _e( "You can format how to validate and display numbers in registration form. You can enter number formats with letter <code>X</code> and <code>-</code> character for spaces, here some examples:", "material-dashboard" ); ?></p>
    <p><?php _e( "<code>XXX-XXX-XXXX</code>: 012-345-6789", "material-dashboard" ); ?></p>
    <p><?php _e( "<code>XXX-XXXX-XXX</code>: 012-3456-789", "material-dashboard" ); ?></p>
    <p><?php _e( "<code>XXXXXXXXXX</code>: 0123456789", "material-dashboard" ); ?></p>
    <p><?php _e( "Please note that country digit number will be added to the first of phone number automatically, for example by using <code>XXX-XXX-XXXX</code> format, your phone number will be something like this: <code>+1 012 345 6789</code>", "material-dashboard" ); ?></p>
    <p><?php _e( "Phone numbers will be saved normally without any space and '-', for example if user phone number format is <code>XXX-XXX-XXXX</code> user saved phone number will be <code>+10123456789</code>.", "material-dashboard" ); ?></p>
    <p><a href="<?php echo esc_url( amd_doc_url( 'custom_country_code' ) ); ?>"
          target="_blank"><?php echo esc_html_x( "Learn how to use number formats", "Admin", "material-dashboard" ); ?></a></p>
	<?php
}

/**
 * Get document URL
 *
 * @param string $id
 * Documentation ID
 * @param bool $html
 * Whether to print 'more info' link or not
 * @param string|null $text
 * 'more info' link text, pass null to use default
 * @param bool $blank
 * Whether to set 'more info' link target to blank page or not
 *
 * @return string
 * @since 1.0.0
 */
function amd_doc_url( $id, $html = false, $text = null, $blank = true ){

	$base_url = AMD_AUTH_SITE . "/amd/d/$id";

	if( $html ){
		if( empty( $text ) )
			$text = " (" . esc_html__( "More info", "material-dashboard" ) . ")";
		ob_start();
		?>
        <a href="<?php echo esc_url( $base_url ); ?>"<?php echo $blank ? ' target="_blank"' : ''; ?>><?php echo $text; ?></a>
		<?php
		return trim( ob_get_clean() );
	}

	return $base_url;

}

/**
 * Print license template
 *
 * @param string $license
 * License key
 *
 * @return string
 * @since 1.0.0
 */
function amd_licence_template( $license ){

    switch( $license ){
        case "fonts":
            return "\nKalameh font is considered a proprietary software.\nTo gain information about the laws regarding the use of these fonts, please visit fontiran.com\nThis set of fonts are used in this project under the license: 342VUY";
    }

	return "";
}

/**
 * Get error code tracking URL in documentation
 *
 * @param string $error_code
 * Error code. e.g: 903
 *
 * @return string
 * @since 1.0.0
 */
function amd_error_code_track_url( $error_code ){

	return amd_doc_url( "error_$error_code" );

}

/**
 * Add CSS stylesheet
 *
 * @param string $id
 * Stylesheet ID. You can specify stylesheet scope by adding ':' character to the beginning of ID. e.g: "dashboard:style_id"
 * @param string $url
 * Stylesheet URL
 * @param string $ver
 * Stylesheet version
 *
 * @return void
 * @sicne 1.0.1
 */
function amd_register_style( $id, $url, $ver="unknown" ){

    global $amdCache;

    $amdCache->addStyle( $id, $url, $ver );

}

/**
 * Remove stylesheet by ID
 * @param string $id
 * Stylesheet ID
 *
 * @return void
 * @since 1.0.1
 */
function amd_deregister_style( $id ){

    global $amdCache;

    $amdCache->removeStyleById( $id );

}

/**
 * Remove stylesheet by scope
 * @param string $scope
 * Scope name
 *
 * @return void
 * @since 1.0.1
 */
function amd_deregister_style_scope( $scope ){

    global $amdCache;

    $amdCache->removeStyleByScope( $scope );

}

/**
 * Add JS script
 *
 * @param string $id
 * Script ID. You can specify script scope by adding ':' character to the beginning of ID. e.g: "dashboard:script_id"
 * @param string $url
 * Script URL
 * @param string $ver
 * Script version
 *
 * @return void
 * @sicne 1.0.1
 */
function amd_register_script( $id, $url, $ver="unknown" ){

    global $amdCache;

    $amdCache->addScript( $id, $url, $ver );

}

/**
 * Remove script by ID
 * @param string $id
 * Script ID
 *
 * @return void
 * @since 1.0.1
 */
function amd_deregister_script( $id ){

	global $amdCache;

	$amdCache->removeScriptById( $id );

}

/**
 * Remove script by scope
 * @param string $scope
 * Scope name
 *
 * @return void
 * @since 1.0.1
 */
function amd_deregister_script_scope( $scope ){

	global $amdCache;

	$amdCache->removeScriptByScope( $scope );

}

/**
 * Switch to another locale
 * @param string $locale
 * Locale code, e.g: "fa_IR", "en_US", "ar"
 * @param bool $updateUserOrCookie
 * Whether to update user meta (if logged in) or cookie (if user is not logged in)
 *
 * @return void
 * @since 1.0.3
 */
function amd_switch_locale( $locale, $updateUserOrCookie=true ){

    # Change WordPress global locale
    switch_to_locale( $locale );

    # Update locale for current user with meta or cookie
    if( $updateUserOrCookie ){

        global $amdCache;

	    if( is_user_logged_in() )
		    update_user_meta( get_current_user_id(), "locale", $locale );
	    else
		    $amdCache->setCookie( "locale", $locale, $amdCache::STAMPS["month"] );

    }

    global $amdCal;

    # Change calendar date mode base on locale
    if( $amdCal )
	    $amdCal->setDateMode( $locale == "fa_IR" ? "j" : "g" );

}

/**
 * Print log content to default logs file
 * <br><b>Note: This is only for debug and localhost back-trace, if you are using this logger on real host,
 * REMEMBER TO DELETE LOG FILE after your work is done, also DO NOT DUMP ANY SENSITIVE DATA in it because it is not hidden to public!</b>
 *
 * @param mixed $data
 * Any data type
 * @param bool $append
 * Whether to append content to existing file
 * @param array|bool $trace
 * Whether to show date and trace at the beginning of line, or `debug_backtrace` returned value
 * @param string $breakLine
 * Line break character
 *
 * @return false|int
 * Returns the returned value of {@see file_put_contents()}
 * @since 1.0.4
 */
function amd_log( $data, $append = false, $trace = false, $breakLine="\n" ){

	$pre = '';
	try {
		$bt = is_array( $trace ) ? $trace : debug_backtrace();
		$caller = array_shift( $bt );
		$fileExp = explode( DIRECTORY_SEPARATOR, $caller['file'] );
		$filename = end( $fileExp );
		if( $filename )
			$pre = "['$filename': " . ( $caller["line"] ?? "_" ) . "]";
	} catch( Exception $e ){}

	$path = apply_filters( "amd_logs_path", ABSPATH . "amd_logs.txt" );

	if( $data === true )
		$data = "True";
    else if( $data === false )
		$data = "False";
    else
	    $data = var_export( $data, true );

    if( is_int( $data ) )
		$content = "$data";
	else if( is_string( $data ) )
		$content = strlen( $data ) > 0 ? $data : "[EMPTY_STRING]";
	else if( is_null( $data ) )
		$content = "NULL";
	else if( is_callable( $data ) )
		$content = "CALLABLE";
	else
		$content = apply_filters( "amd_logs_control", var_export( $data, true ), $data );

	if( !is_string( $content ) )
		$content = "[NOT_PRINTABLE]" . "[" . gettype( $content ) . "][EXPORTED] " . var_export( $content, true );

	$content = $trace ? "[" . date( apply_filters( "amd_logs_time_format", "Y-m-d H:i:s" ) ) . "]" . ( $pre ? " $pre" : "" ) . " $content" : $content;

	if( $data == "\n" ){
		$content = "";
		$breakLine = "\n";
	}

	return file_put_contents( $path, $content . ( $append ? $breakLine : "" ), $append ? FILE_APPEND : 0 );

}

/**
 * Add data content to default logs file
 *
 * @param mixed $data
 * Any data type
 *
 * @return false|int
 * Returns the returned value from {@see amd_log()}
 * @since 1.0.4
 */
function amd_put_log( $data ){

	return amd_log( $data, true, debug_backtrace() );

}

/**
 * Push element to custom input
 * @param string|array $input
 * Input array or string, e.g: "10,11,25", ["a", "b", "c"]
 * @param scalar $element
 * Scalar value for element (int, string, bool, float), e.g: "d", 22, 12.5, false
 * @param string $separator
 * Separator of input list, default is ","
 * @param bool $unique
 * Whether to remove duplicated values
 *
 * @return string
 * Seperated items list, e.g: "a,b,c,12,45,3.14,0"
 * @since 1.0.4
 */
function amd_push_value( $input, $element, $separator=",", $unique=true ){

    # Covert string input to array
    if( is_string( $input ) )
        $input = explode( $separator, $input );

    # Ignore unexpected input values
    if( !is_array( $input ) )
        return $input;

    # Push element to input
    $input[] = $element;

    # Remove duplicated elements from input
    if( $unique )
        $input = array_unique( $input );

    # Convert input array to string
    $out = implode( $separator, $input );

    # Remove trailing separator from output string
    $out = trim( $out, $separator );
    $out = str_replace( $separator . $separator, "", $out );

    return $out;

}

/**
 * Pull out an element from custom input
 * @param string|array $input
 * Input array or string, e.g: "10,11,25", ["a", "b", "c"]
 * @param scalar $element
 * Scalar value to remove (int, string, bool, float), e.g: "d", 22, 12.5
 * @param string $separator
 * Separator of input list, default is ","
 * @param bool $unique
 * Whether to remove duplicated values
 *
 * @return string
 * Seperated items list after removing an element from it, e.g: "a,b,c,12,45,3.14"
 * @since 1.0.4
 */
function amd_pull_value( $input, $element, $separator=",", $unique=true ){

    # Covert string input to array
    if( is_string( $input ) )
        $input = explode( $separator, $input );

    # Ignore unexpected input values
    if( !is_array( $input ) )
        return $input;

    # Exchanges all keys with their associated values in input list
    $input = array_flip( $input );

    # Remove element from your input list
    $input[$element] = null;
    unset( $input[$element] );

	# Re-exchange associated values with their keys in input list
	$input = array_flip( $input );

    # Remove duplicated elements from input
    if( $unique )
        $input = array_unique( $input );

    # Convert input array to string
    $out = implode( $separator, $input );

    # Remove trailing separator from output string
    $out = trim( $out, $separator );
    $out = str_replace( $separator . $separator, "", $out );

    return $out;

}

/**
 * Print phone number in login and registration and other forms
 * @param bool $isForLogin
 * Whether phone field is for login page or not
 *
 * @return void
 * @sicne 1.0.4
 */
function amd_phone_fields( $isForLogin=false ){

    if( $isForLogin ){
        $phone_field = true;
        $phone_field_required = true;
    }
    else{
	    $phone_field = amd_get_site_option( "phone_field" ) == "true";
	    $phone_field_required = amd_get_site_option( "phone_field_required" ) == "true";
    }

	$regions = amd_get_regions();
	$cc_count = $regions["count"];
	$first_cc = $regions["first"];
	$format = $regions["format"];

    ?>
    <?php if( $phone_field ): ?>
        <div id="phone-fields">
			<?php if( $cc_count > 1 ): ?>
                <div class="_phone_field_holder_ ht-magic-select">
                    <label>
                        <input type="text" class="--input" data-field="country_code" data-next="phone_number"
                               placeholder=""<?php echo $phone_field_required ? "required" : ""; ?>>
                        <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
                        <span class="--value" dir="auto"><?php _amd_icon( "phone" ) ?></span>
                    </label>
                    <div class="--options">
						<?php foreach( $regions["regions"] as $region ): ?>
                            <span data-value="<?php echo esc_attr( $region['digit'] ?? '' ); ?>"
                                  data-format="<?php echo esc_attr( $region['format'] ?? '' ); ?>"
                                  data-keyword="<?php echo esc_attr( $region['name'] ?? '' ); ?>">
                                <?php echo apply_filters( "amd_phone_format_name", $region["name"] ?? "", $region["digit"] ?? "", $region["format"] ?? "" ); ?></span>
						<?php endforeach; ?>
                    </div>
                    <div class="--search"></div>
                </div>
                <label class="_phone_field_holder_ ht-input --ltr">
                    <input type="text" class="not-focus" data-field="phone_number" data-pattern="" data-keys="[+0-9]"
                           data-next="<?php echo $isForLogin ? 'password' : ''; ?>"
                           placeholder="" <?php echo $phone_field_required ? "required" : ""; ?>>
                    <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
					<?php _amd_icon( "phone" ); ?>
                </label>
			<?php else: ?>
				<?php if( $first_cc == "98" AND apply_filters( "amd_use_phone_simple_digit", false ) ): ?>
                    <label class="_phone_field_holder_ ht-input --ltr">
                        <input type="text" class="not-focus" data-field="phone_number" data-keys="[0-9]"
                               data-pattern="[0-9]" placeholder=""
							<?php echo $phone_field_required ? "required" : ""; ?>>
                        <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
						<?php _amd_icon( "phone" ); ?>
                    </label>
				<?php else: ?>
                    <div class="_phone_field_holder_ ht-magic-select" style="display:none">
                        <label>
                            <input type="text" class="--input" data-field="country_code" data-next="phone_number"
                                   data-value="<?php echo esc_attr( $first_cc ); ?>" value="<?php echo esc_attr( $first_cc ); ?>" placeholder=""
								<?php echo $phone_field_required ? "required" : ""; ?>>
                            <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
                            <span class="--value" dir="auto"><?php echo esc_html( $first_cc ); ?></span>
                        </label>
                        <div class="--options">
                            <span data-value="<?php echo esc_attr( $first_cc ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-keyword=""></span>
                        </div>
                        <div class="--search"></div>
                    </div>
                    <label class="_phone_field_holder_ ht-input --ltr">
                        <input type="text" class="not-focus" data-field="phone_number" data-pattern="[0-9]{11}"
                               data-keys="[0-9]"
                               placeholder="" <?php echo $phone_field_required ? "required" : ""; ?>>
                        <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
						<?php _amd_icon( "phone" ); ?>
                    </label>
				<?php endif; ?>
			<?php endif; ?>
        </div>
	<?php endif; ?>
    <?php
}

/**
 * Get Amatris material dashboard plugin survey URL
 * @return string
 * @since 1.0.4
 */
function amd_get_survey_url(){

    $domain = amd_replace_url( "%domain%" );
    $url = "https://amatris.ir/amd/survey/?utm_site=" . urlencode( $domain );

    if( is_user_logged_in() ){
        if( $email = amd_get_current_user()->email )
            $url .= "&utm_email=" . urlencode( $email );
    }

    return $url;

}

/**
 * Check if user account requires 2FA login or not
 * @param int|null $uid
 * User ID to check, pass null to use current user
 *
 * @return bool
 * @since 1.0.5
 */
function amd_user_required_2f( $uid=null ){

	$use_login_2fa = amd_get_site_option( "use_login_2fa", "false" ) == "true";
	if( !$use_login_2fa )
		return false;

	$force_login_2fa = amd_get_site_option( "force_login_2fa", "false" ) == "true";
	if( $force_login_2fa )
		return true;

	return amd_get_user_meta( $uid, "use_2fa", "false" ) == "true";
}

/**
 * Initialize locale from user meta or cookie
 * @return void
 * @since 1.0.3
 */
function amd_initialize_locale(){
	global $amdCache;

    # Get preferred locale from user meta (for logged-in users) and cookie (for logged-out users)
    if( is_user_logged_in() )
		$locale = get_user_meta( get_current_user_id(), "locale", true );
	else
        $locale = $amdCache->getCache( "locale", "cookie" );

    # If locale is initialized with a value
	if( $locale ){

        # Change current language without setting cookie and updating meta
		amd_switch_locale( $locale, false );

        # Change locale for using ajax handler with user locale
        add_filter( "locale", function() use ( $locale ){
            return $locale;
        }, 1 );

	}
}
add_action( "init", "amd_initialize_locale" );
add_action( "amd_dashboard_init", "amd_initialize_locale", 1 );

/**
 * Initialize ajax requests
 * @return void
 * @since 1.0.3
 */
function amd_ajax_initialize(){

    # This is for changing user locale in ajax handler which already changed with `locale` filter
    # amd_initialize_locale();

}
add_action( "amd_ajax_init", "amd_ajax_initialize" );