<!-- Repair/Install -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php _ex( "Repair/Install", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <p><?php _ex( "If tables relative to this plugin are deleted or you want to install or repair database click on the button below.", "Admin", "material-dashboard" ); ?></p>
        <p class="color-blue"><?php _ex( "Note: repairing database has no effect on your data. Also it will ignore repairing if there is no problem.", "Admin", "material-dashboard" ); ?></p>
        <button class="amd-btn" id="repair-db"><?php _ex( "Repair", "Admin", "material-dashboard" ); ?></button>
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
</script>
