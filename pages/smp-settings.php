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
    <div class="h-20"></div>
    <div class="h-20"></div>
    <div class="text-center" id="amd-search-section" style="display:none">
        <label>
            <input type="text" class="amd-admin-input" id="amd-settings-search-input" placeholder="<?php esc_attr_e( "Search", "material-dashboard" ); ?>">
        </label>
        <div class="h-10"></div>
        <button type="button" class="amd-admin-button --sm" id="do-search"><?php esc_html_e( "Search", "material-dashboard" ); ?></button>
        <button type="button" class="amd-admin-button --sm --text --primary" id="cancel-search"><?php esc_html_e( "Cancel", "material-dashboard" ); ?></button>
    </div>
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
                "text": _t("search"),
                "icon": `<?php echo amd_icon( "search" ); ?>`,
                "id": "amd-settings-search",
                "extraClass": "bg-primary-low color-primary-im"
            });
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

            const $search_input = $("#amd-settings-search-input");
            const $settings_tab = $("#amd-settings-tab");
            const $search_section = $("#amd-search-section");
            let current_tab = tabs.getActive();

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

            function show_all(){
                $(".amd-admin-card, [data-amd-content]").fadeIn(0);
                $(".amd-admin-alert").fadeIn(0);
            }
            function hide_all(){
                $(".amd-admin-card, [data-amd-content]").fadeOut(0);
                $(".amd-admin-alert").fadeOut(0);
            }
            function do_search(term=null){
                if(term === null)
                    term = $search_input.val();
                if(!term){
                    show_all();
                    return;
                }
                term = term.toLowerCase().trim();
                let $cards = $(".amd-admin-card");
                $cards.fadeIn(0);
                $cards.each(function() {
                    let cardText = $(this).text().toLowerCase();
                    $(this).toggle(cardText.indexOf(term) > -1);
                });
                $(".amd-admin-alert").fadeOut(0);
            }

            $("#amd-settings-search").click(function(){
                current_tab = tabs.getActive();
                $settings_tab.slideUp();
                $search_section.slideDown();
                show_all();
            });

            $search_input.on("keydown keyup", function(){
                do_search($(this).val());
            });

            $("#do-search").click(() => do_search());
            $("#cancel-search").click(function(){
                $search_input.val("");
                $(".amd-admin-card").fadeIn(0);
                $("[data-amd-content]").fadeOut(0);
                $settings_tab.slideDown();
                $search_section.slideUp();
                tabs.switch(current_tab);
            });

        }());
    </script>
</div>