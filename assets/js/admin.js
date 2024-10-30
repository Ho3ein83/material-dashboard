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

                html += `<div${active ? ` class="active"` : ``} data-amd-tab="${id}">${icon.length > 0 ? `${icon} ` : ``}<span>${text}</span></div>`;
            }
            _this.$items.html(html);

            $(document).on("click", "[data-amd-tab]", function() {
                let attr = $(this).attr("data-amd-tab");
                if(attr) {
                    location.href = `#${attr}`;
                    _this.switch(attr);
                }
            });

            this.switch(this.getActive())
            window.addEventListener("popstate", () => _this.switch(_this.getActive()));

        }

        this.getActive = () => {
            let hash = location.href.split("#")[1] || "";
            if(hash) return hash;

            let active = $("[data-amd-tab].active").hasAttr("data-amd-tab", true);
            if(active) return active;

            return conf.default_tab;
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