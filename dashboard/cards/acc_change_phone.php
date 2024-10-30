<?php

/**
 * Force it to display phone edit fields even if phone field is disabled by settings
 * @since 1.0.4
 */
$force_display = apply_filters(  "amd_force_display_phone_edit", false );

/**
 * Allow users change phone number
 * @since 1.0.4
 */
$allow_change_phone_number = apply_filters( "amd_allow_change_phone_number", false );

/*if( !$allow_change_phone_number )
    return;*/

if( amd_get_site_option( "phone_field" ) != "true" AND !$force_display )
    return;

$thisuser = amd_get_current_user();
$is_phone_valid = amd_validate_phone_number( $thisuser->phone );

if( $is_phone_valid AND !$allow_change_phone_number )
    return;

$regions = amd_get_regions();
$cc_count = $regions["count"];
$first_cc = $regions["first"];
$format = $regions["format"];
$countries_cc_html = $regions["html"];
$phone_field_required = true;

?>
<?php ob_start(); ?>
<?php amd_phone_fields(); ?>
<?php $phone_change_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php if( $cc_count > 1 ): ?>
    <div class="_phone_field_holder_ ht-magic-select">
        <label style="height:50px">
            <input type="text" class="--input" data-field="country_code" data-next="phone_number"
                   placeholder=""<?php echo $phone_field_required ? "required" : ""; ?> style="height:40px;font-size:13px;padding-top:24px">
            <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
            <span class="--value" dir="auto"><?php _amd_icon( "phone" ) ?></span>
        </label>
        <div class="--options">
			<?php foreach( $regions["regions"] as $region ): ?>
                <span data-value="<?php echo esc_attr( $region['digit'] ?? '' ); ?>"
                      data-format="<?php echo esc_attr( $region['format'] ?? '' ); ?>"
                      data-keyword="<?php echo esc_attr( $region['name'] ?? '' ); ?>">
                                <?php echo apply_filters( "amd_phone_format_name", $region["name"] ?? "", $region["digit"] ?? "", $region["format"] ?? "" ); ?></span>
			<?php endforeach; ?>
        </div>
        <div class="--search"></div>
    </div>
    <label class="_phone_field_holder_ ht-input --ltr">
        <input type="text" class="not-focus" data-field="phone_number" data-pattern="" data-keys="[+0-9]"
               data-next="submit" value="<?php echo esc_attr( $thisuser->phone ); ?>"
               placeholder="" <?php echo $phone_field_required ? "required" : ""; ?>>
        <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
		<?php _amd_icon( "phone" ); ?>
    </label>
<?php else: ?>
	<?php if( $first_cc == "98" AND apply_filters( "amd_use_phone_simple_digit", false ) ): ?>
        <label class="_phone_field_holder_ ht-input --ltr">
            <input type="text" class="not-focus" data-field="phone_number" data-keys="[0-9]"
                   data-pattern="[0-9]" data-next="submit" placeholder=""
				<?php echo $phone_field_required ? "required" : ""; ?>>
            <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
			<?php _amd_icon( "phone" ); ?>
        </label>
	<?php else: ?>
        <div class="_phone_field_holder_ ht-magic-select" style="display:none">
            <label>
                <input type="text" class="--input" data-field="country_code" data-next="phone_number"
                       data-value="<?php echo esc_attr( $first_cc ); ?>" value="<?php echo esc_attr( $first_cc ); ?>" placeholder=""
					<?php echo $phone_field_required ? "required" : ""; ?>>
                <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
                <span class="--value" dir="auto"><?php echo esc_html( $first_cc ); ?></span>
            </label>
            <div class="--options">
                <span data-value="<?php echo esc_attr( $first_cc ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-keyword=""></span>
            </div>
            <div class="--search"></div>
        </div>
        <label class="_phone_field_holder_ ht-input --ltr">
            <input type="text" class="not-focus" data-field="phone_number" data-pattern="[0-9]{11}"
                   data-keys="[0-9]" data-next="submit"
                   placeholder="" <?php echo $phone_field_required ? "required" : ""; ?>>
            <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
			<?php _amd_icon( "phone" ); ?>
        </label>
	<?php endif; ?>
<?php endif; ?>
<?php $phone_change_content_ = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="plr-8">
    <button type="button" style="<?php echo $is_phone_valid ? '' : 'display:none'; ?>" class="btn" id="display-change-phone-buttons"><?php esc_html_e( "Change", "material-dashboard" ); ?></button>
    <button type="button" style="<?php echo $is_phone_valid ? 'display:none' : ''; ?>" class="btn" data-submit="change-phone"><?php esc_html_e( "Save", "material-dashboard" ); ?></button>
    <button type="button" style="<?php echo $is_phone_valid ? 'display:none' : ''; ?>" class="btn btn-text" data-dismiss="change-phone"><?php esc_html_e( "Dismiss", "material-dashboard" ); ?></button>
</div>
<?php $phone_change_footer = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
	"type" => "content_card",
	"title" => esc_html__( "Change phone number", "material-dashboard" ),
	"content" => $phone_change_content,
	"footer" => $phone_change_footer,
	"_id" => "change-phone-card",
    "_attrs" => 'data-form="change-phone"'
) ); ?>
<?php if( $is_phone_valid ): ?>
    <script>
        (function(){
            let $f1 = $('[data-submit="change-phone"]');
            let $f2 = $('[data-dismiss="change-phone"]');
            let $f3 = $("#display-change-phone-buttons");
            let $holder = $("._phone_field_holder_");
            $holder.find('[data-field="phone_number"]').val(`<?php echo esc_html( $thisuser->phone ); ?>`);
            $holder.setWaiting();
            $f3.click(function(){
                $f3.fadeOut(0);
                $f1.fadeIn();
                $f2.fadeIn();
                $holder.setWaiting(false);
            });
            $f2.click(function(){
                $f3.fadeIn();
                $f1.fadeOut(0);
                $f2.fadeOut(0);
                $holder.setWaiting();
            });
        }());
    </script>
<?php endif; ?>
<script>
    (function(){
        let form = new AMDForm("change-phone-card");
        let $card = $("#change-phone-card");

        form.on("invalid_code", data => {
            let {field, code} = data;
            let id = field.id || "";
            if(id === "phone_number")
                dashboard.toast(_t("phone_incorrect"));
        });
        form.on("submit", () => {
            let data = form.getFieldsData();
            let phone = data.phone_number.value;
            let _n = dashboard.getApiEngine();
            _n.clean();
            _n.put("change_phone", phone);
            _n.on.start = () => {
                $card.cardLoader();
            }
            _n.on.end = (resp, error) => {
                if(!error) {
                    $amd.toast(resp.data.msg);
                    if(resp.success){
                        dashboard.lazyReload();
                    }
                    else{
                        $card.cardLoader(false);
                    }
                }
                else {
                    $card.cardLoader(false);
                    $amd.toast(_t("error"));
                }
            }
            _n.post();
        });

        let $country_code = form.$getField("country_code");
        let $phone_number = form.$getField("phone_number");
        let country_codes = {};
        $country_code.parent().parent().find(".--options > span").each(function() {
            let cc = $(this).hasAttr("data-value", true);
            let format = $(this).hasAttr("data-format", true, "");
            if(cc) {
                country_codes[cc] = {
                    "$e": $(this),
                    "format": format.toUpperCase()
                };
            }
        });

        var getSelectedCC = () => {
            return $country_code.hasAttr("data-value", true, "");
        }
        $country_code.on("change", function() {
            let cc = getSelectedCC();
            if(cc) {
                $phone_number.blur();
                $phone_number.focus();
                $phone_number.val("+" + cc);
            }
        });
        var formatPhoneNumber = (number, format, clean = true) => {
            let cc = getSelectedCC();
            let num = number;
            num.replaceAll(" ", "");
            num = num.replaceAll("+" + cc, "");
            num = num.replaceAll("+", "");
            num = num.replace(cc, "");
            let out = format;
            for(let i = 0; i < num.length; i++) {
                let n = num[i] || "";
                out = out.replace("X", n);
            }
            if(clean) {
                out = out.replaceAll("X", "");
                out = out.replaceAll("-", " ");
            }
            return "+" + cc + " " + out;
        }
        let formatted = "";
        $phone_number.on("input", function(e) {
            let key = e.key;
            let $el = $(this);
            let v = $el.val();
            v = v.replaceAll("+", "");
            v = v.replaceAll(" ", "");
            if(v && !amd_conf.forms.isSpecialKey(key)) {
                if(typeof country_codes[v] !== "undefined") {
                    $phone_number.val("+" + v);
                    $country_code.val(v);
                    $country_code.trigger("change");
                }
                let _cc = getSelectedCC();
                let ff = typeof country_codes[_cc] !== "undefined" ? (country_codes[_cc].format || "") : "";
                if(ff) {
                    let sub = v.substring(_cc.length, v.length);
                    if(sub.startsWith("0")) {
                        do {
                            sub = sub.substring(1, v.length);
                        } while(sub.startsWith("0"));
                        v = sub;
                    }
                    formatted = formatPhoneNumber(v, ff);
                    $phone_number.val(formatted.trimChar(" "));
                }
            }
        });
        $country_code.on("change", function() {
            let cc = $(this).hasAttr("data-value", true, "");
            let _format = (country_codes[cc] || {format: ""}).format || "";
            if(_format) {
                let _f = _format;
                _f = _f.replaceAll("-", "\\s?");
                _f = _f.replaceAll("X", "[0-9]");
                $phone_number.attr("data-pattern", `^\\+${cc}\\s?${_f}$`);
            }
            let val = $country_code.val();
            $country_code.val(val.trimChar(" "));
        });
        $country_code.trigger("change");
    }());
</script>
<style>.ht-magic-select._phone_field_holder_ > label{height:32px}  .ht-magic-select._phone_field_holder_ input{height:40px;font-size:13px;padding-top:24px}</style>