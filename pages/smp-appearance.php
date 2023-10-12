<?php

amd_init_plugin();

amd_admin_head();

$tabs = apply_filters( "amd_get_appearance_tabs", [] );

$tabsJS = "";

?>
<div class="amd-admin">
    <script>
        var network = new AMDNetwork();
        var saver = network.getSaver();
        network.setAction(amd_conf.ajax.private);
    </script>
    <div class="h-20"></div>
    <div class="amd-admin-tabs" id="amd-appearance-tab">
        <div class="--tabs __center"></div>
    </div>
    <div class="pa-10">
	    <?php $tabsJS = amd_dump_admin_tabs( $tabs ); ?>
    </div>
    <script>
        (function () {
            var tabs = new AMDTab({
                element: "amd-appearance-tab",
                default_tab: "colors",
                items: <?php echo wp_json_encode( $tabsJS, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT ); ?>
            });
            tabs.begin();
            tabs.addButton({
                "text": _t("save"),
                "icon": `<?php echo amd_icon( "save" ); ?>`,
                "id": "amd-save-appearance",
                "extraClass": "amd_save_button",
            });

            var unsaved = false;
            $(window).bind("beforeunload", function(e){
                if(unsaved){
                    e.returnValue = "Fields data you've entered may not be saved.";
                    return "Fields data you've entered may not be saved.";
                }
            });
            $("input, textarea, select").on("change", function(e){
                if(e.originalEvent || null) unsaved = true;
            });

            $("#amd-save-appearance").click(function () {
                let $btn = $(this);
                $btn.setWaiting();
                $btn.blur();
                let v = $amd.doEvent("on_appearance_saved");
                network.clean();
                network.put("save_options", v);
                network.on.start = () => $btn.html(`<span>${_t("wait_td")}</span>`);
                network.on.end = (resp, error) => {
                    $btn.html(`${_i("save")} <span>${_t("save")}</span>`);
                    $btn.setWaiting(false);
                    if (!error) {
                        $amd.alert(_t("appearance"), resp.data.msg, {
                            icon: resp.success ? "success" : "error",
                            cancelButton: _t("reload"),
                            onCancel: () => location.reload()
                        });
                        if(resp.success) unsaved = false;
                    } else {
                        $amd.alert(_t("appearance"), _t("error"), {
                            icon: "error"
                        });
                    }
                }
                network.post();
            });

        }());
    </script>
</div>