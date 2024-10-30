<?php

$amd_icon = 'amd_icon';
$__ = '__';

$primary_color = amd_ext_todo_get_primary_color();
$todo_reminder = amd_get_site_option( "todo_reminder_enabled", "true" );

?>
<?php ob_start(); ?>
<div id="todo-editor"></div>
<div class="h-10"></div>
<h4 class="mbt-5"><?php echo esc_html_x( "Priority", "todo", "material-dashboard" ); ?></h4>
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:center">
    <label style="display:block;flex:auto;padding:5px;box-sizing:border-box">
        <input type="range" class="ht-slider" id="todo-priority" min="0" max="100" value="0">
    </label>
    <span class="badge --sm" style="flex:0 0 85px;transition:all ease .3s" id="todo-priority-badge"></span>
</div>
<div id="todo-reminder-section" style="display:none">
    <div class="h-10"></div>
    <label class="ht-input">
        <input type="text" class="" id="todo-reminder-date" placeholder="" readonly required/>
        <span><?php echo esc_html_x( "Reminder", "Todo", "material-dashboard" ); ?></span>
        <?php _amd_icon( "bell" ); ?>
    </label>
</div>
<?php $todo_new_item_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="h-20"></div>
<div class="flex-align" style="justify-content:space-between;flex-wrap:wrap">
    <button class="btn --primary" id="btn-add-todo" style="flex:1"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
    <?php if( amd_get_logical_value( $todo_reminder ) ): ?>
    <button class="btn --green" id="btn-reminder" style="flex:1"><?php echo esc_html_x( "Reminder", "Todo", "material-dashboard" ); ?></button>
    <?php endif; ?>
    <button class="btn btn-text --red" id="btn-cancel-todo" style="flex:1"><?php esc_html_e( "Cancel", "material-dashboard" ); ?></button>
</div>
<?php $todo_new_item_footer = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php esc_html_e( "Todo list", "material-dashboard" ); ?>
<button class="btn --transparent --button mlr-8 --sm _refresh_todo_list_"><?php esc_html_e( "Refresh", "material-dashboard" ); ?></button>
<?php $todo_list_title = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php $todo_list_footer = ob_get_clean(); ?>

<div class="row">
    <div class="col-lg-5">
		<?php amd_dump_single_card( array(
			"id" => "new-todo-card",
			"title" => __( "New item", "material-dashboard" ),
			"content" => $todo_new_item_content,
			"footer" => $todo_new_item_footer,
			"color" => $primary_color,
			"type" => "title_card",
		) ); ?>
    </div>
    <div class="col-lg-7">
        <div class="force-center">
            <button type="button" class="btn btn-text btn-icon-square _refresh_todo_list_"><?php _amd_icon( "refresh" ); ?></button>
        </div>
        <h3 class="margin-0 text-center"><?php esc_html_e( "Todo list", "material-dashboard" ); ?></h3>
        <div class="mbt-5"></div>
        <p class="margin-0 text-center tiny-text color-low" id="todo-caption"></p>
        <div class="h-10"></div>
        <div class="text-center">
            <p class="badge --sm bg-x-low color-primary-im _badge small-text" style="padding:8px 20px" id="todo-empty"><?php esc_html_e( "There is no item to show", "material-dashboard" ); ?></p>
        </div>
        <div class="amd-todo-row" id="todo-list-items"></div>
    </div>
</div>
<div class="h-100"></div>
<!-- @formatter off -->
<script>
    <!-- Initialize Quill editor -->
    $(document).on('change keydown keyup', 'textarea.auto-size', function() {
        $(this).height(0).height(this.scrollHeight);
    }).find('textarea.auto-size').trigger("change");
    var _todo_list_item_delete_icon = `<?php _amd_icon( "delete" ); ?>`;

    (function() {
        var editor = new Quill('#todo-editor', {
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{'list': 'ordered'}, {'list': 'bullet'}],
                    [{'color': []}, {'background': []}],
                    [{'align': []}],
                    ['clean']
                ]
            },
            placeholder: _t("write_a_text_td"),
            theme: 'snow'
        });

        let $textarea = $("#new-todo"), $add = $("#btn-add-todo"), $cancel = $("#btn-cancel-todo"),
            $card_new = $("#new-todo-card");

        let $priority = $("#todo-priority"), $priority_badge = $("#todo-priority-badge");

        var badgeStartColor = $amd.rgbToArray($amd.getCssVariable("--amd-primary-rgb"));
        var badgeEndColor = $amd.rgbToArray($amd.getCssVariable("--amd-color-red-rgb"));

        function mapColor(value, getRGB=false){
            let badgeIntermediateColor = badgeStartColor.map(function(startValue, index) {
                var endValue = badgeEndColor[index];
                return Math.round(startValue + (endValue - startValue) * (value / 100));
            });

            if(getRGB)
                return badgeIntermediateColor.join(",");

            return "#" + badgeIntermediateColor.map(component => {
                var hex = component.toString(16);
                return hex.length === 1 ? "0" + hex : hex;
            }).join("");
        }

        $priority.on("change input", function(){
            let p = parseInt($priority.val());

            if(isNaN(p))
                return;

            if(p === 0)
                $priority_badge.html(`<?php echo esc_html_x( "Default", "todo priority", "material-dashboard" ); ?>`);
            else if(p > 0 && p < 50)
                $priority_badge.html(`<?php echo esc_html_x( "Low", "todo priority", "material-dashboard" ); ?>`);
            else if(p >= 50 && p < 70)
                $priority_badge.html(`<?php echo esc_html_x( "Medium", "todo priority", "material-dashboard" ); ?>`);
            else if(p >= 70)
                $priority_badge.html(`<?php echo esc_html_x( "High", "todo priority", "material-dashboard" ); ?>`);

            let hexColor = mapColor(p), rgbColor = mapColor(p, true);

            $priority_badge.css("background-color", hexColor);
            $priority_badge.attr("data-color-hex", hexColor);
            $priority_badge.attr("data-color-rgb", rgbColor);

        }).change();

        let $list = $("#todo-list-items"), $loading = $("#loading-todo"),
            $caption = $("#todo-caption");

        if(typeof $list.sortable === "function") {
            $list.sortable({
                handle: "._move",
                revert: true,
                tolerance: "pointer",
                stop: () => {
                    if(typeof check_items_movement === "function")
                        check_items_movement();
                }
            });
        }

        let checkList = () => {
            let $empty = $("#todo-empty");
            if($("[data-todo]").length > 0) $empty.fadeOut(0);
            else $empty.fadeIn();
        }
        checkList();

        let setLoader = (b = true) => {
            let $b = $(".btn._refresh_todo_list_");
            if(b) {
                $list.fadeOut(0);
                $loading.fadeIn();
                $card_new.setWaiting();
                $b.setWaiting();
                $b.find("._icon_").setSpinner();
            }
            else {
                $list.fadeIn(0);
                $loading.fadeOut(0);
                $card_new.setWaiting(false);
                $b.setWaiting(false);
                $b.find("._icon_").setSpinner(false);
            }
        }
        setLoader(false);

        setCaption = (text=null) => {
            if(text){
                $caption.fadeOut(0);
                $caption.html(text);
                $caption.fadeIn();
            }
            else{
                $caption.html(_t("todo_double_click_to_edit"));
            }
        }
        setCaption();

        let exportTasksOrder = () => {
            let data = [], $items = $("[data-todo]");
            $items.each(function() {
                let id = parseInt($(this).attr("data-todo"));
                let priority = parseInt($(this).hasAttr("data-priority", true, "0"));
                if(id && !isNaN(priority))
                    data.push(`${id}:${priority}`);
            });
            return data.join(",");
        }

        let reset_orders = () => {
            let priorities = [], $items = $("[data-todo]");
            $items.each(function() {
                let priority = parseInt($(this).hasAttr("data-priority", true, "0"));
                if(isNaN(priority))
                    priority = 0;
                priorities.push(priority);
            });
            priorities = priorities.sort((a, b) => a - b).reverse();
            $items.each(function(i) {
                let pr = typeof priorities[i] !== "undefined" ? priorities[i] : null;
                if(pr === null)
                    return;
                let $el = $(this);
                $el.attr("data-priority", pr);
                $el.find("._indicator").css("background-color", mapColor(pr));
            });
        }

        function toggleTodo($el, $cb, $text, _id) {
            let checked = $cb.is(":checked");
            let priority = $priority.val();
            $el.blur();
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

        function deleteTodo($el, _id, askForConfirmation=false) {
            let _network = dashboard.createNetwork();
            _network.clean();
            _network.put("_ajax_target", "ext_todo");
            _network.put("delete_todo", _id);
            _network.on.start = () => {
                setCaption(_t("deleting_td"));
                $el.setWaiting();
            }
            _network.on.end = (resp, error) => {
                $el.setWaiting(false);
                setCaption();
                if(!error) {
                    if(resp.success) {
                        $el.removeSlow(300);
                        setTimeout(() => {
                            checkList();
                            reorder_items();
                            check_items_movement(false);
                        }, 310);
                    }
                    else {
                        $amd.toast(resp.data.msg);
                    }
                }
                else {
                    $amd.toast(_t("error"));
                }
            }
            if(askForConfirmation){
                let $del = $el.find("._delete");
                if($del.hasAttr("data-confirm")){
                    _network.post();
                }
                else{
                    let lastHtml = $del.html();
                    $del.html(_i("check"));
                    $del.attr("data-confirm", "true");
                    setTimeout(() => {
                        $del.html(lastHtml);
                        $del.removeAttr("data-confirm");
                    }, 3000);
                }
            }
            else{
                _network.post();
            }
        }

        function inlineEdit($el){
            if($el.hasClass("--editing"))
                return;
            if(typeof window.amd_todo_inline_editing_cancel === "function")
                window.amd_todo_inline_editing_cancel();

            let $parent = $el.closest("[data-todo]");
            let task_id = $parent.attr("data-todo");
            if(!task_id)
                return;
            let defaultContent = $el.html();

            let goToEnd = () => {
                $el.focus();
                let range = document.createRange();
                range.selectNodeContents($el.get(0));
                range.collapse(false);
                let selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            }

            let _cancel = () => {
                $el.html(defaultContent);
                $el.removeClass("--editing");
                $el.removeAttr("contenteditable");
                window.amd_todo_inline_editing = false;
                window.amd_todo_inline_editing_cancel = false;
                setCaption();
            }

            let _failed = () => {
                setCaption(_t("todo_enter_to_save"));
            }

            let _save = (cancelOnFail=false) => {
                let text = $el.html();
                if(text === defaultContent){
                    _cancel();
                    return;
                }
                let n = dashboard.createNetwork();
                n.clean();
                n.put("_ajax_target", "ext_todo");
                n.put("edit_todo", task_id);
                n.put("data", {todo_value: text});
                n.on.start = () => {
                    $parent.setWaiting();
                    setCaption(_t("saving_td"));
                }
                n.on.end = (resp, error) => {
                    $parent.setWaiting(false);
                    setCaption();
                    if(!error) {
                        if(resp.success) {
                            _cancel();
                            let formatted_text = resp.data.formatted_text || text;
                            $el.html(formatted_text);
                        }
                        else {
                            if(cancelOnFail)
                                _cancel();
                            else
                                _failed();
                            $amd.toast(resp.data.msg);
                        }
                    }
                    else {
                        if(cancelOnFail)
                            _cancel();
                        else
                            _failed();
                        $amd.toast(_t("error"));
                    }
                }
                n.post();
            }

            window.amd_todo_inline_editing = (cf=false) => _save(cf);
            window.amd_todo_inline_editing_cancel = () => _cancel();

            $el.attr("contenteditable", "true");
            $el.addClass("--editing");
            setCaption(_t("todo_enter_to_save"));
            goToEnd();
        }

        $("body").onDoubleClick(function(e){
            let $el = $(e.target).closest("[data-todo]");
            if($el.length <= 0) {
                if(typeof window.amd_todo_inline_editing === "function")
                    window.amd_todo_inline_editing();
            }
        });
        let inlineEditKeymap = {};
        $list.on("keydown keyup", e => {
            let {type, key} = e;
            if(typeof key === "undefined")
                return;
            let isDown = type === "keydown";
            inlineEditKeymap[key] = isDown;
            if(!isDown)
                delete inlineEditKeymap[key];
            let $e = $(e.target);
            if($e.hasClass("_text") && $e.hasClass("--editing")){
                if(type === "keydown"){
                    let shift = inlineEditKeymap["Shift"] || false;
                    let enter = inlineEditKeymap["Enter"] || false;
                    let escape = inlineEditKeymap["Escape"] || false;
                    if(enter){
                        if(!shift) {
                            e.preventDefault();
                            if(typeof window.amd_todo_inline_editing === "function")
                                window.amd_todo_inline_editing();
                            inlineEditKeymap["Enter"] = false;
                            inlineEditKeymap["Shift"] = false;
                        }
                    }
                    else if(escape) {
                        e.preventDefault();
                        if(typeof window.amd_todo_inline_editing_cancel === "function")
                            window.amd_todo_inline_editing_cancel();
                        inlineEditKeymap["Escape"] = false;
                    }
                }
            }
        });

        let saveOrderTimeout, defaultTasksOrder = "";

        let check_items_movement = (save=true) => {
            clearTimeout(saveOrderTimeout);
            let $items = $("[data-todo]");
            $items.find("._up").removeAttr("disabled").css("opacity", "1");
            $items.find("._down").removeAttr("disabled").css("opacity", "1");
            $items.first().find("._up").attr("disabled", "true").css("opacity", ".3");
            $items.last().find("._down").attr("disabled", "true").css("opacity", ".3");
            reset_orders();

            if(save) {
                saveOrderTimeout = setTimeout(() => {
                    let data = exportTasksOrder();
                    if(data === defaultTasksOrder)
                        return;
                    let n = dashboard.createNetwork();
                    n.clean();
                    n.put("_ajax_target", "ext_todo");
                    n.put("import_tasks_priority", data);
                    n.on.start = () => {
                        $list.setWaiting();
                        setCaption(_t("saving_orders_td"));
                    }
                    n.on.end = (resp, error) => {
                        $list.setWaiting(false);
                        setCaption();
                        if(error)
                            console.log(resp.xhr);
                        else if(!resp.data.success)
                            console.log(resp.data);
                    }
                    n.post();
                }, 5000);
            }
        }

        let reorder_items = () => {
            $list.find("[data-todo]").sort((a, b) => {
                let pa = parseInt($(a).attr("data-priority")),
                    pb = parseInt($(b).attr("data-priority"));
                return pb - pa;
            }).appendTo($list);
        }
        $amd.addPageEvent("tasks_reload", () => reorder_items());

        let reloadListEvents = () => {
            $("[data-todo]").each(function() {
                let $el = $(this);
                if(!$el.hasAttr("data-checked")) {
                    let _id = $el.attr("data-todo");
                    let $cb = $el.find("._checkbox"), $text = $el.find("._text"),
                        $delete = $el.find("._delete"), $move = $el.find("._move"),
                        $up = $el.find("._up"), $down = $el.find("._down");
                    $cb.attr("data-toggle-todo", _id);
                    $cb.on("change", () => toggleTodo($el, $cb, $text, _id));
                    $text.onDoubleClick(() => inlineEdit($text));
                    $delete.attr("data-delete-todo", _id);
                    $delete.on("click", () => deleteTodo($el, _id, true));
                    $el.attr("data-checked", "true");
                    $up.click(function(){
                        var item = $(this).closest("[data-todo]");
                        if (item.prev().length !== 0)
                            item.insertBefore(item.prev());
                        check_items_movement();
                    });
                    $down.click(function() {
                        var item = $(this).closest("[data-todo]");
                        if (item.next().length !== 0)
                            item.insertAfter(item.next());
                        check_items_movement();
                    });
                }
            });

            reorder_items();

            check_items_movement();

            dashboard.initTooltips();
        }
        let reloadList = () => {
            let _network = $amd.clone(network);
            _network.put("_ajax_target", "ext_todo");
            _network.put("get_todo_list", "");
            _network.on.start = () => {
                $list.html("");
                setLoader();
                setCaption(_t("reloading_tasks_td"));
            }
            _network.on.end = (resp, error) => {
                setLoader(false);
                setCaption();
                if(!error) {
                    if(resp.success) {
                        let items = resp.data.data;
                        for(let [id, data] of Object.entries(items)) {
                            const _text = data.text || "", _status = data.status || "", _priority = data.priority || "0";
                            const _reminder_alt = data.reminder_alt || "";
                            let reminder_span = "";
                            if(_reminder_alt.length) reminder_span = `<span class="tiny-text waiting reminder-badge"">${_i("bell")}<span>${_reminder_alt}</span></span>`;
                            const $html = $(`<div class="--item --bg" data-todo="${id}" data-priority="${_priority}">
                        <span class="_indicator"></span>
                        <div>
                            <div class="--text _text" ${_status === "done" ? "style=\"text-decoration:line-through\"" : ""}>${_text}</div>
                            ${reminder_span}
                        </div>
                        <label class="hb-checkbox">
                            <input type="checkbox" class="_checkbox" ${_status === "done" ? "checked" : ""}>
                            <span></span>
                        </label>
                        <button class="btn btn-text _delete no-special" data-tooltip="${_t('delete')}">${_todo_list_item_delete_icon}</button>
                        <span class="btn btn-text _move no-special no-mobile">${_i("move")}</span>
                        <button class="btn btn-text _up no-special no-mobile" data-tooltip="${_t('move_up')}">${_i("chev_up")}</button>
                        <button class="btn btn-text _down no-special no-mobile" data-tooltip="${_t('move_down')}">${_i("chev_down")}</button>
                    </div>`);
                            $list.append($html);
                        }
                        reloadListEvents();
                        checkList();
                        defaultTasksOrder = exportTasksOrder();
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

        let setEditorContent = content => editor.root.innerHTML = content;
        let getEditorContent = () => {
            let inner = editor.root.innerHTML;
            let $html = $(inner);
            if($html.is("p"))
                return $html.html();
            return inner;
        };

        let clearTextarea = () => {
            setEditorContent("");
        }
        const $reminder_input = $("#todo-reminder-date");
        const get_reminder = () => {
            const enabled = $reminder_input.hasAttr("data-enabled", true) === "true";
            const time = parseInt(enabled ? $reminder_input.hasAttr("data-time", true) : 0);
            return {
                time: isNaN(time) ? 0 : time,
                enabled: time ? enabled : false,
                alt: $reminder_input.val()
            }
        }
        $cancel.click(() => clearTextarea());
        $add.click(function() {
            let text = getEditorContent();
            if(!text.length || editor.getText() === "\n") {
                $amd.toast(_t("control_fields"));
                return;
            }
            let priority = $priority.val();
            let _network = dashboard.createNetwork();
            let reminder_data = get_reminder();
            _network.clean();
            _network.put("_ajax_target", "ext_todo");
            _network.put("add_todo", {text, priority, reminder: reminder_data});
            _network.on.start = () => $card_new.cardLoader();
            _network.on.end = (resp, error) => {
                $card_new.cardLoader(false);
                if(!error) {
                    if(resp.success) {
                        clearTextarea();
                        let id = resp.data.id, formatted_text = resp.data.formatted_text || text;
                        let reminder_span = "";
                        if(reminder_data.enabled) reminder_span = `<span class="tiny-text waiting reminder-badge"">${_i("bell")}<span>${reminder_data.alt}</span></span>`;
                        let $html = $(`<div class="--item --bg" data-todo="${id}" data-priority="${priority}">
                        <span class="_indicator"></span>
                        <div>
                            <div class="--text _text">${formatted_text.replaceAll("\n", "<br>")}</div>
                            ${reminder_span}
                        </div>
                        <label class="hb-checkbox">
                            <input type="checkbox" class="_checkbox">
                            <span></span>
                        </label>
                        <button class="btn btn-text _delete no-special" data-tooltip="${_t('delete')}">${_todo_list_item_delete_icon}</button>
                        <span class="btn btn-text _move no-special no-mobile">${_i("move")}</span>
                        <button class="btn btn-text _up no-special no-mobile" data-tooltip="${_t('move_up')}">${_i("chev_up")}</button>
                        <button class="btn btn-text _down no-special no-mobile" data-tooltip="${_t('move_down')}">${_i("chev_down")}</button>
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
<style>.reminder-badge{padding:0 16px;display:flex;align-items:center;gap:5px}.reminder-badge>._icon_{width:18px;height:auto}.reminder-badge>._icon_.bi::before{font-size:15px}body.dark ._badge{color:#fff !important}.amd-todo-row>.--item,.amd-todo-row{display:flex;align-items:center;justify-content:center}.amd-todo-row{position:relative;flex-direction:column;flex-wrap:wrap;margin:16px}.amd-todo-row>.--item{position:relative;box-sizing:border-box;flex-direction:row;border-radius:10px;margin:8px 0;width:100%;padding:8px}@media(min-width:993px){.amd-todo-row{flex-wrap:nowrap}}.amd-todo-row>.--item.--bg{background:var(--amd-wrapper-fg)}.amd-todo-row>.--item .btn{display:flex;flex-wrap:nowrap;align-items:center;justify-content:center;width:32px;min-width:40px;padding:9px;height:auto;aspect-ratio:1;margin:0 4px}.amd-todo-row>.--item .--text{text-align:justify;padding:0 16px;font-size:var(--amd-size-md)}.amd-todo-row>.--item .--text{flex:8}.amd-todo-row>.--item>div{flex:1}.amd-todo-row>.--item>div ._icon_{font-size:20px}.amd-todo-row>.--item ._move{cursor:move}.amd-todo-row>.--item ._indicator{position:absolute;top:0;width:5px;height:100%;background-color:var(--amd-primary);transition:background-color ease .3s}body.rtl .amd-todo-row>.--item ._indicator{right:0;border-radius:0 10px 10px 0}body.ltr .amd-todo-row>.--item ._indicator{left:0;border-radius:10px 0 0 10px}.amd-todo-row>.--item ._text[contenteditable]{margin:0 8px;border:1px dashed rgba(var(--amd-text-color-rgb),.2);padding:4px;text-decoration:none !important;outline:0;border-radius:8px}</style>
<!-- @formatter on -->

<script src="<?php echo esc_url( AMD_MOD . '/persian-date/persian-datepicker.min.js' ); ?>"></script>
<script>
    jQuery($ => {
        const _texts = {
            reminder: `<?php echo esc_html_x( "Reminder", "Todo", "material-dashboard" ); ?>`,
            remove_reminder: `<?php echo esc_html_x( "Remove reminder", "Todo", "material-dashboard" ); ?>`,
            time: `<?php echo esc_html_x( "Time", "clock", "material-dashboard" ); ?>`,
        }
        if(typeof persianDate !== "undefined" && typeof persianDatepicker !== "undefined") {
            const $btn = $("#btn-reminder"), $reminder_section = $("#todo-reminder-section");
            const $input = $("#todo-reminder-date");
            const add_zero = num => num <= 9 ? "0" + num : num;
            const _refresh = () => {
                const state = date_picker.getState(), {unixDate} = state.selected;
                const d = new Date(unixDate), year = d.getFullYear(), month = d.getMonth() + 1, date = d.getDate();
                $input.attr("data-date", `${year}-${add_zero(month)}-${add_zero(date)} ${add_zero(d.getHours())}:${add_zero(d.getMinutes())}`).attr("data-time", unixDate.toString().substring(0, unixDate.toString().length-3));
            }
            const date_picker = $input.persianDatepicker({
                format: `dddd D MMMM YYYY ${_texts.time} H:m`,
                calendarType: "<?php echo get_locale() === 'fa_IR' ? 'persian' : 'gregorian' ?>",
                autoClose: true,
                minDate: new Date().getTime(),
                toolbox: {enabled:true,calendarSwitch:{enabled:true,format:"MMMM"},todayButton:{enabled:true,text:{fa:"امروز",en:"Today"},onToday:_refresh},submitButton:{enabled:true,text:{fa:"تایید",en:"Submit"}},text:{btnToday:"امروز"}},
                navigator: {enabled:true,scroll:{enabled:false},text:{btnNextText: "⮜",btnPrevText: "⮞"}},
                timePicker: {enabled:true,step:1,hour:{enabled:true},minute:{enabled:true},second:{enabled:false},meridian:{enabled:false}},
                onSelect: _refresh
            });
            _refresh();
            const disable = () => {
                $reminder_section.slideUp();
                date_picker.hide();
                $btn.html(_texts.reminder).removeClass("--red").addClass("--green");
                $input.attr("data-enabled", "false");
            }
            const enable = () => {
                $reminder_section.slideDown();
                $btn.html(_texts.remove_reminder).removeClass("--green").addClass("--red");
                $input.attr("data-enabled", "true");
            }
            $btn.click(() => {
                const enabled = $input.hasAttr("data-enabled", true);
                if(enabled === "true") disable();
                else enable();
            });
            $("#btn-cancel-todo").click(() => disable());
            $reminder_section.fadeOut(0);
        }
    });
</script>
