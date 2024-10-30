<?php

$register_enabled = amd_can_users_register();
$phone_field = amd_get_site_option( "phone_field" ) == "true";
$phone_field_required = amd_get_site_option( "phone_field_required" ) == "true";
$single_phone = amd_get_site_option( "single_phone" ) == "true";

$login_after_registration = amd_get_site_option( "login_after_registration", "true" ) == "true";
$lastname_field = amd_get_site_option( "lastname_field" ) == "true";
$username_field = amd_get_site_option( "username_field", "true" ) == "true";
$password_conf_field = amd_get_site_option( "password_conf_field" ) == "true";
$allow_only_persian_names = amd_get_site_option( "allow_only_persian_names" ) == "true";
$username_ag_priority = amd_get_site_option( "username_ag_priority", "random,phone,email" );
$_username_ag_priority = explode( ",", $username_ag_priority );
$username_ag_random_order = array_search( "random", $_username_ag_priority );
$username_ag_phone_order = array_search( "phone", $_username_ag_priority );
$username_ag_email_order = array_search( "email", $_username_ag_priority );

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
    <?php amd_dump_admin_card_keywords( ["register", "حساب کاربری", "user account"] ); ?>
    <h3 class="--title"><?php esc_html_e( "Register", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_registration_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_registration_items" );
            ?>
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
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_registration_items" );
            ?>
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
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_registration_content" );
        ?>
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
    <?php amd_dump_admin_card_keywords( ["form", "فرم", "کد ملی", "موبایل", "phone"] ); ?>
    <h3 class="--title"><?php _ex( "Fields", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_registration_fields_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_registration_fields_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="ask-lastname">
				        <?php esc_html_e( "Lastname", "material-dashboard" ); ?>
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
                    <p class="color-primary text-justify"><?php echo esc_html_x( "If you enable this item users last name will be taken separately, otherwise only one field will be shown for user full-name.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="only-persian-names">
				        <?php esc_html_e( "Only allow Persian characters for name", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="allow_only_persian_names" value="true"
                               id="only-persian-names" <?php echo $allow_only_persian_names ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="password-conf-field">
                        <?php esc_html_e( "Password confirmation", "material-dashboard" ); ?>
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
            <div class="-item">
                <div class="-sub-item">
                    <label for="username-field">
				        <?php esc_html_e( "Username", "material-dashboard" ); ?>
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
                    <p class="color-primary text-justify"><?php echo esc_html_x( "If you don't check this item, the username field will not displayed to users and a username will be generated for them automatically.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_registration_fields_items" );
            ?>
        </div>
        <div class="amd-admin-card _hide_on_username_enabled_">
            <h4 class="color-primary"><?php echo esc_html_x( "Username auto-generate priority", "Admin", "material-dashboard" ); ?></h4>
            <span class="tiny-text color-low"><?php echo esc_html_x( "You can change username auto-generate priority to generate a username for new users automatically. Some fields may be unavailable like phone number, if they are it uses the next priority to generate a new username.", "Admin", "material-dashboard" ); ?></span>
            <div class="amd-order-box" id="username-ag-priorities">
                <div class="--item" data-box="random" data-order="<?php echo esc_attr( $username_ag_random_order === false ? '99' : $username_ag_random_order ); ?>">
                    <div class="--icon -down"><?php _amd_icon( "chev_down" ); ?></div>
                    <div class="--icon -up"><?php _amd_icon( "chev_up" ); ?></div>
                    <span class="--text"><?php esc_html_e( "Random generate", "material-dashboard" ); ?></span>
                </div>
                <div class="--item" data-box="email" data-order="<?php echo esc_attr( $username_ag_email_order === false ? '99' : $username_ag_email_order ); ?>">
                    <div class="--icon -down"><?php _amd_icon( "chev_down" ); ?></div>
                    <div class="--icon -up"><?php _amd_icon( "chev_up" ); ?></div>
                    <span class="--text"><?php esc_html_e( "Email", "material-dashboard" ); ?></span>
                </div>
                <div class="--item" data-box="phone" data-order="<?php echo esc_attr( $username_ag_phone_order === false ? '99' : $username_ag_phone_order ); ?>">
                    <div class="--icon -down"><?php _amd_icon( "chev_down" ); ?></div>
                    <div class="--icon -up"><?php _amd_icon( "chev_up" ); ?></div>
                    <span class="--text"><?php esc_html_e( "Phone number", "material-dashboard" ); ?></span>
                </div>
            </div>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_registration_fields_content" );
        ?>
    </div>
</div>

<!-- Phone number -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php esc_html_e( "Phone", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_registration_phone_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_registration_phone_items" );
            ?>
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
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_registration_phone_dependencies_items" );
            ?>
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
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_registration_phone_dependencies_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_registration_phone_content" );
        ?>
    </div>
</div>

<script>
    (function(){
        $("#username-field").change(function(){
            $("._hide_on_username_enabled_")[$(this).is(":checked") ? "fadeOut" : "fadeIn"]();
        });
        const getUsernameAgPriorities = () => {
            let data = [];
            $("#username-ag-priorities").find(".--item").each(function(){
                const $e = $(this);
                const name = $e.hasAttr("data-box", true);
                if(name) data.push(name);
            });
            return data;
        }
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
                username_ag_priority: getUsernameAgPriorities().join(","),
                allow_only_persian_names: $('input[name="allow_only_persian_names"]').is(":checked") ? "true" : "false",
            }
        });
    }());
</script>

<!-- Country code -->
<div class="amd-admin-card --setting-card show_on_phone_field">
    <h3 class="--title"><?php esc_html_e( "Country codes", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_registration_phone_country_code_content" );
        ?>
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
        <h3 class="color-primary"><?php esc_html_e( "Custom", "material-dashboard" ); ?></h3>
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-name" class="_locale_item">
                        <span><?php esc_html_e( "Country name", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-name" class="amd-admin-input" placeholder="">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-code" class="_locale_item">
                        <span><?php esc_html_e( "Country code", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-code" class="amd-admin-input"
                           placeholder="<?php esc_attr_e( "IR, US, FR, CA, etc.", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-dc" class="_locale_item">
                        <span><?php esc_html_e( "Dialing code", "material-dashboard" ); ?></span>
                    </label>
                </div>
                <div class="-sub-item">
                    <input type="text" id="cc-dc" class="amd-admin-input"
                           placeholder="<?php esc_html_e( "98, 1, etc.", "material-dashboard" ); ?>">
                </div>
            </div>
            <div class="-item">
                <div class="-sub-item">
                    <label for="cc-format" class="_locale_item">
                        <span>
                            <?php esc_html_e( "Number format", "material-dashboard" ); ?>
                            <a href="javascript:void(0)" class="_show_number_format_guide_">(<?php esc_html_e( "More info", "material-dashboard" ); ?>)</a>
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
                    <button class="amd-admin-button --sm" id="custom-cc-add"><?php esc_html_e( "Add", "material-dashboard" ); ?></button>
                </div>
                <div class="-sub-item">
                    <button class="amd-admin-button --sm --primary --text"
                            id="custom-cc-clear"><?php esc_html_e( "Clear", "material-dashboard" ); ?></button>
                </div>
            </div>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_registration_phone_country_code_content" );
        ?>
    </div>
</div>
<script>
    $("._show_number_format_guide_").click(function(){
        $amd.alert(`<?php esc_html_e( "Number format", "material-dashboard" ); ?>`, `<?php amd_dump_number_format_guide(); ?>`);
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
