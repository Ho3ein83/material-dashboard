/**
 * 2.2.3
 * Hello pop
 * Developer: ho3ein.b.83@gmail.com
 * License: ISC (Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted)
 * https://github.com/Ho3ein83/hello-pop
 */
var Hello = (function () {
    "use strict";

    let makeID = () => {
        let d = new Date();
        return "hello-" + Math.ceil(Math.random() * 100 + 100) + "" + d.getMilliseconds();
    }

    let isset = (obj, key, def = null) => typeof obj[key] !== "undefined" ? obj[key] : def

    let replaceArray = (str, find, replace) => str.replace(new RegExp("(" + find.map(function (i) {
        return i.replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&")
    }).join("|") + ")", "g"), function (s) {
        return replace[find.indexOf(s)]
    });

    function Hello(conf = {}) {

        var _this = this;

        this.replaceTemplate = (text) => {
            var iconHTML = "";
            if (["success", "error", "warning", "info"].includes(_this.conf.icon) && typeof _this.templates.icons[_this.conf.icon] !== "undefined")
                iconHTML = _this.templates.icons[_this.conf.icon];
            return replaceArray(
                text,
                ["{text}", "{pop_type}", "{type}", "{attr}", "{id}", "{content}", "{title}", "{icon}", "{loader}"],
                [_this.scope.text, _this.conf.type, _this.scope.type, _this.scope.attr, _this.scope.id, _this.conf.text, _this.conf.title, iconHTML, _this.templates.loader]
            );
        }

        this.default_conf = {
            title: '',
            text: '',
            type: 'popup',
            id: null,
            confirmButton: "Okay",
            cancelButton: false,
            popOnClick: '',
            autoDelete: true,
            forms: {},
            buttons: [],
            allowOutsideClick: true,
            allowEscapeKey: true,
            showLoader: false,
            singleMode: true,
            timeout: null,
            autoFocus: true,
            position: null,
            toastOffset: 10,
            icon: "",
            onBuild: () => null,
            onConfirm: () => null,
            onClose: () => null,
            onCancel: () => null,
            onOpen: () => null,
            onForm: () => null,
            onDelete: () => null
        };

        this.conf = Object.assign(this.default_conf, conf);

        this.scope = {
            id: '',
            text: '',
            type: 'default',
            attr: 'data-hello-close="{id}"'
        }

        if (!this.conf.id)
            this.conf.id = makeID();

        this.scope.id = this.conf.id;

        this.$current = null;

        this.templates = {
            button: `<button class="hello-button {type}" {attr}>{text}</button>`,
            icons: {
                success: `<svg xmlns="http://www.w3.org/2000/svg" class="status-icon success" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/></svg>`,
                error: `<svg xmlns="http://www.w3.org/2000/svg" class="status-icon error" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>`,
                info: `<svg xmlns="http://www.w3.org/2000/svg" class="status-icon info" viewBox="0 0 16 16"><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>`,
                warning: `<svg xmlns="http://www.w3.org/2000/svg" class="status-icon warning" viewBox="0 0 16 16"><path d="M7.005 3.1a1 1 0 1 1 1.99 0l-.388 6.35a.61.61 0 0 1-1.214 0L7.005 3.1ZM7 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0Z"/></svg>`,
            },
            loader: `<div class="indeterminate-progress-bar hello-progress"><div class="indeterminate-progress-bar__progress"></div></div>`,
            card: `<div data-hello="{id}" class="layer-top">
    <div class="hello-pop card">
        <div class="hello-content">
            <span class="hello-content">{content}</span>
        </div>
    </div>
</div>`,
            popup: `<div data-hello="{id}">
    <div class="hello-pop-overlay"></div>
    <div class="hello-pop {pop_type}">
        {loader}
        <div class="hello-head">
            {icon}
            <span class="hello-title" dir="auto">{title}</span>
        </div>
        <div class="hello-content">
            <span class="hello-content" dir="auto">{content}</span>
            <p class="hello-log"></p>
        </div>
        <div class="hello-footer">{footer}</div>
    </div>
</div>`,
            toast: `<div class="hello-toast" data-hello="{id}" dir="auto">{content}</div>`
        };

        this.set = (conf) => this.conf = Object.assign(this.default_conf, conf);

        this.build = () => {
            if(_this.conf.singleMode){
                Hello.closeAll();
            }
            var id = _this.conf.id;
            var html = _this.templates.popup;
            if(_this.conf.type === "card") html = _this.templates.card;
            else if(_this.conf.type === "toast") html = _this.templates.toast;
            var button = _this.templates.button;
            var footer = '';

            let applyButton = (obj, type = "cancel") => {
                if (typeof obj === "string") {
                    _this.scope.text = obj;
                    _this.scope.type = (type === "cancel" ? "danger" : "default");
                    _this.scope.attr = `data-hello-${type}="{id}"`;
                    footer += _this.replaceTemplate(button);
                } else if (typeof obj === "object") {
                    _this.scope.text = isset(obj, "text", (type === "confirm" ? "Okay" : "Cancel"));
                    _this.scope.type = isset(obj, "type", (type === "cancel" ? "danger" : "default"));
                    _this.scope.attr = isset(obj, "attr", `data-hello-${type}="{id}"`);
                    footer += _this.replaceTemplate(button);
                }
            }

            applyButton(_this.conf.confirmButton, "confirm");
            applyButton(_this.conf.cancelButton, "cancel");

            $.each(_this.conf.buttons, (i, v) => applyButton(v, "button"));

            html = html.replaceAll("{footer}", footer);

            if (_this.$current !== null) _this.$current.remove();

            $("body").append(_this.replaceTemplate(html));
            _this.$current = $(`[data-hello="${id}"]`);
            _this.$current.fadeOut(0);
            if(_this.conf.showLoader) _this.$current.find(".hello-progress").fadeIn(0);
            else _this.$current.find(".hello-progress").fadeOut(0);
            _this.conf.onBuild(_this.conf.id);
            _this.init();
            Hello.pops[id] = _this;
            return _this;
        }

        this.init = () => {

            this.initForms();

            if(_this.conf.type === "toast"){
                let $e = _this.$current;
                let w = window.innerWidth, h = window.innerHeight;
                let rw = $e.width(), rh = $e.height();
                let pos = _this.conf.position;
                if(typeof pos !== "string") pos = "bm";
                let posT = pos[0] || "b";
                let posL = pos[1] || "m";
                let l = 0, t = 0, offset = conf.toastOffset;

                if(posT === "t") t = offset;
                else if(posT === "m") t = h/2 - rh/2;
                else if(posT === "b") t = h - rh - offset;

                if(posL === "l") l = offset;
                else if(posL === "m") l = w/2 - rw/2;
                else if(posL === "r") l = w - rw - offset;

                $e.css("left", l);
                $e.css("top", t);
            }

            if (this.conf.popOnClick) {
                $(document).on("click", this.conf.popOnClick, function () {
                    _this.pop();
                })
            }

            $(document).on("click", "[data-hello-confirm]", () => {})

            _this.$current.find("[data-hello-confirm]").click(function (e) {
                e.preventDefault();
                let $btn = $(e.target);
                let id = $btn.attr('data-hello-confirm');
                let hello = Hello.getPop(id);
                $btn.blur();
                let cl = true;
                if (hello !== null && typeof hello.conf.onConfirm === "function") {
                    let r = hello.conf.onConfirm(_this.conf.id);
                    if(r === false) cl = false;
                }
                if(cl) hello.close();
            });

            if (_this.conf.allowOutsideClick) $(document).on("click", ".hello-pop-overlay", () => _this.close())

            if (_this.conf.allowEscapeKey) {
                $(document).on("keydown", e => {
                    if (e.key === "Escape")
                        _this.close()
                })
            }

            _this.$current.find("[data-hello-cancel]").click(function (e) {
                e.preventDefault();
                let $btn = $(this);
                let id = $btn.attr('data-hello-cancel');
                let hello = Hello.getPop(id);
                $btn.blur();
                if (hello !== null && typeof hello.conf.onCancel === "function")
                    hello.conf.onCancel();
                hello.close();
            });

        }

        this.initForms = () => {
            if (_this.conf.forms.length <= 0)
                return;
            var data = {};
            $.each(_this.conf.forms, (i, v) => {
                if (typeof v === "function") {
                    let form = _this.$current.find("#" + i)
                    if (form.length === 1) {
                        form.get(0).addEventListener("submit", (e) => {
                            e.preventDefault();
                            _this.conf.onForm(_this.conf.id);
                            form.find("input").each((i2, v2) => {
                                let $el = $(v2);
                                let name = $el.attr("name");
                                if (typeof name !== "undefined") {
                                    data[name] = {
                                        value: $el.val(),
                                        element: $el.get(0)
                                    }
                                }
                            });
                            let setter = (_text, _type) => _this.setLog(_text, _type);
                            let results = v(data, setter);
                            if (typeof results !== "undefined") {
                                if (typeof results.success !== "undefined" && typeof results.text !== "undefined") {
                                    let type = isset(results, "success", false) ? "success" : "error";
                                    let log = isset(results, "log", false);
                                    let waiting = isset(results, "waiting", false);
                                    if (waiting) form.addClass("waiting")
                                    _this.setLog(_this.replaceTemplate(results.text), log ? 'log' : type);
                                }
                            } else {
                                _this.setLog(null);
                            }
                            return false;
                        });
                    }
                }
            });
        }

        this.setLog = (text, type = "log") => {
            if (_this.$current === null) return null;
            let $el = _this.$current.find(".hello-log");
            if ($el.length > 0) {
                $el.fadeOut(0);
                $el.attr("class", `hello-log ${type.toString()}`);
                $el.html(text);
                $el.fadeIn();
            }
            if (text === null || text.length <= 0) {
                $el.html("");
                $el.attr("class", "hello-log");
            }
        }

        this.pop = () => {
            if (_this.$current === null) return null;
            _this.$current.fadeIn(100);
            _this.conf.onOpen(_this.conf.id);
            if(_this.conf.autoFocus)
                _this.$current.find("[data-hello-confirm]").focus();
            if(_this.conf.timeout !== null && _this.conf.timeout > 0)
                setTimeout(() => _this.close(), _this.conf.timeout);

            return _this;
        }

        this.setLoader = (b=true) => {
            if(b) _this.$current.find(".hello-progress").fadeIn(0);
            else _this.$current.find(".hello-progress").fadeOut(0);
        }

        this.setLoaderProgress = (p=null) => {
            let $c = _this.$current.find(".hello-progress"),
                $p = $c.find(".indeterminate-progress-bar__progress");
            let progress = parseInt(p);
            if(p === null){
                $c.removeClass("--custom");
                $c.removeAttr("style");
                $p.removeAttr("style");
            }
            else if(!isNaN(progress)){
                if(!$c.hasClass("--custom"))
                    $c.addClass("--custom").css("direction", "ltr");
                $p.css("width", p + "%");
                $p.css("animation", "none");
            }
        }

        this.isVisible = () => {
            if(!_this.$current) return false;
            return _this.$current.css("display") !== "none";
        }

        this.close = (notice = true) => {
            if (_this.$current === null) return null;
            _this.$current.fadeOut(100);
            if (typeof _this.conf.onClose === "function" && _this.isVisible() && notice)
                _this.conf.onClose(_this.conf.id);
            if (_this.conf.autoDelete)
                setTimeout(() => _this.destroy(), 300);
            return _this;
        }

        this.destroy = () => {
            if (_this.$current === null) return null;
            $(`[data-hello=${_this.conf.id}]`).remove();
            _this.$current = null;
            Hello.pops[_this.conf.id] = null;
            delete Hello.pops[_this.conf.id];
        }

    }

    Hello.pops = {};

    Hello.fire = (conf = {}) => {
        let hello = new Hello(conf);
        hello.build();
        if (typeof conf.popOnClick === "undefined")
            hello.pop();
        return hello;
    }

    var queue = {};
    let checkQueue = () => {
        // noinspection LoopStatementThatDoesntLoopJS
        for(let [id, pop] of Object.entries(queue)){
            pop.build();
            pop.pop();
            break;
        }
    }
    Hello.queue = (conf={}) => {
        let pop = new Hello(Object.assign({
            onClose: () => {
                if(typeof queue[pop.conf.id] !== "undefined"){
                    delete queue[pop.conf.id];
                    checkQueue();
                }
            },
            singleMode: false
        }, conf));
        queue[pop.conf.id] = pop;
        checkQueue();
    }

    Hello.clearQueue = (closeAll=true) => {
        queue = {};
        if(closeAll) Hello.closeAll();
    }

    Hello.toast = (conf={}) => {
        let hello = new Hello(Object.assign({
            type: "toast"
        }, conf));
        hello.build();
        if (typeof conf.popOnClick === "undefined")
            hello.pop();
        return hello;
    }

    Hello.getPop = (id) => {
        if (typeof Hello.pops[id] === "undefined")
            return null;
        return Hello.pops[id];
    }

    Hello.closePop = (id) => {
        if (typeof Hello.pops[id] !== "undefined")
            Hello.pops[id].close();
    }

    Hello.destroyAll = () => {
        $.each(Hello.pops, id => {
            let pop = Hello.getPop(id);
            pop.close();
            pop.destroy();
        });
    }

    Hello.closeAll = () => {
        $.each(Hello.pops, id => {
            let pop = Hello.getPop(id);
            pop.close();
        });
    }

    Hello.closeOthers = (id) => {
        $.each(Hello.pops, i => {
            if(i !== id){
                let pop = Hello.getPop(i);
                pop.close();
            }
        });
    }

    return Hello;

}());