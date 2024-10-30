var AMDTab = (function() {

    function AMDTab(c) {

        var _this = this;

        var conf = Object.assign({
            element: "",
            items: {},
            default_tab: ""
        }, c);

        _this.$tab = $("#" + conf.element);
        _this.$items = _this.$tab.find(".--tabs");

        this.begin = () => {

            let html = "";
            for(let [id, data] of Object.entries(conf.items)) {
                if(typeof data.id !== "undefined") id = data.id;
                let text = data.text || "", icon = data.icon || "";
                let active = data.active || false;
                const _badge = data.badge || null;
                let badge = _badge ? `<span class="badge --sm --circle --tab bg-primary-low color-primary-im text-center">${_badge}</span>` : "";

                html += `<div class="${active ? `active ` : ``}${_badge ? 'has-badge' : ''}" data-amd-tab="${id}">${icon.length > 0 ? `${icon} ` : ``}<span>${text}</span> ${badge}</div>`;
            }
            _this.$items.html(html);

            $(document).on("click", "[data-amd-tab]", function() {
                let attr = $(this).attr("data-amd-tab");
                if(attr) {
                    location.href = `#${attr}`;
                    _this.switch(attr);
                }
            });

            this.switch(this.getActive());
            let focus = this.getFocus();
            if(focus){
                window.onload = () => $amd.scrollTo($(`[data-ts="${focus}"]`), 1000, 20);
            }
            window.addEventListener("popstate", () => _this.switch(_this.getActive()));

        }

        this.getActive = () => {
            let hash = location.href.split("#")[1] || "";
            if(hash) return hash.split(":")[0];

            let active = $("[data-amd-tab].active").hasAttr("data-amd-tab", true);
            if(active) return active;

            return conf.default_tab;
        }

        this.getFocus = () => {
            let hash = location.href.split("#")[1] || "";
            if(hash.indexOf(":") > -1)
                return hash.split(":")[1];
            return "";
        }

        this.switch = id => {
            $("[data-amd-tab]").removeClass("active");
            $(`[data-amd-tab="${id}"]`).addClass("active");
            let $tab = $(`[data-amd-content="${id}"]`);
            if(!$tab.is(":visible")) {
                $("[data-amd-content]").fadeOut(0);
                $tab.fadeIn();
            }
        }

        this.addButton = data => {
            let html = `<div style="background:var(--amd-primary);color:#fff;" class="${data.extraClass || ""}" id="${data.id || ""}">${data.icon.length > 0 ? data.icon : ""} <span>${data.text || ""}</span></div>`;
            let $html = $(html);
            if(typeof data.callback === "function")
                $html.click(() => data.callback());
            _this.$items.append($html);
        }

    }

    return AMDTab;

}());

window.addEventListener("load", () => {
    jQuery($ => {
        $(".amd-order-box").each(function(){
            const $container = $(this);
            if($container.hasClass("exclude"))
                return;
            function reorder(){
                $container.find(".--item").sort((a, b) => $(a).attr("data-order") - $(b).attr("data-order")).appendTo($container);
            }
            function update_order(){
                const $items = $container.find(".--item")
                $container.find(".--item > .--icon").setWaiting(false);
                $items.first().find(".-up").setWaiting();
                $items.last().find(".-down").setWaiting();
                let order = 0;
                $items.each(function(){
                    $(this).attr("data-order", order);
                    order++;
                });
            }
            reorder();
            update_order();
            $container.sortable({
                update: () => update_order()
            });
            $container.find(".--item").each(function(){
                const $i = $(this);
                const $down = $i.find(".-down"), $up = $i.find(".-up");
                const change_order = x => {
                    const o = parseInt($i.hasAttr("data-order", true, 0));
                    $i.attr("data-order", o+x);
                    $i[x > 0 ? "next" : "prev"]().attr("data-order", o);
                    reorder();
                    update_order();
                }
                $down.click(() => change_order(1));
                $up.click(() => change_order(-1));
            });
        });
    });
});