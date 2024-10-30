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

    public function init_hooks() {



    }

}