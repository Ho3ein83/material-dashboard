"use strict";
const AMDIconPicker = (function () {

    function AMDIconPicker(id, $el, $library) {

        let _this = this;

        this.texts = {
            default_text: `<?php esc_html_e( "Choose icon", "material-dashboard" ); ?>`
        };

        this.class_list = ["amd-icon-picker"];
        this.default_icon = "";

        this.init = (init_events=false) => {
            this.class_list.forEach(i => $el.addClass(i));
            $el.html(`<span class="-title">${_this.texts.default_text}</span><span class="-icon">${_this.default_icon}</span>`);
            let selected = $el.hasAttr("data-pick", true);
            if(selected)
                _this.pick(selected);
            if(init_events)
                _this.init_events();
        }

        this.init_events = () => {
            $el.click(() => _this.open_library());
        }

        this.open_library = () => {
            $library.removeClass("--hidden").attr("data-picker-id", id);
            let selected_icon = _this.get_icon();
            if(selected_icon){
                $library.find("[data-pick-icon]").removeClass("selected");
                $library.find(`[data-pick-icon="${selected_icon}"]`).addClass("selected");
            }
        }

        this.close_library = () => {
            $library.addClass("--hidden").removeAttr("data-picker-id");
        }

        this.pick = icon_id => {
            $el.attr("data-pick", icon_id).change();
            $el.find(".-icon").html(_i(icon_id));
            let link = $el.hasAttr("data-link", true);
            if(link){
                let $link = $(`#${link}`);
                if($link.length)
                    $link.val(icon_id).change();
            }
        }

        this.get_picker = () => $el;

        this.get_library = () => $library;

        this.get_icon = (def="") => $el.hasAttr("data-pick", true, def);

        this.get_id = () => id;

    }

    return AMDIconPicker;

}());

window.amd_icon_pickers = {};

const amd_find_picker = picker_id => {
    if(picker_id && typeof window.amd_icon_pickers[picker_id] !== "undefined") {
        let p = window.amd_icon_pickers[picker_id];
        if(p instanceof AMDIconPicker)
            return p;
    }
    return false;
}
const amd_init_icon_pickers = () => {
    let $library = $("#amd-icon-library");
    $("[data-icon-picker]").each(function(){
        let $e = $(this);
        if($e.hasAttr("data-initialized"))
            return;
        let id = $e.attr("data-icon-picker");
        if(id && id.indexOf("%") <= -1){
            let picker = new AMDIconPicker(id, $(this), $library);
            picker.init(true);
            window.amd_icon_pickers[id] = picker;
            $e.attr("data-initialized", new Date().getTime());
        }
    });
    $library.on("click", ".-icon", function(){
        let $el = $(this);
        let id = $el.hasAttr("data-pick-icon", true);
        let picker_id = $library.hasAttr("data-picker-id", true);
        if(picker_id){
            let picker = amd_find_picker(picker_id);
            if(picker){
                picker.pick(id);
                picker.close_library();
            }
        }
    });
    $library.on("click", ".-close", function(){
        $library.addClass("--hidden").removeAttr("data-picker-id");
    });
    $(window).on("keydown", function(e){
        if($library.hasClass("--hidden") || $library.hasAttr("data-picker-id")) {
            if (e.key === "Escape")
                $library.find(".-close").click();
        }
    });
}

window.addEventListener("load", () => {
    amd_init_icon_pickers();
    $("#amd-icon-library").removeAttr("style");
});