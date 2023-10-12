<?php

$regions = amd_get_regions();
$cc_count = $regions["count"];
$first_cc = $regions["first"];
$format = $regions["format"];
$countries_cc_html = $regions["html"];
$phone_field_required = true;

?>
<?php ob_start(); ?>
<?php if( $cc_count > 1 ): ?>
    <div class="ht-magic-select">
        <label>
            <input type="text" class="--input" data-field="country_code" data-next="phone_number"
                   placeholder=""
				<?php echo $phone_field_required ? "required" : ""; ?>>
            <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
            <span class="--value" dir="auto"><?php _amd_icon( "phone" ) ?></span>
        </label>
        <div class="--options">
			<?php foreach( $regions["regions"] as $region ): ?>
                <span data-value="<?php echo esc_attr( $region['digit'] ?? '' ); ?>"
                      data-format="<?php echo esc_attr( $region['format'] ?? '' ); ?>"
                      data-keyword="<?php echo esc_attr( $region['name'] ?? '' ); ?>">
                                <?php echo esc_html( $region["name"] ?? "" ); ?></span>
			<?php endforeach; ?>
        </div>
        <div class="--search"></div>
    </div>
    <label class="ht-input --ltr">
        <input type="text" class="not-focus" data-field="phone_number" data-pattern="" data-keys="[+0-9]"
               data-next="submit"
               placeholder="" <?php echo $phone_field_required ? "required" : ""; ?>>
        <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
		<?php _amd_icon( "phone" ); ?>
    </label>
<?php else: ?>
	<?php if( $first_cc == "98" AND apply_filters( "amd_use_phone_simple_digit", false ) ): ?>
        <label class="ht-input --ltr">
            <input type="text" class="not-focus" data-field="phone_number" data-keys="[0-9]"
                   data-pattern="[0-9]" data-next="submit" placeholder=""
				<?php echo $phone_field_required ? "required" : ""; ?>>
            <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
			<?php _amd_icon( "phone" ); ?>
        </label>
	<?php else: ?>
        <div class="ht-magic-select" style="display:none">
            <label>
                <input type="text" class="--input" data-field="country_code" data-next="phone_number"
                       data-value="<?php echo esc_attr( $first_cc ); ?>" placeholder=""
					<?php echo $phone_field_required ? "required" : ""; ?>>
                <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
                <span class="--value" dir="auto"><?php echo esc_html( $first_cc ); ?></span>
            </label>
            <div class="--options">
                <span data-value="<?php echo esc_attr( $first_cc ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-keyword=""></span>
            </div>
            <div class="--search"></div>
        </div>
        <label class="ht-input --ltr">
            <input type="text" class="not-focus" data-field="phone_number" data-pattern="[0-9]{11}"
                   data-keys="[0-9]" data-next="submit"
                   placeholder="" <?php echo $phone_field_required ? "required" : ""; ?>>
            <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
			<?php _amd_icon( "phone" ); ?>
        </label>
	<?php endif; ?>
<?php endif; ?>
<?php $phone_change_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="plr-8">
    <button type="button" class="btn" data-submit="change-phone"><?php esc_html_e( "Change", "material-dashboard" ); ?></button>
    <button type="button" class="btn btn-text" data-dismiss="change-phone"><?php esc_html_e( "Dismiss", "material-dashboard" ); ?></button>
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
<script>
    (function(){
        let form = new AMDForm("change-phone-card");

        form.on("invalid_code", data => {
            let {field, code} = data;
            let id = field.id || "";
            if(id === "phone_number")
                dashboard.toast(_t("phone_incorrect"));
        });
        // TODO: continue phone register

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
        });
        $country_code.trigger("change");
    }());
</script>