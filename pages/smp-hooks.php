<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
    return;

$hooks = amd_get_custom_hooks();
$filters = $hooks["filters"] ?? [];
$actions = $hooks["actions"] ?? [];

?>
<div class="h-20"></div>
<h2 class="margin-0">
    <?php echo esc_html_x( "Filters", "Admin hooks manager", "material-dashboard" ); ?>
    <button type="button" class="amd-admin-button _add_filter_ --primary --sm"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
    <button type="button" class="amd-admin-button _export_filters_ --primary --text --sm"><?php esc_html_e( "Export", "material-dashboard" ); ?></button>
    <button type="button" class="amd-admin-button _import_filters_ --primary --text --sm"><?php esc_html_e( "Import", "material-dashboard" ); ?></button>
</h2>
<div class="h-10"></div>
<p class="tiny-text margin-0"><?php printf( esc_html_x( "Filters are used to change behaviour or value of something in your site, %slearn more%s.", "Admin", "material-dashboard" ), '<a href="https://developer.wordpress.org/plugins/hooks/filters" target="_blank">', '</a>' ); ?></p>
<div class="h-20"></div>
<div class="row" id="row-add-filter" style="display:none">
    <div class="col-lg-3">
        <div class="amd-admin-card">
            <label for="new-filter-name" class="mt-20 color-primary size-15px block"><?php esc_html_e( "Hook name", "material-dashboard" ); ?> <span class="color-red">*</span></label>
            <input type="text" class="amd-admin-input mt-10 --full" id="new-filter-name" placeholder="<?php esc_attr_e( "Required", "material-dashboard" ); ?>">

            <label for="new-filter-value" class="mt-20 color-primary size-15px block"><?php esc_html_e( "Hook value", "material-dashboard" ); ?></label>
            <input type="text" class="amd-admin-input mt-10 --full" id="new-filter-value" placeholder="<?php esc_attr_e( "Optional", "material-dashboard" ); ?>">

            <label for="new-filter-callback" class="mt-20 color-primary size-15px block"><?php esc_html_e( "Hook callback", "material-dashboard" ); ?></label>
            <input type="text" class="amd-admin-input mt-10 --full" id="new-filter-callback" placeholder="<?php esc_attr_e( "Optional", "material-dashboard" ); ?>">

            <div class="h-20"></div>
            <p class="tiny-text"><?php esc_html_e( "Please remember to fill out only one of hook value and callback fields, if you fill both only hook value will be considered as filter value.", "material-dashboard" ); ?></p>
            <div class="h-20"></div>
            <button type="button" class="amd-admin-button _submit_add_filter_"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
            <button type="button" class="amd-admin-button _cancel_add_filter_ --primary --text"><?php esc_html_e( "Cancel", "material-dashboard" ); ?></button>
            <div class="h-20"></div>
        </div>
        <div class="h-20"></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-10">
        <div class="amd-table">
            <table>
                <thead>
                <tr>
                    <th><?php esc_html_e( "ID", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Hook name", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Hook value", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Hook callback", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Actions", "material-dashboard" ); ?></th>
                </tr>
                </thead>
                <tbody id="filters-list"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="h-20"></div>
<div class="h-20"></div>
<h2 class="margin-0">
    <?php echo esc_html_x( "Actions", "Admin hooks manager", "material-dashboard" ); ?>
    <button type="button" class="amd-admin-button _add_action_ --primary --sm"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
    <button type="button" class="amd-admin-button _export_actions_ --primary --text --sm"><?php esc_html_e( "Export", "material-dashboard" ); ?></button>
    <button type="button" class="amd-admin-button _import_actions_ --primary --text --sm"><?php esc_html_e( "Import", "material-dashboard" ); ?></button>
</h2>
<div class="h-10"></div>
<p class="tiny-text margin-0"><?php printf( esc_html_x( "Actions are used to change behaviours of site actions, %slearn more%s.", "Admin", "material-dashboard" ), '<a href="https://developer.wordpress.org/plugins/hooks/actions/" target="_blank">', '</a>' ); ?></p>
<div class="h-20"></div>
<div class="row" id="row-add-action" style="display:none">
    <div class="col-lg-3">
        <div class="amd-admin-card">
            <label for="new-action-name" class="mt-20 color-primary size-15px block"><?php esc_html_e( "Hook name", "material-dashboard" ); ?> <span class="color-red">*</span></label>
            <input type="text" class="amd-admin-input mt-10 --full" id="new-action-name" placeholder="<?php esc_attr_e( "Required", "material-dashboard" ); ?>">

            <label for="new-action-callback" class="mt-20 color-primary size-15px block"><?php esc_html_e( "Hook callback", "material-dashboard" ); ?> <span class="color-red">*</span></label>
            <input type="text" class="amd-admin-input mt-10 --full" id="new-action-callback" placeholder="<?php esc_attr_e( "Required", "material-dashboard" ); ?>">

            <div class="h-20"></div>
            <button type="button" class="amd-admin-button _submit_add_action_"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
            <button type="button" class="amd-admin-button _cancel_add_action_ --primary --text"><?php esc_html_e( "Cancel", "material-dashboard" ); ?></button>
            <div class="h-20"></div>
        </div>
        <div class="h-20"></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-10">
        <div class="amd-table">
            <table>
                <thead>
                <tr>
                    <th><?php esc_html_e( "ID", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Hook name", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Hook callback", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Actions", "material-dashboard" ); ?></th>
                </tr>
                </thead>
                <tbody id="actions-list"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function(){
        let n = new AMDNetwork();
        n.setAction(amd_conf.ajax.private);

        const $row_filter = $("#row-add-filter"), $row_action = $("#row-add-action");
        const $filters_list = $("#filters-list"), $actions_list = $("#actions-list");
        const $filter_name = $("#new-filter-name"), $filter_value = $("#new-filter-value"),
            $filter_callback = $("#new-filter-callback");
        const $action_name = $("#new-action-name"), $action_callback = $("#new-action-callback");

        const wait_alert = (text=null) => {
            $amd.alert(_t("wait"), text ? text : _t("saving_td"), {
                confirmButton: false,
                cancelButton: false,
                timeout: 15000,
                showLoader: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        }

        const append_filter = data => {
            let {id, name, value, callback} = data;
            if(id){
                $filters_list.append(`<tr data-filter-id="${id}">
<td>${id}</td>
<td><code class="_name_">${name || ""}</code></td>
<td><code class="_value_">${value || ""}</code></td>
<td><code class="_callback_">${callback || ""}</code></td>
<td><button type="button" class="amd-admin-button --red --text --sm" data-delete-filter="${id}">${_t("delete")}</button></td>
</tr>`);
            }
        }

        const append_action = data => {
            let {id, name, callback} = data;
            if(id){
                $actions_list.append(`<tr data-action-id="${id}">
<td>${id}</td>
<td><code class="_name_">${name || ""}</code></td>
<td><code class="_callback_">${callback || ""}</code></td>
<td><button type="button" class="amd-admin-button --red --text --sm" data-delete-action="${id}">${_t("delete")}</button></td>
</tr>`);
            }
        }

        $("._add_filter_").click(() => $row_filter.slideToggle(500));
        $("._add_action_").click(() => $row_action.slideToggle(500));

        $("._cancel_add_filter_").click(function(){
            $filter_name.val("");
            $filter_value.val("");
            $filter_callback.val("");
            $row_filter.slideUp();
        });
        $("._cancel_add_action_").click(function(){
            $action_name.val("");
            $action_callback.val("");
            $row_action.slideUp();
        });

        const add_hook = (_type, _name, _value, _callback="") => {
            const name = _name.trim(), value = _value.trim(),
                callback = _callback.trim(), type = (_type === "filter" ? "filter" : "action");
            let failed = false;
            if(!name.length){
                failed = true;
                $amd.toast(`<?php esc_html_e( "Please fill out hook name", "material-dashboard" ); ?>`);
            }
            /*if(!value.length && !callback.length){
                failed = true;
                $amd.toast(`<?php //esc_html_e( "Please fill out hook callback or value", "material-dashboard" ); ?>`);
            }*/
            if(failed)
                return;

            n.clean();
            n.put("add_custom_hook", { type, name, value, callback });
            n.on.start = () => wait_alert();
            n.on.end = (resp, error) => {
                Hello.closeAll();
                if(!error){
                    if(resp.success) {
                        const {id} = resp.data.data ?? {};
                        if(id) {
                            const current_data = {id, name, value, callback};
                            _type === "filter" ? append_filter(current_data) : append_action(current_data);
                            if(type === "filter"){
                                $filter_name.val("");
                                $filter_value.val("");
                                $filter_callback.val("");
                            }
                            else if(type === "action"){
                                $action_name.val("");
                                $action_callback.val("");
                            }
                        }
                    } else {
                        $amd.toast(resp.data.msg || _t("failed"));
                    }
                }
                else{
                    $amd.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        }

        const delete_hook = (id, type="") => {
            n.clean();
            n.put("delete_custom_hook", { id, type });
            n.on.start = () => wait_alert(_t("deleting_td"));
            n.on.end = (resp, error) => {
                Hello.closeAll();
                if(!error){
                    if(resp.success) {
                        $(`[data-${type}-id="${id}"]`).removeSlow();
                    } else {
                        $amd.toast(resp.data.msg || _t("failed"));
                    }
                }
                else{
                    $amd.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        }

        $("._submit_add_filter_").click(function(){
            add_hook("filter", $filter_name.val(), $filter_value.val(), $filter_callback.val());
        });
        $("._submit_add_action_").click(function(){
            add_hook("action", $action_name.val(), "", $action_callback.val());
        });

        const export_hook = type => {
            type = type === "filter" ? "filter" : "action";
            n.clean();
            n.put("export_custom_hooks", { type });
            n.on.start = () => wait_alert(_t("exporting"));
            n.on.end = (resp, error) => {
                Hello.closeAll();
                if(!error){
                    if(resp.success) {
                        const content = resp.data.data ?? "";
                        if(content)
                            $amd.saveAs(type + ".export", content);
                    } else {
                        $amd.toast(resp.data.msg || _t("failed"));
                    }
                }
                else{
                    $amd.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        }

        const import_hint_text = `<?php esc_html_e( "Select backup file or paste it on the below text box:", "material-dashboard" ); ?>`;
        const import_hint_text_2 = `<?php esc_html_e( "Paste here", "material-dashboard" ); ?>`;
        const import_hook = type => {
            type = type === "filter" ? "filter" : "action";
            const run = content => {
                n.clean();
                n.put("import_custom_hooks", { type, content });
                n.on.start = () => wait_alert(_t("exporting"));
                n.on.end = (resp, error) => {
                    Hello.closeAll();
                    if(!error){
                        if(resp.success) {
                            $amd.toast(resp.data.msg || _t("success"), {
                                onClose: () => location.reload()
                            });
                        } else {
                            $amd.toast(resp.data.msg || _t("failed"));
                        }
                    }
                    else{
                        $amd.toast(_t("error"));
                        console.log(resp.xhr);
                    }
                }
                n.post();
            }
            let current_file, current_text, $btn, $file, $input;
            $amd.alert(_t("import"), `<p>${import_hint_text}</p>
<input type="file" id="import-from-file" style="display:none" accept=".txt,.export">
<input type="text" class="amd-admin-input text-center" id="import-from-text" placeholder="${import_hint_text_2}">
<div class="h-10"></div>
<button type="button" class="amd-admin-button" id="import-from-file-button">${_t("select_file")}</button>
`, {
                confirmButton: _t("submit"),
                cancelButton: _t("cancel"),
                onBuild: () => {
                    $btn = $("#import-from-file-button"), $file = $("#import-from-file"),
                        $input = $("#import-from-text");
                    $btn.click(() => $file.click());
                    $file.on("change", function(e){
                        current_file = $file.get(0).files[0] ?? null;
                        if(current_file){
                            $btn.html($amd.escapeHtml(current_file.name));
                            $input.fadeOut(0);
                        }
                        else{
                            $btn.html(_t("select_file"));
                            $input.fadeIn(0);
                        }
                    });
                    $input.on("change input", function(){
                        current_text = $(this).val();
                        current_text ? $btn.fadeOut(0) : $btn.fadeIn(0);
                    });
                },
                onConfirm: () => {
                    if(current_text){
                        run(current_text);
                    }
                    else if(current_file){
                        let reader = new FileReader();
                        reader.addEventListener("load", d => {
                            let text = d.currentTarget.result;
                            if(text) run(text);
                        });
                        reader.readAsText(current_file);
                    }
                }
            });
        }

        $("._export_filters_").click(() => export_hook("filter"));
        $("._export_actions_").click(() => export_hook("action"));
        $("._import_filters_").click(() => import_hook("filter"));
        $("._import_actions_").click(() => import_hook("action"));

        $(document).on("click", "[data-delete-filter], [data-delete-action]", function(){
            const $el = $(this);
            const id = $el.hasAttr("data-delete-filter", true, $el.hasAttr("data-delete-action", true, ""));
            const type = $el.hasAttr("data-delete-filter") ? "filter" : "action";
            if(id) delete_hook(id, type);
        });

        try{
            const _filters = JSON.parse(`<?php echo wp_json_encode( $filters ); ?>`), _actions = JSON.parse(`<?php echo wp_json_encode( $actions ); ?>`);
            for(let [, value] of Object.entries(_filters))
                append_filter(Object.assign({id: value.id}, value.data));
            for(let [, value] of Object.entries(_actions))
                append_action(Object.assign({id: value.id}, value.data));
        } catch(e){}
    }());
</script>
