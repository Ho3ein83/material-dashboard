<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
	return;

$tabs = array(
	"import" => array(
		"title" => _x( "Restore", "Admin", "material-dashboard" ),
		"icon"  => "import",
		"page"  => AMD_PAGES . '/admin-tabs/tab_md_import.php',
	),
	"export" => array(
		"title" => _x( "Backup", "Admin", "material-dashboard" ),
		"icon"  => "backup",
		"page"  => AMD_PAGES . '/admin-tabs/tab_md_export.php',
	),
    "usage" => array(
		"title" => _x( "Data usage", "Source usage", "material-dashboard" ),
		"icon"  => "chart",
		"page"  => AMD_PAGES . '/admin-tabs/tab_md_usage.php',
	),
    "cleanup" => array(
		"title" => _x( "Cleanup", "Admin", "material-dashboard" ),
		"icon"  => "cleanup",
		"page"  => AMD_PAGES . '/admin-tabs/tab_md_cleanup.php',
	)
);

/**
 * @since 1.1.0
 */
$tabs = apply_filters( "amd_manage_data_tabs", $tabs );

$tabsJS = "";

?>
<div class="amd-admin">
    <script>
        var network = new AMDNetwork();
        var saver = network.getSaver();
        network.setAction(amd_conf.ajax.private);
    </script>
    <div class="h-20"></div>
    <div class="amd-admin-tabs" id="amd-data-tab">
        <div class="--tabs __center"></div>
        <div class="pa-10">
	        <?php $tabsJS = amd_dump_admin_tabs( $tabs ); ?>
        </div>
    </div>

    <script>
        (function () {
            var tabs = new AMDTab({
                element: "amd-data-tab",
                default_tab: "import",
                items: <?php echo wp_json_encode( $tabsJS, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT ); ?>
            });
            tabs.begin();
        }());
    </script>
</div>