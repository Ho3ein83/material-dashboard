<?php

$use_login_2fa = amd_get_site_option( "use_login_2fa", "false" ) == "true";
$force_login_2fa = amd_get_site_option( "force_login_2fa", "false" ) == "true";
$display_login_reports = amd_get_site_option( "display_login_reports", "true" ) == "true";
$enable_2fa = amd_get_user_meta( null, "use_2fa", "false" ) == "true";

?><?php ob_start(); ?>
<?php if( $use_login_2fa AND !$force_login_2fa ): ?>
    <div class="amd-opt-grid">
        <div>
            <label for="enable-2fa" class="clickable"><?php esc_html_e( "Enable 2FA", "material-dashboard" ); ?></label>
        </div>
        <div>
            <label class="hb-switch">
                <input type="checkbox" role="switch" id="enable-2fa" <?php echo $enable_2fa ? 'checked' : ''; ?>>
                <span></span>
            </label>
        </div>
    </div>
    <div class="plr-8">
        <p class="text-justify color-blue"><?php esc_html_e( "By enabling 2 factor authentication you have to enter received code on your mobile to login to your account.", "material-dashboard" ); ?></p>
    </div>
    <div class="hr"></div>
<?php endif; ?>

<?php if( $display_login_reports ): ?>
    <div class="amd-opt-grid">
        <div>
			<?php esc_html_e( "Login reports", "material-dashboard" ); ?>
        </div>
        <div>
            <a href="?void=account&report=logins" data-turtle="lazy" class="btn btn-sm"><?php esc_html_e( "View reports", "material-dashboard" ); ?></a>
        </div>
    </div>
    <div class="hr"></div>
<?php endif; ?>

<div class="amd-opt-grid">
    <div>
        <?php esc_html_e( "Sessions", "material-dashboard" ); ?>
    </div>
    <div>
        <button type="button" id="close-other-sessions" class="btn btn-text btn-sm --low"><?php esc_html_e( "Close other sessions", "material-dashboard" ); ?></button>
    </div>
</div>
<div class="plr-8">
    <p class="text-justify color-blue"><?php esc_html_e( "If you are logged in with public computers and you need to log-out from other devices, click on this button.", "material-dashboard" ); ?></p>
</div>

<?php $security_card_content = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
	"type" => "content_card",
	"title" => esc_html__( "Security", "material-dashboard" ),
	"content" => $security_card_content,
	"_id" => "security-card"
) ); ?>
<script>
    (function(){
        let $card = $("#security-card");
        $("#close-other-sessions").click(function(){
            let $btn = $(this);
            $btn.blur();
            let n = dashboard.createNetwork();
            n.put("close_other_sessions", "");
            n.on.start = () => {
                $btn.waitHold(_t("wait_td"));
                $card.cardLoader();
            }
            n.on.end = (resp, error) => {
                $btn.waitRelease();
                $card.cardLoader(false);
                if(!error){
                    $amd.toast(resp.data.msg);
                }
                else{
                    console.log(resp.xhr);
                    $amd.toast(_t("error"));
                }
            }
            n.post();
        });
        $("#enable-2fa").change(function(){
            let $cb = $(this);
            let enabled = $cb.is(":checked");
            let n = dashboard.createNetwork();
            n.put("switch_2fa", enabled ? "true" : "false");
            n.on.start = () => {
                $cb.setWaiting();
                $card.cardLoader();
            }
            n.on.end = (resp, error) => {
                $cb.setWaiting(false);
                $card.cardLoader(false);
                if(!error){
                    $amd.toast(resp.data.msg);
                    if(!resp.success)
                        $cb.prop("checked", !enabled);
                }
                else{
                    console.log(resp.xhr);
                    $amd.toast(_t("error"));
                    $cb.prop("checked", !enabled);
                }
            }
            n.post();
        });
    }());
</script>