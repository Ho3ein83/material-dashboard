<?php

$_locales = amd_get_site_option( "locales" );
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "false" );

$currentLocale = get_locale();

$locales = apply_filters( "amd_locales", [] );

?>
<!-- Translate -->
<div class="force-center" style="max-width:100%">
	<?php amd_alert_box( array(
		"text" => esc_html_x( "Select none of below items to disable multi-language in dashboard and use site default or select one of them to use as dashboard language", "Admin", "material-dashboard" ) . ".",
		"type" => "primary",
		"icon" => "info",
        "size" => "lg",
        "align" => "justify"
	) ); ?>
</div>
<div class="amd-admin-card --setting-card">
    <?php amd_dump_admin_card_keywords( ["locale"] ); ?>
	<h3 class="--title" style="display:inline-block"><?php echo esc_html_x( "Languages", "Admin", "material-dashboard" ); ?></h3>
    <span><?php echo amd_doc_url( "localization", true ); ?></span>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_languages_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_languages_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="locale-all" class="_locale_item">
                        <span><?php echo esc_html_x( "Select all", "Admin", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" id="locale-all" role="switch">
                        <span></span>
                    </label>
                </div>
            </div>
            <?php foreach( $locales as $locale => $data ): ?>
            <?php
            $name = $data["name"] ?? "-";
            $region = $data["region"] ?? "";
            $region = !empty( $region ) ? "($region)" : "";
            $flag = $data["flag"] ?? "";
            ?>
                <div class="-item">
                    <div class="-sub-item">
                        <label for="<?php echo esc_attr( "locale-$locale" ); ?>" class="_locale_item">
	                        <?php if( !empty( $flag ) ): ?>
                                <img src="<?php echo esc_url( $flag ); ?>" alt="" class="_locale_flag_">
	                        <?php endif; ?>
                            <span><?php echo esc_html( $name ); ?> <span class="_locale_region_"><?php echo esc_html( $region ); ?></span></span>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" id="<?php echo esc_attr( "locale-$locale" ); ?>" role="switch" class="_locales_" name="<?php echo $locale; ?>">
                            <span></span>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_languages_items" );
            ?>
        </div>
        <h3 class="color-primary"><?php echo esc_html_x( "Translate", "Admin", "material-dashboard" ); ?></h3>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_translate_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="locale-show-region">
                        <span><?php echo esc_html_x( "Show region", "Admin", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" id="locale-show-region" role="switch">
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="locale-show-flag">
                        <span><?php echo esc_html_x( "Show flag", "Admin", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" id="locale-show-flag" role="switch">
                        <span></span>
                    </label>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_translate_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_languages_content" );
        ?>
    </div>
</div>
<script>
    (function(){
        let $locales = $("._locales_"), $all = $("#locale-all");
        let $region = $("#locale-show-region"), $flag = $("#locale-show-flag");
        let getLocales = () => {
            let l = [];
            $locales.each(function(){
                if($(this).is(":checked"))
                    l.push($(this).attr("name"));
            });
            return l;
        }
        function validate(){
            // Check/Uncheck locales
            if($("._locales_:not(:checked)").length <= 0) $all.prop("checked", true);
            else $all.prop("checked", false);

            // Validate regions span
            let $el = $("._locale_region_");
            if($region.is(":checked")) $el.fadeIn();
            else $el.fadeOut();

            // Validate flags span
            $el = $("._locale_flag_");
            if($flag.is(":checked")) $el.fadeIn();
            else $el.fadeOut();
        }
        let setLocales = l => {
            let _l = l.split(",");
            $locales.prop("checked", false);
            for(let i = 0; i < _l.length; i++){
                if(_l[i].length > 0)
                    $(`._locales_[name="${_l[i]}"]`).prop("checked", true);
            }
            validate();
        }
        setLocales(`<?php echo $_locales; ?>`);
        let setOptions = (region, flag) => {
            if(region === "true") $region.prop("checked", true);
            if(flag === "true") $flag.prop("checked", true);
            validate();
        }
        setOptions(`<?php echo $show_regions; ?>`, `<?php echo $show_flags; ?>`);
        $all.on("change", function(){
            $locales.prop("checked", $(this).is(":checked"));
        });
        $(document).on("change", "._locales_", () => validate());
        $region.on("change", () => validate());
        $flag.on("change", () => validate());
        $amd.addEvent("on_settings_saved", () => {
            return {
                "locales": getLocales().join(","),
                "translate_show_regions": $region.is(":checked") ? "true" : "false",
                "translate_show_flags": $flag.is(":checked") ? "true" : "false"
            };
        });
    }());
</script>