<?php

$amd_icon = 'amd_icon';
$__ = '__';

$primary_color = amd_ext_todo_get_primary_color();

?>
<?php ob_start(); ?>
<label class="ht-textarea" style="width:100%;max-width:100%;margin:8px auto;box-sizing:border-box;display:block">
    <textarea id="new-todo" class="auto-size clear-validate-state"></textarea>
    <span><?php esc_html_e( "Text", "material-dashboard" ); ?></span>
    <?php amd_icon( 'text' ); ?>
</label>
<?php $todo_new_item_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<button class="btn --<?php echo esc_attr( $primary_color ); ?>" id="btn-add-todo"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
<button class="btn btn-text --<?php echo esc_attr( $primary_color ); ?>" id="btn-cancel-todo"><?php esc_html_e( "Cancel", "material-dashboard" ); ?></button>
<?php $todo_new_item_footer = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php esc_html_e( "Todo list", "material-dashboard" ); ?>
<button class="btn --transparent --button mlr-8 --sm _refresh_todo_list_"><?php esc_html_e( "Refresh", "material-dashboard" ); ?></button>
<?php $todo_list_title = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="text-center" id="loading-todo">
    <progress class="hb-progress-circular"></progress>
    <p><?php esc_html_e( "Please wait", "material-dashboard" ); ?>...</p>
</div>
<h4 class="text-center" id="todo-empty"><?php esc_html_e( "There is no item to show", "material-dashboard" ); ?></h4>
<div class="amd-todo-row" id="todo-list-items"></div>
<?php $todo_list_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php $todo_list_footer = ob_get_clean(); ?>

<div class="row">
    <div class="col-lg-4">
		<?php amd_dump_single_card( array(
			"id" => "new-todo-card",
			"title" => esc_html__( "New item", "material-dashboard" ),
			"content" => $todo_new_item_content,
			"footer" => $todo_new_item_footer,
			"color" => $primary_color,
			"type" => "title_card",
		) ); ?>
    </div>
    <div class="col-lg-5">
		<?php amd_dump_single_card( array(
			"id" => "cards-list",
			"title" => $todo_list_title,
			"content" => $todo_list_content,
			"color" => $primary_color,
			"type" => "title_card",
		) ); ?>
    </div>
</div>
<div class="h-100"></div>
<script>
    $(document).on('change keydown keyup', 'textarea.auto-size', function() {
        $(this).height(0).height(this.scrollHeight);
    }).find('textarea.auto-size').trigger("change");
    var _todo_list_item_delete_icon = `<?php _amd_icon( "delete" ); ?>`;
    (function() {
        let $textarea = $("#new-todo"), $add = $("#btn-add-todo"), $cancel = $("#btn-cancel-todo"),
            $card_new = $("#new-todo-card");
        let $list = $("#todo-list-items"), $loading = $("#loading-todo");
        let checkList = () => {
            let $empty = $("#todo-empty");
            if($("[data-todo]").length > 0) $empty.fadeOut(0);
            else $empty.fadeIn();
        }
        checkList();
        let setLoader = (b = true) => {
            if(b) {
                $list.fadeOut(0);
                $loading.fadeIn();
                $card_new.setWaiting();
            }
            else {
                $list.fadeIn(0);
                $loading.fadeOut(0);
                $card_new.setWaiting(false);
            }
        }
        setLoader(false);

        function toggleTodo($el, $cb, $text, _id) {
            let checked = $cb.is(":checked");
            let _network = dashboard.createNetwork();
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

        function deleteTodo($el, _id) {
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
            $("[data-todo]").each(function() {
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
                if(!error) {
                    if(resp.success) {
                        let items = resp.data.data;
                        for(let [id, data] of Object.entries(items)) {
                            let _text = data.text || "";
                            let _status = data.status || "";
                            let $html = $(`<div class="--item --bg" data-todo="${id}">
                        <p class="--text _text" ${_status === "done" ? "style=\"text-decoration:line-through\"" : ""}>${_text}</p>
                        <div>
                            <label class="hb-checkbox">
                                <input type="checkbox" class="_checkbox" ${_status === "done" ? "checked" : ""}>
                                <span></span>
                            </label>
                        </div>
                        <div>
                            <button class="btn btn-text _delete no-special">${_todo_list_item_delete_icon}</button>
                        </div>
                    </div>`);
                            $list.append($html);
                        }
                        reloadListEvents();
                        checkList();
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
        reloadList();
        $("._refresh_todo_list_").click(() => reloadList());
        let clearTextarea = () => {
            $textarea.val("");
            $textarea.setInvalid(false);
        }
        $cancel.click(() => clearTextarea());
        $add.click(function() {
            let text = $textarea.val();
            if(!text.length) {
                $textarea.setInvalid();
                return;
            }
            $textarea.setInvalid(false);
            let _network = dashboard.createNetwork();
            _network.clean();
            _network.put("_ajax_target", "ext_todo");
            _network.put("add_todo", {text});
            _network.on.start = () => $card_new.cardLoader();
            _network.on.end = (resp, error) => {
                $card_new.cardLoader(false);
                if(!error) {
                    if(resp.success) {
                        clearTextarea();
                        let id = resp.data.id;
                        let $html = $(`<div class="--item --bg" data-todo="${id}">
                        <p class="--text _text">${text}</p>
                        <label class="hb-checkbox">
                            <input type="checkbox" class="_checkbox">
                            <span></span>
                        </label>
                        <button class="btn btn-text _delete no-special">${_todo_list_item_delete_icon}</button>
                    </div>`);
                        $html.css("display", "none");
                        $list.append($html);
                        $html.fadeIn();
                        checkList();
                        reloadListEvents();
                    }
                    else {
                        $amd.alert(_t("todo_list"), resp.data.msg, {
                            icon: "error"
                        });
                    }
                }
                else {
                    $amd.alert(_t("todo_list"), _t("error"), {
                        icon: "error"
                    });
                }
            }
            _network.post();
        });
    }());
</script>
<!-- @formatter off -->
<style>.amd-todo-row>.--item,.amd-todo-row{display:flex;align-items:center;justify-content:center}.amd-todo-row{position:relative;flex-direction:column-reverse;flex-wrap:wrap;margin:16px}.amd-todo-row>.--item{box-sizing:border-box;flex-direction:row;border-radius:10px;margin:8px 0;width:100%;padding:8px}@media(min-width:993px){.amd-todo-row{flex-wrap:nowrap}}.amd-todo-row>.--item.--bg{background:rgba(var(--amd-primary-rgb),.2)}.amd-todo-row>.--item .btn{display:flex;flex-wrap:nowrap;align-items:center;justify-content:center;width:32px;height:auto;aspect-ratio:1;margin:0 4px}.amd-todo-row>.--item .--text{text-align:justify;padding:4px 24px;font-size:var(--amd-size-md)}.amd-todo-row>.--item .--text{flex:8}.amd-todo-row>.--item>div{flex:1}.amd-todo-row>.--item>div ._icon_{font-size:20px}</style>
<!-- @formatter on -->