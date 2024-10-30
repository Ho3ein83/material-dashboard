<?php

$primary_color = amd_ext_todo_get_primary_color();

/**
 * Set how many items should display in home page
 * @since 1.0.5
 */
$max_tasks_to_show = apply_filters( "amd_ext_todo_max_items_to_show_in_home", 6 );

?>

<?php ob_start(); ?>
<?php echo esc_html_x( "Todo list (news)", "todo", "material-dashboard" ); ?>
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
<div class="text-center" id="todo-show-more" style="display:none">
    <a href="?void=todo" class="btn btn-text --low" data-turtle="lazy"><?php esc_html_e( "More", "material-dashboard" ); ?></a>
    <div class="h-10"></div>
</div>
<?php $todo_card_content = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
    "title" => $todo_card_title,
    "content" => $todo_card_content,
    "color" => $primary_color,
    "type" => "title_card"
) ); ?>

<?php if( false ): ?>
<script>
    (function(){
        var _todo_list_item_delete_icon = `<?php _amd_icon( "delete" ); ?>`;
        let max_items_to_show = parseInt(`<?php echo $max_tasks_to_show; ?>`);
        let $list = $("#todo-list-items"), $loading = $("#loading-todo"),
            $more = $("#todo-show-more");
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
                $more.fadeOut(0);
                setLoader();
            }
            _network.on.end = (resp, error) => {
                setLoader(false);
                if(!error){
                    if(resp.success){
                        let items = resp.data.data;
                        let entries = Object.entries(items);
                        if(entries.length > max_items_to_show)
                            $more.fadeIn(0);
                        let counter = 0;
                        for(let [id, data] of entries){
                            if(counter >= max_items_to_show)
                                break;
                            let _text = data.text || "";
                            let _status = data.status || "";
                            let primary_color = `<?php echo esc_attr( $primary_color ); ?>`;
                            let $html = $(`<div class="--item --bg" data-todo="${id}">
                    <div class="--text _text" ${_status === "done" ? "style=\"text-decoration:line-through\"" : ""}>${_text}</div>
                    <label class="hb-checkbox ${primary_color}">
                        <input type="checkbox" class="_checkbox" ${_status === "done" ? "checked" : ""}>
                        <span></span>
                    </label>
                    <button class="btn ${primary_color} btn-text _delete no-special">${_todo_list_item_delete_icon}</button>
                </div>`);
                            $list.append($html);
                            counter++;
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
<?php else: ?>
<!-- @formatter off -->
<script>!function(){let t=parseInt("<?php echo $max_tasks_to_show; ?>"),e=$("#todo-list-items"),o=$("#loading-todo"),a=$("#todo-show-more"),d=()=>{let t=$("#todo-empty");$("[data-todo]").length>0?(t.fadeOut(0),e.fadeIn()):(t.fadeIn(),e.fadeOut(0))};d();let s=(t=!0)=>{t?(e.fadeOut(0),o.fadeIn()):(e.fadeIn(0),o.fadeOut(0))};s(!1);let n=()=>{$("[data-todo]").each(function(){let t=$(this);if(!t.hasAttr("data-checked")){let e=t.attr("data-todo"),o=t.find("._checkbox"),a=t.find("._text"),s=t.find("._delete");o.attr("data-toggle-todo",e),o.on("change",()=>{var d,s,n,c;let r,l;return d=t,s=o,n=a,c=e,r=s.is(":checked"),l=dashboard.createNetwork(),void(d.blur(),l.clean(),l.put("_ajax_target","ext_todo"),l.put("edit_todo",c),l.put("data",{status:r?"done":"undone"}),l.on.start=()=>d.setWaiting(),l.on.end=(t,e)=>{d.setWaiting(!1),e?(s.prop("checked",!r),$amd.toast(_t("error"))):t.success?r?n.css("text-decoration","line-through"):n.css("text-decoration","none"):(s.prop("checked",!r),$amd.toast(t.data.msg))},l.post())}),s.attr("data-delete-todo",e),s.on("click",()=>{var o,a;let s;return o=t,a=e,void((s=dashboard.createNetwork()).clean(),s.put("_ajax_target","ext_todo"),s.put("delete_todo",a),s.on.start=()=>o.setWaiting(),s.on.end=(t,e)=>{o.setWaiting(!1),e?$amd.toast(_t("error")):t.success?(o.removeSlow(300),setTimeout(()=>d(),310)):$amd.toast(t.data.msg)},s.post())}),t.attr("data-checked","true")}})},c=()=>{let o=$amd.clone(network);o.put("_ajax_target","ext_todo"),o.put("get_todo_list",""),o.on.start=()=>{e.html(""),a.fadeOut(0),s()},o.on.end=(o,c)=>{if(s(!1),c)$amd.toast(_t("error"));else if(o.success){let r=Object.entries(o.data.data);r.length>t&&a.fadeIn(0);let l=0;for(let[i,h]of r){if(l>=t)break;let p=h.text||"",u=h.status||"",f="<?php echo esc_attr( $primary_color ); ?>",g=$(`<div class="--item --bg" data-todo="${i}"><div class="--text _text" ${"done"===u?'style="text-decoration:line-through"':""}>${p}</div><label class="hb-checkbox ${f}"><input type="checkbox" class="_checkbox" ${"done"===u?"checked":""}><span></span></label><button class="btn ${f} btn-text _delete no-special"><?php _amd_icon( "delete" ); ?></button></div>`);e.append(g),l++}n(),d()}else $amd.toast(o.data.msg)},o.post()};c(),$("._refresh_todo_list_").click(()=>c())}();</script>
<!-- @formatter on -->
<?php endif; ?>
<!-- @formatter off -->
<style>.amd-todo-row>.--item,.amd-todo-row{display:flex;align-items:center;justify-content:center}.amd-todo-row{position:relative;flex-direction:column-reverse;flex-wrap:wrap;margin:16px}.amd-todo-row>.--item{box-sizing:border-box;flex-direction:row;border-radius:10px;margin:4px 0;width:100%;padding:8px}@media(min-width:993px){.amd-todo-row{flex-wrap:nowrap}}.amd-todo-row>.--item.--bg{background:rgba(var(<?php echo "--amd-color-$primary_color-rgb"; ?>),.2)}.amd-todo-row>.--item .btn{display:flex;flex-wrap:nowrap;align-items:center;justify-content:center;width:32px;min-width:40px;padding:9px;height:auto;aspect-ratio:1;margin:0 4px}.amd-todo-row>.--item .--text{text-align:justify;padding:0 16px;font-size:var(--amd-size-md)}.amd-todo-row>.--item .--text{flex:8}.amd-todo-row>.--item>div{flex:1}.amd-todo-row>.--item>div ._icon_{font-size:20px}</style>
<style>body.theme-yeti .btn.--transparent{color:var(--amd-primary)}</style>
<!-- @formatter on -->