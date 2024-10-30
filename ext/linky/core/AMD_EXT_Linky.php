<?php

/** @var AMD_EXT_Linky $amdLinkyCore */
$amdLinkyCore = null;

class AMD_EXT_Linky {

    /**
     * Referral table name
     * @var string
     * @sicne 1.2.0
     */
    protected $table;

    public function __construct(){

        # Initialize database
        add_action( "amd_after_cores_init", [$this, "init_database"] );

    }

    /**
     * Register database tables and initialize them
     * @return void
     * @since 1.2.0
     */
    public function init_database(){

        global $amdDB;

        $engine = $amdDB->sanitizeEngine();

        $amdDB->registerTable( "referral", array(
            "id" => "INT NOT NULL AUTO_INCREMENT",
            "user_id" => "INT NOT NULL",
            "referral_code" => "VARCHAR(64) NOT NULL",
            "invites" => "LONGTEXT NOT NULL",
            "meta" => "LONGTEXT NOT NULL",
            "EXTRA" => " PRIMARY KEY (`id`)) ENGINE = $engine;"
        ) );

        $this->table = $amdDB->getTable( "referral" );

    }

    /**
     * Insert new referral to database
     * @param int $user_id
     * User ID (referral owner)
     * @param string $referral_code
     * Referral code
     * @param string|string[] $invites
     * The list of invited users IDs, comma-separated string or simple list, e.g: "8,12,20", [8, 12, 20]
     * @param array $meta
     * Meta data
     *
     * @return false|int
     * The number of rows inserted, or false on error
     * @since 1.2.0
     */
    public function insert( $user_id, $referral_code, $invites = [], $meta = [] ) {

        global $amdDB;

        return $amdDB->db->insert( $this->table, array(
            "user_id" => $user_id,
            "referral_code" => $referral_code,
            "invites" => amd_clean_separated_list( $invites, ",", false ),
            "meta" => is_array( $meta ) ? serialize( $meta ) : serialize( [] )
        ) );

    }

    /**
     * Update referral data in database
     * @param array $data
     * Data array
     * @param array $where
     * Where clause array
     *
     * @return false|int
     * The number of rows updated, or false on error.
     * @sicne 1.2.0
     */
    public function update_where( $data, $where ) {

        global $amdDB;

        return $amdDB->db->update( $this->table, $data, $where );

    }

    /**
     * Update user referral data in database
     * @param int $user_id
     * Target user ID
     * @param array $data
     * Data array
     *
     * @return false|int
     * The number of rows updated, or false on error.
     * @sicne 1.2.0
     */
    public function update( $user_id, $data ) {

        return $this->update_where( $data, ["user_id" => $user_id] );

    }

    /**
     * Insert data into database if it doesn't exist, otherwise update the existing one
     * @param array $data
     * Data array, see {@see wpdb::insert()} data parameter
     *
     * @return false|int
     * False on failure, inserted row ID on insert, affected rows number on update
     * @since 1.2.0
     */
    public function upsert( $data ) {
        $user_id = $data["user_id"] ?? null;
        if( !$user_id )
            return false;
        if( !empty( $this->select( ["user_id", "is", $user_id] ) ) )
            return $this->update( $user_id, $data );

        $referral_code = $data["referral_code"] ?? null;
        $invites = $data["invites"] ?? "";
        $meta = $data["invites"] ?? [];
        return empty( $referral_code ) ? false : $this->insert( $user_id, $referral_code, $invites, $meta );
    }

    /**
     * Select rows from database
     * @param array ...$search
     * Query array, see {@see self::parse_search_query()}
     *
     * @return mixed
     * {@see self::query()} returned value
     * @since 1.2.0
     */
    public function select( ...$search ) {
        global $amdDB;
        return $amdDB->select( $this->table, ...$search );
    }

    /**
     * Check if a referral code is taken by a user or is free
     * @param string $referral_code
     * Referral code to search for
     *
     * @return false|int
     * False if referral code is free, otherwise the user ID of the referral code owner
     * @since 1.2.0
     */
    public function referral_exist( $referral_code ) {
        return !empty( $this->select( ["referral_code", "is", $referral_code] ) );
    }

    /**
     * Generate a referral key
     * @param bool $unique
     * Whether to regenerate referral key if it's already in use by a user
     *
     * @return string
     * Referral key
     * @sicne 1.2.0
     */
    public function generate_referral( $unique = true ) {
        do {
            $code = amd_generate_string_pattern( "[number:3][upper:3]" );
        } while( amd_starts_with( $code, "0" ) OR ( $unique AND $this->referral_exist( $code ) ) );
        return $code;
    }

    /**
     * Get user referral code
     * @param int|null $user_id
     * User ID or null for current user
     * @param bool $use_cache
     * Whether to use cache for storing referral code
     *
     * @return string
     * The referral code of user
     * @since 1.2.0
     */
    public function get_user_referral_code( $user_id = null, $use_cache = true ) {

        $user = $user_id === null ? amd_get_current_user() : amd_get_user( $user_id );

        if( $user instanceof AMDUser ){

            global $amdCache;

            if( $use_cache ){
                if( $ref = $amdCache->cacheExists( "ext_linky_user_{$user->ID}_referral_code", true ) )
                    return $ref;
            }

            $results = $this->select( ["user_id", "is", $user->ID] );
            $referral = "";
            if( !empty( $results ) AND !empty( $results[0]->referral_code ) )
                $referral = $results[0]->referral_code;
            if( empty( $referral ) ) {
                $referral = $this->generate_referral();
                $this->upsert( [ "user_id" => $user->ID, "referral_code" => $referral ] );
            }

            if( $use_cache )
                $amdCache->setCache( "ext_linky_user_{$user->ID}_referral_code", $referral );

            return $referral;

        }

        return "";
    }

    /**
     * Get user object by referral code
     * @param string $referral_code
     * Referral code
     * @return AMDUser|false
     * User object on success, false on failure
     * @sicne 1.2.0
     */
    public function get_user_by_referral( $referral_code ) {

        $result = $this->select( ["referral_code", "is", $referral_code] );

        if( empty( $result ) OR empty( $result[0]->user_id ) )
            return false;

        $uid = $result[0]->user_id;
        $user = amd_get_user( $uid );

        if( $user AND $user instanceof AMDUser )
            return $user;

        return false;
    }

    /**
     * Check if referral code is valid or not
     * @param string $referral_code
     * Referral code
     * @param bool $required
     * If false, referral code can be empty string and still be valid,
     * otherwise it must match the referral code pattern, default is false
     * @param bool $check_for_user
     * Whether to check if the referral code belongs to a user, default is false
     *
     * @return bool
     * True if referral code is valid, otherwise false
     * @since 1.2.0
     */
    public function validate_referral( $referral_code, $required = false, $check_for_user = false ) {

        $referral_code = trim( $referral_code );

        if( $required AND empty( $referral_code ) )
            return false;

        $pattern = apply_filters( "amd_ext_linky_referral_pattern", "(^$)|(^[0-9]{3}[a-zA-Z]{3}$)" );
        if( !preg_match( "/$pattern/", $referral_code ) )
            return false;

        if( !$check_for_user )
            return true;

        $user = $this->get_user_by_referral( $referral_code );

        return $user instanceof AMDUser;

    }

    /**
     * Update row meta data
     * @param int $row_id
     * Row ID you want to update
     * @param string|string[] $key_s
     * Meta key(s) you want to update, you can use single scalar values
     * like "meta_key" or array list like ["key_1", "key_2]
     * @param mixed|array $value_s
     * Meta value(s) you want to update to, you can use single scalar values
     * like bool, int, null, string or listed values like ["value_1", "value_2", false, 1]
     *
     * @return false|int
     * False on failure, affected rows number on success
     * @since 1.2.0
     */
    public function set_row_meta( $row_id, $key_s, $value_s ) {
        $row = $this->select( ["id", "is", $row_id] );

        if( !empty( $row ) AND !empty( $key_s ) ){

            $meta = unserialize( $row[0]->meta ?? serialize( [] ) );

            if( is_scalar( $key_s ) )
                $key_s = [$key_s];
            if( is_scalar( $value_s ) )
                $value_s = [$value_s];

            $last_value = null;
            for( $i = 0; $i < count( $key_s ); $i++ ){
                $last_value = $value_s[$i] ?? $last_value;
                $meta[$key_s[$i]] = $last_value;
            }

            return $this->update_where( ["meta" => serialize( $meta )], ["id" => $row_id] );

        }

        return false;
    }

    /**
     * Remove meta data items from a row
     * @param int $row_id
     * Row ID you want to update
     * @param string|string[] $key_s
     * Meta key(s) you want to remove, you can use single scalar values
     * like "meta_key" or array list like ["key_1", "key_2]
     *
     * @return false|int
     * False on failure, affected rows number on success
     * @since 1.2.0
     */
    public function remove_row_meta( $row_id, $key_s ) {
        $row = $this->select( ["id", "is", $row_id] );

        if( !empty( $row ) AND !empty( $key_s ) ){

            $meta = unserialize( $row[0]->meta ?? serialize( [] ) );

            if( is_scalar( $key_s ) )
                $key_s = [$key_s];

            for( $i = 0; $i < count( $key_s ); $i++ ){
                $meta[$key_s[$i]] = null;
                unset( $meta[$key_s[$i]] );
            }

            return $this->update_where( ["meta" => serialize( $meta )], ["id" => $row_id] );

        }

        return false;
    }

    /**
     * Remove a user from another user invitation list
     * <br><b>Note: user ID is not validated and may be an invalid user ID, check it before using it here</b>
     * @param int $user_id_to_leave
     * Invited user ID who wants to leave their invitation list
     *
     * @return bool
     * True on success, false on failure
     * @since 1.2.0
     */
    public function leave( $user_id_to_leave ) {

        global $amdDB;

        $x = intval( $user_id_to_leave );
        /** @noinspection SqlNoDataSourceInspection */
        $sql = $amdDB->db->prepare( "SELECT * FROM %i WHERE `invites` REGEXP %s", $this->table, "(^$x,)|(,$x,)|(,$x$)|(^$x$)" );
        $results = $amdDB->query( $sql );

        if( !empty( $results ) ){
            $r = $results[0];
            $row_id = $r->id;
            $invites = amd_pull_value( $r->invites, $user_id_to_leave );
            $this->update_where( ["invites" => $invites], ["id" => $row_id] );
            $this->remove_row_meta( $row_id, "joined_$user_id_to_leave" );
            amd_delete_user_meta( $user_id_to_leave, "invited_by" );
            return true;
        }

        return false;

    }

    /**
     * Join a user to another user invitation list
     * <br><b>Note: user IDs are not validated and may be an invalid user ID, check it before using it here</b>
     * @param int $user_id
     * User ID who invited the other user
     * @param int $user_id_to_join
     * Invited user ID
     *
     * @return bool
     * True on success, false on failure
     * @since 1.2.0
     */
    public function join( $user_id, $user_id_to_join ) {

        $results = $this->select( ["user_id", "is", $user_id] );

        if( !empty( $results ) ){

            # Add $user_id_to_join to user invitation list
            $invites = $results[0]->invites ?? "";
            $invites = amd_push_value( $invites, $user_id_to_join );

            # Update user invite list
            $this->update_where( ["invites" => $invites], ["user_id" => $user_id] );

            # Update joining date
            $this->set_row_meta( $results[0]->id, "joined_$user_id_to_join", time() );

            # Update user meta for referral user ID
            amd_set_user_meta( $user_id_to_join, "invited_by", $user_id );

            /**
             * User join event
             * @since 1.2.0
             */
            do_action( "amd_ext_linky_user_joined", $user_id, $user_id_to_join );

            return true;
        }

        return false;

    }

    /**
     * Join a user to another user invitation list by referral code
     * <br><b>Note: user ID is not validated and may be an invalid user ID, check it before using it here</b>
     * @param int $user_id_to_join
     * User ID who invited the other user
     * @param string $referral_code
     * The referral code of the user who invited the other one
     *
     * @return bool
     * True on success, false on failure
     * @since 1.2.0
     */
    public function join_referral( $user_id_to_join, $referral_code ) {

        $user = $this->get_user_by_referral( $referral_code );
        if( $user instanceof AMDUser )
            return $this->join( $user->ID, $user_id_to_join );
        return false;

    }

    public function get_user_invite_list( $user_id, $get_invite_time = false ) {

        $results = $this->select( ["user_id", "is", $user_id] );

        $list = [];
        if( !empty( $results ) ) {
            $invites = explode( ",", $results[0]->invites ?? "" );
            if( $get_invite_time ){
                $meta = unserialize( $results[0]->meta );
                if( empty( $meta ) OR !is_array( $meta ) )
                    $meta = [];
                foreach( $invites as $uid ){
                    $invite_time = $meta["joined_$uid"] ?? 0;
                    $list[$uid] = "$uid:$invite_time";
                }
            }
            else{
                $list = $invites;
            }
        }

        return $list;

    }

    /**
     * Get user friends list
     * @param bool $export_users
     * Whether to export the users or {@see AMDUser::export() export} them
     * @param int|null $user_id
     * User ID or null for current user
     * @param int $limit
     * The maximum number of items you want to have in the list, pass 0 for no limit. Default is 0
     *
     * @return AMDUser[]|array[]|array
     * Empty user if no friends found, otherwise user object list or
     * exported user array list based on $export_users parameter
     * @since 1.2.0
     */
    public function get_friends_list( $export_users = false, $user_id = null, $limit = 0 ) {

        $user = $user_id === null ? amd_get_current_user() : amd_get_user( $user_id );

        $list = [];
        if( $user instanceof AMDUser ){
            $user_id = $user->ID;
            $counter = 0;
            foreach( $this->get_user_invite_list( $user_id, true ) as $part ){
                if( $limit > 0 AND $counter >= $limit )
                    break;
                $exp = explode( ":", $part );
                $friend_id = $exp[0];
                $friend = amd_get_user( $friend_id );
                if( $friend instanceof AMDUser ){
                    $time = $exp[1] ?? 0;
                    $extra_data = [];
                    if( $time ) {
                        $extra_data["invite_time"] = $time;
                        $extra_data["invite_date"] = amd_true_date( "j F Y", $time );
                    }
                    $list[$friend->ID] = $export_users ? array_merge( $friend->export(), $extra_data ) : $friend;
                    $counter++;
                }
            }
        }


        return $list;

    }

    public function filter_invites( $filter_callback ){

        $results = $this->select( ["invites", "not", ""] );

        $data = [];
        foreach( $results as $result ){
            $user_id = $result->user_id;
            $invites = $result->invites;
            $meta = unserialize( $result->meta );
            $meta = is_array( $meta ) ? $meta : [];
            $invite_list = [];
            foreach( explode( ",", $invites ) as $invited ){
                $filter = !is_callable( $filter_callback ) || call_user_func( $filter_callback, $result, $meta, $invited );
                if( $filter )
                    $invite_list[] = $invited;
            }
            $data[$user_id] = $invite_list;
        }

        return $data;
    }

}