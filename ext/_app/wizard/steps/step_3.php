<?php

$_locales = amd_get_site_option( "locales" );
$show_regions = amd_get_site_option( "translate_show_regions", "false" );
$show_flags = amd_get_site_option( "translate_show_flags", "false" );

$currentLocale = get_locale();

$locales = apply_filters( "amd_locales", [] );

?><h1 class="--title"><?php _ex( "Languages", "Admin", "material-dashboard" ); ?></h1>

<div class="force-center" style="max-width:100%">
	<?php amd_alert_box( array(
		"text" => esc_html_x( "Select none of below items to disable multi-language in dashboard and use site default or select one of them to use as dashboard language", "Admin", "material-dashboard" ) . ".",
		"type" => "primary",
		"icon" => "info",
		"size" => "lg",
		"align" => "justify"
	) ); ?>
</div>

<div class="__option_grid" style="width:80%;margin:auto">
    <div class="-item">
        <div class="-sub-item">
            <label for="locale-all" class="_locale_item clickable">
                <span><?php _ex( "Select all", "Admin", "material-dashboard" ); ?></span>
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
                <label for="<?php echo esc_attr( "locale-$locale" ); ?>" class="_locale_item clickable">
					<?php if( !empty( $flag ) ): ?>
                        <img src="<?php echo esc_url( $flag ); ?>" alt="" class="_locale_flag_">
					<?php endif; ?>
                    <span><?php echo esc_html( $name ); ?>
                        <span class="_locale_region_"><?php echo esc_html( $region ); ?></span>
                    </span>
                </label>
            </div>
            <div class="-sub-item">
                <label class="hb-switch">
                    <input type="checkbox" id="<?php echo esc_attr( "locale-$locale" ); ?>" role="switch" class="_locales_" name="<?php echo esc_attr( $locale ); ?>">
                    <span></span>
                </label>
            </div>
        </div>
	<?php endforeach; ?>
</div>
<h3 class="color-primary"><?php echo esc_html_x( "Translate", "Admin", "material-dashboard" ); ?></h3>
<div class="__option_grid" style="width:80%;margin:auto">
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
</div>
<!-- @formatter off -->
<style>._locale_item{display:inline-flex;flex-wrap:nowrap;align-items:center;flex-direction:row-reverse}._locale_item>img{width:auto;height:20px;margin:0 6px}</style>
<!-- @formatter on -->
<script>
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
    setLocales(`<?php echo esc_js( $_locales ); ?>`);
    let setOptions = (region, flag) => {
        if(region === "true") $region.prop("checked", true);
        if(flag === "true") $flag.prop("checked", true);
        validate();
    }
    setOptions(`<?php echo $show_regions == "true" ? "true" : "false"; ?>`, `<?php echo $show_flags == "true" ? "true" : "false"; ?>`);
    $all.on("change", function(){
        $locales.prop("checked", $(this).is(":checked"));
    });
    $(document).on("change", "._locales_", () => validate());
    $region.on("change", () => validate());
    $flag.on("change", () => validate());

    function _step_3(done){
        network.clean();
        network.put("save_options", {
            locales: getLocales().join(","),
            translate_show_regions: $region.is(":checked") ? "true" : "false",
            translate_show_flags: $flag.is(":checked") ? "true" : "false"
        });
        network.on.end = (resp, error) => {
            if(!error){
                if(resp.success){done();}
                else{done(false);$amd.toast(resp.data.msg);}
            }
            else{
                done(false);
                $amd.toast(_t("error"));
            }
        }
        network.post();
    }
</script>