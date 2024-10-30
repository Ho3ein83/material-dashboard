<?php ob_start(); ?>
<input type="password" name="_password_ignore" style="width:0;height:0;border:0;padding:0;opacity:0">
<label class="ht-input">
    <input type="password" data-field="current_password" value="" placeholder="" required>
    <span><?php esc_html_e( "Current password", "material-dashboard" ); ?></span>
	<?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
	<?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
</label>
<label class="ht-input">
    <input type="password" data-field="password" value="" placeholder="" required>
    <span><?php esc_html_e( "New password", "material-dashboard" ); ?></span>
	<?php _amd_icon( "show_password", null, [], [ "class" => "clickable -pt --hide-password" ] ); ?>
	<?php _amd_icon( "hide_password", null, [], [ "class" => "clickable -pt --show-password" ] ); ?>
</label>
<div class="plr-8">
    <p class="text-justify color-blue"><?php esc_html_e( "Note: after you change your password you have to log-in again", "material-dashboard" ); ?>.</p>
</div>
<?php $change_password_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="plr-8">
    <button type="button" class="btn" data-submit="change-password"><?php esc_html_e( "Change", "material-dashboard" ); ?></button>
    <button type="button" class="btn btn-text" data-dismiss="change-password"><?php esc_html_e( "Dismiss", "material-dashboard" ); ?></button>
</div>
<?php $change_password_footer = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
	"type" => "content_card",
	"title" => esc_html__( "Change password", "material-dashboard" ),
	"content" => $change_password_content,
	"footer" => $change_password_footer,
	"_id" => "change-password-card",
    "_attrs" => 'data-form="change-password"'
) ); ?>
<script>
    (function(){
        let $card = $("#change-password-card");
        let form = new AMDForm("change-password-card");
        form.on("submit", () => {
            let data = form.getFieldsData();
            let _engine = dashboard.getApiEngine();
            _engine.clean();
            _engine.put("change_password", {
                current_password: data.current_password.value,
                new_password: data.password.value,
            });
            _engine.on.start = () => $card.cardLoader();
            _engine.on.end = (resp, error) => {
                $card.cardLoader(false);
                if(!error) {
                    if(resp.success) {
                        form.dismiss();
                        $amd.alert(_t("change_password"), resp.data.msg, {
                            icon: "success",
                            onConfirm: () => {
                                dashboard.suspend();
                                location.reload();
                            }
                        });
                    }
                    else{
                        $amd.alert(_t("change_password"), resp.data.msg, {
                            icon: "error"
                        });
                    }
                }
                else {
                    $amd.alert(_t("change_password"), _t("error"), {
                        icon: "error"
                    });
                }
            }
            _engine.post();
        });
        form.on("invalid_code", d => {
            let {field, code} = d;
            let id = field.id;
            if(id === "password" || id === "old_password") {
                if(code === 4 || code === 1)
                    dashboard.toast(_t("password_8"));
            }
        });
    }());
</script>