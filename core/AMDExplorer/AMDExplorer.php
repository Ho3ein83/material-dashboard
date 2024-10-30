<?php

/** @var AMDExplorer $amdExp */
$amdExp = null;

class AMDExplorer{

	/**
	 * Custom path for plugin stuff and uploads
	 * @var string
	 * @since 1.0.0
	 */
	protected $path;

	/**
	 * Uploads URL for plugin stuff
	 * @var string
	 * @since 1.0.0
	 */
	protected $url;

	/**
	 * File system object
	 * @var WP_Filesystem_Base
	 * @since 1.0.0
	 */
	public $sys;

	/**
	 * File explorer core
	 */
	public function __construct(){

		global /** @var WP_Filesystem_Base $wp_filesystem */
		$wp_filesystem;

		require_once( ABSPATH . "/wp-admin/includes/file.php" );

		WP_Filesystem();

		$this->sys = $wp_filesystem;

		$this->path = WP_CONTENT_DIR . "/uploads/" . AMD_DIRECTORY;

		if( !file_exists( $this->path ) )
			mkdir( $this->path );

		$this->url = get_site_url() . "/wp-content/uploads/" . AMD_DIRECTORY;

	}

	/**
	 * Initialize
	 * @return void
	 */
	public function init(){

		self::makeDirectoryRecursive( [ "avatars", "backups" ] );

	}

	/**
	 * Get default paths
	 *
	 * @param string $id
	 * Path ID, properties: "upload|uploads", "avatar|avatars", "backup", "user_upload"
	 * @param bool $single
	 * Whether to return single directory name or the full path
	 *
	 * @return string|null
	 * @since 1.0.0
	 */
	public function getPath( $id, $single = false ){

		$prefix = ( $single ? "" : $this->path . "/" );
		switch( $id ){
			case "upload":
            case "backup":
            case "uploads":
				return $prefix . "backup";
			case "avatar":
			case "avatars":
				return $prefix . "avatars";
            case "users_upload":
				return $prefix . "users";
		}

		return ( $single ? null : $this->path );

	}

	/**
	 * Get path URL. e.g:
	 * <br><code>echo $amdExp->pathURL( "backup", "my_file.png" );</code>
	 * <br>Output:
	 * <br>`http://site.com/path/to/backup/my_file.png`
	 *
	 * @param string $id
	 * Path ID
	 * @param string $file
	 * Filename
	 *
	 * @return string
	 * @see self::getPath()
	 * @since 1.0.0
	 */
	public function pathURL( $id, $file = "" ){

		$path = self::getPath( $id, true );

		return trim( $this->url . "/$path/$file", "/" );

	}

	/**
	 * Create directory in plugin uploads path
	 *
	 * @param string $dir
	 * Directory name
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function makeDirectory( $dir ){

		$dir = sanitize_file_name( $dir );

		if( !$this->sys->exists( $this->path . "/$dir" ) ){
			$create = $this->sys->mkdir( $this->path . "/$dir" );
			if( !$create )
				return false;
		}

		return true;

	}

	/**
	 * Create directory from an array recursively
	 *
	 * @param array $array
	 * Directories list, e.g: ["dir_1", "dir_2", "dir_3"]
	 *
	 * @return array|false
	 * False on failure, flags array on success, e.g:
	 * <br>$flags:
	 * <br><code>array(
	 *   "dir_1" =&gt; true,
	 *   "dir_2" =&gt; true,
	 *   "dir_3" =&gt; false
	 * )</code>
	 * @since 1.0.0
	 */
	public function makeDirectoryRecursive( $array ){

		if( !is_array( $array ) )
			return false;

		$flags = [];

		foreach( $array as $dir )
			$flags[$dir] = self::makeDirectory( $dir );

		return $flags;

	}

	/**
	 * Delete directory or file
	 * @param string $dir
	 * Directory or file path
	 * @param bool $recursive
	 * Whether to delete directory recursively or not
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function deleteFile( $dir, $recursive = false ){

		return $this->sys->rmdir( $dir, $recursive );

	}

	/**
	 * Delete files with pattern from directory
	 * @param string $path
	 * Target directory path
	 * @param string $pattern
	 * File regex pattern, e.g:
	 * <ul>
	 * <li>Delete all png files: <b>(.*)\.png</b></li>
	 * <li>Delete all png, jpg and svg files: <b>(.*)\.(png|jpg|svg)</b></li>
	 * <li>Delete all files starts with 'abc': <b>^abc</b></li>
	 * </ul>
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function deletePattern( $path, $pattern ){

		$files = glob( "$path/*", GLOB_BRACE );

		if( !$files )
			return false;

		foreach( $files as $file ){
			$filename = pathinfo( $file, PATHINFO_BASENAME );
			if( preg_match( $pattern, $filename ) )
				self::deleteFile( "$path/$filename" );
		}

		return true;

	}

	/**
	 * Check if file(s) with pattern exists
	 * @param string $path
	 * Target directory path
	 * @param string $pattern
	 * Regex pattern, e.g: <code>/^image_(.*)\.png$/</code>
	 * @param bool $get_first_match
	 * Whether to get first matched file or just check files availability
	 *
	 * @return string|bool
	 * <code>$get_first_match: true</code> -> First matched file name if found, empty string otherwise
	 * <br><code>$get_first_match: false</code> -> True on file pattern found, false otherwise
	 * @since 1.0.0
	 */
	public function patternExists( $path, $pattern, $get_first_match=false ){

		$files = glob( "$path/*", GLOB_BRACE );

		if( !$files )
			return false;

		foreach( $files as $file ){
			$filename = (string) pathinfo( $file, PATHINFO_BASENAME );
			if( preg_match( $pattern, $filename ) )
				return $get_first_match ? $filename : true;
		}

		return false;

	}

	/**
	 * Put backup file content to desired directory
	 *
	 * @param string|mixed $file_content
	 * File content data (string or binary data)
	 * @param string $file_name
	 * Target filename
	 *
	 * @return bool
	 * False on failure, file size on success
	 * @since 1.0.0
	 */
	public function makeBackup( $file_content, $file_name = null ){

		self::makeDirectory( $this->getPath( "backup", true ) );

		if( empty( $file_name ) )
			$file_name = time() . "_" . amd_generate_string( 4, "number" );

		return file_put_contents( $this->getPath( "backup" ) . "/$file_name", $file_content );

	}

	/**
	 * Remove backup file from default directory
	 * @param string $filename
	 * Filename
	 *
	 * @return bool
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function removeBackup( $filename ){

		$file = $this->getPath( "backup" ) . "/$filename";

		if( !$this->sys->exists( $file ) )
			return true;

		return $this->sys->rmdir( $file );

	}

	/**
	 * Move uploaded file to users upload directory
	 *
	 * @param array $file
	 * User uploaded single file item from $_FILE
	 * @param string $temp
	 * Upload temp file path
	 * @param string $filename
	 * New filename
	 * @param string $target_dir
	 * Target directory
	 *
	 * @return array
	 * True on success, false on failure
	 * @since 1.0.0
	 */
	public function moveUserUpload( $file, $temp, $filename, $target_dir ){

		$path = $this->getPath( "users_upload" );

		$this->makeDirectory( $this->getPath( "users_upload", true ) );

		if( !file_exists( "$path/$target_dir" ) OR !is_dir( "$path/$target_dir" ) )
			$this->sys->mkdir( "$path/$target_dir" );

		# Validate allowed file types
		$extension = pathinfo( $filename, PATHINFO_EXTENSION );
		$allowed_formats = apply_filters( "amd_user_upload_allowed_formats", ["*"] );
		if( is_array( $allowed_formats ) ){
			if( !in_array( "*", $allowed_formats ) ){
				$mime = amd_guess_mime_type_from_extension( $extension );
				if( !in_array( $mime, $allowed_formats ) AND !in_array( $extension, $allowed_formats ) OR empty( $extension ) )
					return array(
						"success" => false,
						"error" => sprintf( esc_html__( "%s file format is not allowed", "material-dashboard" ), $extension )
					);
			}
		}

		# Validate max file size
		$file_size = filesize( $temp );
		$max_size = apply_filters( "amd_user_upload_max_upload_size", 1024*1024*5 ); // 5MB
		if( $file_size > $max_size )
			return array(
				"success" => false,
				"error" => sprintf(  esc_html__( "Uploaded file is too large. Max size is %s", "material-dashboard" ), size_format( $max_size ) )
			);

		$target_upload_path = "$path/$target_dir/$filename";

		$handle = wp_handle_upload( $file, ["test_form" => false] );

		if( !$handle OR isset( $handle["error"] ) )
			return array(
				"success" => false,
				"error" => $handle["error"]
			);

		$success = rename( $handle["file"], $target_upload_path );
		$error = null;

		if( !$success )
			$error = esc_html__( "Failed", "material-dashboard" );

		return array(
			"success" => $success,
			"error" => $error
		);

	}

	/**
	 * Require once file if exists
	 * @param string $path
	 * File path
	 *
	 * @return void
	 * @since 1.0.4
	 */
	public function requireOnceFile( $path ){

		if( file_exists( $path ) AND is_file( $path ) )
			require_once( $path );

	}

	/**
	 * Require file if exists
	 * @param string $path
	 * File path
	 *
	 * @return void
	 * @since 1.0.4
	 */
	public function requireFile( $path ){

		if( file_exists( $path ) AND is_file( $path ) )
			require( $path );

	}

    public function getFileSize( $path ) {

        if( !file_exists( $path ) )
            return 0;

        if( is_file( $path ) )
            return filesize( $path );

        $size = 0;

        $dir = opendir( $path );

        while( ( $file = readdir( $dir ) ) !== false ) {
            if( $file != '.' && $file != '..' ) {
                $filePath = $path . DIRECTORY_SEPARATOR . $file;

                if( is_dir( $filePath ) )
                    $size += $this->getFileSize( $filePath );
                else
                    $size += filesize( $filePath );
            }
        }

        closedir( $dir );

        return $size;
    }

    public function getFileSizeOptimized( $path ) {

        if( !file_exists( $path ) )
            return 0;

        $size = 0;

        $iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ) );

        foreach( $iterator as $file )
            $size += $file->getSize();

        return $size;
    }

}