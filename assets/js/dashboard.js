var AMDDashboard = (function() {

    function AMDApiEngine(c) {

        var conf = Object.assign({
            api_url: "",
            action: ""
        }, c);

        _network = new AMDNetwork();
        _network.setAction(conf.action);

        this.on = {
            start: () => null,
            success: () => null,
            failed: () => null,
            error: () => null,
            end: () => null
        }

        this.getNetwork = () => _network;
        this.clean = () => {
            this.on = {
                start: () => null,
                success: () => null,
                failed: () => null,
                error: () => null,
                end: () => null
            }
            _network.clean();
        }
        this.put = (k, v) => _network.put(k, v);
        this.set = d => _network.set(d);
        this.getData = () => _network.getData();
        this.post = (xhrF = null) => {
            _network.on = this.on;
            _network.postTo(conf.api_url, xhrF);
        }

    }

    function AMDDashboard(c) {

        var _this = this;
        var isSuspended = false;

        var conf = Object.assign({
            sidebar_id: "",
            navbar_id: "",
            wrapper_id: "",
            content_id: "",
            loader_id: "",
            api_url: "",
            mode: "dashboard",
            loading_text: _t("wait_td"),
            prefix: "amd_",
            languages: {},
            network: null,
            translate: {
                "show_region": false,
                "show_flag": false
            },
            defaultCookieExpire: "1 month",
            default_indicator: "indicator-1",
            lazy_load: true,
            is_broken: false,
            sidebar_items: {},
            navbar_items: {},
            quick_options: {},
            checkin_interval: 15000,
            user: {}
        }, c);

        var $sidebar = null, $navbar = null, $loader = null, $wrapper = null, $content, $after_content = null, $before_content = null;
        var thisuser = null, _api_engine = null;
        var actions = {}, keymap = {}, keymap_lower = {}, keyEvents = [], keyEvents2 = {};
        var _floating_buttons = {}, _checkin_interval;
        var defaultContentHtml = "";
        var lazyEvent = {
            open: [],
            reload: [],
            request: [],
            start: [],
            before_start: [],
            success: [],
            failed: [],
            error: [],
            end: []
        };

        var _queue = [];

        var _error = (msg, type = null) => {
            if(type === "alert") {
                _this.alert(_t("error"), msg, {
                    icon: "error"
                });
            }
            else if(type === "private") {
                _this.alert(_t("error"), _t("private_error"), {
                    icon: "warning",
                    confirmButton: false,
                    cancelButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });
                console.log(msg);
            }
            else if(type === "public") {
                _this.alert(_t("error"), msg);
                console.log(msg);
            }
            else {
                console.log(msg);
            }
        }

        var handleKeys = e => {
            for(let i = 0; i < keyEvents.length; i++) {
                let item = keyEvents[i];
                let shortcut = item.shortcut || null, callable = item.callable || null;
                if(typeof shortcut !== "string" || typeof callable !== "function")
                    continue;
                if(shortcut === "NO_KEY") {
                    for(let [_k, _v] of Object.entries(keymap_lower)) {
                        if(_v)
                            return;
                    }
                    callable(e);
                    return;
                }
                let keys = shortcut.split("+");
                let keysLen = keys.length, counter = 0;
                for(let j = 0; j < keysLen; j++) {
                    let k = keys[j].toLowerCase();
                    if(typeof keymap_lower[k] === "undefined" || !keymap_lower[k])
                        break;
                    counter++;
                }
                if(counter !== keysLen || keysLen !== Object.keys(keymap_lower).length)
                    continue;
                callable(e);
            }
        }

        this.started = false;

        this.setSidebarItemBadge = (item, badge, color = null) => {
            let $item = $(`[data-menu-item="${item}"]`);
            if($item.length) {
                let $badge = $item.find(".--badge");
                $badge.removeClass((index, className) => (className.match(/(^|\s)bg-\S+/g) || []).join(" "));
                if(badge !== null) {
                    $badge.html(badge);
                    if(color) {
                        $badge.removeClass((index, className) => (className.match(/(^|\s)bg-\S+/g) || []).join(" "));
                        $badge.addClass("bg-" + color);
                    }
                }
                else {
                    $badge.html("");
                    $badge.addClass("bg-none");
                }
            }
        }

        this.setNavbarItemBadge = (item, badge, color = null) => {
            let $item = $(`#nav-item-${item}`);
            if($item.length) {
                let $badge = $item.find(".--badge");
                $badge.removeClass((index, className) => (className.match(/(^|\s)bg-\S+/g) || []).join(" "));
                if(badge !== null) {
                    $badge.html(badge);
                    if(color) {
                        $badge.removeClass((index, className) => (className.match(/(^|\s)bg-\S+/g) || []).join(" "));
                        $badge.addClass("bg-" + color);
                    }
                }
                else {
                    $badge.html("");
                    $badge.addClass("bg-none");
                }
            }
        }

        this.getSidebarItemBadge = (item) => {
            let $item = $(`[data-menu-item="${item}"]`);
            if($item.length) {
                let $badge = $item.find(".--badge");
                let text = $badge.html();
                let classes = $badge.attr("class").split(" ");
                let color = "";
                for(let i = 0; i < classes.length; i++) {
                    let c = classes[i] || "";
                    if(c.startsWith("bg-")) color = c.replace("bg-", "");
                }
                return {
                    text: text,
                    classes: classes,
                    color: color
                }
            }
        }

        this.addSidebarItem = (id, data) => {
            conf.sidebar_items[id] = data;
            let _pr = data.priority || 10;
            if(_pr >= 9999)
                return;
            let _id = data.id || id;
            let $menu = $sidebar.find("ul.amd-sidebar-menu");
            let icon = data.icon || "", text = data.text || "";
            let url = data.url || "";
            let _void = data.void || "";
            let turtle = data.turtle || "lazy", indicator = data.indicator || conf.default_indicator;
            if(!url) {
                url = "javascript:void(0)";
            }
            else {
                if(_void)
                    url = $amd.mergeUrlQueryOnly(location.href, url, ["page_id", "void"]);
            }
            let attr = $amd.makeAttributes(data.attributes || {});
            let extraClass = data.extraClass || "";
            let active = typeof data.active === "undefined" ? false : Boolean(data.active);
            let badge = typeof data.badge !== "undefined" ? data.badge : "", badge_color = data.badge_color || "none",
                _badge = "";
            if(badge !== null) _badge = ` <span class="--badge bg-${badge_color}">${badge}</span>`;
            else _badge = ` <span class="--badge"></span>`;
            if(indicator.length <= 0) indicator = conf.default_indicator;
            let html = `<li class="amd-menu-item${extraClass.length > 0 ? ' ' + extraClass + ' ' : ' '}${indicator}${active ? ' active' : ''}" id="menu-item-${_id}" data-menu-item="${_id}">
    <a href="${url}" data-href="${_void}" data-turtle="${turtle}" ${attr}>${icon} <span class="--text">${text}</span>${_badge}</a>
</li>`;
            if($menu.find(`#menu-item-${id}`).length > 0)
                $menu.find(`#menu-item-${id}`).replaceWith(html);
            else
                $menu.append(html);
        }

        this.addQuickItem = (id, data) => {
            conf.quick_options[id] = data;
            let $quick = $sidebar.find(".--quick-options");
            let icon = data.icon || "", text = data.text || "";
            let url = data.url || "javascript: void(0)";
            let turtle = data.turtle || "none";
            let attr = data.attributes || {};
            let tooltip = data.tooltip || null;
            if(tooltip) attr = Object.assign({"data-tooltip": tooltip}, attr);
            attr = $amd.makeAttributes(attr);
            let extraClass = data.extraClass || "";
            let html = `<a href="${url}" id="quick-option-${id}" class="${extraClass.length > 0 ? extraClass : ''}" data-quick-option="${id}" data-turtle="${turtle}" ${attr}>${icon}</a>`;
            if($quick.find(`#quick-option-${id}`).length > 0)
                $quick.find(`#quick-option-${id}`).replaceWith(html);
            else
                $quick.append(html);
        }

        this.addNavItem = (id, data, side = "left") => {
            conf.navbar_items[id] = {};
            conf.navbar_items[id][side] = data;
            let $side = $navbar.find(`.--side-${side}`);
            if($side.length <= 0) return;
            let icon = data.icon || "";
            let url = data.url || "javascript: void(0)";
            let turtle = data.turtle || "none";
            let attr = data.attributes || {};
            let tooltip = data.tooltip || null;
            let badge = data.badge || "", badge_color = data.badge_color || "none";
            if(!badge) badge_color = "none";
            if(tooltip) attr = Object.assign({"data-tooltip": tooltip}, attr);
            attr = $amd.makeAttributes(attr);
            let extraClass = data.extraClass || "";
            let html = `<a href="${url}" id="nav-item-${id}" data-turtle="${turtle}" class="navbar-item --${side} ${extraClass.length > 0 ? ' ' + extraClass : ''}" ${attr}>${icon} <span class="--badge bg-${badge_color}">${badge}</span></a>`;
            if($side.find(`#nav-item-${id}`).length > 0)
                $side.find(`#nav-item-${id}`).replaceWith(html);
            else
                $side.append(html);
        }

        this.initSidebar = () => {
            if(!$sidebar) return;
            let $menu = $sidebar.find("ul.amd-sidebar-menu");
            let $quick = $sidebar.find(".--quick-options");
            $menu.html("");
            $quick.html("");
            for(let [id, data] of Object.entries(conf.sidebar_items))
                _this.addSidebarItem(id, data);
            for(let [id, data] of Object.entries(conf.quick_options))
                _this.addQuickItem(id, data);
            $sidebar.find(".--user-name").html(thisuser.getFullname());
            this.setAvatar(thisuser.getAvatar());
        }

        this.setAvatar = url => {
            $sidebar.find(".--avatar").attr("src", url);
            $("img._self_avatar_").attr("src", url);
            $amd.doEvent("set_user_avatar", { url });
        }

        this.setUserName = name => {
            $sidebar.find(".--user-name").html(name);
            $amd.doEvent("set_user_name", { name });
        }

        this.initNavbar = () => {
            if(!$navbar) return;
            let $left = $navbar.find(".--side-left");
            let $right = $navbar.find(".--side-right");
            $left.html("");
            $right.html("");
            for(let [id, data] of Object.entries(conf.navbar_items.left || {}))
                _this.addNavItem(id, data, "left");
            for(let [id, data] of Object.entries(conf.navbar_items.right || {}))
                _this.addNavItem(id, data, "right");
        }

        this.initCookies = () => {
            let initTime = parseInt(_this.getCookie("init"));
            if(isNaN(initTime)) initTime = 0;
            if(initTime <= $amd.getTime()) {
                _this.setCookie("init", $amd.getTime() + 60 * 60 * 24, "1 day");
            }
        }

        this.setCookie = (key, value, expire = null) => {
            key = conf.prefix + key;
            if(expire === null)
                expire = conf.defaultCookieExpire;
            $amd.setCookie(key, value, expire);
        }

        this.getCookie = key => {
            key = conf.prefix + key;
            return $amd.getCookie(key);
        }

        this.getTheme = () => {
            let cookie = _this.getCookie("theme");
            if(cookie && ["dark", "light"].includes(cookie))
                return cookie;
            return $("body").hasClass("dark") ? "dark" : "light";
        }

        this.switchTheme = (mode = "toggle") => {
            let $b = $("body");
            if(mode === "light") {
                $b.removeClass("dark");
                $b.addClass("light");
            }
            else if(mode === "dark") {
                $b.removeClass("light");
                $b.addClass("dark");
            }
            else {
                $b.toggleClass("dark light");
                mode = $b.hasClass("dark") ? "dark" : "light";
            }
            _this.setCookie("theme", mode);
            $amd.doEvent("change_theme_mode", mode);
        }

        this.setUser = user => conf.user = user;

        this.removeQueryParam = (q, reload = false) => {
            let queries = [];
            if(typeof q === "string")
                queries.push(q);
            else if(typeof q === "object")
                queries = Object.assign(queries, q);
            let params = new URLSearchParams(location.search);
            let has = false;
            for(let i = 0; i < queries.length; i++) {
                let key = queries[i] || null;
                if(params.has(key)) {
                    params.delete(key);
                    has = true;
                }
            }
            this.pushState("?" + params.toString())
            if(reload && has) location.reload();
        }

        this.hasQueryParam = q => {
            let params = new URLSearchParams(location.search);
            return params.has(q);
        }

        this.addLazyEvent = (event, callable) => {
            if(typeof lazyEvent[event] === "undefined")
                return false;
            if(typeof lazyEvent[event] !== "object")
                lazyEvent[event] = [];
            lazyEvent[event].push(callable);
            return true;
        }

        let dispatchLazyEvent = (e, data = {}) => {
            if(typeof lazyEvent[e] !== "object")
                return null;
            for(let i = 0; i < lazyEvent[e].length; i++){
                let d = lazyEvent[e][i](data);
                if(d === false)
                    return false;
            }
            return true;
        }

        this.awake = () => {
            _this.resetCheckin();
            (function() {
                let $clocks = $(".__live_clock");
                if($clocks.length > 0) {
                    $clocks.each(function(){
                        let $clock = $(this);
                        if($clock.hasAttr("--checked"))
                            return;
                        let format = $clock.hasAttr("data-format", true, "h:m:s");
                        let setClock = () => {
                            let d = new Date();
                            let hh = d.getHours().toString();
                            let mm = d.getMinutes().toString();
                            let ss = d.getSeconds().toString();
                            // $clock.html(`${hh.addZero()}:${mm.addZero()}:${ss.addZero()}`);
                            let text = format.replaceAll("h", hh.addZero()).replaceAll("m", mm.addZero()).replaceAll("s", ss.addZero());
                            $clock.html(text);
                        }
                        setClock();
                        setInterval(() => setClock(), 1000);
                    });
                }
            }());
        }

        this.init = () => {

            this.switchTheme(_this.getTheme());

            thisuser = new (function() {
                function AMDUser(d = {}) {
                    var data = Object.assign({
                        "ID": null,
                        "username": null,
                        "email": null,
                        "first_name": null,
                        "last_name": null,
                        "avatar": null,
                    }, d);
                    this.get = () => data;
                    this.getFullname = () => `${data.first_name} ${data.last_name}`;
                    this.getAvatar = () => data.avatar;
                }

                return AMDUser;
            }())(conf.user);

            if(conf.sidebar_id)
                $sidebar = $("#" + conf.sidebar_id);
            if(conf.navbar_id)
                $navbar = $("#" + conf.navbar_id);
            if(conf.content_id)
                $content = $("#" + conf.content_id);
            if(conf.loader_id)
                $loader = $("#" + conf.loader_id);
            if(conf.wrapper_id)
                $wrapper = $("#" + conf.wrapper_id);

            $before_content = $("#before-content");
            $after_content = $("#after-content");

            this.setLoader(false);

            if(typeof AMDNetwork === "undefined" || !conf.network instanceof AMDNetwork) {
                _error("AMDNetwork [bundle.js] is undefined, this module is required for dashboard. Please feel free to ask us for any help you need, you can find our contact info in plugin settings.", "private");
                return false;
            }

            _api_engine = _this.createApiEngine("_api_handler");

            this.initSidebar();
            this.initNavbar();
            this.initCookies();

            this.setLoaderText(conf.loading_text);

            $(document).on("click", "[data-toggle-sidebar]", function(e) {
                e.stopPropagation();
                let action = $(this).attr("data-toggle-sidebar");
                if(["open", "expand"].includes(action)) _this.expandSidebar();
                else if(["close", "collapse"].includes(action)) _this.collapseSidebar();
                else _this.toggleSidebar();
            });

            $(document).on("click", "body, .amd-sidebar", e => {
                if(!conf.sidebar_id) return;
                let $el = $(e.target), sid = conf.sidebar_id;
                let isSidebar = $el.parents(`#${sid}`).length > 0;
                if($el.hasAttr("id", true) === sid) isSidebar = true;
                if(!isSidebar) _this.collapseSidebar()
            });

            $(document).on("click", "[data-switch-theme]", function() {
                let mode = $(this).hasAttr("data-switch-theme", true);
                if(mode) {
                    _this.switchTheme(mode);
                    return;
                }
                _this.switchTheme();
            });

            $(document).on("click", "[data-action]", function() {
                let $el = $(this);
                let action = $el.attr("data-action");
                if(action) {
                    $el.addClass("waiting");
                    $el.blur();
                    _this.runAction(action, () => $el.setWaiting(), () => $el.setWaiting(false));
                }
            });

            $(document).on("click", "[data-change-locale]", function() {
                if(!_this.isMultiLingual())
                    return false;
                let locale = $(this).hasAttr("data-change-locale", true, "");
                if(locale) {
                    if($("body").hasClass(locale))
                        return;
                    let query = $amd.queryToArray();
                    query["lang"] = locale;
                    location.href = $amd.arrayToQuery(query);
                    return;
                }
                _this.languagePopup();
            });

            $(document).on("click", "[data-turtle]", function(e) {
                e.preventDefault();

                var $el = $(this), turtle = $el.attr("data-turtle");
                var href = $el.hasAttr("href", true, "void(0)");
                if(href.length <= 0 || href === "void(0)")
                    href = $el.hasAttr("data-href", true, href);

                if(href === "void(0)" || turtle === "none" || turtle.length <= 0)
                    return false;

                if(!conf.lazy_load && turtle === "lazy")
                    turtle = "normal";

                $amd.doEvent("_lazy_request");

                var allowed = dispatchLazyEvent("request", {
                    type: turtle,
                    href: href,
                    element: $el.get(0)
                });

                if(allowed === false) {
                    e.preventDefault();
                    return false;
                }

                if(href === "refresh") {
                    location.reload();
                    return false;
                }

                switch(turtle) {
                    case "lazy":
                        e.preventDefault();
                        _this.collapseSidebar();
                        _this.lazyOpen(href);
                        break;
                    case "blank":
                        window.open(href);
                        break;
                    case "reload":
                        _this.lazyReload(true, "turtle");
                        break;
                    case "normal":
                        location.href = href;
                        break;
                }

                return true;

            });

            $(document).on("focus", ".focus-select", function() {
                let $el = $(this);
                $el.select();
            });

            $(document).on("keydown", "[data-input]", function(e) {
                let $el = $(this), pattern = $el.attr("data-input");
                let key = e.key, allowedKey, forms = amd_conf.forms;

                if(pattern.regex("^%.*%$")) {
                }
                allowedKey = forms.getAllowedKeys(pattern);

                let isSpecial = forms.special_keys.includes(key);
                if(_this.isKeyDown("Control"))
                    isSpecial = true;

                if(!key.regex(allowedKey) && !isSpecial) {
                    e.preventDefault();
                    return false;
                }

            });

            $(document).on("click", "[data-lazy-query]", function() {
                let query = $(this).attr("data-lazy-query");
                let toRemove = $(this).hasAttr("data-query-toRemove", true, []);
                _this.lazyOpen($amd.mergeCurrentQuery(query, toRemove));
            });

            $(document).on("click", "[data-close-box]", function() {
                let id = $(this).attr("data-close-box");
                if(id) {
                    let $box = $(`#amd-box-${id}`);
                    $box.removeSlow();
                    $amd.doEvent(`close_alert_box:${id}`);
                }
            });

            $(document).on("click", "[data-copy]", function() {
                let text = $(this).attr("data-copy");
                if(text) $amd.copy(text, false, true);
            });

            $(window).on('popstate', () => _this.lazyReload(true, "popstate"));

            $(window).on("keydown keyup", function(e) {
                let {type, key} = e;
                if(typeof key === "undefined")
                    return;
                keymap[key] = type === "keydown";
                keymap_lower[key.toLowerCase()] = type === "keydown";

                // Check events
                for(let [_key, events] of Object.entries(keyEvents2)) {
                    let all = _key.split(",");
                    for(let j = 0; j < all.length; j++) {
                        let k = all[j];
                        for(let i = 0; i < events.length; i++) {
                            let data = events[i] || {};
                            let callable = data.callable || null;
                            let _type = data.type || "*";
                            if(typeof _type !== "string" || typeof callable !== "function") continue;
                            if(_type !== type && _type !== "*") continue;
                            if(k === key) callable(e);
                        }
                    }
                }

                if(type === "keyup") {
                    delete keymap[key];
                    delete keymap_lower[key.toLowerCase()];
                }
                handleKeys(e);
            });

            $(window).on("load", _this.resume());

            _this.resetCheckin();

            _this.addLazyEvent("success", () => _this.awake());

            if(this.hasQueryParam("lang")) {
                $content.html(_t("wait_td"));
                this.removeQueryParam("lang", true);
            }
            else {
                if(!conf.is_broken)
                    _this.lazyReload(false);
            }

            if(conf.is_broken) {
                conf.lazy_load = false;
                _this.started = true;
                _this.checkQueue();
            }

            if(!_this.isMultiLingual())
                $("[data-change-locale]").remove();

            $amd.addEvent("_lazy_end", () => {
                $.each($amd.events, (key, ignored) => {
                    if(key.startsWith("_PAGE_"))
                        $amd.unbindEvent(key);
                });
                if(!_this.started) {
                    _this.started = true;
                    _this.checkQueue();
                }
            });

        }

        var loaderInterval = null;

        this.setLoader = (b = true) => {
            let $_loader = $loader;
            if(conf.mode === "form")
                $_loader = $("#form-loader");
            $amd.doEvent("loader_display", { display: b });
            if(!$_loader) return;
            let $e = $("._show_on_loader_");
            let $dots = $("._loader_dots_");
            let dots = 0, maxDots = 3;
            $dots.html("<span style='opacity:0'>.</span>".repeat(maxDots));
            if(b) {
                if(conf.mode === "form")
                    $_loader.fadeIn();
                else
                    $_loader.removeClass("__stop");
                $e.fadeIn(0);
                $dots.html("<span style='opacity:0'>.</span>".repeat(maxDots - dots));
                loaderInterval = setInterval(() => {
                    if(dots < 0) dots = 0;
                    else if(dots > maxDots) dots = 0;
                    $dots.html("<span>.</span>".repeat(dots) + "<span style='opacity:0'>.</span>".repeat(maxDots - dots));
                    dots++;
                }, 700);
            }
            else {
                if(conf.mode === "form")
                    $_loader.fadeOut();
                else
                    $_loader.addClass("__stop")
                $e.fadeOut(0);
                clearInterval(loaderInterval);
            }
        }

        this.setLoaderText = text => {
            let $_loader = $loader;
            if(conf.mode === "form")
                $_loader = $("#form-loader");
            if(!$_loader) return;
            $("._loading_text_").html(text);
        }

        this.setLoaderVisibility = (b, t = 400) => {
            let $_loader = $loader;
            if(conf.mode === "form")
                $_loader = $("#form-loader");
            if(!$_loader) return;
            if(b)
                $_loader.find("._loader_").fadeIn(t);
            else
                $_loader.find("._loader_").fadeOut(t);
        }

        this.suspend = (b = true, timeout = 0) => {
            isSuspended = b;
            let $screen = $("._suspend_screen_");
            if(!$screen.length) return;
            if(b) $screen.fadeIn();
            else $screen.fadeOut();
            if(b && timeout > 0) setTimeout(() => _this.resume(), timeout);
            if(!b) _this.checkQueue();
        }

        this.setSuspendText = text => {
            let $sus = $("._suspend_screen_");
            $sus.find("suspend-text").remove();
            if(text)
                $sus.append(`<p class="suspend-text">${text}</p>`);
        }

        this.stop = () => this.suspend(true);

        this.resume = () => this.suspend(false);

        this.isSuspended = () => isSuspended;

        this.getSidebar = () => {
            if(!$sidebar) $sidebar = $("#" + conf.sidebar_id);
            return $sidebar;
        }

        this.checkQueue = () => {

            if(_this.started && !_this.isSuspended()) {
                setTimeout(() => {
                    _queue.forEach(c => {
                        if(typeof c === "function")
                            c();
                    });
                }, 500);
            }

        }

        this.queue = callable => {

            _queue.push(callable);

            _this.checkQueue();

        }

        this.expandSidebar = () => this.getSidebar().removeClass("collapse");

        this.collapseSidebar = () => this.getSidebar().addClass("collapse");

        this.toggleSidebar = () => this.getSidebar().toggleClass("collapse")

        this.initTooltips = () => {
            if(typeof HBTooltip !== "undefined")
                new HBTooltip().init();
        };

        this.availableLanguages = () => Object.entries(conf.languages).length;

        this.isMultiLingual = () => _this.availableLanguages() > 1;

        this.languagePopup = (closeButton = true) => {
            let html = "";
            let entries = Object.entries(conf.languages);
            let showFlag = conf.translate.show_flag, showReg = conf.translate.show_region;
            if(entries.length > 1) {
                for(let [locale, data] of entries) {
                    let region = data.region || "";
                    let flag = data.flag || "";
                    if(region.length > 0) region = ` (${region})`;
                    if(flag.length > 0) flag = `<img src="${flag}" alt="">`;
                    let isActive = $("body").hasClass(locale);
                    html += `<a href="javascript: void(0)" dir="auto" class="btn ${isActive ? 'active' : 'btn-text'}" data-change-locale="${locale}"><span>${showFlag ? flag : ""}<span>${data.name}${showReg ? region : ""}</span></span></a>`;
                }
                if(closeButton) closeButton = _t("close");
                $amd.alert(_t("language"), "", {
                    text: `<div class="select-bar full-width">${html}</div>`,
                    confirmButton: false,
                    cancelButton: closeButton
                });
            }
        }

        this.clearFloatingButtons = () => {
            if(!$wrapper) return;
            _floating_buttons = {};
            $wrapper.find(".amd-floating-buttons").remove();
        }

        this.addFloatingButtons = (id, data) => {
            _floating_buttons[id] = data;
        }

        this.renderFloatingButtons = (position = "bottom") => {
            if(!$wrapper) return;
            let $html = $(`<div class="amd-floating-buttons --${position}"></div>`);
            for(let [id, data] of Object.entries(_floating_buttons)) {
                let text = data.text || "";
                let _args = data.args || [], args = "";
                let _attrs = data.attrs || {}, attrs = "";
                let callable = data.onClick || null;
                for(let i = 0; i < _args.length; i++) args += `--${_args[i]} `;
                for(let [key, value] of Object.entries(_attrs)) attrs += `${key}="${value}" `;
                args = args.trimChar(" ");
                $btn = $(`<button type="button" class="btn --button ${args}" ${attrs} id="floating-button-${id}">${text}</button>`);
                if(typeof callable === "function") $btn.click(e => callable(e));
                $html.append($btn);
            }
            $wrapper.find(".amd-floating-buttons").remove();
            $wrapper.append($html);
        }

        this.getUser = (getObject = false) => {
            return getObject ? thisuser.get() : thisuser;
        }

        this.alert = (title, text, conf = {}) => $amd.alert(title, text, conf);

        this.pushState = (url, data = {}) => window.history.pushState(data, "", url);

        this.lazyStart = data => {
            var request_allowed = dispatchLazyEvent("before_start", data);
            if(request_allowed === false)
                return false;

            if(!$content || !conf.lazy_load) return false;
            $content.html(defaultContentHtml);
            if($after_content.length)
                $after_content.html("");
            if($before_content.length)
                $before_content.html("");
            this.setLoader();

            $amd.doEvent("_lazy_start", data);

            var allowed = dispatchLazyEvent("start", data);
            if(allowed === false)
                return false;

            let done = content => {
                _this.setLoader(false);
                $content.html(content);
            }
            let network = this._net();
            network.clean();
            network.put("lazyloading", data);
            network.on.end = (resp, error) => {
                $amd.doEvent("_lazy_end", resp);
                dispatchLazyEvent("end", resp);
                if(!error) {
                    done(resp.data.html);
                    let title = resp.data.title || null;
                    if(title) _this.setTitle(title);
                    let redirect = resp.data.redirect || null;
                    if(redirect){
                        _this.setLoader(true);
                        location.href = redirect;
                    }
                    $amd.doEvent("_lazy_success", resp);
                    dispatchLazyEvent("success", resp);
                }
                else {
                    done(resp.xhr.responseText || _t("error"));
                    $amd.doEvent("_lazy_error", resp);
                    dispatchLazyEvent("error", resp);
                    console.log(resp.error, resp.xhr);
                }
            };
            network.post();

            return true;

        }

        this.getSearchParams = (href = null) => {
            if(href === null)
                href = location.search;
            return new URLSearchParams(href.split("?")[1] || "");
        };

        this.lazyReload = (hardReload=true, ref=null) => {
            if(!conf.lazy_load && hardReload) {
                location.reload();
                return;
            }
            var href = location.href;
            var _params = this.getSearchParams();
            var params = {};
            for(var [key, value] of _params.entries())
                params[key] = value;
            var data = {
                url: href.split("#")[0],
                params: params,
                _void: _params.has("void") ? _params.get("void") : "",
                hash: href.split("#")[1] || "",
                ref: ref
            };
            $amd.doEvent("_lazy_reload", data);
            var allowed = dispatchLazyEvent("reload", data);
            if(allowed === false)
                return false;

            this.lazyStart(data);

            return true;
        }

        this.lazyOpen = url => {
            if(!conf.lazy_load) {
                location.href = url;
                return;
            }
            $amd.doEvent("_lazy_open", url);
            var allowed = dispatchLazyEvent("open", url);
            if(allowed === false)
                return false;
            if(url.startsWith("?")) {
                let params = _this.getSearchParams();
                let _params = new URLSearchParams(url);
                if(params.has("page_id"))
                    _params.set("page_id", params.get("page_id"));
                url = "?" + _params;
            }
            this.pushState(url, {});
            this.lazyReload(true, "opener");
        }

        this._net = () => {
            let n = conf.network;
            n.clean();
            n.setAction(amd_conf.ajax.dashboard);
            return n;
        }

        this.toast = (text, time = 3000, conf = {}) => {
            $amd.toast(text, Object.assign({
                position: $amd.isMobile() ? "bm" : "mm",
                toastOffset: 50,
                timeout: time
            }, conf));
        }

        this.makeForm = form => {
            let $form = $(`[data-form="${form}"]`);
            let $inputs = $form.find("input");
            let defaults = {};
            $inputs.each(function() {
                let $e = $(this);
                let type = $e.hasAttr("type", true, "text");
                let name = $e.hasAttr("name", true);
                let value = $e.val();
                if(type === "checkbox" || type === "radio")
                    value = $e.is(":checked");
                if(!name)
                    return;
                defaults[name] = value;
            });
            return {
                validate: () => {
                    $inputs.each(function() {
                        let $e = $(this);
                        amd_conf.forms.validateInput($e);
                    });
                },
                defaults: defaults,
                dismiss: () => {
                    $inputs.each(function() {
                        let $e = $(this);
                        let type = $e.hasAttr("type", true, "text");
                        let name = $e.hasAttr("name", true);
                        let value = typeof defaults[name] !== "undefined" ? defaults[name] : null;
                        if(value === null)
                            return;
                        if(type === "checkbox" || type === "radio") {
                            if(typeof value === "boolean")
                                $e.prop("checked", value);
                        }
                        else {
                            $e.val(value);
                        }
                    });
                }
            };
        }

        this.setTitle = title => document.title = title;

        this.runAction = (action, success = () => {
        }, failed = () => {
        }) => {
            if(typeof actions[action] !== "function") return null;
            return actions[action](success, failed);
        }

        this.addAction = (action, callable) => actions[action] = callable

        this.addHotKey = (shortcut, callable, type = "*") => {
            keyEvents.push({
                "shortcut": shortcut,
                "callable": callable,
                "type": type
            });
        }

        this.isKeyDown = key => typeof keymap[key] !== "undefined" && keymap[key];

        this.onKey = (key, callable, type = "*") => {
            keyEvents2[key] = [];
            keyEvents2[key].push({
                "callable": callable,
                "type": type
            });
        }

        this.onKeyup = (key, callable) => this.onKey(key, callable, "keyup");

        this.onKeydown = (key, callable) => this.onKey(key, callable, "keydown");

        this.checkin = () => {
            if(_this.isLoggedIn()) this.createNetwork().clean().cleanEvents().put("_checkin", true).post()
        };

        this.resetCheckin = () => {
            clearInterval(_checkin_interval);
            if(_this.isLoggedIn()) _checkin_interval = setInterval(() => _this.checkin(), conf.checkin_interval);
        }

        this.isLoggedIn = () => _this.getUser(true).ID !== null;

        /**
         * @returns {AMDApiEngine}
         */
        this.getApiEngine = () => _api_engine;

        /**
         * @param action
         * @returns {AMDApiEngine}
         */
        this.createApiEngine = (action = "_api_handler") => {
            return new AMDApiEngine({
                api_url: conf.api_url,
                action: action
            });
        }

        /**
         * Create new object of network class
         * @param c
         * @returns {AMDNetwork}
         */
        this.createNetwork = (c = {}) => {
            let n = new AMDNetwork(c);
            n.setAction(amd_conf.ajax.private);
            n.clean();
            return n;
        }

        // since 1.0.9
        this.setDefaultContentHtml = html => defaultContentHtml = html;

        // since 1.0.9
        this.get$content = () => $content;
        this.get$sidebar = () => $sidebar;
        this.get$navbar = () => $navbar;
        this.get$wrapper = () => $wrapper;
        this.get$loader = () => $loader;

    }

    return AMDDashboard;

}());