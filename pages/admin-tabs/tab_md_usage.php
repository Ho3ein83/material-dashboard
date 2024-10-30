<?php

global $amdDB, $amdExp;

$avatars_path = $amdExp->getPath( "avatars" );
$users_upload_path = $amdExp->getPath( "users_upload" );

$tables_size = $amdDB->extractDatabaseSize();
$database_size = 0;
$dashboard_database_size = 0;
$db_prefix = $amdDB->prefix;
$wp_prefix = $amdDB->wp_prefix;
foreach( $tables_size as $table => $size ){
    if( $amdDB->isDashboardTable( preg_replace( "/^$db_prefix/", "", $table ) ) )
        $dashboard_database_size += $size;
    else
        $database_size += $size;
}
/*$database_size = $amdDB->estimateDatabaseSize();
$dashboard_database_size = $amdDB->estimateDashboardDatabaseSize();*/

$avatar_files_size = $amdExp->getFileSizeOptimized( $avatars_path );
$uploads_files_size = $amdExp->getFileSizeOptimized( $users_upload_path );

$db_temp_size = $amdDB->estimateTableSize( $amdDB->getTable( "temp" ) );
$db_todo_size = $amdDB->estimateTableSize( $amdDB->getTable( "todo" ) );
$db_reports_size = $amdDB->estimateTableSize( $amdDB->getTable( "reports" ) );

$total_usage = $database_size + $avatar_files_size + $uploads_files_size;

$tables_alias = array(
    "{$db_prefix}users_meta" => __( "Users meta", "material-dashboard" ),
    "{$db_prefix}options" => __( "Settings", "material-dashboard" ),
    "{$db_prefix}temp" => _x( "Temporary data", "Admin", "material-dashboard" ),
    "{$db_prefix}todo" => __( "Todo list", "material-dashboard" ),
    "{$db_prefix}reports" => __( "Reports", "material-dashboard" ),
    "{$db_prefix}components" => _x( "Components", "Admin", "material-dashboard" ),
    "{$db_prefix}tasks" => _x( "Tasks", "Background tasks", "material-dashboard" ),
    "{$db_prefix}referral" => _x( "Referral", "Invites", "material-dashboard" ),
    # WordPress
    "{$wp_prefix}users" => __( "Users" ),
    "{$wp_prefix}usermeta" => __( "Users meta", "material-dashboard" ),
);
$tables_alias = apply_filters( "amd_tables_alias", $tables_alias );

$db_extension = null;
if( !isset( $_GET["ignore-errors"] ) AND is_object( $amdDB->db->dbh ) AND get_class( $amdDB->db->dbh ) != "mysqli" ){
    ?>
    <div class="force-center">
        <?php
        amd_alert_box( array(
            "text" => sprintf( __( "This section only meant to be for MySQLi servers, and it seems your host is not using, however you can %suse it anyway%s with your own consideration.", "material-dashboard" ), '<a href="' . esc_attr( admin_url( "admin.php?page=amd-data&ignore-errors#usage" ) ) . '">', '</a>' ),
            "icon" => "warning",
            "type" => "warning",
            "size" => "lg"
        ) );
        ?>
    </div>
    <?php
    return;
}

?>
<!-- Data usage -->
<div class="amd-admin-card text-center --setting-card" id="card-cleanup">
	<div class="--content">
        <div>
            <canvas id="data-usage-chart" style="width:500px;height:500px"></canvas>
        </div>
        <p class="color-primary"><?php echo esc_html_x( "Please note that this information is collected from plugin data and you may have other files on your host and database information may not be highly accurate.", "Admin", "material-dashboard" ); ?></p>
	</div>
</div>

<!-- Database details -->
<div class="amd-admin-card text-center --setting-card" id="card-cleanup">
	<h3 class="--title"><?php echo esc_html_x( "Database details", "Source usage", "material-dashboard" ); ?></h3>
	<div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item flex-center" style="justify-content:start">
                    <button type="button" id="sort-handle-name" class="amd-admin-button flex-center sqr-btn --sm --primary --text"><?php _amd_icon( "sort_asc_letter" ); ?></button>
                </div>
                <div class="-sub-item flex-center" style="justify-content:end">
                    <button type="button" id="sort-handle-size" class="amd-admin-button flex-center sqr-btn --sm --primary --text"><?php _amd_icon( "sort_asc_digits" ); ?></button>
                </div>
            </div>
        </div>
        <div class="__option_grid" id="tables-details">
            <?php $counter = 0; ?>
            <?php foreach( $tables_size as $table => $size ): ?>
            <?php
                $size_array = amd_format_bytes( $size );
                $key = preg_replace( "/^$db_prefix/", "", $table );
                $counter++;
            ?>
                <div class="-item" data-name="<?php echo esc_attr( $counter ); ?>" data-size="<?php echo esc_attr( $size ); ?>">
                    <div class="-sub-item">
                        <span class="small-text color-low"><?php echo esc_html( $table ); ?></span>
                        <?php if( !empty( $tables_alias[$table] ) ): ?>
                        <span class="tiny-text color-primary">(<?php echo esc_html( $tables_alias[$table] ); ?>)</span>
                        <?php endif; ?>
                        <?php if( $amdDB->isDashboardTable( $key ) ): ?>
                        <div class="h-10"></div>
                        <span class="badge --sm --primary"><?php esc_html_e( "Material Dashboard", "material-dashboard" ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="-sub-item">
                        <span dir="auto"><?php echo esc_html( number_format( $size_array[0], 2 ) . " " . $size_array[1] ); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
	</div>
</div>

<!-- Source info -->
<div class="amd-admin-card text-center --setting-card" id="card-cleanup">
	<h3 class="--title"><?php echo esc_html_x( "Source info", "Source usage", "material-dashboard" ); ?></h3>
	<div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <span><?php echo esc_html_x( "Avatars directory", "Admin", "material-dashboard" ); ?></span>
                </div>
                <div class="-sub-item">
                    <pre><code>&hellip;<?php echo esc_html( str_replace( ABSPATH, "", $avatars_path ) ); ?></code></pre>
                    <div class="h-10"></div>
                    <button type="button" data-copy="<?php echo esc_attr( $avatars_path ); ?>" class="amd-admin-button --sm --primary --text"><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <span><?php echo esc_html_x( "Users uploads directory", "Admin", "material-dashboard" ); ?></span>
                </div>
                <div class="-sub-item">
                    <pre><code id="users-upload-path">&hellip;<?php echo esc_html( str_replace( ABSPATH, "", $users_upload_path ) ); ?></code></pre>
                    <div class="h-10"></div>
                    <button type="button" data-copy="<?php echo esc_attr( $users_upload_path ); ?>" class="amd-admin-button --sm --primary --text"><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
                </div>
            </div>
        </div>
	</div>
</div>

<script>
    (function(){
        var $wrapper = $("#tables-details");

        const $sort_handle_name = $("#sort-handle-name"), $sort_handle_size = $("#sort-handle-size");
        const sorting = ["name", "asc"];
        function resort(by, direction){
            $wrapper.find(".-item").sort(function(a, b) {
                if(by === "size")
                    return direction === "asc" ? (+a.dataset.size - +b.dataset.size): (+b.dataset.size - +a.dataset.size);
                else if(by === "name")
                    return direction === "asc" ? (+a.dataset.name - +b.dataset.name): (+b.dataset.name - +a.dataset.name);
                return 0;
            }).appendTo($wrapper);
            $sort_handle_name.removeClass("active");
            $sort_handle_size.removeClass("active");
            if(by === "name" && direction === "desc") $sort_handle_name.addClass("active");
            if(by === "size" && direction === "desc") $sort_handle_size.addClass("active");
            sorting[0] = by;
            sorting[1] = direction;
        }
        resort(sorting[0], sorting[1]);

        $sort_handle_name.click(() => resort("name", sorting[0] === "name" ? (sorting[1] === "asc" ? "desc" : "asc") : "desc"));
        $sort_handle_size.click(() => resort("size", sorting[0] === "size" ? (sorting[1] === "asc" ? "desc" : "asc") : "desc"));

        $("[data-copy]").click(function() {
            let text = $(this).attr("data-copy");
            if(text.startsWith("~#")){
                text = $(text.replace("~#", "#")).get(0).innerText;
            }
            if(text) $amd.copy(text, false, true);
        });
        const _texts = {
            "data_usage": `<?php echo esc_html_x( "Data usage", "Source usage", "material-dashboard" ); ?>`,
            "size": `<?php echo esc_html_x( "Size", "File size", "material-dashboard" ); ?>`,
            "avatar_files": `<?php esc_html_e( "Avatar files", "material-dashboard" ); ?>`,
            "uploaded_files": `<?php esc_html_e( "Uploaded files", "material-dashboard" ); ?>`,
            "database": `<?php esc_html_e( "Database", "material-dashboard" ); ?>`,
            "dashboard_database": `<?php esc_html_e( "Dashboard database", "material-dashboard" ); ?>`,
        };
        const charts_conf = {
            font_family: "shabnam",
            font_size_sm: 14,
            font_size_md: 15,
            font_size_lg: 18,
            font_default: {
                size: 15,
                family: "shabnam"
            },
            tooltip_font_default: {
                size: 13,
                family: "shabnam"
            }
        };
        const format_number = x => x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        const size_base = 1024;
        const total_usage = $amd.byteTo(JSON.parse(`<?php echo wp_json_encode( $total_usage ); ?>`), null, size_base);
        const _style = getComputedStyle(document.body);
        new Chart(document.getElementById("data-usage-chart"), {
            type: "pie",
            data: {
                labels: [
                    _texts.avatar_files,
                    _texts.uploaded_files,
                    _texts.dashboard_database,
                    _texts.database,
                ],
                datasets: [{
                    label: _texts.size,
                    data: [
                        JSON.parse(`<?php echo wp_json_encode( $avatar_files_size ); ?>`),
                        JSON.parse(`<?php echo wp_json_encode( $uploads_files_size ); ?>`),
                        JSON.parse(`<?php echo wp_json_encode( $dashboard_database_size ); ?>`),
                        JSON.parse(`<?php echo wp_json_encode( $database_size ); ?>`),
                    ],
                    backgroundColor: [
                        _style.getPropertyValue("--amd-color-red"),
                        _style.getPropertyValue("--amd-color-blue"),
                        _style.getPropertyValue("--amd-color-orange"),
                        _style.getPropertyValue("--amd-primary"),
                    ],
                    hoverOffset: 4,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: charts_conf.font_default
                        }
                    },
                    title: {
                        display: true,
                        text: _texts.data_usage + " (" + [format_number(Math.round(total_usage[0])), total_usage[1]].join("") + ")",
                        font: charts_conf.font_default
                    },
                    tooltip: {
                        titleFont: charts_conf.tooltip_font_default,
                        bodyFont: charts_conf.tooltip_font_default,
                        footerFont: charts_conf.tooltip_font_default,
                        backgroundColor: _style.getPropertyValue("--amd-wrapper-bg"),
                        titleColor: _style.getPropertyValue("--amd-primary"),
                        bodyColor: _style.getPropertyValue("--amd-text-color"),
                        footerColor: _style.getPropertyValue("--amd-text-color"),
                        displayColors: false,
                        callbacks: {
                            _title: function(context) {
                                let data = context[0] || {};
                                return data.label || "";
                            },
                            label: function(context) {
                                const values = $amd.byteTo(context.parsed, null, size_base);
                                return Math.round(values[0] * 100) / 100 + " " + values[1];
                            }
                        }
                    }
                },
            }
        });
    }());
</script>

<style>
    .sqr-btn {
        padding: 0 !important;
        width: 40px;
        height: auto;
        aspect-ratio: 1;
        background: transparent !important;
    }
    .sqr-btn.active {
        background: var(--amd-primary) !important;
        color: #fff !important;
    }
</style>