<?php

$register_enabled = amd_can_users_register();
$phone_field = amd_get_site_option( "phone_field" ) == "true";
$phone_field_required = amd_get_site_option( "phone_field_required" ) == "true";
$single_phone = amd_get_site_option( "single_phone" ) == "true";

$login_after_registration = amd_get_site_option( "login_after_registration", "true" ) == "true";
$lastname_field = amd_get_site_option( "lastname_field" ) == "true";
$username_field = amd_get_site_option( "username_field", "true" ) == "true";
$password_conf_field = amd_get_site_option( "password_conf_field" ) == "true";

$country_codes = amd_get_site_option( "country_codes" );
$country_codes = amd_unescape_json( $country_codes );
$phoneRegions = apply_filters( "amd_country_codes", [] );

// Add saved custom regions
$selectedRegions = [];
foreach( $country_codes as $key => $value ){
	$selectedRegions[$key] = true;
    if( empty( $phoneRegions[$key] ) ) $phoneRegions[$key] = (array) $value;
}

?>
<!-- Register -->
<div class="amd-admin-card --setting-card" id="rg-card">
    <h3 class="--title"><?php esc_html_e( "Register", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="enable-register">
						<?php echo esc_html_x( "Anyone can register", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="enable_registration" value="true"
                               id="enable-register" <?php echo $register_enabled ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "This item should be enabled to show you the rest of options and let users to sign-up.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
        </div>
        <?php if( $register_enabled ): ?>
            <div class="__option_grid">
                <div class="-item">
                    <div class="-sub-item">
                        <label for="login-after-registration">
					        <?php esc_html_e( "Sign in after registration", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" role="switch" name="login_after_registration" value="true"
                                   id="login-after-registration" <?php echo $login_after_registration ? 'checked' : ''; ?>>
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    (function(){
        let $cb = $("#enable-register"), $card = $("#rg-card");
        $cb.on("change", function(){
            let $el = $(this);
            let checked = $el.is(":checked");
            $amd.alert(_t("notice"), _t("unsaved_changes_notice"), {
                icon: "warning",
                cancelButton: _t("continue"),
                confirmButton: _t("close"),
                onCancel: () => {
                    $el.blur();
                    $card.cardLoader();
                    network.clean();
                    network.put("enable_registration", checked);
                    network.on.end = (resp, error) => {
                        $card.cardLoader(false);
                        if(!error) {
                            location.reload();
                            $el.setWaiting();
                        }
                        else {
                            $amd.alert(_t("settings"), _t("error"));
                            $el.prop("checked", !checked);
                        }
                    };
                    network.post();
                },
                onConfirm: () => {
                    $el.prop("checked", !checked);
                }
            });
        });
    }())
</script>

<?php
if( !$register_enabled ):
    return;
endif;
?>

<!-- Fields -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php _ex( "Fields", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="ask-lastname">
				        <?php _e( "Lastname", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="lastname_field" value="true"
                               id="ask-lastname" <?php echo $lastname_field ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php _ex( "If you enable this item users last name will be taken separately, otherwise only one field will be shown for user full-name.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="username-field">
				        <?php _e( "Username", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="username_field" value="true"
                               id="username-field" <?php echo $username_field ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php _ex( "If you don't check this item, the username field will not displayed to users and a username will be generated for them automatically.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="password-conf-field">
				        <?php _e( "Password confirmation", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="password_conf_field" value="true"
                               id="password-conf-field" <?php echo $password_conf_field ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phone number -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php _e( "Phone", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="phone-field">
						<?php _ex( "Phone field", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="phone_field" value="true"
                               id="phone-field" <?php echo $phone_field ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php _ex( "By enabling this option users can enter their phone number on registration form, you can see more options after you enable it.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
        </div>
        <div class="__option_grid show_on_phone_field">
            <div class="-item">
                <div class="-sub-item">
                    <label for="phone-field-required">
						<?php _ex( "This field is required", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="phone_field_required" value="true"
                               id="phone-field-required" <?php echo $phone_field_required ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php _ex( "By enabling this item this field will be required and users have to fill it out.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="single-phone">
						<?php _ex( "Unique phone number", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="single_phone" value="true"
                               id="single-phone" <?php echo $single_phone ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php _ex( "Enable it if you want to prevent users to register existing phone number", "Admin", "material-dashboard" ); ?>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        $amd.addEvent("on_settings_saved", () => {
            let $loginAfter = $('input[name="login_after_registration"]'), $lnField = $('input[name="lastname_field"]');
            let $unField = $('input[name="username_field"]'), $pcField = $('input[name="password_conf_field"]'),
                $spField = $('input[name="single_phone"]');
            return {
                phone_field: $('input[name="phone_field"]').is(":checked") ? "true" : "false",
                phone_field_required: $('input[name="phone_field_required"]').is(":checked") ? "true" : "false",
                login_after_registration: $loginAfter.is(":checked") ? "true" : "false",
                lastname_field: $lnField.is(":checked") ? "true" : "false",
                username_field: $unField.is(":checked") ? "true" : "false",
                password_conf_field: $pcField.is(":checked") ? "true" : "false",
                single_phone: $spField.is(":checked") ? "true" : "false",
            }
        });
    }());
</script>

<!-- Country code -->
<div class="amd-admin-card --setting-card show_on_phone_field">
    <h3 class="--title"><?php _e( "Country codes", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid" id="cc-items">
			<?php foreach( $phoneRegions as $cc => $data ): ?>
				<?php
				$name = $data["name"] ?? "";
				$flag = $data["flag"] ?? "";
				$code = $data["digit"] ?? "";
				$format = $data["format"] ?? "";
				$sl = $selectedRegions[$cc] ?? false;
				if( empty( $name ) OR empty( $code ) OR empty( $format ) )
					continue;
				?>
                <div class="-item" data-cc="<?php echo esc_attr( $cc ); ?>" data-digit="<?php echo esc_attr( $code ); ?>"
                     data-name="<?php echo esc_attr( $name ); ?>" data-format="<?php echo esc_attr( $format ); ?>">
                    <div class="-sub-item">
                        <label for="<?php echo esc_attr( "cc-$cc" ); ?>" class="_locale_item">
							<?php if( $flag ): ?>
                                <img src="<?php echo esc_url( $flag ); ?>" alt="" class="_locale_flag_">
							<?php endif; ?>
                            <span dir="auto"><?php echo esc_html( "+$code - $name" ); ?></span>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" role="switch" id="<?php echo esc_attr( "cc-$cc" ); ?>" name="country_code"
                                   value="<?php echo esc_attr( $cc ); ?>" <?php echo $sl ? 'checked' : ''; ?>>
                            <span></span>
                        </label>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
        <h3 class="color-primary"><?php _e( "Custom", "material-dashboard" ); ?></h3>
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-name" class="_locale_item">
                        <span><?php _e( "Country name", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-name" class="amd-admin-input" placeholder="">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-code" class="_locale_item">
                        <span><?php _e( "Country code", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-code" class="amd-admin-input"
                           placeholder="<?php _e( "IR, US, FR, CA, etc.", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-dc" class="_locale_item">
                        <span><?php _e( "Dialing code", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-dc" class="amd-admin-input"
                           placeholder="<?php _e( "98, 1, etc.", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-format" class="_locale_item">
                        <span>
                            <?php _e( "Number format", "material-dashboard" ); ?>
                            <a href="javascript:void(0)" class="_show_number_format_guide_">(<?php _e( "More info", "material-dashboard" ); ?>)</a>
                        </span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-format" maxlength="15" class="amd-admin-input" placeholder="">
                    <p class="color-low tiny-text-im" id="cc-format-preview" style="direction:ltr;text-align:left"></p>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <button class="amd-admin-button --sm" id="custom-cc-add"><?php _e( "Add", "material-dashboard" ); ?></button>
                </div>
                <div class="-sub-item">
                    <button class="amd-admin-button --sm --primary --text"
                            id="custom-cc-clear"><?php _e( "Clear", "material-dashboard" ); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("._show_number_format_guide_").click(function(){
        $amd.alert(`<?php _e( "Number format", "material-dashboard" ); ?>`, `<?php amd_dump_number_format_guide(); ?>`);
    });
    (function () {
        let $field = $("#phone-field");
        let $e = $(".show_on_phone_field");
        let check = (t = 300) => {
            if ($field.is(":checked")) $e.fadeIn(t);
            else $e.fadeOut(t);
        }
        check(0);
        $field.on("change", function () {
            check();
        });
    }());
    (function () {
        let isAnyChecked = () => $('[name="country_code"]:checked').length > 0;
        $(document).on("change", '[name="country_code"]', function () {
            let $el = $(this);
            if (!$el.is(":checked")) {
                if (!isAnyChecked()) {
                    $amd.alert(_t("country_codes"), _t("least_one"));
                    $el.prop("checked", true);
                }
            }
        });
        if (!isAnyChecked()) $('[name="country_code"]').first().prop("checked", true);
        let $list = $("#cc-items");
        let $name = $("#cc-name"), $code = $("#cc-code"), $digit = $("#cc-dc"), $format = $("#cc-format");
        let $add = $("#custom-cc-add"), $clear = $("#custom-cc-clear");
        let $fp = $("#cc-format-preview");
        let clear = () => {
            $name.val("");
            $code.val("");
            $digit.val("");
            $format.val("");
            $fp.html("");
        }
        let replaceFormat = str => {
            for (let i = 0; i < str.length; i++) {
                let index = i;
                if (index > 9) index = $amd.generate("1", "numbers");
                str = str.replace("X", index);
            }
            return str;
        }
        $clear.click(() => clear());
        $code.on("keyup", () => $code.val($code.val().toUpperCase()));
        $format.on("keyup", function () {
            let v = $(this).val().toUpperCase();
            if (!v.regex("^[-X]+$")) $fp.html("");
            else $fp.html(replaceFormat(v));
            $format.val($format.val().toUpperCase());
        });
        $add.click(function () {
            let name = $name.val(), code = $code.val(), digit = $digit.val(), format = $format.val().toUpperCase();
            let valid = true;
            $name.setInvalid(false);
            $code.setInvalid(false);
            $digit.setInvalid(false);
            $format.setInvalid(false);
            if (name.length <= 0) {
                $name.setInvalid();
                valid = false;
            }
            if (!code.regex("^[A-Z]{2}$")) {
                $code.setInvalid();
                valid = false;
            }
            if (!digit.regex("^[0-9]+$")) {
                $digit.setInvalid();
                valid = false;
            }
            if (!format.regex("^[-X]+$")) {
                $format.setInvalid();
                valid = false;
            }
            if (!valid)
                return false;
            let html = `<div class="-item" data-cc="${code}" data-digit="${digit}" data-name="${name}" data-format="${format}">
                    <div class="-sub-item">
                        <label for="cc-${code}" class="_locale_item">
                            <span dir="auto">+${digit} - ${name}</span>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="checkbox" role="switch" id="cc-${code}" name="country_code"
                                   value="${code}" checked>
                            <span></span>
                        </label>
                    </div>
                </div>`;
            if ($(`[data-cc="${code}"]`).length <= 0) {
                let $html = $(html);
                $html.css("display", "none");
                $list.append($html);
                $html.fadeIn();
                clear();
            } else {
                $amd.alert(_t("country_codes"), _t("cc_exists"));
            }
        });

        function getCountryCodes() {
            let data = {};
            $("[data-cc]").each(function () {
                let $el = $(this);
                let cc = $el.hasAttr("data-cc", true), digit = $el.hasAttr("data-digit", true);
                let name = $el.hasAttr("data-name", true), format = $el.hasAttr("data-format", true);
                if (!$(`input[name="country_code"][value="${cc}"]`).is(":checked"))
                    return;
                if (!cc || !digit || !name || !format)
                    return;
                data[cc] = {
                    name: name,
                    digit: digit,
                    format: format
                };
            });
            return data;
        }

        $amd.addEvent("on_settings_saved", () => {
            return {
                country_codes: JSON.stringify(getCountryCodes())
            }
        });

    }());
</script>
