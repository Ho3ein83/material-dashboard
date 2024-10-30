<?php

$ctrack_allowed = amd_get_site_option( "ctrack_allowed", "unset" );

?><div class="amd-admin-card --setting-card">
    <div class="--content">
        <h3 class="text-center color-primary margin-0"><?php echo esc_html_x( "Help us to improve our products", "Admin", "material-dashboard" ); ?></h3>
        <div class="h-10"></div>
        <p class="color-primary text-center margin-0"><?php echo esc_html_x( "By letting us to collect your non-sensitive data we can see how you are using this plugin, so we can improve our products' for better experience.", "Admin", "material-dashboard" ); ?></p>
        <div class="h-20"></div>
        <h3 class="text-center color-primary margin-0"><?php echo esc_html_x( "What do we collect?", "Admin", "material-dashboard" ); ?></h3>
        <div class="h-10"></div>
        <p class="color-primary text-center margin-0"><?php echo esc_html_x( "We only collect your site settings that stored into this plugin tables in your database, that means we don't collect any data from your website at all, only this plugin settings and configurations.", "Admin", "material-dashboard" ); ?></p>
        <div class="h-20"></div>
        <h3 class="text-center color-primary margin-0"><?php echo esc_html_x( "Where do you save my data?", "Admin", "material-dashboard" ); ?></h3>
        <div class="h-10"></div>
        <p class="color-primary text-center margin-0"><?php echo esc_html_x( "We save encrypted data into our site database and we won't share it with any third-party applications or services.", "Admin", "material-dashboard" ); ?></p>
        <div class="h-20"></div>
        <div class="text-center">
            <p>
                <?php echo esc_html_x( "Data collecting status", "Admin", "material-dashboard" ); ?>:
                <?php if( $ctrack_allowed == "true" ): ?>
                    <span class="color-green"><?php echo esc_html_x( "Allowed", "cTrack allowed", "material-dashboard" ); ?></span>
                <?php else: ?>
                    <span class="color-red"><?php echo esc_html_x( "Disallowed", "cTrack allowed", "material-dashboard" ); ?></span>
                <?php endif; ?>
            </p>
            <?php if( $ctrack_allowed == "true" ): ?>
                <button type="button" class="amd-admin-button --primary --text" data-change-ctrack="deny"><?php echo esc_html_x( "I don't want my data be collected anymore", "Admin", "material-dashboard" ); ?></button>
            <?php else: ?>
                <button type="button" class="amd-admin-button --primary" data-change-ctrack="allow"><?php echo esc_html_x( "I allow to collect non-sensitive data from my site", "Admin", "material-dashboard" ); ?></button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    jQuery($ => {
        $("[data-change-ctrack]").click(function(){
            let $el = $(this);
            let $buttons = $("[data-change-ctrack]");
            let n = new AMDNetwork();
            let action = $el.attr("data-change-ctrack");
            n.clean();
            n.setAction(amd_conf.ajax.private);
            n.put("save_options", {
                ctrack_allowed: action
            });
            n.on.start = () => {
                $buttons.setWaiting();
                $el.waitHold(_t("wait_td"));
            }
            n.on.end = (resp, error) => {
                $buttons.setWaiting(false);
                $el.waitRelease();
                if(!error){
                    if(resp.success) location.reload();
                    else $amd.toast(resp.data.msg);
                }
                else{
                    $amd.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        });
    });
</script>