<?php



?>

<!-- Custom menu items -->
<div class="amd-admin-card --setting-card" id="custom-menu-card">
    <h3 class="--title">
        <?php echo esc_html_x( "Custom menu items", "Admin", "material-dashboard" ); ?>
        <button type="button" class="amd-admin-button _custom_menu_refresh --sm --primary --text"><?php esc_html_e( "Refresh", "material-dashboard" ); ?></button>
    </h3>
    <div class="--content">
        <p class="margin-0">
            <?php echo esc_html_x( "Please remember to save after editing, changes won't be applied automatically.", "Admin", "material-dashboard" ); ?>
            <?php if( function_exists( "adp_plugin" ) ): ?>
                <?php echo sprintf( esc_html__( "You can also change the order of menu items in %sSidebar settings%s", "material-dashboard" ), '<a href="' . admin_url( "admin.php?page=adp-tools#sidebar" ) . '">', '</a>' ); ?>.
            <?php endif; ?>
        </p>
        <div class="h-10"></div>
        <div class="row">
            <div class="col-lg-12" id="custom-menu-items"></div>
        </div>
        <div class="h-20"></div>
        <div class="text-center">
            <button type="button" class="amd-admin-button _custom_menu_save --primary"><?php esc_html_e( "Save", "material-dashboard" ); ?></button>
            <button type="button" class="amd-admin-button _add_menu_item --primary --text"><?php esc_html_e( "New item", "material-dashboard" ); ?></button>
        </div>
    </div>
</div>
<script>
    (function(){
        const menu_item_dropdown_html = `<div class="adp-admin-dropdown" data-cmi="{ID}">
<div class="--title">
<span class="-text _title_ font-title">{TITLE}</span>
<?php _amd_icon( "chev_down" ); ?>
</div>
<div class="--content">
<div class="__option_grid">
<div class="-item">
<div class="-sub-item">
<label for="cmi-{ID}-title">
<?php esc_html_e( "Title", "material-dashboard" ); ?>
</label>
</div>
<div class="-sub-item">
<input type="text" class="amd-admin-input _title_input_" id="cmi-{ID}-title" value="">
</div>
</div>
<div class="-item">
<div class="-sub-item">
<label for="cmi-{ID}-url">
<?php esc_html_e( "URL", "material-dashboard" ); ?>
</label>
</div>
<div class="-sub-item">
<input type="text" class="amd-admin-input _url_input_" id="cmi-{ID}-url" value="">
</div>
</div>
<div class="-item">
<div class="-sub-item">
<label>
<?php esc_html_e( "Icon", "material-dashboard" ); ?>
</label>
</div>
<div class="-sub-item">
<div data-icon-picker="cmi-{ID}"></div>
</div>
</div>
<div class="-item">
<div class="-sub-item">
<label for="cmi-{ID}-new-tab">
<?php esc_html_e( "Open link in new tab", "material-dashboard" ); ?>
</label>
</div>
<div class="-sub-item">
<label class="hb-switch">
<input type="checkbox" role="switch" class="_new_tab_" id="cmi-{ID}-new-tab" value="true">
<span></span>
</label>
</div>
</div>
<div class="-item">
<div class="-sub-item">
<button type="button" class="amd-admin-button --sm --red --text" data-delete-cmi="{ID}"><?php esc_html_e( "Delete", "material-dashboard" ); ?></button>
<button type="button" class="amd-admin-button --sm --blue --text" data-duplicate-cmi="{ID}"><?php esc_html_e( "Duplicate", "material-dashboard" ); ?></button>
</div>
</div>
</div>
</div>
</div>
<div class="h-10"></div>`;

        let $card = $("#custom-menu-card"), $list = $("#custom-menu-items");
        let trash = [];
        let n = new AMDNetwork();
        n.setAction(amd_conf.ajax.private);

        function init_new_item(item_id){
            let $item = $(`[data-cmi="${item_id}"]`);
            let $title = $item.find("._title_");
            let $title_input = $item.find("._title_input_");
            let $url_input = $item.find("._url_input_");
            let $icon = $item.find("[data-icon-picker]");
            let $new_tab = $item.find("._new_tab_");
            let $delete = $(`[data-delete-cmi="${item_id}"]`);
            let $duplicate = $(`[data-duplicate-cmi="${item_id}"]`);
            $title_input.on("keydown keyup", function(){
                $title.html($(this).val());
            })
            $delete.click(function(){
                $(this).setWaiting();
                $item.removeSlow();
                trash.push(item_id);
                if(typeof trash.uniqueBy === "function")
                    trash.uniqueBy();
            });
            $duplicate.click(function(){
                insert_item(null, $title_input.val(), $url_input.val(), $icon.hasAttr("data-pick", true, ""), $new_tab.is(":checked"));
            });
        }

        function insert_item(id, title, url, icon, new_tab){
            if(id === null)
                id = $amd.generate(16).toLowerCase();
            let html = menu_item_dropdown_html;
            html = html.replaceAll("{ID}", id).replaceAll("{TITLE}", title).replaceAll("{title_value}", "").replaceAll("{url_value}", "");
            let $dropdown = $(html);
            $list.append($dropdown);
            $(`[data-icon-picker="cmi-${id}"]`).attr("data-pick", icon);
            $dropdown.find("._new_tab_").prop("checked", Boolean(new_tab));
            $dropdown.find("._title_input_").val(title);
            $dropdown.find("._url_input_").val(url);
            init_new_item(id);
            amd_init_icon_pickers();
        }

        function load_items(items){
            for(let [row_id, value] of Object.entries(items)){
                let { id, title, url, icon, new_tab} = value;
                insert_item(id, title, url, icon, new_tab);
            }
        }

        function export_menu_items(){
            let data = {};
            $("[data-cmi]").each(function(){
                let $el = $(this);
                let id = $el.attr("data-cmi");
                if(id){
                    let title = $el.find("._title_input_").val();
                    let url = $el.find("._url_input_").val();
                    let $icon = $(`[data-icon-picker="cmi-${id}"]`);
                    let icon = $icon.hasAttr("data-pick", true, "");
                    let new_tab = $el.find("._new_tab_").is(":checked");
                    data[id] = { id, title, url, icon, new_tab };
                }
            });
            return data;
        }

        function refresh_menu_items(){
            n.clean();
            n.put("_ajax_target", "menu_manager");
            n.put("get_items", "");
            n.on.start = () => {
                $card.cardLoader();
                $amd.toast(_t("wait_td"));
            }
            n.on.end = (resp, error) => {
                $card.cardLoader(false);
                Hello.closeAll();
                if(!error){
                    if(resp.success){
                        if(typeof resp.data.items !== "undefined"){
                            let items = resp.data.items;
                            $list.html("");
                            load_items(items);
                        }
                    }
                    else{
                        $amd.toast(resp.data.msg);
                    }
                }
                else{
                    $amd.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        }
        refresh_menu_items();

        $("._add_menu_item").click(function(){
            let new_title = `<?php esc_html_e( "New item", "material-dashboard" ); ?>`;
            insert_item(null, new_title, "", "", false);
        });

        $("._custom_menu_save").click(function(){
            n.clean();
            n.put("_ajax_target", "menu_manager");
            n.put("save_menu", {
                "menu_items": export_menu_items(),
                "trash": trash
            });
            n.on.start = () => {
                $card.cardLoader();
                $amd.toast(_t("saving_td"));
            }
            n.on.end = (resp, error) => {
                Hello.closeAll();
                $card.cardLoader(false);
                if(!error){
                    if(resp.success){}
                    else{}
                    $amd.toast(resp.data.msg);
                }
                else{
                    $amd.toast(_t("error"));
                }
            }
            n.post();
        });

        $("._custom_menu_refresh").click(() => refresh_menu_items());
    }());
</script>
