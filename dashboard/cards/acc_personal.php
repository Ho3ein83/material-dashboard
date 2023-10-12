<?php

$lastname_field = amd_get_site_option( "lastname_field" ) == "true";
$username_field = amd_get_site_option( "username_field" ) == "true";
$phone_field = amd_get_site_option( "phone_field" ) == "true";

$thisuser = amd_simple_user();

$change_email_allowed = apply_filters( "amd_change_email_allowed", false );

$temps = amd_find_temp( "temp_key", "change_email:{$thisuser->ID}_", true );
$pending_email = "";
if( is_countable( $temps ) AND count( $temps ) > 0 )
	$pending_email = $temps[0]->temp_value;

$ncode = amd_get_user_meta( $thisuser->ID, "ncode" );

?>
<?php ob_start(); ?>
<div>
	<?php if( $lastname_field ): ?>
        <div class="ht-input-row">
            <label class="ht-input">
                <input type="text" data-field="first_name" data-next="last_name"
                       value="<?php echo esc_attr( $thisuser->firstname ); ?>" placeholder="">
                <span><?php esc_html_e( "First name", "material-dashboard" ); ?></span>
				<?php _amd_icon( "person" ); ?>
            </label>
            <label class="ht-input">
                <input type="text" data-field="last_name" data-next="email"
                       value="<?php echo esc_attr( $thisuser->lastname ); ?>" placeholder="">
                <span><?php esc_html_e( "Last name", "material-dashboard" ); ?></span>
				<?php _amd_icon( "person" ); ?>
            </label>
        </div>
	<?php else: ?>
        <label class="ht-input">
            <input type="text" data-field="first_name" data-next="email"
                   value="<?php echo esc_attr( $thisuser->fullname ); ?>" placeholder="">
            <span><?php esc_html_e( "First name", "material-dashboard" ); ?></span>
			<?php _amd_icon( "person" ); ?>
        </label>
	<?php endif; ?>
    <label class="ht-input<?php echo $change_email_allowed ? '' : ' waiting'; ?>">
        <input type="text" <?php echo $change_email_allowed ? 'data-pattern="%email%" data-field="email" data-next="submit"' : ''; ?>
               value="<?php echo esc_attr( $thisuser->email ); ?>" placeholder="">
        <span><?php esc_html_e( "Email", "material-dashboard" ); ?></span>
		<?php _amd_icon( "email" ); ?>
    </label>
    <div style="margin:0 8px 16px; <?php echo !empty( $pending_email ) ? '' : 'display:none'; ?>"
         id="email-pending-box">
		<?php amd_alert_box( array(
			"text" => sprintf( esc_html__( "You have a pending request for changing your email, check your inbox. %s %scancel%s %ssend again%s", "material-dashboard" ), "<br><code id=\"pending_email\">$pending_email</code>", "<br><button type=\"button\" class=\"btn _cancel_email_change_\">", "</button>", "<button class=\"btn _resend_email_change_\">", "</button>" ),
			"type" => "primary",
			"is_front" => true,
			"align" => "justify"
		) ); ?>
    </div>
    <label class="ht-input waiting">
        <input type="text" value="<?php echo esc_attr( $thisuser->username ); ?>" placeholder="">
        <span><?php esc_html_e( "Username", "material-dashboard" ); ?></span>
		<?php _amd_icon( "username" ); ?>
    </label>
	<?php if( $phone_field ): ?>
        <label class="ht-input waiting">
            <input type="text" value="<?php echo esc_attr( $thisuser->phone ); ?>" placeholder="" style="direction:ltr">
            <span><?php esc_html_e( "Phone", "material-dashboard" ); ?></span>
			<?php _amd_icon( "phone" ); ?>
        </label>
	<?php endif; ?>

    <?php if( $ncode ): ?>
        <label class="ht-input waiting">
            <input type="text" value="<?php echo esc_attr( $ncode ); ?>" placeholder="" style="direction:ltr">
            <span><?php esc_html_e( "National code", "material-dashboard" ); ?></span>
        </label>
	<?php endif; ?>
</div>
<?php $personal_card_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="plr-8">
    <button type="button" class="btn" data-submit="personal"><?php esc_html_e( "Save", "material-dashboard" ); ?></button>
    <button type="button" class="btn btn-text" data-dismiss="personal"><?php esc_html_e( "Cancel", "material-dashboard" ); ?></button>
</div>
<?php $personal_card_footer = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
	"type" => "content_card",
	"title" => esc_html__( "Personal information", "material-dashboard" ),
	"content" => $personal_card_content,
	"footer" => $personal_card_footer,
	"_id" => "personal-form",
    "_attrs" => 'data-form="personal"'
) ); ?>


<script>
    (function() {
        let engine = dashboard.getApiEngine();
        var $card = $("#personal-form");
        var form = new AMDForm("personal-form");
        var $ep_box = $("#email-pending-box");
        var $cancel_email_change = $("._cancel_email_change_"), $resend_email_change = $("._resend_email_change_");
        form.on("submit", d => {
            let data = form.getFieldsEntries();
            engine.clean();
            engine.put("edit_account", data);
            engine.on.start = () => $card.cardLoader()
            engine.on.end = (resp, error) => {
                $card.cardLoader(false);
                if(!error) {
                    $amd.alertQueue(_t("account_info"), resp.data.msg, {
                        icon: resp.success ? "success" : "error"
                    });
                    if(resp.success){
                        dashboard.setUserName((data.first_name || "") + " " + (data.last_name || ""));
                    }
                    if(resp.data.toasts) {
                        for(let [key, conf] of Object.entries(resp.data.toasts)) dashboard.toast("", 3000, conf);
                    }
                    if(resp.data.email_pending) {
                        $("#pending_email").html(resp.data.email_pending);
                        $ep_box.fadeIn();
                    }
                }
                else {
                    $amd.alert(_t("account_info"), _t("error"), {
                        icon: "error"
                    });
                }
            }
            engine.post();
        });
        $cancel_email_change.click(function() {
            engine.clean();
            engine.put("cancel_email_pending", "");
            engine.on.start = () => $ep_box.setWaiting();
            engine.on.end = (resp, error) => {
                $ep_box.setWaiting(false);
                if(!error) {
                    if(resp.success) {
                        $ep_box.fadeOut();
                    }
                    else {
                        $amd.alertQueue("", resp.data.msg, {
                            icon: "error"
                        });
                    }
                }
                else {
                    $amd.alert("", _t("error"), {
                        icon: "error"
                    });
                }
            }
            engine.post();
        });
        let resendTime = 30, resendInterval;
        $resend_email_change.click(function(){
            let $btn = $(this);
            let lastHtml = $btn.html();
            engine.clean();
            engine.put("resend_email_pending", "");
            engine.on.start = () => $ep_box.setWaiting();
            engine.on.end = (resp, error) => {
                $ep_box.setWaiting(false);
                if(!error) {
                    if(resp.success) {
                        if(resp.data.toasts) {
                            for(let [key, conf] of Object.entries(resp.data.toasts)) dashboard.toast("", 3000, conf);
                        }
                        t = resendTime;
                        $btn.setWaiting();
                        $btn.html(`${lastHtml} ${$amd.secondsToClock(t)}`);
                        resendInterval = setInterval(() => {
                            $btn.html(`${lastHtml} ${$amd.secondsToClock(t)}`);
                            t--;
                            if(t <= 0) {
                                clearInterval(resendInterval);
                                $btn.html(lastHtml);
                                $btn.setWaiting(false);
                            }
                        }, 1000);
                        resendTime += 30;
                    }
                    else {
                        $amd.alertQueue("", resp.data.msg, {
                            icon: "error"
                        });
                    }
                }
                else {
                    $amd.alert("", _t("error"), {
                        icon: "error"
                    });
                }
            }
            engine.post();
        });
    }());
</script>