<!-- Repair/Install -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "Repair/Install", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <p><?php echo esc_html_x( "If tables relative to this plugin are deleted or you want to install or repair database click on the button below.", "Admin", "material-dashboard" ); ?></p>
        <p class="color-blue"><?php echo esc_html_x( "Note: repairing database has no effect on your data. Also it will ignore repairing if there is no problem.", "Admin", "material-dashboard" ); ?></p>
        <button class="amd-btn" id="repair-db"><?php echo esc_html_x( "Repair", "Admin", "material-dashboard" ); ?></button>
    </div>
</div>

<!-- Database language -->
<div class="amd-admin-card --setting-card">
    <?php amd_dump_admin_card_keywords( ["fix", "تعمیر"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Database language", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <select class="amd-admin-select" id="database-language-collation" aria-label="">
            <?php foreach( apply_filters( "amd_database_language_collations", [] ) as $item ): ?>
                <option value="<?php echo esc_attr( $item["collation"] ); ?>">
                    <?php echo esc_html( $item["name"] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p><?php echo esc_html_x( "If database values shows as a series of question sign (e.g: ????) when trying to store some non English values in database, you may have collation conflicts in your database.", "Admin", "material-dashboard" ); ?></p>
        <p><?php echo esc_html_x( "You can fix this issue by selecting your target language. Please note that it won't affect your site database and only make changes to this plugin database table, but any corrupted value will not be fixed and you need to insert them again.", "Admin", "material-dashboard" ); ?></p>
        <button class="amd-btn" id="fix-language"><?php echo esc_html_x( "Apply", "Admin", "material-dashboard" ); ?></button>
    </div>
</div>

<script>
    $("#repair-db").click(function () {
        let $btn = $(this);
        let lastHtml = $btn.html();
        $btn.html(_t("wait_td"));
        $btn.setWaiting();
        $btn.blur();
        network.clean();
        network.put("repair_db", "");
        network.on.end = (resp, error) => {
            $btn.html(lastHtml);
            $btn.setWaiting(false);
            if (!error) {
                $amd.alert(_t("database"), resp.data.msg, {
                    icon: resp.success ? "success" : "error"
                });
            } else {
                $amd.alert(_t("database"), _t("error"), {
                    icon: "error"
                });
            }
        }
        network.post();
    });
    $("#fix-language").click(function() {
        const $btn = $(this);
        const collation = $("#database-language-collation").val();
        console.log(collation);
        if(!collation){
            $amd.toast(_t("select_one_item"));
            return;
        }
        network.clean();
        network.put("repair_db", "");
        network.put("fix_collation", collation);
        network.on.start = () => {
            $btn.waitHold();
            $btn.blur();
            $amd.alert(_t("database"), _t("wait_td"), {
                allowEscapeKey: false,
                allowOutsideClick: false,
                confirmButton: false,
                showLoader: true,
            });
        }
        network.on.end = (resp, error) => {
            $btn.waitRelease();
            if (!error) {
                $amd.alert(_t("database"), resp.data.msg, {
                    icon: resp.success ? "success" : "error"
                });
            } else {
                $amd.alert(_t("database"), _t("error"), {icon: "error"});
                console.log(resp.xhr);
            }
        }
        $amd.alert(_t("database"), _t("database_collation_fix_confirm"), {
            onConfirm: () => network.post(),
            confirmButton: _t("yes"),
            cancelButton: _t("no"),
        });
    });
</script>
