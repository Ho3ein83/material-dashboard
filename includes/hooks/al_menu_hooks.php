<?php

/**
 * Menu manager ajax handler
 * @param array $r
 * Data array
 * @return void
 * @since 1.1.0
 */
function amd_ajax_target_menu_manager( $r ){

    if( isset( $r["get_items"] ) ){

        $item_id = $r["get_items"] ?: "";

        global $amdDB;

        $items = $amdDB->getComponents( "type", "menu_item", true );

        $data = [];
        if( !empty( $items ) ){

            /** @var AMDComponent $item */
            foreach( $items as $item ){
                $id = $item->get_id();
                $data[$id] = $item->get_decoded_data();
                if( !empty( $item_id ) AND $id == $item_id )
                    break;
            }
        }

        wp_send_json_success( ["msg" => esc_html__( "Success", "material-dashboard" ), "items" => $data] );

    }
    else if( !empty( $r["save_menu"] ) ){

        $data = $r["save_menu"];
        $items = $data["menu_items"] ?? [];
        $trash = $data["trash"] ?? [];

        global $amdDB;

        if( !empty( $items ) ){

            foreach( $items as $item ){
                $id = $item["id"] ?? "";
                if( $id AND in_array( $id, $trash ) )
                    continue;
                $title = $item["title"] ?? "";
                $icon = $item["icon"] ?? "";
                $url = $item["url"] ?? "#";
                $new_tab = ( $item["new_tab"] ?? "false" ) == "true";
                if( $id AND $title ){
                    $new_data = array(
                        "id" => $id,
                        "title" => $title,
                        "url" => $url,
                        "icon" => $icon,
                        "new_tab" => $new_tab,
                    );
                    if( $cid = $amdDB->componentExist( $id, "key" ) )
                        $amdDB->updateComponent( $cid, [ "component_data" => json_encode( $new_data )] );
                    else
                        $amdDB->addComponent( "menu_item", json_encode( $new_data ), $id );
                }
            }

        }

        if( !empty( $trash ) ){
            foreach( $trash as $delete ) {
                if( $amdDB->componentExist( $delete, "key" ) )
                    $amdDB->deleteComponentBy( "key", $delete );
            }
        }

        amd_send_api_success( [ "msg" => esc_html__( "Success", "material-dashboard" ) ] );

    }

    amd_send_api_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ) ] );

}

/**
 * Add registered custom menu items to dashboard
 * @return void
 * @since 1.1.0
 */
function amd_register_custom_menu_items(){

    // since 1.1.0
    if( !apply_filters( "amd_enable_custom_menu_items", true ) )
        return;

    global $amdDB;
    $items = $amdDB->getComponents( "type", "menu_item", true );

    /** @var AMDComponent $menu_item */
    foreach( $items as $menu_item ){
        $data = $menu_item->get_decoded_data();
        $title = $data["title"] ?: "";
        $id = $data["id"] ?? "";
        if( $id AND $title ){
            $url = $data["url"] ?? "#";
            $icon = $data["icon"] ?? "";
            $new_tab = $data["new_tab"] ?: false;

            do_action( "amd_add_dashboard_sidebar_menu", array(
                $id => array(
                    "text" => $title,
                    "icon" => $icon,
                    "void" => null,
                    "url" => $url,
                    "turtle" => $new_tab ? "blank" : "normal",
                    "priority" => 30
                )
            ) );

        }
    }

}
add_action( "amd_init_sidebar_items", "amd_register_custom_menu_items" );