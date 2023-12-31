<?php

amd_init_plugin();

amd_admin_head();

$tabs = apply_filters( "amd_get_setting_tabs", [] );

$tabsJS = "";

$API_OK = amd_api_page_required();
if( !$API_OK )
	return;

?>
<div class="amd-admin">
    <script>
        var network = new AMDNetwork();
        var saver = network.getSaver();
        network.setAction(amd_conf.ajax.private);
    </script>
    <div class="h-10"></div>
    <div class="amd-admin-tabs" id="amd-settings-tab">
        <div class="--tabs __center"></div>
    </div>
    <div class="pa-10">
	    <?php $tabsJS = amd_dump_admin_tabs( $tabs ); ?>
        <div data-amd-content="general"></div>
    </div>
    <script>
        (function () {
            var tabs = new AMDTab({
                element: "amd-settings-tab",
                default_tab: "general",
                items: <?php echo wp_json_encode( $tabsJS, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT ); ?>
            });
            tabs.begin();
            tabs.addButton({
                "text": _t("save"),
                "icon": `<?php echo amd_icon( "save" ); ?>`,
                "id": "amd-save-settings"
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

            $("#amd-save-settings").click(function(e){
                // [SETTINGS_SAVE_HANDLER]
                let $btn = $(this);
                $btn.blur();
                let v = $amd.doEvent("on_settings_saved", {e});
                let allowed = true;
                if(typeof e.isDefaultPrevented !== "undefined")
                    allowed = !e.isDefaultPrevented();
                else if(typeof e.defaultPrevented !== "undefined")
                    allowed = !e.defaultPrevented;
                if(!allowed)
                    return;
                $btn.setWaiting();
                network.clean();
                network.put("save_options", v);
                network.on.start = () => $btn.html(`<span>${_t("wait_td")}</span>`);
                network.on.end = (resp, error) => {
                    $btn.html(`${_i("save")} <span>${_t("save")}</span>`);
                    $btn.setWaiting(false);
                    if(!error){
                        $amd.alert(_t("settings"), resp.data.msg, {
                            icon: resp.success ? "success" : "error"
                        });
                        if(resp.success) unsaved = false
                    }
                    else{
                        $amd.alert(_t("settings"), _t("error"), {
                            icon: "error"
                        });
                    }
                }
                network.post();
            });

        }());
    </script>
</div>