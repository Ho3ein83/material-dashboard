<?php

$primary_color = amd_ext_todo_get_primary_color();

?>

<?php ob_start(); ?>
<?php echo esc_html_x( "Todo list", "todo", "material-dashboard" ); ?>
<button class="btn btn-icon-square bis-sm --transparent --button mlr-8 --sm _refresh_todo_list_" data-tooltip="<?php esc_html_e( "Refresh", "material-dashboard" ); ?>">
	<?php _amd_icon( "refresh" ); ?>
</button>
<button class="btn btn-icon-square bis-sm --transparent --button --right mlr-8 --sm" data-lazy-query="?void=todo" data-tooltip="<?php esc_html_e( "Manage", "material-dashboard" ); ?>">
	<?php _amd_icon( "todo" ); ?>
</button>
<?php $todo_card_title = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="text-center" id="loading-todo">
    <progress class="hb-progress-circular"></progress>
    <p><?php esc_html_e( "Please wait", "material-dashboard" ); ?>...</p>
</div>
<h4 class="text-center" id="todo-empty"><?php esc_html_e( "There is no item to show", "material-dashboard" ); ?></h4>
<div class="amd-todo-row" id="todo-list-items"></div>
<?php $todo_card_content = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
    "title" => $todo_card_title,
    "content" => $todo_card_content,
    "color" => $primary_color,
    "type" => "title_card"
) ); ?>

<script>
    (function(){
        var _todo_list_item_delete_icon = `<?php _amd_icon( "delete" ); ?>`;
        let $list = $("#todo-list-items"), $loading = $("#loading-todo");
        let checkList = () => {
            let $empty = $("#todo-empty");
            if($("[data-todo]").length > 0) {
                $empty.fadeOut(0);
                $list.fadeIn();
            }
            else {
                $empty.fadeIn();
                $list.fadeOut(0);
            }
        }
        checkList();
        let setLoader = (b=true) => {
            if(b) {
                $list.fadeOut(0);
                $loading.fadeIn();
            }
            else {
                $list.fadeIn(0);
                $loading.fadeOut(0);
            }
        }
        setLoader(false);
        function toggleTodo($el, $cb, $text, _id){
            let checked = $cb.is(":checked");
            let _network = dashboard.createNetwork();
            $el.blur();
            _network.clean();
            _network.put("_ajax_target", "ext_todo");
            _network.put("edit_todo", _id);
            _network.put("data", {status: checked ? "done" : "undone"});
            _network.on.start = () => $el.setWaiting()
            _network.on.end = (resp, error) => {
                $el.setWaiting(false);
                if(!error) {
                    if(resp.success) {
                        if(checked) $text.css("text-decoration", "line-through");
                        else $text.css("text-decoration", "none");
                    }
                    else {
                        $cb.prop("checked", !checked);
                        $amd.toast(resp.data.msg);
                    }
                }
                else {
                    $cb.prop("checked", !checked);
                    $amd.toast(_t("error"));
                }
            }
            _network.post();
        }
        function deleteTodo($el, _id){
            let _network = dashboard.createNetwork();
            _network.clean();
            _network.put("_ajax_target", "ext_todo");
            _network.put("delete_todo", _id);
            _network.on.start = () => $el.setWaiting()
            _network.on.end = (resp, error) => {
                $el.setWaiting(false);
                if(!error) {
                    if(resp.success) {
                        $el.removeSlow(300);
                        setTimeout(() => checkList(), 310);
                    }
                    else {
                        $amd.toast(resp.data.msg);
                    }
                }
                else {
                    $amd.toast(_t("error"));
                }
            }
            _network.post();
        }
        let reloadListEvents = () => {
            $("[data-todo]").each(function(){
                let $el = $(this);
                if(!$el.hasAttr("data-checked")) {
                    let _id = $el.attr("data-todo");
                    let $cb = $el.find("._checkbox"), $text = $el.find("._text"), $delete = $el.find("._delete");
                    $cb.attr("data-toggle-todo", _id);
                    $cb.on("change", () => toggleTodo($el, $cb, $text, _id));
                    $delete.attr("data-delete-todo", _id);
                    $delete.on("click", () => deleteTodo($el, _id));
                    $el.attr("data-checked", "true");
                }
            });
        }
        let reloadList = () => {
            let _network = $amd.clone(network);
            _network.put("_ajax_target", "ext_todo");
            _network.put("get_todo_list", "");
            _network.on.start = () => {
                $list.html("");
                setLoader();
            }
            _network.on.end = (resp, error) => {
                setLoader(false);
                if(!error){
                    if(resp.success){
                        let items = resp.data.data;
                        for(let [id, data] of Object.entries(items)){
                            let _text = data.text || "";
                            let _status = data.status || "";
                            let primary_color = `<?php echo esc_attr( $primary_color ); ?>`;
                            let $html = $(`<div class="--item --bg" data-todo="${id}">
                        <p class="--text _text" ${_status === "done" ? "style=\"text-decoration:line-through\"" : ""}>${_text}</p>
                        <label class="hb-checkbox ${primary_color}">
                            <input type="checkbox" class="_checkbox" ${_status === "done" ? "checked" : ""}>
                            <span></span>
                        </label>
                        <button class="btn ${primary_color} btn-text _delete no-special">${_todo_list_item_delete_icon}</button>
                    </div>`);
                            $list.append($html);
                        }
                        reloadListEvents();
                        checkList();
                    }
                    else{
                        $amd.toast(resp.data.msg);
                    }
                }
                else{
                    $amd.toast(_t("error"));
                }
            }
            _network.post();
        }
        reloadList();
        $("._refresh_todo_list_").click(() => reloadList());
    }());
</script>
<!-- @formatter off -->
<style>.amd-todo-row>.--item,.amd-todo-row{display:flex;align-items:center;justify-content:center}.amd-todo-row{position:relative;flex-direction:column-reverse;flex-wrap:wrap;margin:16px}.amd-todo-row>.--item{box-sizing:border-box;flex-direction:row;border-radius:10px;margin:4px 0;width:100%;padding:8px}@media(min-width:993px){.amd-todo-row{flex-wrap:nowrap}}.amd-todo-row>.--item.--bg{background:rgba(var(<?php echo "--amd-color-$primary_color-rgb"; ?>),.2)}.amd-todo-row>.--item .btn{display:flex;flex-wrap:nowrap;align-items:center;justify-content:center;width:32px;min-width:40px;padding:9px;height:auto;aspect-ratio:1;margin:0 4px}.amd-todo-row>.--item .--text{text-align:justify;padding:0 16px;font-size:var(--amd-size-md)}.amd-todo-row>.--item .--text{flex:8}.amd-todo-row>.--item>div{flex:1}.amd-todo-row>.--item>div ._icon_{font-size:20px}</style>
<!-- @formatter on -->