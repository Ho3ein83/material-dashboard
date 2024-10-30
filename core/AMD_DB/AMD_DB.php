<?php

/** @var AMD_DB $amdDB */
$amdDB = null;

class AMD_DB {

	/**
	 * Tables list
	 * @var array
	 * @since 1.0.0
	 */
	protected $tables;

	/**
	 * Dashboard tables list
	 * @var array
	 * @since 1.0.0
	 */
	public $dashboard_tables;

	/**
	 * WordPress database prefix
	 * @var string
	 * @since 1.0.0
	 */
	public $wp_prefix;

	/**
	 * AMD database prefix
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
	 * Allowed HTML tags for to-do and other contents saving
	 * @var array
	 * @since 1.0.5
	 */
	protected $allowedHtmlTags;

	/**
	 * Version code for database setup
	 * @var int
	 * @since 1.0.5
	 */
	const db_version = 3;

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

		$this->dashboard_tables = [];

		$this->export_variants = [];

		$this->allowedHtmlTags = amd_allowed_tags_with_attr( "p,button,a,div,img,i,b,strong,span,br,small,ul,ol,li,em,u" );

		# initialize database
		self::init();

		# Setup database
		add_action( "amd_after_cores_init", function(){

			$ver = intval( amd_get_site_option( "db_version", "0" ) );
			if( $ver < self::db_version ){

				/**
				 * Update database
				 * @sicne 1.0.5
				 */
				do_action( "amd_update_db", self::db_version );

				amd_set_site_option( "db_version", self::db_version );

			}

		}, 99 );

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

		self::registerTable( "reports", array(
			"id" => "INT NOT NULL AUTO_INCREMENT",
			"report_key" => "VARCHAR(64) NOT NULL",
			"report_user" => "VARCHAR(64) NOT NULL",
			"report_value" => "LONGTEXT NOT NULL",
			"report_time" => "VARCHAR(64) NOT NULL",
			"meta" => "LONGTEXT NOT NULL",
			"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = MyISAM;"
		) );

		self::registerTable( "components", array(
			"id" => "INT NOT NULL AUTO_INCREMENT",
			"component_key" => "VARCHAR(64) NOT NULL",
			"component_type" => "VARCHAR(64) NOT NULL",
			"component_data" => "LONGTEXT NOT NULL",
			"component_time" => "VARCHAR(64) NOT NULL",
			"meta" => "LONGTEXT NOT NULL",
			"EXTRA" => " PRIMARY KEY (`id`)) ENGINE = MyISAM;"
		) );

		if( $install == true )
			self::install();

	}

	/**
	 * Repair database tables and fix collations
	 * @return void
	 * @since 1.0.5
	 */
	public function repairTables(){

		foreach( self::getTables() as $table_name => $table ){

			if( self::isDashboardTable( $table_name ) ){

				$collation = self::getTableCollation( $table );

				/**
				 * Default collation for dashboard tables
				 * @since 1.0.5
				 */
				$default_collation = apply_filters( "amt_default_tables_collation", "utf8mb4_persian_ci" );

				if( $collation != $default_collation )
					self::collateTable( $table, $default_collation );

			}

		}

		/**
		 * Repair database tables
		 * @since 1.0.6
		 */
		do_action( "amd_repair_database_tables" );

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

		$this->dashboard_tables[$table_name] = true;

		return true;

	}

	/**
	 * Check if table is one of the dashboard tables
	 * @param string $table_name
	 * Table name
	 *
	 * @return bool
	 * @since 1.0.5
	 */
	public function isDashboardTable( $table_name ){

		return (bool) ($this->dashboard_tables[$table_name] ?? false);

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
	 * @param bool $listedMessages
	 * Whether to get listed messages or not
	 *
	 * @return array
	 * Array data. Template:
	 * <br><code>array( "success" => [bool], "data" => [array] )</code>
	 * @since 1.0.0
	 */
	public function importJSON( $mode, $json, $overwrite = true, $listedMessages=false ){

		if( empty( $json ) )
			return [
				"success" => false,
				"data" => [ "msg" => esc_html__( "JSON data is invalid", "material-dashboard" ) ],
				"messages" => []
			];

		$messages = [];

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
					list( , $msg, $p, $m ) = call_user_func( $callable, $d, $overwrite );
					if( $listedMessages AND $msg )
						$messages[] = $msg;
					$missed += $m;
					$progress[$id] = $p;
				}

			}

			$msg = esc_html__( "Data imported successfully", "material-dashboard" );
			if( $missed > 0 )
				$msg .= esc_html__( ", however some data couldn't be imported. For more information check for error code 901 in documentation", "material-dashboard" );

			return [
				"success" => true,
				"data" => [ "msg" => $msg, "missed" => $missed, "progress" => $progress ],
				"messages" => $messages
			];

		}
		else{
			do_action( "amd_handle_import_method_$mode", $json, $overwrite );
			// TODO: take method response
		}

		return [
			"success" => false,
			"data" => [ "msg" => esc_html__( "Import method is not allowed", "material-dashboard" ) ],
			"messages" => []
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
			$result = $amdDB->importJSON( "auto", $json_content, $overwrite, true );
			if( $result["success"] ?? false ){
				if( is_array( $result["messages"] ?? false ) ){
					foreach( $result["messages"] as $message ){
						if( !$message )
							continue;
						$messages[] = [
							"success" => true,
							"msg" => $message
						];
					}
				}
				else{
					$messages[] = [
						"success" => true,
						"msg" => esc_html_x( "Site settings imported", "Admin", "material-dashboard" )
					];
				}
			}
			else{
				$messages[] = [
					"success" => false,
					"msg" => esc_html_x( "Site settings import failed", "Admin", "material-dashboard" )
				];
			}
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
	 * @param bool $force
	 * Whether to guess table name or return empty string, default is `false`
	 *
	 * @return string
	 * Table name or empty string for undefined tables
	 * @since 1.0.0
	 */
	public function getTable( $tableName, $force=false ){

		self::init();

		return $this->tables[$tableName] ?? ( $force ? $this->prefix . $tableName : "" );

	}

	/**
	 * Get tables list
	 *
	 * @return array
	 * @since 1.0.5
	 */
	public function getTables(){

		return $this->tables;

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

		if( !empty( $filters ) AND is_iterable( $filters ) ){

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

		if( !empty( $orders ) AND is_iterable( $orders ) ){

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
	 * @param bool $ignoreCaches
	 * <code>[Since 1.0.5] </code>
	 * Whether to ignore caches and set site option only into database and do not change cached items,<br>
	 * using caches can improve site performance but site options may be old
	 *
	 * @return bool|int|mysqli_result|resource|null
	 * On update: The number of rows updated, or false on error
	 * <br>On insert: The number of rows inserted, or false on error
	 * @since 1.0.0
	 */
	public function setSiteOption( $on, $ov, $ignoreCaches=false ){

		global $amdCache;
		$cache_key = "_so:$on";

		/**
		 * Whether to ignore caches and set options only into database
		 * @since 1.0.5
		 */
		$ignoreCaches = apply_filters( "amd_ignore_site_option_cache", $ignoreCaches, $on ) === true;

		if( !$amdCache )
			$ignoreCaches = true;

		$table = $this->getTable( "options" );

		if( !self::siteOptionExists( $on ) )
			$complete = $this->db->insert( $table, [ 'option_name' => $on, 'option_value' => $ov ] );
		else
			$complete = $this->db->update( $table, [ 'option_value' => $ov ], [ 'option_name' => $on ] ) !== false;

		if( !$ignoreCaches AND $complete )
			$amdCache->setCache( $cache_key, $ov );

		return $complete;

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

		global $amdCache;
		$cache_key = "_so:$on";

		if( $amdCache AND $amdCache->cacheExists( $cache_key ) )
			$amdCache->removeCache( $cache_key );

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
	 * @param bool $ignoreCaches
	 * <code>[Since 1.0.5] </code>
	 * Whether to ignore caches and get site option directly from database or get it from system caches,<br>
	 * using caches can improve site performance but site options may be old
	 *
	 * @return bool|int|mysqli_result|resource|null
	 * @since 1.0.0
	 */
	public function addSiteOption( $on, $ov, $ignoreCaches=false ){

		global $amdCache;
		$cache_key = "_so:$on";

		/**
		 * Whether to ignore caches and set options only into database
		 * @since 1.0.5
		 */
		$ignoreCaches = apply_filters( "amd_ignore_site_option_cache", $ignoreCaches, $on ) === true;

		if( !$amdCache )
			$ignoreCaches = true;

		$table = $this->getTable( "options" );

		if( !$ignoreCaches )
			$amdCache->setCache( $cache_key, $ov );

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
	 * @param bool $ignoreCaches
	 * <code>[Since 1.0.5] </code>
	 * Whether to ignore caches and get site option directly from database or get it from system caches,<br>
	 * using caches can improve site performance but site options may be old
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getSiteOption( $on, $default="", $ignoreCaches=false ){

		$on = sanitize_text_field( $on );

		global $amdCache;
		$cache_key = "_so:$on";

		/**
		 * Whether to ignore caches and read options directly from database
		 * @since 1.0.5
		 */
		$ignoreCaches = apply_filters( "amd_ignore_site_option_cache", $ignoreCaches, $on ) === true;

		if( !$amdCache )
			$ignoreCaches = true;

		if( !$ignoreCaches ){
			if( $amdCache->cacheExists( $cache_key ) ){
				$v = $amdCache->getCache( $cache_key, "scope" );

				/**
				 * Cache restore hook
				 * @since 1.0.5
				 */
				do_action( "amd_site_option_cache_restored", $v, $on );

				return $v;
			}
		}

		$table = $this->getTable( "options" );

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE option_name=%s", $table, $on );

		$res = $this->safeQuery( $table, $sql );

		$value = !empty( $res[0]->option_value ) ? $res[0]->option_value : null;

		if( !$ignoreCaches ){
			$amdCache->setCache( $cache_key, $value );

			/**
			 * Cache store hook
			 * @since 1.0.5
			 */
			do_action( "amd_site_option_cache_stored", $res, $on );
		}

		return $value !== null ? $value : $default;

	}

	/**
	 * Get result of regex search inside site_options table
	 * @param string $regex
	 * Search regex
	 * @param bool $ignoreCaches
	 * <code>[Since 1.0.5] </code>
	 * Whether to ignore caches and get site option directly from database or get it from system caches,<br>
	 * using caches can improve site performance but site options may be old
	 *
	 * @return array|object|stdClass|null
	 * @since 1.0.0
	 */
	public function searchSiteOption( $regex, $ignoreCaches=false ){

		global $amdCache;
		$cache_key = "_so_search:$regex";

		/**
		 * Whether to ignore caches and read options directly from database
		 * @since 1.0.5
		 */
		$ignoreCaches = apply_filters( "amd_ignore_site_option_search_cache", $ignoreCaches ) === true;

		if( !$amdCache )
			$ignoreCaches = true;

		if( !$ignoreCaches ){
			if( $amdCache->cacheExists( $cache_key ) ){
				$v = $amdCache->getCache( $cache_key, "scope" );

				/**
				 * Cache restore hook
				 * @since 1.0.5
				 */
				do_action( "amd_site_option_search_cache_restored", $v, $regex );

				return $v;
			}
		}

		$table = $this->getTable( "options" );

		$res = $this->safeQuery( $table, "SELECT * FROM `%{TABLE}%` WHERE `option_name` REGEXP '$regex'" );

		if( !$ignoreCaches ){
			$amdCache->setCache( $cache_key, $res );

			/**
			 * Cache store hook
			 * @since 1.0.5
			 */
			do_action( "amd_site_option_search_cache_stored", $res, $regex );
		}

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

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE temp_key=%s", $table, $name );

		$res = $this->safeQuery( $table, $sql );

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
			return $this->db->update( $table, [ 'temp_value' => $value ], [ 'temp_key' => $name ] ) !== false;
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

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE temp_key=%s", $table, $name );

		$res = $this->safeQuery( $table, $sql );

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

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE %i REGEXP '$regex'", $table, $col );
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
			$sql = "DELETE FROM %i WHERE expire <= " . time();
		else
			$sql = "DELETE FROM %i WHERE temp_key='$name'" . ( $timeCheck ? " AND expire <= " . time() : "" );

		$sql = $this->db->prepare( $sql, $table );

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

		$sql = $this->db->prepare( "DELETE FROM %i WHERE `temp_key` REGEXP '$regex'", $table );

		$this->safeQuery( $table, $sql );

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
	 * Inserted row ID on success, false on failure
	 * @since 1.0.0
	 */
	public function addTodo( $key, $value, $status, $salt, $meta = [], $encode = true ){

		$encoded_value = $encode ? json_encode( amd_encrypt_aes( self::formatHtml( $value ), $salt ) ) : $value;

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
	 * Format HTML contents and strip tags
	 *
	 * @param string $html
	 * HTML content
	 * @param array|null $tags
	 * Allowed tags for {@see wp_kses} function or null to use default tags
	 *
	 * @return string
	 * Filtered HTML content
	 * @since 1.0.5
	 */
	public function formatHtml( $html, $tags=null ){

		if( $tags === null )
			$tags = $this->allowedHtmlTags;

		return wp_kses( $html, $tags );

	}

	/**
	 * Get allowed HTML tags
	 *
	 * @param bool $simple
	 * Whether to get simple tags array list or get complete tags data
	 *
	 * @return array
	 * @since 1.1.1
	 */
	public function getAllowedHtmlTags( $simple=false ){

		if( $simple )
			return array_keys( $this->allowedHtmlTags );

		return $this->allowedHtmlTags;

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
	public function updateTodo( $data, $where, $salt = "" ){

		if( !empty( $where["id"] ) ){
			$id = $where["id"];
			if( !empty( $data["priority"] ?? null )){
				global $amdDB;
				$amdDB->setTodoMeta( $id, "priority", intval( $data["priority"] ) );
			}
		}

		if( !empty( $data["todo_value"] ) ){
			$v = $data["todo_value"];
			$v = wp_kses( $v, $this->allowedHtmlTags );
			$data["todo_value"] = json_encode( amd_encrypt_aes( $v, $salt ) );
		}

		if( !empty( $data["status"] ) ){
			$s = $data["status"];
			if( !in_array( $s, apply_filters( "amd_ext_todo_allowed_status_ids", [] ) ) )
				$data["status"] = "pending";
		}

		$table = $this->getTable( "todo" );

		return $this->db->update( $table, $data, $where ) !== false;

	}

	/**
	 * Update to-do item meta-data
	 * @param int $id
	 * To-do ID
	 * @param string $meta_name
	 * Meta name
	 * @param string $meta_value
	 * Meta value to change / push / pull
	 * @param false|int $pullOrPush
	 * Whether to update the whole value or pull / push<br>
	 * <b>false:</b> Update,
	 * <b>1:</b> Push,
	 * <b>2:</b> Pull
	 * @param bool $delete
	 * Whether to delete meta item or not. If pass true, $meta_value and $pullOrPush parameters won't be used
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function setTodoMeta( $id, $meta_name, $meta_value, $pullOrPush=false, $delete=false ){

		$task = self::getTodoList( ["id" => $id] );

		if( !empty( $task[0] ) ){

			$meta = unserialize( $task[0]->meta ?? serialize( [] ) );

			if( !is_array( $meta ) )
				$meta = [];

			if( $delete ){
				$meta[$meta_name] = null;
				unset( $meta[$meta_name] );
			}
			else{
				$value = $meta_value;
				if( $pullOrPush ){
					$value = $meta[$meta_name] ?? "";
					if( $pullOrPush === 1 )
						$value = amd_push_value( $value, $meta_value );
					else if( $pullOrPush === 2 )
						$value = amd_pull_value( $value, $meta_value );
				}
				$meta[$meta_name] = $value;
			}

			return self::updateTodo( ["meta" => serialize( $meta )], ["id" => $id] );

		}

		return false;

	}

	/**
	 * Get to-do item meta-data
	 * @param int $id
	 * To-do item ID
	 * @param string $meta_name
	 * Meta name
	 * @param string|mixed $default
	 * Default value to be returned, default is empty string
	 *
	 * @return mixed|string
	 * String meta value on success, $default parameter on failure
	 * @since 1.0.5
	 */
	public function getTodoMeta( $id, $meta_name, $default="" ){

		$task = self::getTodoList( ["id" => $id] );

		if( !empty( $task[0] ) ){

			$meta = unserialize( $task[0]->meta ?? serialize( [] ) );

			return $meta[$meta_name] ?: $default;

		}

		return $default;

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
	 * @since 1.0.0
	 */
	public function getTodoList( $filters, $single = false ){

		$filter = $this->makeFilters( $filters );

		$table = $this->getTable( "todo" );

		$sql = $this->db->prepare( "SELECT * FROM %i " . $filter . " ORDER BY `id` DESC", $table );

		$res = $this->safeQuery( $table, $sql );

		return $single ? ( count( $res ) > 0 ? $res[0]->todo_value : "" ) : $res;

	}

	/**
	 * Delete to-do list or item
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

	/**
	 * Get table collation
	 * @param string $table
	 * Table name
	 *
	 * @return string
	 * Table collation string or empty string on failure
	 * @since 1.0.5
	 */
	public function getTableCollation( $table ){

		$res = self::safeQuery( $table, "SHOW TABLE STATUS LIKE '%{TABLE}%'" );

		if( empty( $res[0] ) )
			return "";

		return $res[0]->Collation ?? "";

	}

	/**
	 * Change table collation
	 * @param string $table
	 * Table name
	 * @param string $collation
	 * New collation string
	 *
	 * @return void
	 * @since 1.0.5
	 */
	public function collateTable( $table, $collation ){

		$sql = $this->db->prepare( "ALTER TABLE %i COLLATE %s", $table, $collation );

		$this->db->query( $sql );

	}

	/**
	 * Register new report
	 * @param string $key
	 * Report key
	 * @param string $value
	 * Report value
	 * @param int|false $user
	 * User ID or false to ignore user
	 * @param array $meta
	 * Meta-data array
	 *
	 * @return false|int
	 * Inserted row ID or false on failure
	 * @since 1.0.5
	 */
	public function addReport( $key, $value, $user=false, $meta=[] ){

		$table = $this->getTable( "reports" );

		return $this->safeInsert( $table, array(
			"report_key" => $key,
			"report_value" => $value,
			"report_user" => $user ?: "",
			"report_time" => time(),
			"meta" => serialize( $meta )
		) );

	}

	/**
	 * Read report card(s) from database
	 * @param string $key
	 * Report key
	 * @param int|false $user
	 * User ID or false to ignore user
	 * @param bool $single
	 * Whether to get first result
	 * @param string $order
	 * Results order, 'ASC' or 'DESC'
	 *
	 * @return array|mixed|object|stdClass
	 * Result array for group results, stdClass for single result
	 * @since 1.0.5
	 */
	public function readReports( $key, $user=false, $single=false, $order="DESC" ){

		$table = $this->getTable( "reports" );

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE `report_key`=%s AND `report_user`=%s ORDER BY `id` $order", $table, $key, $user ? "$user" : "" );

		$res = $this->safeQuery( $table, $sql );

		if( !empty( $res ) )
			return $single ? $res[0] : $res;

		return [];

	}

	/**
	 * Get report by ID
	 * @param int $report_id
	 * Report ID
	 *
	 * @return mixed
	 * Report single object result from database
	 * @since 1.0.5
	 */
	public function getReport( $report_id ){

		$table = $this->getTable( "reports" );

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE `id`=%d", $table, $report_id );

		$res = $this->safeQuery( $table, $sql );

		return !empty( $res[0] ) ? $res[0] : [];

	}

	/**
	 * Edit report item from database
	 * @param int $id
	 * Report ID
	 * @param array $data
	 * Report data
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function editReport( $id, $data ){

		$table = $this->getTable( "reports" );

		return $this->db->update( $table, $data, ["id" => $id] ) !== false;

	}

	/**
	 * Delete report
	 * @param array $where
	 * Where clauses
	 *
	 * @return bool
	 * True on success, false on failure
	 * @sonce 1.0.7
	 */
	public function deleteReport( $where ){

		$table = $this->getTable( "reports" );

		return (bool) $this->db->delete( $table, $where );

	}

	/**
	 * Delete report
	 * @param string $key
	 * Report key
	 * @param string $value
	 * Report value
	 * @param int|false $user
	 * User ID or false to ignore user
	 *
	 * @return bool
	 * True on success, false on failure
	 * @sonce 1.0.7
	 */
	public function deleteReportClauses( $key, $value, $user=false ){

		$where = array(
			"report_key" => $key,
			"report_value" => $value,
		);

		if( $user )
			$where["report_user"] = $user;

		return self::deleteReport( $where );

	}

	/**
	 * Search inside reports with custom filter
	 * @param array $filters
	 * Filters array, see {@see AMD_DB::makeFilters()}
	 *
	 * @param array $orders
	 * Orders array, see {@see AMD_DB::makeOrder())}
	 *
	 * @return array|object|stdClass|null
	 * @since 1.0.7
	 */
	public function searchReports( $filters = [], $orders = [] ){

		$filter = $this->makeFilters( $filters );
		$order = $this->makeOrder( $orders );

		$table = $this->getTable( "reports" );

		$sql = $this->db->prepare( "SELECT * FROM %i " . $filter . " " . $order, $table );

		return $this->safeQuery( $table, $sql );

	}

	/**
	 * Insert new component in database
	 * @param string $type
	 * Component type (custom string)
	 * @param string $data
	 * Component data (must be string, you can encode objects to JSON)
	 * @param string|null $key
	 * Component unique key, pass null to generate automatically
	 * @param array|null $meta
	 * Component meta-data, pass null to use default meta-data
	 *
	 * @return false|int
	 * Row ID on success, otherwise false
	 * @since 1.0.5
	 */
	public function addComponent( $type, $data, $key=null, $meta = null ){

		if( !$key )
			$key = amd_generate_string( 16 );

		if( $meta === null )
			$meta = ["author" => get_current_user_id()];

		$table = $this->getTable( "components" );

		return $this->safeInsert( $table, array(
			"component_key" => $key,
			"component_type" => $type,
			"component_data" => $data,
			"component_time" => time(),
			"meta" => json_encode( $meta )
		) );

	}

	/**
	 * Insert new component or update if it already exists
	 *
	 * @param int|empty $id
	 * Component ID
	 * @param array $data
	 * Data array
	 * @param bool $allow_insert
	 * Whether to insert new component if component doesn't exist
	 *
	 * @return bool|int
	 * True on successfully update, row ID on successfully insert, otherwise false
	 * @since 1.0.5
	 */
	public function upsertComponent( $id, $data, $allow_insert=true ){

		$type = $data["component_type"];
		$_data = $data["component_data"];
		$key = $data["component_key"] ?? null;
		$meta = $data["meta"] ?? null;

		if( empty( $id ) OR !self::componentExist( $id ) )
			return $allow_insert ? self::addComponent( $type, $_data, $key, $meta ) : false;

		$update_data = ["component_data" => $_data];

		if( $key )
			$update_data["component_key"] = $key;

		return self::updateComponent( $id, $update_data );

	}

	/**
	 * Check if component exist
	 * @param string|array $field
	 * Value to search inside components or filter array to use custom filter, default is component ID
	 * @param string $by
	 * Field to search into, default is "id"
	 *
	 * @return bool|int
	 * True or first result ID if component(s) does exist, otherwise false
	 * @since 1.0.5
	 */
	public function componentExist( $field, $by="id" ){

		$table = $this->getTable( "components" );

		if( is_array( $field ) ){
			$filter = self::makeFilters( $field );
			$sql = $this->db->prepare( "SELECT * FROM %i " . $filter, $table );
		}
		else{
			$by = strtolower( $by );
			$by = str_replace( "type", "component_type", $by );
			$by = str_replace( "key", "component_key", $by );

			$sql = $this->db->prepare( "SELECT * FROM %i WHERE %i=%s", $table, $by, $field );
		}

		$res = $this->safeQuery( $table, $sql );

		return !empty( $res ) ? $res[0]->id : false;

	}

	/**
	 * Update component using where clauses
	 * @param array $data
	 * Data to update
	 * @param array $where
	 * A named array of WHERE clauses
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 * @see wpdb::update()
	 */
	public function updateComponentWhere( $data, $where ){

		$table = $this->getTable( "components" );

		return $this->db->update( $table, $data, $where ) !== false;

	}

	/**
	 * Update component
	 * @param int $id
	 * Component ID
	 * @param array $data
	 * Component data to update with {@see wpdb::update()}
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function updateComponent( $id, $data ){

		return self::updateComponentWhere( $data, ["id" => $id] );

	}

	/**
	 * Delete component with ID
	 * @param int $id
	 * Component ID to delete
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function deleteComponent( $id ){

		return self::deleteComponentBy( "id", $id );

	}

	/**
	 * Delete component(s) with specific field
	 * @param string $by
	 * Case-insensitive field name (e.g: "ID", "id", "type", "key", "component_key", "component_type")
	 * @param string $field
	 * Field value to search
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function deleteComponentBy( $by, $field ){

		$by = strtolower( $by );
		$by = str_replace( "type", "component_type", $by );
		$by = str_replace( "key", "component_key", $by );

		return self::deleteComponentWhere( [$by => $field] );

	}

	/**
	 * Delete component(s) with custom filter
	 * @param array $where
	 * A named array of WHERE clauses
	 *
	 * @return bool
	 * @since 1.0.5
	 * @see wpdb::delete()
	 */
	public function deleteComponentWhere( $where ){

		$table = $this->getTable( "components" );

		return (bool) $this->db->delete( $table, $where );

	}

	/**
	 * Get components using custom filter and order
	 * @param array $filters
	 * Filters array, see {@see AMD_DB::makeFilters()}
	 * @param array $orders
	 * Orders array, see {@see AMD_DB::makeOrder()}
	 * @param bool $make
	 * Whether to make object for results or not
	 * @param bool $single
	 * Whether to return first result or full results
	 *
	 * @return AMDComponent|array|mixed|object|stdClass|null
	 * {@see AMDComponent} object if you need to make object, otherwise {@see wpdb::query()} results
	 * @since 1.0.8
	 */
	public function filterComponents( $filters=[], $orders=[], $make=false, $single=false ){

		$filter = self::makeFilters( $filters );
		$order = self::makeOrder( $orders );

		$table = $this->getTable( "components" );

		$sql = $this->db->prepare( "SELECT * FROM %i $filter $order", $table );

		$res = $this->safeQuery( $table, $sql );

		if( $make )
			return self::makeComponents( $res, $single );

		return $single ? ( $res[0] ?? [] ) : $res;

	}

	/**
	 * Get components by specific field
	 * @param string $by
	 * Case-insensitive field name (e.g: "ID", "id", "type", "key", "component_key", "component_type")
	 * @param string $part
	 * Field value to search
	 * @param bool $make
	 * Whether to make object for results or not
	 * @param bool $single
	 * Whether to return first result or full results
	 *
	 * @return AMDComponent|array|mixed|object|stdClass|null
	 * {@see AMDComponent} object if you need to make object, otherwise {@see wpdb::query()} results
	 * @since 1.0.5
	 */
	public function getComponents( $by, $part, $make=false, $single=false ){

		$by = strtolower( $by );
		$by = str_replace( "type", "component_type", $by );
		$by = str_replace( "key", "component_key", $by );

		$table = $this->getTable( "components" );

		$sql = $this->db->prepare( "SELECT * FROM %i WHERE %i=%s", $table, $by, $part );

		$res = $this->safeQuery( $table, $sql );

		if( $make )
			return self::makeComponents( $res, $single );

		return $single ? ( $res[0] ?? [] ) : $res;

	}

	/**
	 * Make component objects
	 * @param mixed $results
	 * Database query results
	 * @param bool $single
	 * Whether to get first object or full results list
	 *
	 * @return AMDComponent|array
	 * Single {@see AMDComponent} object if $single is true, otherwise array of made objects
	 *
	 * @since 1.0.5
	 */
	public function makeComponents( $results, $single = false ){

		require_once( AMD_CORE . "/objects/AMDComponent.php" );

		if( empty( $results ) )
			return new AMDComponent();


		$out = [];

		foreach( $results as $result ){

			$id = $result->id ?? null;

			if( !$id )
				continue;

			$obj = new AMDComponent();

			$obj->set_id( $id );
			$obj->set_type( $result->component_type );
			$obj->set_key( $result->component_key );
			$obj->set_data( $result->component_data );
			$obj->set_time( intval( $result->component_time ) );
			$obj->set_meta( @json_decode( $result->meta, true ) );

			if( $single )
				return $obj;

			$out[] = $obj;

		}

		return $out;

	}

	/**
	 * Update component meta-data from database
	 * @param int $id
	 * Component ID
	 * @param string|string[] $meta_key_s
	 * Single meta key like "my_field" or names list for multiple items, like: ["field_1", "field_2", "field_3"]
	 * @param scalar|array $meta_value_s
	 * Single meta value like "my_value" ot values list for multiple items, like ["value_1", 100, "value_3"]
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.5
	 */
	public function setComponentMeta( $id, $meta_key_s, $meta_value_s ){

		$c = self::getComponents( "id", $id, true, true );

		if( $c->get_id() ){

			$meta = $c->get_meta();

			if( !is_array( $meta ) )
				$meta = [];

			if( is_string( $meta_key_s ) )
				$meta[$meta_key_s] = $meta_value_s;

			if( is_array( $meta_key_s ) ){
				for( $i = 0; $i < count( $meta_key_s ); $i++ )
					$meta[$meta_key_s[$i]] = $meta_value_s[$i] ?? "";
			}

			return self::updateComponent( $id, ["meta" => json_encode( $meta )] );

		}

		return false;

	}

	/**
	 * Build component data array to export or print in JavaScript
	 * @param array $items
	 * {@see AMD_DB::getComponents()} array list output
	 *
	 * @return array
	 * Simple data array
	 * @since 1.0.5
	 */
	public function buildComponentsData( $items ){

		$data = [];

		foreach( $items as $item ){
			$key = $item->component_key ?? "";
			if( $key )
				$data[$key] = @json_decode( $item->component_data ?? "" );
		}

		return $data;
	}

}