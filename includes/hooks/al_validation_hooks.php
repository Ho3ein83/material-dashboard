<?php

/**
 * Validation fields data
 * @since 1.2.0
 */
add_filter( "amd_account_validation_fields", function( $list ){
    $list["phone"] = array(
        "id" => "phone",
        "icon" => "phone"
    );
    return $list;
} );

/**
 * Validation phone field
 * @sicne 1.2.0
 */
add_action( "amd_account_validation_field_phone", function(){
    ?>
    <h3 class="margin-0"><?php esc_html_e( "Phone number", "material-dashboard" ); ?></h3>
    <div class="h-10"></div>
    <div id="phone-validate-form">
        <?php amd_phone_fields(); ?>
    </div>
    <script>
        (function(){
            let phone_validate_form = new AMDForm("phone-validate-form", {});
            phone_validate_form.on("invalid", field => {
                let {id} = field;
                if (id === "phone_number")
                    dashboard.toast(_t("phone_incorrect"));
            });
            window._validate_step_phone = (complete, fail) => {
                if(!phone_validate_form.validate()) {
                    fail();
                    return;
                }
                let n = dashboard.getApiEngine();
                n.clean();
                n.put("__validate", {
                    phone: phone_validate_form.$getField("phone_number").val()
                });
                n.on.end = (resp, error) => {
                    if(!error){
                        console.log(resp);
                        if(resp.success){
                            complete();
                        }
                        else{
                            fail();
                            $amd.toast(resp.data.msg);
                        }
                    }
                    else{
                        fail();
                        $amd.toast(_t("error"));
                        console.log(resp.xhr);
                    }
                }
                n.post();
            }
        }());
    </script>
    <?php
} );