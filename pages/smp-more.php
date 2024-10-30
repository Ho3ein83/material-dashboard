<?php

amd_init_plugin();

amd_admin_head();

$tabs = array(
	"contact_us" => array(
		"title" => esc_html_x( "Contact us", "Admin", "material-dashboard" ),
		"icon"  => "phone",
		"page"  => AMD_PAGES . "/admin-tabs/tab_more_contact.php",
	),
    "documentation" => array(
		"title" => esc_html_x( "Documentation", "Admin", "material-dashboard" ),
		"icon"  => "page",
		"page"  => AMD_PAGES . "/admin-tabs/tab_more_documentation.php",
	),
    "data-collect" => array(
		"title" => esc_html_x( "Data collect", "Admin", "material-dashboard" ),
		"icon"  => "star",
		"page"  => AMD_PAGES . "/admin-tabs/tab_more_data_collect.php",
	)
);

$tabsJS = "";

?>

<div class="amd-admin">
	<div class="h-20"></div>
	<div class="amd-admin-tabs" id="amd-more-tabs">
		<div class="--tabs __center"></div>
		<div class="pa-10">
			<?php $tabsJS = amd_dump_admin_tabs( $tabs ); ?>
		</div>
	</div>

	<script>
        (function () {
            var tabs = new AMDTab({
                element: "amd-more-tabs",
                default_tab: "contact_us",
                items: <?php echo wp_json_encode( $tabsJS, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT ); ?>
            });
            tabs.begin();
        }());
	</script>
</div>