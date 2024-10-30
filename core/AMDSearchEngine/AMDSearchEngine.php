<?php

/** @var AMDSearchEngine $amdSearch */
$amdSearch = null;

class AMDSearchEngine {

    public function __construct(){

        # Load ajax handler
        require_once( __DIR__ . "/ajax.php" );

        # Initialize hooks
        self::init_hooks();

    }

    /**
     * Initialize hooks
     * @return void
     * @since 1.1.0
     */
    public function init_hooks(){}

    /**
     * Search for a user
     * @param string $query
     * User query like user email, ID, username, etc.
     * @return AMDUser|null
     * User object on success, null on failure
     * @since 1.2.0
     */
    public function search_user( $query ) {

        global $amdSilu;
        $result = $amdSilu->getUserAuto( $query );
        if( empty( $result["user"] ) )
            return null;

        $user = $result["user"];
        if( $user instanceof AMDUser )
            return $user;

        return null;

    }

}