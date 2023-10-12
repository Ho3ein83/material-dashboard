<?php

if( is_user_logged_in() )
    return;

/**
 * 2-factor authentication code length
 * @since 1.0.5
 */
$code_len = apply_filters( "amd_2fa_code_length", 4 );

$token = sanitize_text_field( $_GET["scope"] ?? "" );

$temp = amd_get_temp( "2fa_token_{$token}_resend", false );
$_expire = $temp->expire ?? 0;
$expire = $_expire > 0 ? $_expire - time() : 0;

?>

<div>
    <input type="hidden" value="<?php echo esc_attr( $token ); ?>" id="2fa-token">
    <p class="color-blue small-text font-title"><?php esc_html_e( "Verification code has been sent to your email and/or phone number", "material-dashboard" ); ?></p>
    <label class="ht-input">
        <input type="text" class="clear-invalid-state" id="2fa-code">
        <span><?php esc_html_e( "Verification code", "material-dashboard" ); ?></span>
    </label>
    <div class="text-center">
        <button type="button" class="btn btn-text --low waiting margin-auto" id="2fa-resend"><?php esc_html_e( "Resend code", "material-dashboard" ); ?></button>
    </div>
</div>
<div class="h-20"></div>
<div class="amd-lr-buttons">
	<button type="button" class="btn" id="submit-2fa"><?php esc_html_e( "Submit", "material-dashboard" ); ?></button>
	<a href="<?php echo esc_url( amd_get_login_page() ); ?>" class="btn btn-text --low" id="2fa-back"><?php esc_html_e( "Back", "material-dashboard" ); ?></a>
</div>

<script>
	(function(){
		let code_len = JSON.parse(`<?php echo wp_json_encode( $code_len ); ?>`);
		let resend_time = parseInt(`<?php echo $expire; ?>`);
        if(isNaN(code_len))
            code_len = -1;
        if(isNaN(resend_time))
            resend_time = -1;
        let $submit = $("#submit-2fa"), $input = $("#2fa-code");

        let $resend = $("#2fa-resend");
        let resend_html = $resend.html();

        let _back = () => {
            let url = $("#2fa-back").hasAttr("href", true);
            if(url) location.href = url;
        }

        $input.on("input", function(){
            let v = $(this).val();
            if(code_len > 0 && v.length === code_len)
                $submit.click();
        });
        $input.on("keydown keyup", function(e){
            if(e.key === "Enter")
                $submit.click();
        });

        let resend_interval;
        function start_resend_timer(time){
            resend_time = time;
            clearInterval(resend_interval);
            resend_interval = setInterval(() => {
                $resend.html(resend_html + " (" + $amd.secondsToClock(resend_time) + ")").setWaiting();
                resend_time--;
                if(resend_time <= 0){
                    clearInterval(resend_interval);
                    $resend.html(resend_html).setWaiting(false);
                }
            }, 1000);
        }
        start_resend_timer(resend_time);

        $submit.click(function(){
            let code = $input.val();
            if(!code){
                $input.setInvalid();
                return;
            }
            let token = $("#2fa-token").val();
            let n = dashboard.createNetwork();
            n.clean();
            n.setAction(amd_conf.ajax.public);
            n.put("2fa_submit", code);
            n.put("token", token);
            n.on.start = () => {
                dashboard.suspend();
            }
            n.on.end = (resp, error) => {
                if(!error){
                    if(resp.success){
	                    let url = resp.data.url || null;
	                    if(url)
                            location.href = url;
                        else
                            _back();
                    }
                    else{
                        dashboard.resume();
                        dashboard.toast(resp.data.msg);
                    }
                }
                else{
                    dashboard.resume();
                    dashboard.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        });

        $resend.click(function(){
            let token = $("#2fa-token").val();
            let n = dashboard.createNetwork();
            n.clean();
            n.setAction(amd_conf.ajax.public);
            n.put("2fa_resend", token);
            n.on.start = () => {
                $resend.waitHold(_t("wait_td"));
            }
            n.on.end = (resp, error) => {
                if(!error){
                    if(resp.success){
	                    let i = resp.data.resend_interval || 0;
                        if(i >= 0)
                            start_resend_timer(i);
                    }
                    else{
                        $resend.waitRelease();
                        dashboard.toast(resp.data.msg);
                    }
                }
                else{
                    $resend.waitRelease();
                    dashboard.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        });

	}());
</script>