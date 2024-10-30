<?php

amd_init_plugin();

amd_admin_head();

$locale = get_locale();

$search_hint_approved = amd_get_site_option( "search_hint_approved" ) == "true";

if( !$search_hint_approved AND isset( $_GET["accept"] ) ){
    $search_hint_approved = true;
    amd_set_site_option( "search_hint_approved", "true" );
}

?>
<?php if( !$search_hint_approved ): ?>
<div class="_amd_tracker_screens_ amd-tracker-consent-overlay"></div>
<div class="_amd_tracker_screens_ amd-tracker-consent text-center">
    <h1 class="font-title color-primary"><?php echo esc_html_x( "Search terms and policies", "Admin", "material-dashboard" ); ?></h1>
    <p><?php echo esc_html_x( "We are here to help you to save your resource and time using our applications and find what you are looking for without contacting us and waiting for answer.", "Admin", "material-dashboard" ); ?></p>
    <p><?php printf( esc_html__( "Here you can use our search tools to find instruction and related contents. This tool uses our own server to fetch data and handle them, so by using this tool you accept our %sPrivacy terms%s.", "material-dashboard" ), '<a href="' . esc_url( "https://amatris.ir/policy?lang=$locale" ) . '">', '</a>' ); ?></p>
    <div class="h-20"></div>
    <div class="--buttons">
        <a href="<?php echo esc_url( admin_url( "admin.php?page=amd-search&accept" ) ); ?>" class="amd-admin-button" data-change-ctrack="allow"><?php esc_html_e( "Accept", "material-dashboard" ); ?></a>
        <a href="<?php echo esc_url( admin_url( "admin.php?page=material-dashboard" ) ); ?>" class="amd-admin-button --primary --text" data-change-ctrack="deny"><?php esc_html_e( "Reject", "material-dashboard" ); ?></a>
    </div>
</div>
<!-- @formatter off -->
<style>.amd-tracker-consent{position:fixed;top:100px;left:calc(50% - 300px);width:600px;height:max-content;background:var(--amd-wrapper-fg);border-radius:16px;padding:16px;z-index:10;opacity:0}.amd-tracker-consent.--loaded{opacity:1;transition:opacity ease .3s}.amd-tracker-consent-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0);z-index:9;transition:all ease .3s}.amd-tracker-consent-overlay.--loaded{backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);background:rgba(0,0,0,.3);transition:background-color ease .3s}.amd-tracker-consent .--buttons{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;flex-direction:column}.amd-tracker-consent .--buttons>*{width:100%;height:50px;margin:4px auto;display:flex;align-items:center;box-sizing:border-box;justify-content:center}</style>
<script>$(window).on("load",function(){$(".amd-tracker-consent,.amd-tracker-consent-overlay").addClass("--loaded");});</script>
<!-- @formatter on -->
<?php return; ?>
<?php endif; ?>

<!--<div class="row">-->
<!--    <div class="col-lg-6 margin-auto">-->
<!--    </div>-->
<!--</div>-->
<div class="amd-card-columns">
    <div class="amd-admin-card --main --settings-card">
        <div class="h-10"></div>
        <div class="text-center">
            <h2>
                <?php esc_html_e( "Advanced search", "material-dashboard" ); ?>
                <span id="search-state"></span>
            </h2>
            <p class="small-text margin-0 color-primary"><?php esc_html_e( "Search anything you want with any keywords", "material-dashboard" ); ?></p>
            <p class="small-text margin-0 color-red"><?php esc_html_e( "Smart search is not available and you can only search in available documents.", "material-dashboard" ); ?></p>
            <div id="search-container">
                <div class="h-20"></div>
                <div class="amd-search-box">
                    <input type="text" class="amd-admin-input focus-select --input" id="search-input" placeholder="<?php esc_attr_e( "Write anything", "material-dashboard" ); ?>..." aria-label="">
                    <button type="button" class="amd-admin-button --button --sm --primary" id="search-enter"><?php esc_html_e( "Go", "material-dashboard" ); ?></button>
                </div>
                <div class="h-20"></div>
                <button type="button" class="amd-admin-button --sm --primary --text" id="update-database"><?php esc_html_e( "Update database", "material-dashboard" ); ?></button>
                <div class="h-20"></div>
                <div id="search-results"></div>
                <div class="h-20"></div>
            </div>
        </div>
        <div class="h-10"></div>
    </div>
</div>

<script src="<?php echo esc_url( AMD_MOD . "/fuse/fuse.js" ); ?>"></script>

<script>
    (function(){

        let _texts = {
            "fetching": `<?php esc_html_e( "Fetching data", "material-dashboard" ); ?>`,
            "open_in_new_tab": `<?php esc_html_e( "Open in new tab", "material-dashboard" ); ?>`,
        }

        let max_in_page = 50;

        let $container = $("#search-container"), $results = $("#search-results"),
            $state = $("#search-state");
        let $search_input = $("#search-input"), $search_button = $("#search-enter"),
            $update_db = $("#update-database");

        let _index = {}, _fuse = null;

        $container.fadeOut(0);

        $search_button.click(function(){
            let v = $search_input.val();
            $results.html("");
            if(v && _fuse){
                let results = _fuse.search("=" + v);
                if(Object.entries(results).length){
                    let counter = 0;
                    for ([i, v] of Object.entries(results)) {
                        if(counter >= max_in_page)
                            break;
                        let item = v.item || null;
                        if (item) {
                            let {id, url, title, description, thumbnail, translations} = item;
                            if (typeof amd_conf.locale !== "undefined" && amd_conf.locale) {
                                let texts = translations[amd_conf.locale] || null;
                                if (texts) {
                                    title = texts.title || title;
                                    description = texts.description || description;
                                }
                            }
                            let html = `<div class="--result" data-search-result="${id}">
                        ${thumbnail ? `<div class="-thumbnail"><img src="${thumbnail}" alt=""></div>` : ``}
                        <div class="-content">
                            <h3 class="-title">${title}</h3>
                            <p class="-text">${description}</p>
                            <a href="${url}" target="_blank" class="amd-admin-button --sm --primary --text">${_texts.open_in_new_tab}</a>
                        </div>
                    </div>`;
                            $results.append($(html));
                            counter++;
                        }
                    }
                    set_state(_n("n_results_found", counter), "blue");
                }
                else{
                    $results.html(`<h2 class="text-center w-full">${_t("no_results")}</h2>`);
                }
            }
            else{
                $results.html("");
            }
        });

        let throttle = null;

        $search_input.on("keyup", function(e){
            let {key} = e;
            if(key === "Enter") {
                clearInterval(throttle);
                $search_button.click();
            }
            if($(this).val().length <= 0) {
                clearInterval(throttle);
                $results.html("");
                set_state("");
                return;
            }
            throttle = setTimeout(() => $search_button.click(), 1500);
        });

        $search_input.on("keydown", function(e){
            clearTimeout(throttle);
        });

        let set_state = (text, color="", time=200) => {
            if(!text){
                $state.fadeOut(time);
                setTimeout(() => $state.html(""), time+100);
                return;
            }
            $state.html(`${text}`);
            if(["red", "blue", "green", "orange", "text"].includes(color))
                $state.removeClass("color-red color-blue color-green color-orange color-text").addClass(`color-${color}`);
            $state.fadeIn(time);
        }

        function start_indexing(index, onIndex=()=>null){
            _index = index;
            _fuse = new Fuse(index, {
                keys: ["keywords", "keywords_translate"]
            });
            $container.fadeIn();
            onIndex();
        }

        let n = new AMDNetwork();
        n.setAction(amd_conf.ajax.private);

        function fetch_search_index(use_cache=true, onIndex=()=>null){
            console.log("Cache: ", use_cache);
            $results.html("");
            n.clean();
            n.put("_ajax_target", "search_engine");
            n.put("fetch_index", "");
            n.put("use_cache", use_cache);
            n.on.start = () => {
                set_state(_texts.fetching + "...", "blue");
            }
            n.on.end = (resp, error) => {
                if(!error){
                    if(resp.success){
                        set_state("");
                        let {data} = resp.data;
                        let index = data.data || null;
                        if(index)
                            start_indexing(index, onIndex);
                        else
                            set_state(_t("failed"), "red");
                    }
                    else{
                        set_state(resp.data.msg, "red");
                    }
                }
                else{
                    set_state(_t("error"), "red");
                }
            }
            n.post();
        }
        fetch_search_index();

        $update_db.click(() => fetch_search_index(false, () => $search_button.click()));

    }());
</script>

<style>
    .amd-admin-card.--main {
        background: var(--amd-wrapper-bg);
        border: 1px solid rgba(var(--amd-text-color-rgb), .1);
        border-radius: 0;
    }

    .amd-search-box {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        width: 90%;
        max-width: 400px;
        background: red;
        height: 50px;
        margin: auto;
        overflow: hidden;
    }
    .amd-search-box > .--input {
        flex: 10;
        height: 100%;
        padding: 0 !important;
        padding-inline-start: 16px !important;
        margin: 0 !important;
        border-radius: 0 !important;
        background: #e9e9e9 !important;
        color: #414141 !important;
        border-color: transparent !important;
        outline: none !important;
    }
    .amd-search-box > .--button {
        flex: 1;
        height: 100%;
        aspect-ratio: 1;
        border-radius: 0;
        margin: 0;
    }

    #search-state {
        font-size: 15px;
    }

    #search-results,
    #search-results > .--result {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        flex-direction: row;
    }
    #search-results > .--result {
        flex-direction: column;
        align-items: start;
        justify-content: start;
        flex: 0 0 100%;
        max-width: 51%;
        padding: 0;
        margin: 16px auto;
        background: var(--amd-wrapper-fg);
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid rgba(var(--amd-text-color-rgb), .1);
    }
    #search-results > .--result > .-thumbnail {
        display: flex;
        width: 100%;
        aspect-ratio: 3;
        overflow: hidden;
    }
    #search-results > .--result > .-thumbnail > img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #search-results > .--result > .-content {
        box-sizing: border-box;
        display: flex;
        align-items: start;
        justify-content: space-between;
        flex-direction: column;
        text-align: start;
        width: 100%;
        height: 100%;
        padding: 16px;
    }
    #search-results > .--result > .-content .-title {
        margin: 0 0 8px;
        font-size: 18px;
    }
    #search-results > .--result > .-content .-text {
        margin: 8px 0;
        font-size: 14px;
    }
</style>