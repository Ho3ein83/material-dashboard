"use strict";

function amd_open_user_picker(picker_id, selected_users=[], just_reload=false){
    //const $picker = $(`[data-user-picker="${picker_id}"]`);
    const $picker = $("#amd-user-picker-root");
    if($picker.length){
        let selection = [], users = [], current_selection = selected_users;

        const reload = () => {
            current_selection = [...new Set(current_selection)];
            $(`input[data-user-picker-target=${picker_id}]`).val(current_selection.join(",")).trigger("change");
            $(`[data-user-picker-counter=${picker_id}]`).html(current_selection.length);
            $(`[data-user-picker-label=${picker_id}]`).html(current_selection.length > 0 ? _n("n_users_selected", current_selection.length) : _t("select_users"));
        };

        if(just_reload) {
            reload();
            return;
        }

        const $input = $picker.find("._input"),
            $add = $picker.find("._add_btn"), $search = $picker.find("._search_btn"),
            $list = $picker.find("._items_list"), $list_log = $picker.find("._list_log"),
            $table = $picker.find("._table"), $items = $picker.find("._table_items"),
            $progress = $picker.find("._progress"), $selected = $picker.find("._selected_users_"),
            $continue = $picker.find("._continue_btn"), $selection_table = $picker.find("._selection_table"),
            $selection_table_items = $picker.find("._selection_table_items"), $close = $picker.find("._close_picker_");

        $picker.attr("data-user-picker", picker_id);
        $picker.fadeIn(100);
        $picker.css("backdrop-filter", "blur(5px)");

        const _is_selected = user_id => {
            user_id = parseInt(user_id);
            if(isNaN(user_id))
                return false;
            for(let i = 0; i < current_selection.length; i++){
                if(parseInt(current_selection[i]) === parseInt(user_id))
                    return true;
            }
            return false;
        }
        const get_selected_users = () => {
            return current_selection;
        }
        const refresh_selection = () => {
            $selected.html(get_selected_users().length);
        }
        const check_selection = () => {
            if(selection.length){
                $list.fadeIn(0);
                $list_log.fadeIn();
            }
            else{
                $list_log.fadeOut(0);
                $list.html("").fadeOut(0);
            }
        }
        const check_items = () => {
            if(users.length){
                $items.fadeIn(0);
                refresh_selection();
                $table.fadeIn(0);
            }
            else{
                $items.html("").fadeOut(0);
                $selected.html(0);
                $table.fadeOut(0);
            }
        }
        const print_current_selection = () => {
            if(current_selection.length){
                let n = new AMDNetwork();
                n.setAction(amd_conf.ajax.private);
                n.clean();
                n.put("_ajax_target", "search_engine");
                n.put("export_users_by_id", current_selection);
                n.on.start = () => {
                    $picker.setWaiting();
                    $progress.fadeIn(0);
                    $selection_table.fadeOut(0);
                }
                n.on.end = (resp, error) => {
                    $picker.setWaiting(false);
                    $progress.fadeOut(0);
                    $selection_table.fadeIn(0);
                    if(!error){
                        if(resp.success){
                            const _users = resp.data.users ?? {};
                            if(typeof _users === "object"){
                                users = [];
                                for(let [user_id, data] of Object.entries(_users)) {
                                    $selection_table_items.append(`<tr><td><span>${user_id}</span></td><td><span>${data.fullname}</span></td><td><button type="button" class="amd-admin-button --sm --red" data-selected data-select-user="${user_id}" style="margin:4px;">${_t("delete")}</button></td></tr>`);
                                }
                                refresh_selection();
                            }
                        }
                        else{
                            $amd.toast(resp.data.msg || _t("error"));
                        }
                    }
                    else{
                        console.log(resp.xhr);
                        $amd.toast(_t("error"));
                    }
                }
                n.post();
            }
        }
        const check_current_selections = () => {
            if(users.length || selection.length || !current_selection.length){
                $selection_table.fadeOut(0);
                $selection_table_items.html("").fadeOut(0);
            }
            else{
                $selection_table.fadeIn(0);
                $selection_table_items.fadeIn(0);
                print_current_selection();
            }
        }

        const reset = () => {
            $picker.removeAttr("data-user-picker");
            $input.val("").fadeIn(0);
            $add.fadeIn(0);
            $search.fadeIn(0);
            $list.html("");
            $selection_table_items.html("");
            $items.html("");
            $progress.fadeOut(0);
            $continue.fadeOut(0);
            selected_users = [];
            users = [];
            selection = [];
            check_selection();
            check_items();
            check_current_selections();
        }

        reset();

        if(!$picker.hasClass("events-initialized")) {
            $(document).on("keydown", e => {
                if (e.key === "Escape") $close.click();
            });
            $add.click(function(){
                const s = $input.val();
                if(s){
                    if(selection.includes(s)) {
                        $input.val("");
                        return;
                    }
                    let $i = $(`<span class="amd-admin-button --sm --primary --text" data-value="${s}">${s}</span>`);
                    $i.click(() => {
                        $i.removeSlow();
                        selection.removeValue(s);
                        check_selection();
                    });
                    $list.append($i);
                    selection.push(s);
                    check_selection();
                    $input.val("");
                }
            });
            $input.on("keydown", e => {
                if(e.key === "Enter") $add.click();
            });
            $search.click(function(){
                if(!selection.length) return;
                let n = new AMDNetwork();
                n.setAction(amd_conf.ajax.private);
                n.clean();
                n.put("_ajax_target", "search_engine");
                n.put("search_users", selection);
                n.on.start = () => {
                    $table.fadeOut(0);
                    $progress.fadeIn(0);
                    $items.html("").fadeOut(0);
                    $continue.fadeIn(0);
                    $list.fadeOut(0);
                    $list_log.fadeOut(0);
                    $input.fadeOut(0);
                    $search.fadeOut(0);
                    $add.fadeOut(0);
                    $selection_table_items.html("").fadeOut(0);
                    $selection_table.fadeOut(0);
                }
                n.on.end = (resp, error) => {
                    $progress.fadeOut(0);
                    if(!error){
                        if(resp.success){
                            const _users = resp.data.users ?? {};
                            if(typeof _users === "object"){
                                users = [];
                                for(let [user_id, data] of Object.entries(_users)){
                                    users.push(user_id);
                                    let is_selected = _is_selected(user_id);
                                    $items.append(`<tr><td><span>${user_id}</span></td><td><span>${data.fullname}</span></td><td><button type="button" class="amd-admin-button --sm ${is_selected ? '--red' : '--blue'}"${is_selected ? ' data-selected' : ''} data-select-user="${user_id}" style="margin:4px;">${is_selected ? _t("delete") : _t("select")}</button></td></tr>`);
                                }
                                check_items();
                            }
                        }
                        else{
                            $amd.toast(resp.data.msg || _t("error"));
                        }
                    }
                    else{
                        console.log(resp.xhr);
                        $amd.toast(_t("error"));
                    }
                }
                n.post();
            });
            $continue.click(function(){
                $continue.fadeOut(0);
                $input.fadeIn(0);
                $list.fadeIn(0);
                $list_log.fadeIn(0);
                $search.fadeIn();
                $add.fadeIn();
                selection = [];
                users = [];
                check_selection();
                check_items();
                check_current_selections();
            });
            $picker.on("click", "[data-select-user]", function(){
                const $btn = $(this);
                const is_selected = $btn.hasAttr("data-selected");
                const id = $btn.attr("data-select-user");
                if(id) {
                    const $e = $(`[data-select-user="${id}"]`);
                    $e.html(!is_selected ? _t("delete") : _t("select")).removeClass("--blue --red --primary").addClass(!is_selected ? "--red" : "--blue");
                    is_selected ? $e.removeAttr("data-selected") : $e.attr("data-selected", "");
                    !is_selected ? users.push(id) : users.removeValue(id);
                    !is_selected ? current_selection.push(id) : current_selection.removeValue(id);
                    current_selection = [...new Set(current_selection)];
                    refresh_selection();
                }
            });
            $picker.on("click", "._pick_selected_users_", () => {
                reload();
                $picker.fadeOut(100);
                selection = [];
                //reset();
            });
            $close.click(() => {
                reset();
                $picker.fadeOut(100);
            });
            $picker.addClass("events-initialized");
            $amd.addEvent("amd_reset_user_picker", () => {
                current_selection = [];
            });
        }
    }
}

function amd_reload_user_picker(picker_id, selected_users=[]){
    amd_open_user_picker(picker_id, selected_users, true);
}

function amd_reset_user_picker(picker_id){
    amd_open_user_picker(picker_id, [], true);
    $amd.doEvent("amd_reset_user_picker");
}

window.addEventListener("load", function(){
    jQuery($ => {
        $(document).on("click", "[data-pick-user]", function(){
            const id = $(this).attr("data-pick-user");
            if(id) {
                let selection = ($(`input[data-user-picker-target="${id}"]`).val() || "");
                if(selection.length)
                    selection = selection.split(",");
                else
                    selection = [];
                amd_open_user_picker(id, selection);
            }
        });
        $("[data-pick-user]").each(function(){
            const id = $(this).attr("data-pick-user");
            if(id) amd_reload_user_picker(id);
        });
    });
});