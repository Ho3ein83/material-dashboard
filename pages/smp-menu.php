<?php

amd_init_plugin();

amd_admin_head();

$tabs = array(
    "custom_menu" => array(
        "title" => esc_html_x( "Custom menu", "Admin", "material-dashboard" ),
        "icon"  => "add_group",
        "page"  => AMD_PAGES . '/admin-tabs/tab_custom_menu.php',
    )
);

/**
 * @since 1.1.0
 */
$tabs = apply_filters( "amd_menu_settings_tabs", $tabs );

$tabsJS = "";

?>
<div class="amd-admin">
    <script>
        var network = new AMDNetwork();
        var saver = network.getSaver();
        network.setAction(amd_conf.ajax.private);
    </script>
    <div class="h-20"></div>
    <div class="amd-admin-tabs" id="amd-menu-tab">
        <div class="--tabs __center"></div>
        <div class="pa-10">
            <?php $tabsJS = amd_dump_admin_tabs( $tabs ); ?>
        </div>
    </div>

    <script>
        (function () {
            var tabs = new AMDTab({
                element: "amd-menu-tab",
                default_tab: "custom_menu",
                items: <?php echo wp_json_encode( $tabsJS, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT ); ?>
            });
            tabs.begin();
        }());
    </script>
</div>