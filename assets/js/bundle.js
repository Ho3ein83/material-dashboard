/* main.js */
// noinspection JSUnusedLocalSymbols

jQuery.fn.extend({
    hasAttr: function(name, get = false, def = null) {
        if(typeof $(this).attr(name) === 'undefined')
            return get ? (def !== null ? def : "") : false;
        return get ? $(this).attr(name) : true
    },
    setWaiting: function(bool = true) {
        if(bool) $(this).addClass("waiting");
        else $(this).removeClass("waiting");
    },
    setSpinner: function(bool = true) {
        if(bool) $(this).addClass("adp-spin --rev --ease");
        else $(this).removeClass("adp-spin --rev --ease");
    },
    cardLoader: function(bool = true) {
        if(bool) {
            $(this).append(`<div class="indeterminate-progress-bar --card-progress"><div class="indeterminate-progress-bar__progress"></div></div>`);
            $(this).addClass("waiting");
        }
        else {
            $(this).find('.--card-progress').remove();
            $(this).removeClass("waiting");
        }
    },
    setInvalid: function(bool = true, mode = "invalid", clear = false) {
        let classList = "invalid valid info warning";
        let $el = $(this), $pr = $el.parent();
        if(bool && classList.split(" ").includes(mode)) {
            $el.removeClass(classList);
            $el.addClass(mode);
            $pr.removeClass(classList);
            $pr.addClass(mode);
            if(clear) $el.addClass("--clear-validate-state");
        }
        else {
            $el.removeClass(classList);
            $pr.removeClass(classList);
        }
    },
    onLongClick: function(callback, timeout = 500) {
        var handler = null;
        var $el = $(this);
        $el.on("mousedown touchstart", function(e) {
            handler = setTimeout(() => callback(e), timeout);
        });
        $el.on("mouseup touchend", () => clearTimeout(handler));
    },
    onDoubleClick: function(callback, timeout = 500) {
        var handler = null;
        var $el = $(this);
        $el.on("click", function(e) {
            if($(this).hasClass("--double-click-check")){
                $el.removeClass("--double-click-check");
                clearTimeout(handler);
                callback(e);
                return;
            }
            $el.addClass("--double-click-check");
            handler = setTimeout(() => {
                $el.removeClass("--double-click-check");
            }, timeout);
        });
    },
    onKeyPress: function(key, callback) {
        $(this).on("keydown", e => {
            if(e.key === key)
                callback();
        });
    },
    waitHold: function(text=null){
        let $el = $(this);
        let lastHtml = $el.html();
        $el.blur();
        $el.setWaiting();
        if(text){
            $el.attr("data-last-html", encodeURIComponent(lastHtml));
            $el.html(text);
        }
    },
    waitRelease: function(){
        let $el = $(this);
        $el.setWaiting(false);
        let lastHtml = $el.hasAttr("data-last-html", true);
        if(lastHtml) $el.html(decodeURIComponent(lastHtml)).removeAttr("data-last-html");
    },
    removeSlow: function(t = 700, fromParent = false, done=()=>null) {
        let $el = fromParent ? $(this).parent() : $(this);
        $el.fadeOut(t, () => {
            $el.remove();
            done();
        });
    },
    winload: (callback) => window.addEventListener("load", callback),
    translateDigits: function(translate_persian=true, translate_arabic=true, once=false){
        const $e = $(this);
        const do_translate = () => {
            translate_persian ? $e.val($amd.p2e($e.val())) : "";
            translate_arabic ? $e.val($amd.a2e($e.val())) : "";
        }
        if(once){
            do_translate();
            return;
        }
        if(!$e.hasClass("amd-digit-translate")){
            $e.on("input", () => do_translate());
            $e.addClass("amd-digit-translate");
        }
    }
});

String.prototype.regex = function(r) {
    let regex = new RegExp(r, "g");
    return regex.test(this);
};

String.prototype.addZero = function() {
    let num = parseInt(this);
    if(isNaN(num))
        return "00";
    return num <= 9 ? "0" + num : num;
};

Array.prototype.remove = function(index) {
    this.splice(index, 1);
};

Array.prototype.reOrder = function() {
    let arr = this;
    let newArray = [];
    for(let i = 0; i < this.length; i++) {
        if(arr[i] && arr[i] !== null) newArray.push(arr[i]);
    }
    return newArray;
};

Array.prototype.removeValue = function(value) {
    var arr = this;
    $.each(arr, function(i, v) {
        if(v === value) arr.splice(i, 1);
    });
};

Array.prototype.last = function() {
    return this[this.length - 1];
};

String.prototype.isNumeric = function() {
    return !isNaN(this) && !isNaN(parseFloat(this));
}

String.prototype.encodeEntity = function() {
    return this.replace(/[\u00A0-\u9999<>&]/gim, function(i) {
        return '&#' + i.charCodeAt(0) + ';';
    });
}

String.prototype.isEmpty = function() {
    if(this === null) return true;
    return this.length <= 0;
}

String.prototype.replaceArray = function(find, replace) {
    var replaceString = this;
    for(var i = 0; i < find.length; i++)
        replaceString = replaceString.replace(find[i], replace[i]);
    return replaceString;
};

String.prototype.shuffle = function() {
    var a = this.split(""), n = a.length;
    for(var i = n - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var tmp = a[i];
        a[i] = a[j];
        a[j] = tmp;
    }
    return a.join("");
}

String.prototype.slugify = function(sp = '-') {
    let str = this;
    str = str.replace(/[`~!@#$%^&*()\-+=\[\]{};:'"\\|\/,.<>?\s]/g, ' ').toLowerCase();
    str = str.replace(/^\s+|\s+$/gm, '');
    str = str.replace(/\s+/g, sp);
    return str;
}

String.prototype.trimChar = function(character) {
    let string = this;
    let first = [...string].findIndex(char => char !== character);
    let last = [...string].reverse().findIndex(char => char !== character);
    return string.substring(first, string.length - last);
}
/* End of main.js */

/* amd.js */
var $amd = {
    /**
     * Hello popup simple alert
     * @param {string|*} title
     * @param {string|*} text
     * @param {{cancelButton: (string|*), onConfirm: (function(): void), confirmButton: (string|*)}|{icon: string}} conf
     * @return Hello
     */
    alert: (title, text, conf = {}) => {
        return Hello.fire(Object.assign({
            title: title,
            text: text,
            confirmButton: _t("ok"),
            type: "popup",
        }, conf));
    },
    /**
     * @param {String} title
     * @param {String} text
     * @param {Function} onConfirm
     * @param {Function} onCancel
     * @param {{confirmButton: (string|*), cancelButton: (string|*)}} conf
     * @since 1.2.0
     */
    confirm: (title, text, onConfirm=()=>null, onCancel=()=>null, conf={}) => {
        Hello.fire(Object.assign({
            title: title,
            text: text,
            confirmButton: _t("yes"),
            cancelButton: _t("no"),
            onConfirm: () => onConfirm(),
            onCancel: () => onCancel(),
            type: "popup",
        }, conf));
    },
    alertQueue: (title, text, conf = {}) => {
        Hello.queue(Object.assign({
            title: title,
            text: text,
            confirmButton: _t("ok"),
            type: "popup",
        }, conf));
    },
    /**
     * Hello popup simple toast
     * @param {*} text
     * @param {{cancelButton: (string|*), onConfirm: (function(): void), confirmButton: (string|*)}|{icon: string}} conf
     * @return Hello
     */
    toast: (text, conf = {}) => {
        return Hello.queue(Object.assign({
            title: "",
            text: text,
            type: "toast",
            singleMode: false,
            position: $amd.isMobile() ? "bm" : "mm",
            toastOffset: 50,
            timeout: 3000
        }, conf));
    },
    /**
     * Generate random string<br>
     * Key table:
     * "lower": Lowercase
     * "upper": Uppercase
     * "letters": Lower and Uppercase
     * "numbers": Numbers
     * <br>Anything else: Lowercase, uppercase and numbers
     */
    generate: (len, key = "*") => {
        switch(key) {
            case "lower":
                key = "ksmprzgbjcnltofydhiweaqvxu"; // Lowercase
                break;
            case "upper":
                key = "HYBWDXTURCAZPQOMFKGESJLVIN"; // Uppercase
                break;
            case "letters":
                key = "BRxuPvymLbwCKThesXWUtjSNQOgkdrlpqAVMYcfGioznZJHaDIFE"; // Lower and Uppercase
                break;
            case "numbers":
                key = "4765291380"; // Numbers
                break;
            default:
                key = "BRxuPvymLbwCKThesXWUtjSNQOgkdrlpqAVMYcfGioznZJHaDIFE4765291380"; // Lowercase, uppercase and numbers
        }

        let string = '';

        for(var i = 0; i < len; i++)
            string = string + key[Math.floor(Math.random() * key.length)];

        return string;
    },
    /**
     * Decode json
     * @param {string} str
     */
    jsonDecoder: str => {
        try {
            str = str.replaceAll("'", "\"");
            let json = JSON.parse(str);
            return {
                'this': json,
                'get': (key) => {
                    if(typeof json[key] !== 'undefined')
                        return json[key];
                    else
                        return null;
                }
            };
        } catch {
            return null;
        }
    },
    /**
     * Copy text to clipboard
     * @param text
     * @param alert
     * @param toast
     * @param $el
     */
    copy: (text, alert = false, toast = false, $el=false) => {
        let textarea = document.createElement("textarea");
        textarea.innerHTML = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        textarea.remove();
        if(alert && typeof Hello !== "undefined")
            $amd.alert(_t("copied"), text);
        if(toast && typeof Hello !== "undefined")
            $amd.toast(_t("copied"));
        if($el.length) {
            let lh = $el.html();
            $el.html(_t("copied")).setWaiting();
            setTimeout(() => $el.html(lh).setWaiting(false), 2000);
        }
    },
    /**
     * Set page title, ignore if title is null
     * @param title
     * @param delay
     */
    setTitle: (title, delay = 0) => {
        if(title !== null) {
            if(delay <= 0) document.title = title;
            else setTimeout(() => document.title = title, delay);
        }
    },
    /**
     * Smooth scroll to element
     * @param el
     * @param t
     * @param offset
     */
    scrollTo: (el, t = 1500, offset = 0) => {
        if($(el).length <= 0) return;
        let top = $(el).offset().top;
        $([document.documentElement, document.body]).animate({
            scrollTop: top - (offset + 20)
        }, t);
    },
    events: {},
    /**
     * Set event listener
     * @param event
     * @param callback
     */
    setEvent: (event, callback) => $amd.events[event] = callback,
    /**
     * Add multiple events
     * @param event
     * @param callback
     */
    addEvent: (event, callback) => {
        if(typeof $amd.events[event] === "undefined") $amd.events[event] = [];
        if(typeof $amd.events[event].push === "undefined") $amd.events[event] = [];
        if(event.startsWith("_SINGLE_"))
            $amd.events[event] = [callback];
        else
            $amd.events[event].push(callback);
    },
    /**
     * Add one-time-use event
     * @param event
     * @param callback
     * @since 1.0.4
     */
    addDisposableEvent: (event, callback) => {
        let key = "_DISPOSABLE_" + event;
        $amd.addEvent(key, callback);
    },
    /**
     * Single events only accepts one callback for their event handler
     * @param event
     * @param callback
     * @since 1.0.4
     */
    addSingleEvent: (event, callback) => {
        let key = "_SINGLE_" + event;
        $amd.addEvent(key, callback);
    },
    /**
     * These events will be removed after reloading page in any ways (lazy-load or har reload)
     * @param event
     * @param callback
     * @since 1.0.4
     */
    addPageEvent: (event, callback) => {
        let key = "_PAGE_" + event;
        $amd.addEvent(key, callback);
    },
    /**
     * Unbind event
     * @param event
     */
    unbindEvent: event => {
        if(typeof $amd.events[event] !== "undefined") $amd.events[event] = [];
    },
    /**
     * Unbind every event that starts with {prefix}
     * @param prefix
     */
    unbindEvents: prefix => {
        for(let [e, events] of Object.entries($amd.events)) {
            if(e.startsWith(prefix)) {
                $amd.events[e] = [];
                delete $amd.events[e];
            }
        }
    },
    /**
     * Run existing events
     * @param event
     * @param arg
     * @returns {null|boolean|*}
     */
    doEvent: (event, arg = null) => {
        let type = typeof $amd.events[event];
        let disposal_key = "_DISPOSABLE_" + event;
        let disposable_type = typeof $amd.events[disposal_key];
        if(disposable_type === "function"){
            let v = $amd.events[disposal_key]();
            delete $amd.events[disposal_key];
            return v;
        }
        else if(disposable_type === "object"){
            let out = {};
            $.each($amd.events[disposal_key], (i, v) => {
                if(typeof v === "function") {
                    let d = v(arg);
                    out = Object.assign(out, d);
                }
                $amd.events[disposal_key][i] = null;
                delete $amd.events[disposal_key][i];
            });
            return out;
        }
        if(typeof $amd.events["_SINGLE_" + event] !== "undefined")
            return $amd.doEvent("_SINGLE_" + event, arg);
        if(typeof $amd.events["_PAGE_" + event] !== "undefined")
            return $amd.doEvent("_PAGE_" + event, arg);
        if(type === "undefined")
            return null;
        else if(type === "function")
            return $amd.events[event]();
        else if(type === "object") {
            let out = {};
            $.each($amd.events[event], (i, v) => {
                if(typeof v === "function") {
                    let d = v(arg);
                    out = Object.assign(out, d);
                }
            });
            return out;
        }
        return null;
    },
    /**
     * Do events with their name prefix
     * @param prefix
     * @param arg
     * @returns {null}
     */
    doEventsPrefix: (prefix, arg = null) => {
        let out = null;
        $.each($amd.events, (i, v) => {
            if(i.startsWith(prefix)) {
                if(out === null)
                    out = {};
                if(typeof v === "function") {
                    let d = v(arg);
                    out = Object.assign(out, d);
                }
            }
        });
        return out;
    },
    /**
     * Remove event
     * @param event
     */
    cleanEvent: (event = "*") => {
        if(["*", "all"].includes(event))
            $amd.events = {};
        else if(typeof $amd.events[event] !== "undefined")
            $amd.events[event] = null;
    },
    /**
     * Replace "value" attribute value with DOM value for better security
     * @param $el
     */
    protectInput: $el => {
        let val = $el.val();
        $el.removeAttr("value");
        $el.val(val);
    },
    /**
     * Set inputs default value
     * @param def
     */
    applyInputsDefault: def => {
        for(let [key, value] of Object.entries(def)) {
            let $input = $(`input[type="hidden"][name="${key}"]`);
            if($input.length > 0){
                $input.val(value);
                continue;
            }
            $(`input[type="radio"][name="${key}"][value="${value}"]`).prop("checked", true);
            $(`input[type="checkbox"][name="${key}"][value="${value}"]`).prop("checked", true);
            $(`input[type="text"][name="${key}"]`).val(value);
            $(`input[type="number"][name="${key}"]`).val(value);
            $(`select[name="${key}"]`).val(value);
            $(`textarea[name="${key}"]`).val(value);
        }
    },
    /**
     * Check if current device is in mobile view
     * @returns {boolean}
     */
    isMobile: () => window.innerWidth <= 993,
    /**
     * Convert object to HTML attribute
     * <br>e.g: <code>{ "data-test": "hello_world" }</code> --> <code>data-test="hello_world"</code>
     * @param obj
     * @returns {string}
     */
    makeAttributes: obj => {
        let attr = [];
        for(let [key, value] of Object.entries(obj))
            attr.push(`${key}="${value}"`);
        return attr.join(" ");
    },
    /**
     * Set cookie
     * @param name
     * @param value
     * @param expire
     */
    setCookie: (name, value, expire) => {
        let d = new Date();
        d.setTime(d.getTime() + $amd.stringToTime(expire));
        let expires = "expires=" + d.toUTCString();
        sameCookie = amd_conf.isLocal ? "SameSite=None;Secure;" : "";
        document.cookie = `${name}=${value};${expires};${sameCookie}path=/`;
    },
    /**
     * Get cookie
     * @param name
     * @returns {string}
     */
    getCookie: name => {
        let n = name + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while(c.charAt(0) === ' ')
                c = c.substring(1);
            if(c.indexOf(n) === 0)
                return c.substring(n.length, c.length);
        }
        return "";
    },
    /**
     * Convert string to time
     * <br>e.g:
     * <br>"1 day" => 86,400
     * <br>"3 days" => 259,200
     * <br>"2 months" => 5,260,000
     * @param str
     * @returns {number}
     */
    stringToTime: str => {
        if(typeof str === "number") str = str + " days";
        str = str.trim();
        var splits = str.split("+");
        var time = 0;
        var toTime = (str) => {
            let s = str.split(" ");
            let n = s[0], v = s[1];
            let factor = 0;
            if(v === "month" || v === "months") factor = 30 * 24 * 60 * 60 * 1000;
            else if(v === "week" || v === "weeks") factor = 7 * 24 * 60 * 60 * 1000;
            else if(v === "day" || v === "days") factor = 24 * 60 * 60 * 1000;
            else if(v === "hour" || v === "hours") factor = 60 * 60 * 1000;
            else if(v === "minute" || v === "minutes") factor = 60 * 1000;
            else if(v === "second" || v === "seconds") factor = 1000;
            return parseInt(n) * factor;
        }
        $.each(splits, (i, v) => {
            v = v.trim();
            if(v.regex(/^\d*\s(month|week|day|hour|minute|second)(s?)$/)) time += toTime(v);
        })
        return time;
    },
    /**
     * Get time
     * @returns {number}
     */
    getTime: () => parseInt(new Date().getTime() / 1000),
    /**
     * Convert URL query to array
     * @param query
     * @returns {{}}
     */
    queryToArray: (query = null) => {
        if(query === null || query.length <= 0) query = location.search;
        if(!query.startsWith("?")) query = "?" + query;
        let params = new URLSearchParams(query);
        let items = params.toString().split("&");
        let out = {};
        if(items.length > 0) {
            for(let i = 0; i < items.length; i++) {
                let item = items[i];
                let exp = item.split("=");
                out[exp[0]] = exp[1] || "";
            }
        }
        return out;
    },
    /**
     * Convert array to URL query
     * @param arr
     * @returns {string}
     */
    arrayToQuery: (arr = []) => {
        if(typeof arr !== "object") return "";
        let out = "";
        if(Object.entries(arr).length > 0) {
            for(let [key, value] of Object.entries(arr)) out += key + (value.length > 0 ? "=" + value : "") + "&";
            out = out.replace(/^&/, "");
            out = out.replace(/&$/, "");
            if(out.length > 0)
                out = "?" + out;
        }
        return out;
    },
    /**
     * Download file from URL
     * @param url
     * @param filename
     */
    download: (url, filename = "") => {
        let a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
    },
    /**
     * @param {string} filename
     * @param {Object} data
     * @param {string} type
     */
    saveAs: (filename, data, type="text/plain") => {
        const blob = new Blob([data], {type});
        if(window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveBlob(blob, filename);
        }
        else{
            const elem = window.document.createElement('a');
            elem.href = window.URL.createObjectURL(blob);
            elem.download = filename;
            document.body.appendChild(elem);
            elem.click();
            document.body.removeChild(elem);
        }
    },
    escapeHtml: html => new Option(html).innerHTML,
    escapeAttr: html => {
        html = $amd.escapeHtml(html);
        html = html.replaceAll("'", "&#39;");
        return html.replaceAll('"', "&quot;");
    },
    mergeUrlQuery: (url, query, toRemove = [], onlyQuery = true) => {
        let params = new URLSearchParams(url.split("?")[1] || "");
        let _params = new URLSearchParams(query.indexOf("?") > -1 ? (query.split("?")[1] || "") : query);
        for(var [key, value] of _params.entries())
            params.set(key, value);
        if(typeof toRemove === "string") toRemove = toRemove.split(",");
        if(typeof toRemove === "object" && toRemove.length > 0) {
            for(let i = 0; i < toRemove.length; i++) {
                let r = toRemove[i] || null;
                if(r && params.has(r)) params.delete(r);
            }
        }
        return onlyQuery ? "?" + params.toString() : url.split("?")[0] + "?" + params.toString();
    },
    mergeUrlQueryOnly: (url, url2, only = []) => {
        let _p = new URLSearchParams(location.search);
        let _p2 = new URLSearchParams();
        for(let i = 0; i < only.length; i++) {
            let k = only[i];
            if(_p.has(k)) _p2.set(k, _p.get(k));
        }
        return $amd.mergeUrlQuery(url.split("?")[0] + "?" + _p2.toString(), url2);
    },
    /**
     * Merge current URL parameters with custom query
     * @param query
     * @param toRemove
     */
    mergeCurrentQuery: (query, toRemove = []) => $amd.mergeUrlQuery(location.href, query, toRemove),
    /**
     * Open URL with query without changing current parameters
     * @param query
     */
    openQuery: query => location.href = $amd.mergeCurrentQuery(query),
    /**
     * Check if string is match with pattern.
     * @param str
     * Input string
     * @param pattern
     * Regex or pattern. Patterns are keywords around % character like %email% or %zipcode%
     * @returns {boolean}
     * Whether string matches pattern/regex or not
     */
    validate: (str, pattern) => {
        if(typeof str !== "string")
            str = str.toString();
        var regex = pattern;
        if(pattern.startsWith("%") && pattern.endsWith("%")) {
            switch(pattern.trimChar("%")) {
                case "phone":
                    regex = "^0[0-9]{10,}$";
                    break;
                case "email":
                    regex = "^[a-zA-Z0-9.!#$%&'*+\\/\\=\\?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\\.[a-zA-Z0-9-]{2,})*$";
                    break;
                case "username":
                    regex = "^[a-zA-Z0-9_.]{3,}$";
                    break;
                case "password":
                    regex = ".{8,}";
                    break;
                case "zipcode":
                    regex = "^[0-9]{5,}$";
                    break;
                default:
                    return false;
            }
        }
        return str.regex(regex);
    },
    /**
     * Search for a keyword in an array elements
     * @param array
     * @param keyword
     * @returns {*}
     */
    searchArray: (array, keyword) => {
        keyword = keyword.toString().toLowerCase();
        return array.filter(o => Object.keys(o).some(k => o[k].toString().toLowerCase().indexOf(keyword) !== -1));
    },
    /**
     * Clone object and get cloned value
     * @param obj
     */
    clone: obj => Object.create(obj),
    /**
     * Convert seconds number to time clock. e.g: 70 seconds => "01:10"
     * @param seconds
     * Seconds number
     * @param sp
     * Clock separator
     * @returns {string}
     */
    secondsToClock: (seconds, sp = ":") => {
        if(seconds > 0) {
            let ss = seconds % 60;
            let mm = parseInt(seconds / 60 % 60);
            let hh = parseInt(seconds / 3600);
            if(hh > 0)
                return `${hh.toString().addZero()}:${mm.toString().addZero()}:${ss.toString().addZero()}`;
            else
                return `${mm.toString().addZero()}:${ss.toString().addZero()}`;
        }
        return "00:00";
    },
    /**
     * Get CSS variable of document or custom element
     * @param property_name
     * @param element
     * @returns {string}
     * @since 1.0.5
     */
    getCssVariable: (property_name, element=null) => {
        if(element === null)
            element = document.body;
        return getComputedStyle(element).getPropertyValue(property_name);
    },
    /**
     * Convert RGB string (e.g: "128, 23, 67") to array (e.g: [128, 23, 67])
     * @param rgbString
     * @returns []
     */
    rgbToArray: rgbString => {
        let exp = rgbString.split(",");
        let out = [];
        for(let i = 0; i < exp.length; i++){
            let a = exp[i] || "";
            if(a){
                let n = parseInt(a);
                if(!isNaN(n))
                    out.push(n);
            }
        }
        return out;
    },
    /**
     * English digits to Persian
     * @param s
     * @return string
     */
    e2p: s => s.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]),
    /**
     * English digits to Arabic
     * @param s
     * @return string
     */
    e2a: s => s.replace(/\d/g, d => '٠١٢٣٤٥٦٧٨٩'[d]),
    /**
     * Persian digits to English
     * @param s
     * @return string
     */
    p2e: s => s.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)),
    /**
     * Arabic digits to English
     * @param s
     * @return string
     */
    a2e: s => s.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)),
    /**
     * Get CSS variable
     * @param var_name
     * @param element
     * @returns {string}
     */
    getCssVar: (var_name, element=null) => {
        if(element === null)
            element = document.body;
        return getComputedStyle(element).getPropertyValue(var_name);
    },
    /**
     * Generate QR code
     * @param config {{width: number}|{height: number}|{data: string|any}|{margin: number}}
     * @param qr_color {string|null}
     * @param qr_bg {string|null}
     * @return {QRCodeStyling|null}
     */
    generateQR: (config, qr_color=null, qr_bg=null) => {
        const color = qr_color ? (qr_color.startsWith("--") ? $amd.getCssVar(qr_color) : qr_color) : $amd.getCssVar("--amd-title-color");
        const bg = qr_bg ? (qr_bg.startsWith("--") ? $amd.getCssVar(qr_bg) : qr_bg) : $amd.getCssVar("--amd-wrapper-bg");
        if(typeof QRCodeStyling === "undefined")
            return null;
        return new QRCodeStyling(Object.assign({
            "width": 300,
            "height": 300,
            "data": "",
            "margin": 0,
            "qrOptions": {"typeNumber": "0", "mode": "Byte", "errorCorrectionLevel": "Q"},
            "imageOptions": {"hideBackgroundDots": true, "imageSize": 0.4, "margin": 0},
            "dotsOptions": {"type": "rounded", "color": color},
            "backgroundOptions": {"color": bg},
            "dotsOptionsHelper": {
                "colorType": {"single": true, "gradient": false},
                "gradient": {"linear": true, "radial": false, "color1": "#6a1a4c", "color2": "#6a1a4c", "rotation": "0"}
            },
            "cornersSquareOptions": {"type": "extra-rounded", "color": color},
            "cornersSquareOptionsHelper": {
                "colorType": {"single": true, "gradient": false},
                "gradient": {"linear": true, "radial": false, "color1": "#000000", "color2": "#000000", "rotation": "0"}
            },
            "cornersDotOptions": {"type": "dot", "color": color},
            "cornersDotOptionsHelper": {
                "colorType": {"single": true, "gradient": false},
                "gradient": {"linear": true, "radial": false, "color1": "#000000", "color2": "#000000", "rotation": "0"}
            },
            "backgroundOptionsHelper": {
                "colorType": {"single": true, "gradient": false},
                "gradient": {
                    "linear": true,
                    "radial": false,
                    "color1": color,
                    "color2": bg,
                    "rotation": "0"
                }
            }
        }, config))
    },
    /**
     * Parse markdown content
     * @param content {string}
     * @return {string|*}
     */
    markDown: content => {
        if(typeof marked !== "undefined" && typeof marked.parse === "function")
            return marked.parse($(`<div>${content}</div>`).text());
        return content;
    },
    markDownElements: () => {
        if(typeof $ !== "undefined"){
            $(".amd-markdown-section, .adp-markdown-section").each(function(){
                $(this).html($amd.markDown($(this).text()));
            });
        }
    },
    byteTo: (bytes, to=null, base=1024) => {
        let power = 1;
        const units = ["B", "KB", "MB", "GB", "TB"];
        if(to === null){
            for(let i = units.length-1; i >= 0; i--){
                if(bytes >= Math.pow(base, i)){
                    power = i;
                    break;
                }
            }
        }
        if(typeof to === "string"){
            const index = units.indexOf(to.toUpperCase());
            if(index >= 0)
                power = index;
        }
        return [bytes / Math.pow(base, power), units[power] || ""];
    }
}
/* End of amd.js */

/* tooltip.js */
var HBTooltip = (function() {

    function HBTooltip(c) {

        var _this = this;

        var conf = Object.assign({
            mode: "dashboard_default",
            tooltipHeight: 30,
            fadeInTimeout: 250,
            fadeOutTimeout: 250,
            displayInterval: 300,
            hideInterval: 500
        }, c);

        var $tooltip = null;

        this.getTooltip = () => {
            if(!$tooltip)
                $tooltip = $("#amd-tooltip");
            if($tooltip.length <= 0) {
                $tooltip = $(`<div id="amd-tooltip" class="amd-tooltip"></div>`);
                $tooltip.css("display", "none");
                $("body").append($tooltip);
            }
            return $tooltip;
        }

        this.makeRect = r => {
            let tooltipRect = $tooltip.get(0).getBoundingClientRect();
            let left, top;
            let width = r.width, height = r.height;
            left = r.left - (tooltipRect.width / 2 - width / 2);
            top = r.top + height + 5;
            if(top + conf.tooltipHeight + 5 > window.innerHeight)
                top = r.top - (conf.tooltipHeight + 5);
            if(left - 5 < 0)
                left = 22;
            else if(left + tooltipRect.width + 5 > window.innerWidth)
                left = window.innerWidth - tooltipRect.width - 22;
            return {
                left: left,
                top: top,
                width: width,
                height: height,
            }
        }

        this.init = () => {
            let mode = conf.mode;
            if(mode === "dashboard_default")
                _this.initDashboardMode();
            else if(mode === "system_default")
                _this.initSystemMode();
        }

        this.initDashboardMode = () => {
            _this.getTooltip();
            let showing, hiding;
            $(document).on("mouseover", "[data-tooltip]", function(e) {
                if($amd.isMobile()) return;
                clearTimeout(hiding);
            });
            $("[data-tooltip]").each(function() {
                let $el = $(this);
                let text = $el.attr("data-tooltip");
                if(text) {
                    let handleTooltip = () => {
                        let isShowing = $tooltip.is(":visible");
                        let _text = $tooltip.html();
                        $tooltip.html(text);
                        if(!isShowing) $tooltip.fadeIn(0);
                        let rect = _this.makeRect($el.get(0).getBoundingClientRect());
                        if(!isShowing) $tooltip.fadeOut(0);
                        $tooltip.css("top", rect.top + "px");
                        $tooltip.css("left", rect.left + "px");
                        $tooltip.fadeIn(conf.fadeInTimeout);
                    }
                    $el.on("mouseover", function(e) {
                        if($amd.isMobile()) return;
                        clearTimeout(hiding);
                        showing = setTimeout(() => handleTooltip(), conf.displayInterval);
                    });
                    $el.on("mouseout", function() {
                        clearTimeout(showing);
                        hiding = setTimeout(() => {
                            $tooltip.fadeOut(conf.fadeOutTimeout);
                            setTimeout(() => $tooltip.html(""), conf.fadeOutTimeout + 100);
                        }, conf.hideInterval);
                    });
                }
            });
        }

        this.initSystemMode = () => {
            $("[data-tooltip]").each(function() {
                let $el = $(this);
                $el.attr("title", $el.attr("data-tooltip"));
            });
        }

    }

    return HBTooltip;

}());
/* End of tooltip.js */

/* network.js */
class AMDNetwork {
    constructor(c = {}) {
        this.data = Object.assign({}, c);
        this.options = {};
        this.request = null;
        this.on = {
            progress: () => null,
            start: () => null,
            success: () => null,
            failed: () => null,
            error: () => null,
            end: () => null
        }
        this.files = {};
    }

    setAction(action) {
        this.data["action"] = action;
        return this;
    }

    set(data) {
        this.data = data;
        return this;
    }

    put(key, value) {
        this.data[key] = value;
        return this;
    }

    putFile(filename, file) {
        this.files[filename] = file;
    }

    post(xhrF = null) {
        if(typeof send_ajax !== "function")
            return false;
        let _this = this;
        _this.on.start();
        this.request = send_ajax(_this.data, resp => {
            _this.clean();
            _this.on.end(resp, false);
            if(resp.success)
                _this.on.success(resp.data);
            else
                _this.on.failed(resp.data);
        }, (xhr, options, error) => {
            _this.clean();
            _this.on.end({xhr, options, error}, true);
            _this.on.error(xhr, options, error);
        }, xhrF);
        return this.request;
    }

    postTo(url, xhrF = null) {
        let _this = this;
        _this.on.start();
        let send = (data, doOnSuccess, doOnError) => {
            if(xhrF === null) xhrF = () => new window.XMLHttpRequest();
            var data_set = {
                xhr: xhrF,
                url: url,
                type: 'POST',
                dataType: 'JSON',
                data: data,
                success: doOnSuccess,
                error: doOnError
            }
            return $.ajax(data_set);
        }
        return send(_this.data, resp => {
            _this.clean();
            _this.on.end(resp, false);
            if(resp.success)
                _this.on.success(resp.data);
            else
                _this.on.failed(resp.data);
        }, (xhr, options, error) => {
            _this.clean();
            _this.on.end({xhr, options, error}, true);
            _this.on.error(xhr, options, error);
        });
    }

    abort() {
        if(this.request && typeof this.request.abort === "function") {
            this.request.abort();
            return true;
        }
        return false;
    }

    upload(key = "default") {
        let _this = this;
        if(!Object.entries(this.files).length)
            return false;
        let form = new FormData();
        for(let [name, f] of Object.entries(this.files))
            form.append(name, f);
        let xhrF = new window.XMLHttpRequest();
        xhrF.upload.addEventListener("progress", function(e) {
            if(e.lengthComputable)
                _this.on.progress((e.loaded / e.total) * 100);
        });
        this.on.start();
        $.ajax({
            xhr: () => xhrF,
            url: $amd.mergeUrlQuery(amd_conf.api_url, "?_accept_upload=" + key, [], false),
            dataType: "JSON",
            cache: false,
            contentType: false,
            processData: false,
            data: form,
            type: "post",
            success: resp => {
                _this.clean();
                _this.on.end(resp, false);
                if(resp.success)
                    _this.on.success(resp.data);
                else
                    _this.on.failed(resp.data);
            },
            error: (xhr, options, error) => {
                _this.clean();
                _this.on.end({xhr, options, error}, true);
                _this.on.error(xhr, options, error);
            }
        });
        return xhrF;
    }

    clean() {
        this.files = {};
        if(this.getAction()) {
            let action = this.data.action;
            this.data = {action};
        }
        else {
            this.data = {};
        }
        return this;
    }

    cleanEvents() {
        this.on = {
            start: () => null,
            success: () => null,
            failed: () => null,
            error: () => null,
            end: () => null
        }
        return this;
    }

    cleanAll() {
        this.data = {};
        this.files = {};
        return this;
    }

    getAction() {
        if(typeof this.data.action !== "undefined")
            return this.data.action;
        return null;
    }

    getData() {
        return this.data;
    }

    cleanOptions() {
        this.options = {};
        return this;
    }

    setOption(key, value) {
        this.options[key] = value;
        return this;
    }

    getOption(key) {
        return this.options[key] || null;
    }

    getSaver(c = {}) {
        let saver = new AMDNetwork(c);
        saver.clean();
        saver.set({
            "save_options": this.options
        });
        return saver;
    }

    static post(data, doOnSuccess, doOnError, xhrF = null) {
        return send_ajax(data, doOnSuccess, doOnError, xhrF);
    }
}

/* End of network.js */

/* AMDForm.js */
var AMDForm = (function() {

    function AMDForm(formID, c = {}) {

        var _this = this, conf;
        var $form, $logo, $log;
        var $submit, $dismiss;
        var _form = "", _fields = {}, _defaults = {};
        var disposed = false;

        const single_input_types = ["text", "number", "password", "hidden", "email", "color", "date", "range", "datetime-local", "month", "week", "search", "tel", "time"];

        conf = Object.assign({
            form_id: formID,
            logo: null,
            log_id: "",
            disposable: true,
            autoInit: true
        }, c);

        if(conf.form_id)
            $form = $("#" + conf.form_id);
        if(conf.log_id) {
            $log = $("#" + conf.log_id);
            $log.fadeOut(0);
        }
        $logo = $form.find(".--logo");

        this.on = (ev, callback) => $amd.addEvent(`_form_${_form}_${ev}`, callback)
        this.fire = (ev, v = null) => $amd.doEvent(`_form_${_form}_${ev}`, v);

        this.getInputValue = $el => {
            let localName = $el.get(0).localName || "";
            let value = null;
            if(localName === "input") {
                let type = $el.attr("type") || "text";
                if(single_input_types.includes(type)) {
                    value = $el.val();
                }
                else if(type === "radio") {
                    let n = $el.hasAttr("name", true);
                    if(n) {
                        let $ch = $(`input[type="radio"][name="${n}"]:checked`);
                        value = $ch.hasAttr("value", true, value);
                    }
                }
                else if(type === "checkbox") {
                    let n = $el.hasAttr("name", true);
                    if(n) {
                        let $ch = $(`input[type="checkbox"][name="${n}"]`);
                        if($ch.length === 1) {
                            value = $el.is(":checked");
                        }
                        else {
                            let v = "";
                            $ch.each(function() {
                                v += $(this).is(":checked") ? $(this).hasAttr("value", true, "") + "," : "";
                                v = v.trimChar(",");
                            });
                            value = v;
                        }
                    }
                    else {
                        value = $el.is(":checked");
                    }
                }
            }
            else if(localName === "select" || localName === "textarea") {
                value = $el.val();
            }
            return typeof value === "string" ? value.trim() : value;
        }

        this.setFieldValue = (fieldID, value) => {
            if(disposed) return;
            let $field = _this.$getField(fieldID);
            let localName = $field.get(0).localName || "";
            if(localName === "input") {
                let type = $field.attr("type") || "text";
                if(single_input_types.includes(type)) {
                    $field.val(value);
                }
                else if(type === "radio") {
                    let n = $field.hasAttr("name", true);
                    if(n) {
                        let $ch = $(`input[type="radio"][name="${n}"]:checked`);
                        $ch.prop("checked", Boolean(value));
                    }
                }
                else if(type === "checkbox") {
                    let n = $field.hasAttr("name", true);
                    if(n) {
                        let $ch = $(`input[type="checkbox"][name="${n}"]`);
                        $ch.prop("checked", Boolean(value));
                    }
                }
            }
            else if(localName === "select" || localName === "textarea") {
                $field.val(value);
            }
        }

        this.getFields = () => _fields;

        this.getFieldsData = (_id = null) => {
            let fields = {};
            for(let [id, $el] of Object.entries(_this.getFields())) {
                if(_id && _id !== id)
                    continue;
                let el = $el.get(0);
                let localName = el.localName;
                let type = localName;
                if(localName === "input")
                    type = $el.attr("type") || "text";
                let value = _this.getInputValue($el);
                let pattern = $el.hasAttr("data-pattern", true, null);
                fields[id] = {
                    id: id,
                    type: type,
                    localName: localName,
                    element: $el,
                    value: typeof value === "string" ? value.trim() : value,
                    pattern: pattern
                };
            }
            return fields;
        }

        this.getFieldsEntries = () => {
            let data = {};
            for(let [id, d] of Object.entries(_this.getFieldsData())) {
                let v = d.value || null;
                if(v !== null) data[id] = v;
            }
            return data;
        }

        this.getObjectedFields = () => {
            let data = {};
            for(let [id, d] of Object.entries(_this.getFieldsData()))
                data[id] = typeof d.value === "undefined" ? null : d.value;
            return data;
        }

        this.getField = id => {
            let fields = this.getFieldsData(id);
            if(Object.keys(fields).length <= 0)
                return null;
            return fields[id] || null;
        };

        this.$getField = id => {
            let field = _this.getField(id);
            if(!field || typeof field.id === "undefined") return $("");
            return $(`[data-field="${field.id}"]`);
        };

        this.disable = () => {
            if(disposed) return;
            for(let [id, $el] of Object.entries(this.getFields())) {
                $el.parent().addClass("waiting");
                let _id = $el.hasAttr("id", true);
                if(_id) $(`[for="${_id}"]`).addClass("waiting");
            }
            $form.find("a, button, .form-field, [data-submit], [data-dismiss]").addClass("waiting");
        }

        this.enable = () => {
            if(disposed) return;
            for(let [id, $el] of Object.entries(this.getFields())) {
                $el.parent().removeClass("waiting");
                let _id = $el.hasAttr("id", true);
                if(_id) $(`[for="${_id}"]`).removeClass("waiting");
            }
            $form.find("a, button, .form-field, [data-submit], [data-dismiss]").removeClass("waiting");
        }

        this.getType = () => _form;

        this.setLogo = url => {
            if(disposed) return;
            if(url) {
                $logo.find("img").attr("src", url);
                $logo.fadeIn(0);
            }
            else {
                $logo.find("img").attr("src", "");
                $logo.fadeOut(0);
            }
        }

        this.validateInput = (el, mark = true) => {
            let $el = $(el);
            if($el.hasAttr("data-ignore"))
                return 0;
            let invalid = 0;
            let pattern = $el.hasAttr("data-pattern", true, null);
            let isRequired = $el.hasAttr("required");
            let match = $el.hasAttr("data-match", true);
            let minlength = $el.hasAttr("minlength", true);
            let maxlength = $el.hasAttr("maxlength", true);
            let val = _this.getInputValue($el);
            if(isRequired && val.length <= 0) invalid = 1;
            if(match) {
                let $match = $(`[data-field="${match}"]`);
                if($match.length > 0) {
                    if($match.val() !== val)
                        invalid = 2;
                }
            }
            if(pattern.length > 0) {
                if(!$amd.validate(val, pattern))
                    invalid = 3;
                else
                    invalid = 0;
            }
            if(minlength && val.length < minlength) invalid = 4;
            if(maxlength && val.length > maxlength) invalid = 5;
            if(mark) {
                $el.setInvalid(invalid > 0);
                if(invalid)
                    $el.addClass("--clear-validate-state");
                else
                    $el.removeClass("--clear-validate-state");
            }
            return invalid;
        }

        this.validate = (mark = true) => {
            if(disposed) return;
            let valid = true;
            for(let [id, $el] of Object.entries(_fields)) {
                let v = _this.validateInput($el, mark);
                if(v > 0) valid = false;
                _this.fire((valid ? "" : "in") + "valid", _this.getField(id));
                _this.fire((valid ? "" : "in") + "valid_code", {
                    field: _this.getField(id),
                    code: v
                });
            }
            return valid;
        }

        this.clearValidationState = () => {
            for(let [key, $field] of Object.entries(this.getFields())) $field.setInvalid(false);
        }

        this.submit = (e = null) => {
            if(disposed) return true;
            if(e === null) {
                $submit.click();
            }
            else {
                _this.fire("before_submit", e);
                let allowed = true;
                if(typeof e.isDefaultPrevented !== "undefined")
                    allowed = !e.isDefaultPrevented();
                else if(typeof e.defaultPrevented !== "undefined")
                    allowed = !e.defaultPrevented;
                if(!allowed) {
                    e.preventDefault();
                    _this.fire("interrupt_submit", e);
                    return false;
                }
                $submit.blur();
                _this.fire("submit", "");
            }
        }

        this.resetDefaults = () => {
            if(disposed) return;
            for(let [id, $el] of Object.entries(_this.getFields())) _defaults[id] = _this.getField(id);
        }

        this.dismiss = (e = null) => {
            if(disposed) return;
            if(e === null) $dismiss.click();
            else {
                _this.fire("dismiss_form", e);
                let allowed = true;
                if(typeof e.isDefaultPrevented !== "undefined")
                    allowed = !e.isDefaultPrevented();
                else if(typeof e.defaultPrevented !== "undefined")
                    allowed = !e.defaultPrevented;
                if(!allowed) {
                    e.preventDefault();
                    _this.fire("interrupt_dismiss", e);
                    return false;
                }
                for(let [id, data] of Object.entries(_defaults)) {
                    let value = typeof data.value !== "undefined" ? data.value : null;
                    if(value !== null) _this.setFieldValue(id, value);
                }
                $dismiss.blur();
                _this.fire("dismiss");
            }
        }

        this.log = (text, color = "text") => {
            if(!$log.length)
                return;
            $log.fadeOut(0);
            $log.html(text);
            if(!text) return;
            let c = $log.hasClass("_bg_") ? "bg" : "color";
            $log.removeClass(`${c}-red ${c}-green ${c}-blue ${c}-orange ${c}-white ${c}-black ${c}-primary ${c}-low ${c}-title ${c}-text`);
            $log.addClass(`${c}-${color}`);
            $log.fadeIn();
        }

        this.isDisposed = () => disposed;

        this.dispose = () => {
            disposed = true;
            $amd.unbindEvents(`_form_${_form}`);
        }

        this.reload_fields = () => {
            $form.find(`[data-field]`).each(function() {
                let $el = $(this), el = $el.get(0);
                if($el.hasAttr("data-ignore"))
                    return;
                let id = $el.attr("data-field");
                _fields[id] = $el;
                const digit_translation = $el.hasAttr("data-translate-digits", true, "").toString().toLowerCase();
                const translate_persian = ["all", "*", "persian", "fa", "fa_IR"].includes(digit_translation);
                const translate_arabic = ["all", "*", "arabic", "ar"].includes(digit_translation);
                let next = $el.hasAttr("data-next", true);
                let allowed_keys = $el.hasAttr("data-keys", true, "");
                if(next) {
                    $el.on("keyup", e => {
                        if(e.key === "Enter") {
                            if(next === "submit") {
                                _this.submit();
                            }
                            else if(next.indexOf(":") === -1) {
                                if(next.indexOf("|") >= 0) {
                                    let exp = next.split("|");
                                    for(let i = 0; i <= exp.length; i++) {
                                        if(exp[i].length > 0) {
                                            if(exp[i] === "submit") {
                                                _this.submit();
                                                break;
                                            }
                                            else {
                                                let $n = $(`[data-field="${exp[i]}"]`);
                                                if($n.length > 0) {
                                                    $n.focus();
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                else {
                                    $(`[data-field="${next}"]`).focus();
                                }
                            }
                            else {
                                let exp = next.split(":");
                                let action = exp[0] || "";
                                let el = exp[1] || "";
                                if(action === "click" && el) {
                                    $el.blur();
                                    $(el).click();
                                }
                            }
                        }
                    });
                }
                $el.on("keydown", function(e) {
                    if(allowed_keys === "\\0") {
                        e.preventDefault();
                        return false;
                    }
                    let key = e.key || "";
                    if(translate_persian) key = $amd.p2e(key);
                    if(translate_arabic) key = $amd.a2e(key);
                    let isSpecial = amd_conf.forms.special_keys.includes(key);
                    if(!isSpecial && allowed_keys && !key.regex(allowed_keys)){
                        e.preventDefault();
                        return false;
                    }
                    digit_translation ? $el.translateDigits(translate_persian, translate_arabic, true) : "";
                });
                digit_translation ? $el.translateDigits(translate_persian, translate_arabic) : "";
            });
        }

        this.reload_events = () => {
            $form.find(".ht-magic-input").each(function() {
                let $el = $(this);
                let $keys = $el.find(".--keys");
                let $input = $el.find("input").first();
                let allowed_keys = $el.hasAttr("data-keys", true, "");
                let length = $el.hasAttr("data-length", true, "");
                let currentValue = $input.val();
                if(length) $keys.html('<span></span>'.repeat(length));
                var recheck = () => {
                    let v = currentValue, lastValue = $input.val();
                    $input.val(v);
                    if(lastValue !== v) $input.trigger("change");
                    $keys.find("span").html("").removeAttr("class");
                    if(v) {
                        let exp = v.split("");
                        for(let i = 0; i < exp.length; i++) {
                            let c = exp[i];
                            let $span = $keys.find(`span:nth-child(${i + 1})`);
                            $span.html(c);
                            $span.attr("class", "text");
                            if(i === v.length - 1 || v.length === 0) $span.addClass("cursor")
                        }
                        $el.addClass("text");
                    }
                    else {
                        $el.removeClass("text");
                        $keys.find("span").first().attr("class", "cursor");
                    }
                };
                recheck();
                $input.on("focusin", () => $el.addClass("focus"));
                $input.on("focusout", () => $el.removeClass("focus"));
                $input.on("keydown", function(e) {
                    e.preventDefault();
                    let key = e.key || "";
                    let isSpecial = amd_conf.forms.special_keys.includes(key);
                    if(currentValue.length >= length && !isSpecial) return true;
                    if(!isSpecial && allowed_keys && !key.regex(allowed_keys)) return true;
                    if(key.length === 1)
                        currentValue += key;
                    else if(key === "Backspace")
                        currentValue = currentValue.substring(0, currentValue.length - 1);
                    recheck();
                });
                $el.one("click", () => $input.focus());
            });
            $form.find(".ht-magic-select").each(function() {
                let $el = $(this);
                let $input = $el.find(".--input"), $value = $el.find(".--value");
                let $options = $el.find(".--options"), $search = $el.find(".--search");
                $search.fadeOut(0);
                let dataMap = [], codes = {};
                $options.find("span").each(function() {
                    let $_el = $(this);
                    let _v = $_el.hasAttr("data-value", true);
                    if(_v) {
                        let _k = $_el.hasAttr("data-keyword", true, ""), _h = $_el.html();
                        dataMap.push({
                            html: _h,
                            value: _v,
                            keyword: _k,
                        });
                        codes[_v] = _h;
                    }
                });
                let closeSearch = () => $search.fadeOut(150);
                let reloadSearch = () => {
                    let v = $input.val();
                    let results = dataMap;
                    if(v) results = $amd.searchArray(dataMap, v);
                    $search.fadeIn(150);
                    let html = "";
                    for(let i = 0; i < results.length; i++) {
                        let r = results[i];
                        html += `<span data-ms-pick="${r.value}">${r.html}</span>`;
                    }
                    if(html.length <= 0) $search.html(`<span class="exclude">${_t("no_results")}</span>`);
                    else $search.html(`<span class="exclude">${_t("results")}:</span>${html}`);
                };
                $(document).on("click", "[data-ms-pick]", function() {
                    let $e = $(this), $cl = $e.closest(".ht-magic-select");
                    if($el.get(0) !== $cl.get(0)) return;
                    let _v = $e.hasAttr("data-ms-pick", true);
                    if(_v) {
                        $value.html(_v);
                        $input.val($e.html());
                        $input.attr("data-value", _v);
                        $input.trigger("change");
                        $input.blur();
                    }
                });
                $input.on("focus keyup", () => reloadSearch());
                $input.on("focusout", () => closeSearch());
                $input.on("change", () => {
                    let v = $input.val();
                    let $e = $el.find(`[data-value="${v}"]`);
                    if($e.length > 0) {
                        let cc = codes[v] || "";
                        if(cc) {
                            $value.html(v);
                            $input.attr("data-value", v);
                            $input.val(cc);
                        }
                    }
                });
            });
        }

        this.init = () => {
            if(!$form.length || disposed)
                return;
            _form = $form.hasAttr("data-form", true, _form);

            $submit = $form.find(`[data-submit="${_form}"]`);
            $dismiss = $form.find(`[data-dismiss="${_form}"]`);

            // Initialize action events
            _this.on("before_submit", (e) => {
                if(!_this.validate())
                    e.preventDefault();
            });

            // Initialize DOM events
            _this.reload_fields();

            _this.resetDefaults();

            _this.reload_events();

            $submit.on("click", e => _this.submit(e));
            $dismiss.on("click", e => _this.dismiss(e));

            // Initialize forms
            this.setLogo(conf.logo);
            this.fire("init");

            if(typeof dashboard !== "undefined" && conf.disposable) {
                dashboard.addLazyEvent("start", () => _this.dispose());
            }
        }

        if(conf.autoInit)
            this.init();

        // Initialize events
        $form.find(".-pt").fadeOut(0);
        $form.find("input[type='password']").parent().find(".--show-password").fadeIn();
        $form.find("input[type='text']").parent().find(".--hide-password").fadeIn();
        let $hide_pass = $form.find(".--hide-password"), $show_pass = $form.find(".--show-password");
        $hide_pass.on("click", function(e) {
            e.preventDefault();
            let $hide = $(this);
            let $p = $hide.parent(), $in = $p.find("input").first();
            if($in.length <= 0) return;
            let $show = $p.find(".--show-password");
            $hide.fadeOut(0);
            $show.fadeIn(0);
            $in.attr("type", "password");
        });
        $show_pass.on("click", function(e) {
            e.preventDefault();
            let $show = $(this);
            let $p = $show.parent(), $in = $p.find("input").first();
            if($in.length <= 0) return;
            let $hide = $p.find(".--hide-password");
            $hide.fadeIn(0);
            $show.fadeOut(0);
            $in.attr("type", "text");
            $in.focus();
            $in.on("focusout", () => $hide.click());
        });

    }

    return AMDForm;
}());
/* End of AMDForm.js */

$(document).on("keydown change input", ".clear-validate-state", function() {
    $(this).setInvalid(false);
});
$(document).on("keydown change input", ".--clear-validate-state", function() {
    $(this).removeClass("--clear-validate-state");
    $(this).setInvalid(false);
});
$(document).on("focus", "[data-field]:not(.not-focus), .focus-select", function() {
    $(this).one("mouseup", function() {
        $(this).select();
        return false;
    }).select();
});