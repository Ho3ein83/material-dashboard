<?php

/** @var AMD_DB $amdDB */
$amdDB = null;

class AMD_DB {

	/**
	 * Tables name
	 * @var string
	 * @since 1.0.0
	 */
	public $tables;

	/**
	 * WordPress database prefix
	 * @var string
	 * @since 1.0.0
	 */
	public $wp_prefix;

	/**
	 * HBD database prefix
	 * @var string
	 * @since 1.0.0
	 */
	public $prefix;

	/**
	 * Database name (for create new tables)
	 * @var string
	 * @since 1.0.0
	 */
	public $db_name;

	/**
	 * wpdb class object
	 * @var wpdb
	 * @since 1.0.0
	 */
	public $db;

	/**
	 * Tables SQL structure
	 * @var array
	 * @since 1.0.0
	 */
	protected $tablesSQL;

	/**
	 * Export variants
	 * @var array
	 * @since 1.0.0
	 */
	protected $export_variants;

	/**
	 * Database manager
	 */
	function __construct(){

		global /** @var wpdb $wpdb */
		$wpdb;

		$this->db = $wpdb;
		$this->db_name = $wpdb->dbname;
		$this->wp_prefix = $wpdb->prefix;
		$this->prefix = $this->wp_prefix . "amd_";

		$this->tables = array(
			"wp_users" => $this->wp_prefix . "users"
		);

		$this->export_variants = [];

		# initialize database
		self::init();

	}

	/**
	 * Initialize database data and/or install database
	 *
	 * @param false $install
	 * Whether to install database or not
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init( $install = false ){

		self::registerTable( "users_meta", array(
			"id" => "INT NOT NULL AUTO_INCREMENT",
			"user_id" => "INT NOT NULL",
			"meta_name" => "VARCHAR(64) NOT NULL",
			"meta_value" => "LONGTEXT NOT NULL",
			"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = MyISAM;"
		) );

		self::registerTable( "options", array(
			"id" => "INT NOT NULL AUTO_INCREMENT",
			"option_name" => "VARCHAR(64) NOT NULL",
			"option_value" => "LONGTEXT NOT NULL",
			"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = MyISAM;"
		) );

		self::registerTable( "temp", array(
			"id" => "INT NOT NULL AUTO_INCREMENT",
			"temp_key" => "VARCHAR(64) NOT NULL",
			"temp_value" => "LONGTEXT NOT NULL",
			"expire" => "VARCHAR(64) NOT NULL",
			"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = MyISAM;"
		) );

		self::registerTable( "todo", array(
			"id" => "INT NOT NULL AUTO_INCREMENT",
			"todo_key" => "VARCHAR(64) NOT NULL",
			"todo_value" => "LONGTEXT NOT NULL",
			"status" => "VARCHAR(64) NOT NULL",
			"meta" => "LONGTEXT NOT NULL",
			"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = MyISAM;"
		) );

		if( $install == true )
			self::install();

	}

	/**
	 * Register table item. If a table not installed database upgrade message displays in admin pages
	 *
	 * @param string $table_name
	 * Table name
	 * @param array $data_array
	 * Table columns data
	 *
	 * @return bool
	 */
	public function registerTable( $table_name, $data_array ){

		$table = $this->prefix . $table_name;

		if( !empty( $this->tables[$table_name] ) )
			return false;

		$this->tables[$table_name] = $table;

		$this->tablesSQL[$table_name] = $data_array;

		return true;

	}

	/**
	 * Install database
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function install(){

		$this->mct( $this->tablesSQL );

	}

	/**
	 * Check if database is installed
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function isInstalled(){

		return $this->mct( $this->tablesSQL, false );

	}

	/**
	 * Repair data
	 *
	 * @param string $json
	 * Encoded JSON string
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function repairData( $json ){

		if( empty( $json ) )
			return false;

		return self::importData( $json, false );

	}

	/**
	 * Import site options from JSON data
	 *
	 * @param string $json
	 * Encoded JSON string
	 * @param bool $overwrite
	 * Replace existing items with new ones ($overwrite=true) or only add new items ($overwrite=false)
	 *
	 * @return bool
	 * True on success, otherwise false
	 * @since 1.0.0
	 */
	public function importData( $json, $overwrite = true ){

		$data = json_decode( $json );

		if( empty( $json ) or empty( $data ) )
			return false;

		foreach( $data as $key => $value ){
			if( !amd_is_option_allowed( $key ) )
				continue;
			if( $overwrite )
				self::setSiteOption( $key, $value );
			else
				self::addSiteOption( $key, $value );
		}

		return true;

	}

	/**
	 * Import JSON data automatically
	 *
	 * @param string $mode
	 * Import mode. e.g: "site_options", "auto"
	 * @param string $json
	 * Encoded JSON string
	 * @param bool $overwrite
	 * Replace existing items with new ones ($overwrite=true) or only add new items ($overwrite=false)
	 *
	 * @return array
	 * Array data. Template:
	 * <br><code>array( "success" => [bool], "data" => [array] )</code>
	 * @since 1.0.0
	 */
	public function importJSON( $mode, $json, $overwrite = true ){

		if( empty( $json ) )
			return [
				"success" => false,
				"data" => [ "msg" => esc_html__( "JSON data is invalid", "material-dashboard" ) ]
			];

		if( $mode == "auto" ){

			$data = json_decode( $json );

			$progress = [];
			$progress["site_options"] = [];
			$progress["users_meta"] = [];
			$missed = 0;

			foreach( $this->export_variants as $id => $variant ){
				$type = $variant["export_type"] ?? "";
				$d = $data->{$id} ?? null;
				if( $type != "json" OR empty( $d ) )
					continue;

				$callable = $variant["import"] ?? null;
				if( is_callable( $callable ) ){
					list( , , $p, $m ) = call_user_func( $callable, $d, $overwrite );
					$missed += $m;
					$progress[$id] = $p;
				}

			}

			$msg = esc_html__( "Data imported successfully", "material-dashboard" );
			if( $missed > 0 )
				$msg .= esc_html__( ", however some data couldn't be imported. For more information check for error code 901 in documentation", "material-dashboard" );

			return [
				"success" => true,
				"data" => [ "msg" => $msg, "missed" => $missed, "progress" => $progress ]
			];

		}
		else{
			do_action( "amd_handle_import_method_$mode", $json, $overwrite );
		}

		return [
			"success" => false,
			"data" => [ "msg" => esc_html__( "Import method is not allowed", "material-dashboard" ) ]
		];

	}

	/**
	 * Export site options to array
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function exportSiteOptions(){

		$table = self::getTable( "options" );

		$results = self::safeQuery( $table, "SELECT * FROM `%{TABLE}%`" );

		if( empty( $results ) )
			return [];

		$d = [];

		foreach( $results as $result ){
			$name = $result->option_name;
			$value = $result->option_value;
			$d[$name] = $value;
		}

		return $d;

	}

	/**
	 * Export users meta to array ("amd_users_meta" not WordPress "usermeta" table)
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function exportUsersMeta(){

		$table = self::getTable( "users_meta" );

		$results = self::safeQuery( $table, "SELECT * FROM `%{TABLE}%`" );

		if( empty( $results ) )
			return [];

		$d = [];

		foreach( $results as $result ){
			$uid = $result->user_id;
			if( empty( $d[$uid] ) OR !is_array( $d[$uid] ) )
				$d[$uid] = [];
			$name = $result->meta_name;
			$value = $result->meta_value;
			$d[$uid][$name] = $value;
		}

		return $d;

	}

	/**
	 * Export Temporarily data to array
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function exportTempData(){

		$table = self::getTable( "temp" );

		$results = self::safeQuery( $table, "SELECT * FROM `%{TABLE}%`" );

		if( empty( $results ) )
			return [];

		$d = [];

		foreach( $results as $result ){
			$key = $result->temp_key;
			$value = $result->temp_value;
			$expire = $result->expire;
			$d[$key] = array(
				"value" => $value,
				"expire" => $expire
			);
		}

		return $d;

	}

	/**
	 * Register new export variant, multiple variants are allowed in recursive array.
	 *
	 * @param array $variant
	 * Variant array
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function registerExportVariant( $variant ){

		if( $this->export_variants == null )
			$this->export_variants = [];
		if( $variant == null )
			$variant = [];

		$this->export_variants = array_merge( $this->export_variants, $variant );

	}

	/**
	 * Export registered variant
	 *
	 * @param string $variant
	 * Variant ID
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function exportVariant( $variant ){

		if( empty( $this->export_variants[$variant] ) )
			return null;

		$type = $this->export_variants[$variant]["export_type"] ?? "";
		$callback = $this->export_variants[$variant]["export"];

		if( is_callable( $callback ) AND $type == "json" )
			return call_user_func( $callback );

		return null;

	}

	/**
	 * Get `$export_variants` object
	 * @return array
	 * @see AMD_DB::$export_variants
	 * @since 1.0.0
	 */
	public function getExportVariants(){

		return $this->export_variants;

	}

	/**
	 * Export multiple variants to JSON
	 *
	 * @param string $variants
	 * Comma separated string. e.g: "variant_1,variant_2,variant_3"
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function exportJSON( $variants ){

		$exp = explode( ",", $variants );

		global $wp_version;

		$data = array(
			"date" => amd_true_date( "l j F Y" ),
			"time" => time(),
			"is_premium" => function_exists( "adp_plugin" ) ? true : false,
			"premium_version" => function_exists( "adp_plugin" ) ? adp_plugin()["Version"] ?? "unknown" : null,
			"version" => amd_plugin()["Version"] ?? "unknown",
			"author" => wp_get_current_user()->user_login,
			"wp_version" => !empty( $wp_version ) ? $wp_version : "unknown",
			"php_version" => phpversion(),
			"from" => amd_replace_url( "%domain%" )
		);

		foreach( $exp as $item ){

			$d = self::exportVariant( $item );

			if( !empty( $d ) )
				$data[$item] = $d;

		}

		return json_encode( $data );

	}

	/**
	 * Make backup from avatars directory (uploaded avatars)
	 *
	 * @return array|false
	 * @since 1.0.0
	 * @uses ZipArchive
	 */
	public function exportAvatars(){

		if( !class_exists( "ZipArchive" ) )
			return false;

		$zip = new ZipArchive();
		$avatars_dir = amd_get_avatars_path();
		$zip_file = "$avatars_dir/backup_avatars_" . date( "Ymd" ) . ".zip";

		global /** @var AMDExplorer $amdExp */
		$amdExp;

		$amdExp->deletePattern( $avatars_dir, "/^backup_avatars_(.*)\.zip$/" );

		if( $zip->open( $zip_file, ZipArchive::CREATE ) !== true )
			return false;

		$files = glob( "$avatars_dir/*", GLOB_BRACE );
		if( !$files )
			return false;

		foreach( $files as $file ){
			$filename = pathinfo( $file, PATHINFO_BASENAME );
			$zip->addFile( "$avatars_dir/$filename", "avatars/$filename" );
		}

		$zip->addFromString( "DO_NOT_CHANGE_FILES_NAME", "" );

		$zip->close();

		return [ $zip_file, $zip ];

	}

	/**
	 * Export zip archives
	 * @param string $path
	 * Archive file destination
	 * @param string $variants
	 * Comma separated string, e.g: "users_avatar,users_upload"
	 * @param mixed $data
	 * Export data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function exportArchives( $path, $variants, $data ){

		if( !class_exists( "ZipArchive" ) )
			return [ 0, "", null ];

		$parent_zip = new ZipArchive();

		$exp = explode( ",", $variants );

		if( empty( $exp ) )
			return [ 0, "", $parent_zip ];

		$target_path = "$path/backup_all_" . date( "Ymd" ) . ".zip";

		global /** @var AMDExplorer $amdExp */
		$amdExp;

		$amdExp->makeDirectory( $amdExp->getPath( "backup", true ) );

		$amdExp->deletePattern( $path, "/^backup_all_(.*)\.zip$/" );

		if( $parent_zip->open( $target_path, ZipArchive::CREATE ) !== true )
			return [ 0, "", $parent_zip ];

		$progress = 0;
		$trash = [];
		foreach( self::getExportVariants() as $id => $variant ){
			if( in_array( $id, $exp ) ){
				$v = $this->export_variants[$id] ?? null;
				if( $v AND ( $v["export_type"] ?? "" ) == "zip" ){
					$callable = $v["export"] ?? null;
					if( is_callable( $callable ) ){
						$d = call_user_func( $callable, $data );
						if( $d ){
							list( $zip_file, $zip ) = $d;
							$parent_zip->addFile( $zip_file, pathinfo( $zip_file, PATHINFO_BASENAME ) );
							$trash[] = $zip_file;
							$progress++;
						}
					}
				}
			}
		}

		$parent_zip->addFromString( "DO_NOT_CHANGE_FILES_NAME", "" );
		$parent_zip->addFromString( "bundle.backup", time() );
		$parent_zip->close();

		if( !$progress ){
			$amdExp->deletePattern( $path, "/^backup_all_(.*)\.zip$/" );

			return [ 0, "", $parent_zip ];
		}

		foreach( $trash as $trash_path )
			$amdExp->deleteFile( $trash_path, true );

		$zip_basename = pathinfo( $target_path, PATHINFO_BASENAME );

		$size = filesize( $target_path );
		$backup_url = $amdExp->pathURL( "backup", $zip_basename );

		return [ $size, $backup_url, $parent_zip, $target_path ];

	}

	/**
	 * Import archive files
	 * @param string $path
	 * Backup files directory
	 * @param bool $overwrite
	 * Whether to overwrite existing options in database or not
	 *
	 * @return array[]
	 * Result array<br>index[0] -> $messages
	 * @since 1.0.0
	 */
	public function importArchives( $path, $overwrite ){

		global $amdExp;

		$messages = [];

		if( $json = $amdExp->patternExists( $path, "/^backup_(.*).json$/", true ) ){
			$json_content = file_get_contents( "$path/$json" );
			global /** @var AMD_DB $amdDB */
			$amdDB;
			$result = $amdDB->importJSON( "auto", $json_content, $overwrite );
			if( $result["success"] ?? false )
				$messages[] = [ "success" => true, "msg" => esc_html_x( "Site settings imported", "Admin", "material-dashboard" ) ];
			else
				$messages[] = [ "success" => false, "msg" => esc_html_x( "Site settings import failed", "Admin", "material-dashboard" ) ];
		}

		foreach( $this->export_variants as $id => $variant ){

			$v = $this->export_variants[$id];
			$type = $v["export_type"] ?? "";
			if( $type != "zip" )
				continue;

			$pattern = $v["backup_pattern"] ?? null;
			$import = $v["import"] ?? null;

			if( !empty( $pattern ) AND $f = $amdExp->patternExists( $path, $pattern, true ) ){
				$d = call_user_func( $import, "$path/$f" );
				list( $success, $msg, ) = $d;
				$messages[] = [ "success" => $success, "msg" => $msg ];
			}

		}

		return [ $messages ];

	}

	/**
	 * Get table name
	 *
	 * @param string $tableName
	 * Table name
	 *
	 * @return string
	 * Table name or empty string for undefined tables
	 * @since 1.0.0
	 */
	public function getTable( $tableName ){

		self::init();

		return $this->tables[$tableName] ?? "";

	}

	/**
	 * Create table if not exists
	 *
	 * @param array $tables
	 * Tables data array
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function mct( $tables, $create = true ){

		$installed = true;

		foreach( $tables as $key => $value ){

			$tablename = $this->prefix . $key;

			if( !$this->tableExists( $tablename ) ){

				if( $create ){
					$sql = "CREATE TABLE `$this->db_name`.`$tablename` ( ";
					$sql .= $this->array_to_sql( $value );
					$this->query( $sql );
				}

				$installed = false;

			}

		}

		return $installed;

	}

	/**
	 * Check if table exists
	 *
	 * @param string $tablename
	 * Table name
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function tableExists( $tablename ){

		$sql = "SHOW TABLES LIKE '$tablename'";

		$res = $this->query( $sql );

		return count( $res ) > 0;

	}

	/**
	 * Run SQL query
	 *
	 * @param string $sql
	 * SQL query
	 *
	 * @return mixed
	 * Query result
	 * @since 1.0.0
	 */
	public function query( $sql ){

		return $this->db->get_results( $sql );

	}

	/**
	 * Run SQL query if table exists
	 *
	 * @param $table
	 * Target table
	 * @param $sql
	 * SQL query, <code>%{TABLE}%</code> will be replaced with table name (if table exists)
	 *
	 * @return array|object|stdClass|null
	 * The results of SQL query on success or empty array if table doesn't exist to prevent errors
	 * @since 1.0.0
	 */
	public function safeQuery( $table, $sql ){

		if( !self::tableExists( $table ) )
			return [];

		$sql = str_replace( "%{TABLE}%", $table, $sql );

		return $this->db->get_results( $sql );

	}

	/**
	 * Insert data to database if table exist and prevent SQL errors
	 *
	 * @param string $table
	 * Table name
	 * @param array $data
	 * Data array
	 *
	 * @return false|int
	 * Row ID on success, otherwise false
	 * @since 1.0.0
	 */
	public function safeInsert( $table, $data ){

		if( !self::tableExists( $table ) )
			return false;

		$r = $this->db->insert( $table, $data );

		return $r ? $this->db->insert_id : false;

	}

	/**
	 * Convert data array to SQL query
	 *
	 * @param array $arr
	 * Array
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function array_to_sql( $arr ){

		$sql = '';

		$counter = 1;

		foreach( $arr as $key => $value ){

			if( $key != 'EXTRA' )
				$sql .= "`" . $key . "` " . $value . ( ( $counter <= count( $arr ) - 1 ) ? " , " : "" );
			else
				$sql .= $value;

			$counter++;

		}

		return $sql;

	}

	/**
	 * Call after cores loaded
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function after(){}

	/**
	 * Convert array to SQL filters<br>
	 *
	 * @param array $filters
	 * Filters array. e.g:
	 * <code>["hello" => "world", "CUSTOM" => "`test`='123'"]</code>
	 * Output: <code>" WHERE `hello` = 'world' AND `test`='123'"</code>
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function makeFilters( $filters = [] ){

		$filter = "";

		if( !empty( $filters ) ){

			$filter = " WHERE ";

			foreach( $filters as $key => $value ){
				if( $key == 'CUSTOM' )
					$filter .= " $value AND";
				else
					$filter .= " `$key` = '$value' AND";
			}
			$filter = trim( $filter, 'AND' );

		}

		return $filter;

	}

	/**
	 * Convert array to SQL order<br>
	 *
	 * @param array $orders
	 * Order array. e.g:
	 * <code>["date" => "ASC"]</code>
	 * Output: <code>" ORDER BY date ASC"</code>
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function makeOrder( $orders = [] ){

		$order = "";

		if( !empty( $orders ) ){

			foreach( $orders as $key => $value )
				$order = " ORDER BY `$key` $value";

		}

		return $order;

	}

	/**
	 * Add/Update site option
	 *
	 * @param string $on
	 * Option name
	 * @param string $ov
	 * Option value
	 *
	 * @return bool|int|mysqli_result|resource|null
	 * On update: The number of rows updated, or false on error
	 * <br>On insert: The number of rows inserted, or false on error
	 * @since 1.0.0
	 */
	public function setSiteOption( $on, $ov ){

		$table = $this->getTable( "options" );

		if( !self::siteOptionExists( $on ) ){
			if( amd_is_option_allowed( $on ) )
				return $this->db->insert( $table, [ 'option_name' => $on, 'option_value' => $ov ] );
			else
				return false;
		}

		return $this->db->update( $table, [ 'option_value' => $ov ], [ 'option_name' => $on ] );

	}

	/**
	 * Delete site option
	 *
	 * @param string $on
	 * Option name
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function deleteSiteOption( $on ){

		$table = $this->getTable( "options" );

		if( !self::siteOptionExists( $on ) )
			return false;

		return (bool) $this->db->delete( $table, [ 'option_name' => $on ] );

	}

	/**
	 * Add site option if not exists
	 *
	 * @param string $on
	 * Option name
	 * @param string $ov
	 * Option value
	 *
	 * @return bool|int|mysqli_result|resource|null
	 * @since 1.0.0
	 */
	public function addSiteOption( $on, $ov ){

		$table = $this->getTable( "options" );

		if( !self::siteOptionExists( $on ) )
			return $this->db->insert( $table, [ 'option_name' => $on, 'option_value' => $ov ] );

		return false;

	}

	/**
	 * Check if site option exists in database
	 *
	 * @param string $on
	 * Option name
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function siteOptionExists( $on ){

		$table = $this->getTable( "options" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` WHERE option_name='$on'" );

		return count( $res ) > 0;

	}

	/**
	 * Get site option from database
	 *
	 * @param string $on
	 * Option name
	 * @param string $default
	 * Default value
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getSiteOption( $on, $default="" ){

		$table = $this->getTable( "options" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` WHERE option_name='$on'" );

		return !empty( $res[0]->option_value ) ? $res[0]->option_value : $default;

	}

	/**
	 * Get result of regex search inside site_options table
	 * @param string $regex
	 * Search regex
	 *
	 * @return array|object|\stdClass|null
	 * @since 1.0.0
	 */
	public function searchSiteOption( $regex ){

		$table = $this->getTable( "options" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` WHERE `option_name` REGEXP '$regex'" );

		return $res;

	}

	/**
	 * Get temporarily data from database
	 *
	 * @param string $name
	 * Temp name
	 * @param bool $single
	 * Whether to return a single value of temp value or full row object.
	 *
	 * @return array|false|string
	 * @since 1.0.0
	 */
	public function getTemp( $name, $single = true ){

		self::cleanExpiredTemps();

		$table = $this->getTable( "temp" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` WHERE temp_key='$name'" );

		if( count( $res ) <= 0 )
			return $single ? "" : false;

		if( $single )
			return $res[0]->temp_value ?? "";
		else
			return $res[0];

	}

	/**
	 * Set temporarily data in database
	 *
	 * @param string $name
	 * Temp name
	 * @param string $value
	 * Temp value
	 * @param int $expire
	 * Temp expiration in seconds, e.g: 3600 (seconds) for 1 hour
	 *
	 * @return bool|int|mysqli_result|resource|null
	 * @since 1.0.0
	 */
	public function setTemp( $name, $value, $expire = 3600 ){

		self::cleanExpiredTemps();

		$expire = time() + $expire;

		$table = $this->getTable( "temp" );

		if( $this->tempExists( $name ) )
			return $this->db->update( $table, [ 'temp_value' => $value ], [ 'temp_key' => $name ] );
		else
			return $this->db->insert( $table, [ 'temp_key' => $name, 'temp_value' => $value, 'expire' => $expire ] );

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
	public function tempExists( $name ){

		$table = $this->getTable( "temp" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` WHERE temp_key='$name'" );

		return count( $res ) > 0;

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
	 * @return bool|mixed
	 * @since 1.0.0
	 */
	public function findTemp( $col, $regex, $get = false ){

		self::cleanExpiredTemps();

		$table = $this->getTable( "temp" );

		$sql = "SELECT * FROM `%{TABLE}%` WHERE `$col` REGEXP '$regex'";
		$res = $this->safeQuery( $table, $sql );

		return $get ? $res : count( $res ) > 0;

	}

	/**
	 * Search for 'temp_key' column in database
	 *
	 * @param string $regex
	 * Regex string. e.g: "test_[0-9]" -> match -> "test_12"
	 * @param bool $get
	 * Whether to get single 'temp_value' string or rows data object
	 *
	 * @return bool|mixed
	 * <code>$get=true</code>: true if any rows exist, otherwise false
	 * <br><code>$get=false</code>: founded rows data object
	 * @since 1.0.0
	 */
	public function findTempKey( $regex, $get = false ){

		self::cleanExpiredTemps();

		return $this->findTemp( "temp_key", $regex, $get );

	}

	/**
	 * Search for 'temp_value' column in database
	 *
	 * @param string $regex
	 * Regex string. e.g: "test_[0-9]" -> match -> "test_12"
	 * @param bool $get
	 * Whether to get single 'temp_value' string or rows data object
	 *
	 * @return bool|mixed
	 * <code>$get=true</code>: true if any rows exist, otherwise false
	 * <br><code>$get=false</code>: founded rows data object
	 * @since 1.0.0
	 */
	public function findTempValue( $regex, $get = false ){

		self::cleanExpiredTemps();

		return $this->findTemp( "temp_value", $regex, $get );

	}

	/**
	 * Delete temporarily data from database
	 *
	 * @param string $name
	 * The name of temp data or leave it empty for expiration check
	 * @param bool $timeCheck
	 * If you pass $name you can let it check expiration time and keep it if not expired.
	 * Otherwise, if you set it to false it'll delete it anyway.
	 *
	 * @return array|object|stdClass
	 * Result of DELETE query
	 * @since 1.0.0
	 */
	public function deleteTemp( $name = null, $timeCheck = false ){

		$table = $this->getTable( "temp" );

		if( empty( $name ) )
			$sql = "DELETE FROM `%{TABLE}%` WHERE expire <= " . time();
		else
			$sql = "DELETE FROM `%{TABLE}%` WHERE temp_key='$name'" . ( $timeCheck ? " AND expire <= " . time() : "" );

		return $this->safeQuery( $table, $sql );

	}

	/**
	 * Remove expired temps
	 * @return void
	 */
	public function cleanExpiredTemps(){

		self::deleteTemp( null, true );

	}

	/**
	 * Remove expired temps with temp_key regex
	 *
	 * @param string $regex
	 * Regex string. e.g: "[0-9]{5}"
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function cleanExpiredTempsKeys( $regex ){

		if( empty( $regex ) )
			return;

		$table = $this->getTable( "temp" );

		$this->safeQuery( $table, "DELETE FROM `%{TABLE}%` WHERE `temp_key` REGEXP '$regex'" );

	}

	/**
	 * Add _todo item
	 *
	 * @param string $key
	 * _Todo key. e.g: "user_1"
	 * @param string $value
	 * _Todo text. e.g: "Contact customer #12"
	 * @param string $status
	 * _Todo status. e.g: "pending", "done", "undone"
	 * @param string $salt
	 * _Todo salt for encoding
	 * @param array $meta
	 * _Todo meta-data. e.g: ["date" => "2023-01-02"]
	 * @param bool $encode
	 * Whether to encode $value or not.
	 * <br><b>Note: You always have to use encoded value for _todo lists, but if your _todo text is already encoded you can pass false to skip encoding</b>
	 *
	 * @return false|int
	 * Inserted row ID on success, false on failure
	 * @since 1.0.0
	 */
	public function addTodo( $key, $value, $status, $salt, $meta = [], $encode = true ){

		$encoded_value = $encode ? json_encode( amd_encrypt_aes( $value, $salt ) ) : $value;

		$table = $this->getTable( "todo" );

		$success = (bool) $this->db->insert( $table, [
			"todo_key" => $key,
			"todo_value" => $encoded_value,
			"status" => $status,
			"meta" => serialize( $meta )
		] );

		return $success ? $this->db->insert_id : false;

	}

	/**
	 * Update _todo item/list
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
	public function updateTodo( $data, $where, $salt = "" ){

		if( !empty( $data["todo_value"] ) ){
			$v = $data["todo_value"];
			$data["todo_value"] = json_encode( amd_encrypt_aes( $v, $salt ) );
		}

		$table = $this->getTable( "todo" );

		return (bool) $this->db->update( $table, $data, $where );

	}

	/**
	 * Get _todo list
	 *
	 * @param array $filters
	 * Filters array
	 * @param bool $single
	 * Whether to get single todo_value string or full row results
	 *
	 * @return array|object|stdClass|string|null
	 * @see AMD_DB::makeFilters()
	 * @since 1.0.0
	 */
	public function getTodoList( $filters, $single = false ){

		$filter = $this->makeFilters( $filters );

		$table = $this->getTable( "todo" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` " . $filter . " ORDER BY `id` DESC" );

		return $single ? ( count( $res ) > 0 ? $res[0]->todo_value : "" ) : $res;

	}

	/**
	 * Delete _todo list or item
	 *
	 * @param array $where
	 * Where array. e.g: ["id" => 12]
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function deleteTodoList( $where ){

		$table = $this->getTable( "todo" );

		return (bool) $this->db->delete( $table, $where );

	}

	/**
	 * Clean plugin data from database
	 *
	 * @return false|mixed
	 * @since 1.0.0
	 */
	public function cleanup(){

		$tables = "";
		foreach( $this->tablesSQL as $table_name => $sql )
			$tables .= "`" . $this->prefix . "$table_name`, ";

		$tables = trim( $tables, ", " );

		if( empty( $tables ) )
			return false;

		return $this->query( "DROP TABLE $tables" );

	}

}