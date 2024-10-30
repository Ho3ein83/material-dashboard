const AMDProductPicker = (function(){

    function AMDProductPicker(c={}){

        let _configs = Object.assign({
            multiple: true,
            selection: [],
            id: $amd.generate(16)
        }, c);

        let _pid = _configs.id === null ? $amd.generate(16) : _configs.id, _picker, _overlay, _selection = _configs.selection;
        let $input, $btn_close, $btn_search, $btn_continue, $table, $table_items, $note;

        const _this = this;

        const templates = {
            picker: `<div class="amd-product-picker">
    <div class="--title flex-align">
        <button type="button" class="amd-admin-button _close_picker_ --red --text"><?php esc_html_e( "Close", "material-dashboard" ); ?></button>
        <button type="button" class="amd-admin-button no-mobile _use_selection_"><?php esc_html_e( "Use selected products", "material-dashboard" ); ?> (<span class="_products_counter"></span>)</button>
    </div>
    <div class="--content no-desktop text-center font-title size-3"><?php esc_html_e( "This feature is not available in mobile, please use desktop instead.", "material-dashboard" ); ?></div>
    <div class="--content no-mobile">
        <div class="text-center">
            <div class="_hide_on_search_result">
                <h3><?php echo esc_html_x( "Search products", "Admin", "material-dashboard" ); ?></h3>
                <input type="text" class="amd-admin-input _search_input_" placeholder="<?php esc_attr_e( "Keywords", "material-dashboard" ); ?>">
            </div>
            <p class="tiny-text color-low _note_"></p>
            <div class="h-10"></div>
            <button type="button" class="amd-admin-button _hide_on_search_result _search_button_"><?php esc_html_e( "Search", "material-dashboard" ); ?></button>
            <button type="button" class="amd-admin-button _show_on_search_result _continue_button_ --blue --text"><?php esc_html_e( "Continue", "material-dashboard" ); ?></button>
            <div class="h-50"></div>
            <table class="adp-admin-table _table_" style="width:100%;background:var(--amd-primary-x-low);border-radius:12px">
                <thead>
                <tr>
                    <th><?php esc_html_e( "Product ID", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Product name", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Actions", "material-dashboard" ); ?></th>
                </tr>
                </thead>
                <tbody class="_table_items_"></tbody>
            </table>
        </div>
    </div>
</div>`,
            overlay: `<div class="amd-product-picker-overlay"></div>`,
            select_btn: `<button type="button" class="amd-admin-button --sm {CLASS} ma-8" data-select-product="{ID}">{TEXT}</button>`
        };

        this.create_api_engine = () => {
            let n = new AMDNetwork();
            n.clean();
            n.setAction(amd_conf.ajax.api);
            return n;
        }

        this.perform_search = query => {
            return new Promise((resolve, reject) => {
                let n = _this.create_api_engine();
                n.put("search_product", query);
                n.on.end = (resp, error) => {
                    error ? reject(Object.assign(resp.xhr, {data:{msg: _t("error")}})) : (resp.success ? resolve(resp) : reject(resp));
                }
                n.postTo(amd_conf.api_url);
            });
        }

        this.display_products = (results, stop=true) => {
            let html = "";
            $table_items.html("");
            $table.fadeOut(0);
            $note.html("").fadeIn(0);
            if(Object.entries(results).length){
                for(let [post_id, data] of Object.entries(results)){
                    let thumbnail = data.thumbnail ?? false;
                    let thumbnail_img = thumbnail ? `<img src="${thumbnail}" alt="" class="amd-product-picker-small-thumbnail">` : "";
                    let btn = templates.select_btn.replaceAll("{ID}", post_id).replaceAll("{CLASS}", _this.is_selected(post_id) ? "--red" : "--blue").replaceAll("{TEXT}", _t(_this.is_selected(post_id) ? "delete" : "select"));
                    html += `<tr><td style="min-width:80px">${post_id}</td><td class="flex-align flex-center" style="justify-content:start;gap:8px;padding:10px">${thumbnail_img}<span>${data.title}</span></td><td>${btn}</td></tr>`;
                }
                $table_items.html(html);
                $table.fadeIn(0);
            }
            else{
                $note.html(`<?php echo esc_html_x( "No results found", "Search results", "material-dashboard" ); ?>`).fadeIn(0);
            }
            if(stop) {
                _picker.find("._show_on_search_result").fadeIn(0);
                _picker.find("._hide_on_search_result").fadeOut(0);
            }
            return this;
        }

        this.search = (query) => {
            let last_val = $input.val();
            if(last_val.length <= 0)
                return this;
            $input.val("");
            $input.blur();
            $input.setWaiting();
            $btn_search.waitHold(_t("wait_td"));
            const end = () => {
                $input.setWaiting(false);
                $btn_search.waitRelease();
            }
            _this.perform_search(query).then(resp => {
                end();
                this.display_products(resp.data.results);
            }).catch(error => {
                end();
                $input.val(last_val);
                $amd.toast(error.data.msg);
                console.error(error);
            });
            return this;
        }

        this.is_selected = id => _selection.indexOf(parseInt(id)) >= 0;

        this.toggle_select = (id, toggle=null) => {
            if(toggle === null)
                toggle = _this.is_selected(id) ? "deselect" : "select";
            toggle === "select" ? _selection.push(id) : _selection.removeValue(id);
            _selection = [...new Set(_selection)];
            $(`[data-select-product="${id}"]`).html(_t(toggle === "select" ? "delete" : "select")).removeClass("--blue --red").addClass(toggle === "select" ? "--red" : "--blue");
            _picker.find("._products_counter").html(_selection.length);
            return this;
        }

        this.continue = () => {
            $note.html(`<?php esc_html_e( "Enter the keywords, the smaller the keyword is, the smaller the result will be.", "material-dashboard" ); ?>`).fadeIn(0);
            $table_items.html("");
            $table.fadeOut(0);
            _picker.find("._show_on_search_result").fadeOut(0);
            _picker.find("._hide_on_search_result").fadeIn(0);
            if(_selection.length > 0){
                let n = _this.create_api_engine();
                n.put("get_posts", _selection.join(","));
                n.put("type", "product");
                n.on.start = () => {
                    $input.setWaiting();
                    $btn_search.waitHold(_t("wait_td"));
                }
                n.on.end = (resp, error) => {
                    $input.setWaiting(false);
                    $btn_search.waitRelease();
                    if(!error){
                        if(resp.success){
                            _this.display_products(resp.data.results, false);
                        }
                    }
                    else{
                        console.log(resp.xhr);
                    }
                }
                n.postTo(amd_conf.api_url);
            }
        }

        /**
         * @param {"open"|"close"|"pick"|"build"} event
         * @param {function} callback
         * @return {AMDProductPicker}
         */
        this.on = (event, callback) => {
            if(["open", "close", "pick", "build"].includes(event))
                $amd.addEvent(`amd_product_picker_${_pid}_${event}`, callback);
            return this;
        }

        /**
         * @param {"open"|"close"|"pick"|"build"} event
         * @param {object} args
         * @return {AMDProductPicker}
         */
        this.trigger = (event, args={}) => {
            $amd.doEvent(`amd_product_picker_${_pid}_${event}`, Object.assign({ picker: _this }, args));
            return this;
        }

        this.pick = () => {
            _this.trigger("pick", { selection: _selection });
            _this.close();
            return this;
        }

        this.init_events = () => {
            $btn_close.click(() => _this.close());
            $btn_search.click(() => _this.search($input.val()));
            $btn_continue.click(() => _this.continue());
            _picker.find("._use_selection_").click(() => _this.pick());
            $input.onKeyPress("Enter", () => $btn_search.click());
            _picker.on("click", "[data-select-product]", function(){
                let id = parseInt($(this).attr("data-select-product"));
                if(id) _this.toggle_select(id);
            });
            return this;
        }

        this.get_configs = () => _configs;

        this.get_selection = () => _selection;

        this.get_id = () => _pid;

        this.build = () => {
            if(!_pid) _pid = $amd.generate(16);
            _picker.attr("data-amd-product-picker", _pid);
            _picker.fadeOut(0);
            _overlay.fadeOut(0);
            $("body").append(_picker).append(_overlay);
            $input = _picker.find("._search_input_");
            $btn_close = _picker.find("._close_picker_");
            $btn_search = _picker.find("._search_button_");
            $btn_continue = _picker.find("._continue_button_");
            $table = _picker.find("._table_");
            $table_items = _picker.find("._table_items_");
            $note = _picker.find("._note_");
            $btn_continue.fadeOut(0);
            $table.fadeOut(0);
            $table_items.html("");
            _picker.find("._products_counter").html(_selection.length);
            _this.init_events();
            _this.trigger("build");
            return this;
        }

        this.open = () => {
            _picker.fadeIn(0);
            _overlay.fadeIn();
            _this.continue();
            _this.trigger("open");
            return this;
        }

        this.close = () => {
            _picker.fadeOut(100);
            _overlay.fadeOut(100);
            _this.trigger("close");
            return this;
        }

        this.fire = (c={}, open=true) => {
            let $pickers = $(".amd-product-picker");
            if($pickers.length) $pickers.remove();
            _configs = Object.assign(_configs, c);
            _selection = _configs.selection
            _pid = _configs.id || null;
            _picker = $(templates.picker);
            _overlay = $(templates.overlay);
            _this.build();
            if(open) _this.open();
            return this;
        }

    }

    return AMDProductPicker;

}());