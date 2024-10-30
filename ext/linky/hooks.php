<?php

add_action( "amd_registration_fields_after", function(){
    $referral = !empty( $_GET["referral"] ) ? $_GET["referral"] : "";
    $editable = empty( $referral );
    $pattern = apply_filters( "amd_ext_linky_referral_pattern", "(^$)|(^[0-9]{3}[a-zA-Z]{3}$)" );
    $keys = apply_filters( "amd_ext_linky_referral_allowed_keys", "[0-9a-zA-Z]" );
    $length = apply_filters( "amd_ext_linky_referral_allowed_keys", 6 );
    ?>
    <label class="ht-input" id="ext-field-referral">
        <input type="text" id="ext-linky-referral" value="<?php echo esc_attr( $referral ); ?>" placeholder="" required maxlength="<?php echo esc_attr( $length ); ?>" data-field="Custom:ext_link_referral" data-pattern="<?php echo esc_attr( $pattern ); ?>" data-keys="<?php echo esc_attr( $keys ); ?>">
        <span><?php echo esc_html_x( "Referral code", "linky", "material-dashboard" ); ?></span>
        <?php echo amd_icon( "person" ); ?>
    </label>
    <style>#ext-field-referral .indeterminate-progress-bar{position:absolute;top:0;left:0;width:100%}</style>
    <script>
        (function(){
            const $field = $("#ext-field-referral");
            const $i = $("#ext-linky-referral");
            const pattern = $i.attr("data-pattern");
            const _log = (icon="person") => {
                let color = icon === "person" ? "default" : (icon === "check" ? "green" : (["block", "sync"].includes(icon) ? "red" : "default"))
                $field.find("._icon_").replaceWith(_i(icon)).removeClass("color-red-im color-green-im color-blue-im color-default-im").addClass(`clickable color-${color}-im`);
                $field.find("._icon_").removeClass("color-red-im color-green-im color-blue-im color-default-im").addClass(`clickable color-${color}-im`);
            }
            const recheck_referral = () => {
                let n = dashboard.createNetwork();
                n.setAction(amd_conf.ajax.public);
                n.clean();
                n.put("_ajax_target", "ext_linky_public");
                n.put("check_referral", $i.val());
                n.on.start = () => {
                    $i.blur();
                    $i.setWaiting();
                    $field.cardLoader();
                }
                n.on.end = (resp, error) => {
                    $i.setWaiting(false);
                    $field.cardLoader(false);
                    if(!error){
                        _log(resp.success ? "check" : "block");
                    }
                    else{
                        $i.setWaiting(false);
                        $field.cardLoader(false);
                        _log("sync");
                    }
                }
                n.post();
            }
            _log();
            let last_value = $i.val()
            $field.on("click", "._icon_", function(){
                recheck_referral();
            });
            $i.on("change", () => {
                const v = $i.val();
                if(!v){
                    _log();
                    return;
                }
                if($amd.validate(v, pattern)){
                    recheck_referral();
                }
            });
            $i.on("keydown keyup", () => {
                const v = $i.val();
                if(last_value !== v && $amd.validate(v, pattern)) {
                    $i.change();
                    last_value = v;
                }
            });
            window.addEventListener("load", () => $i.trigger("change"));
        }());
    </script>
    <?php
}, 20 );

function amd_ext_linky_validate_custom_fields( $custom_fields ){
    if( !empty( $custom_fields["ext_link_referral"] ) ){
        $referral_code = $custom_fields["ext_link_referral"];
        if( !amd_ext_linky_validate_referral_code( $referral_code, false, true ) ) {
            wp_send_json_error( [
                "msg" => __( "Referral code is invalid", "material-dashboard-pro" ),
                "errors" => array(
                    array(
                        "id" => "referral_invalid",
                        "error" => __( "Referral code is invalid", "material-dashboard-pro" ),
                        "field" => "Custom:ext_link_referral"
                    )
                )
            ] );
        }
    }
}
add_action( "amd_register_validate_custom_fields", "amd_ext_linky_validate_custom_fields" );

function amd_ext_linky_save_user_custom_fields( $uid, $fields ){
    if( !empty( $fields["ext_link_referral"] ) ){
        $referral_code = $fields["ext_link_referral"];
        global $amdLinkyCore;
        $amdLinkyCore->join_referral( $uid, $referral_code );
    }
}
add_action( "amd_save_user_custom_fields", "amd_ext_linky_save_user_custom_fields", 10, 2 );

add_action( "delete_user", function( $user_id ){
    if( !empty( $user_id ) ) {
        global $amdLinkyCore;
        $amdLinkyCore->leave( $user_id );
    }
} );