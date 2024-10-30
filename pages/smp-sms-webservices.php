<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
    return;

$sms_active_webservice = amd_get_site_option( "sms_active_webservice" );

$defaultSettings = array(
    "sms_active_webservice" => $sms_active_webservice,
);

/**
 * SMS webservices
 * @since 1.1.2
 */
$sms_webservices = apply_filters( "adp_sms_webservices", [] );

?>

<!-- SMS webservices -->
<div class="amd-admin-card --setting-card">
    <div class="__option_grid">
        <div class="-item">
            <div class="-sub-item">
                <h3 class="--title"><?php esc_html_e( "SMS webservices", "material-dashboard" ); ?></h3>
            </div>
            <div class="-sub-item">
                <button type="button" class="amd-admin-button" id="amd-save-settings"><?php esc_html_e( "Save", "material-dashboard" ); ?></button>
            </div>
            <div class="-sub-item --full">
                <?php if( !empty( $sms_webservices ) ): ?>
                <p class="color-red"><?php echo esc_html_x( "Please note that some of these webservices needs subscription and does not include testing plans and we can't test all of them, so we only can follow their documentation and instructions for deployment, therefore if you have a problem with them, contact us and we'll check the as soon as possible.", "Admin", "material-dashboard" ); ?></p>
                <?php endif; ?>
                <p class="color-blue"><?php echo esc_html_x( "If you can't see your required SMS webservice here, you can contact us to add them as soon as possible.", "Admin", "material-dashboard" ); ?></p>
            </div>
        </div>
    </div>
    <div class="--content">
        <form action="">
            <input type="text" name="_username" style="width: 0; height: 0; border: 0; padding: 0" />
            <input type="email" name="_email" style="width: 0; height: 0; border: 0; padding: 0" />
            <input type="password" name="_password" style="width: 0; height: 0; border: 0; padding: 0" />
        <?php $counter = 0; ?>
        <?php foreach( $sms_webservices as $webservice ): ?>
            <?php
            $id = $webservice["id"];
            $name = $webservice["name"] ?? "";
            $desc = $webservice["desc"] ?? "";
            $logo = $webservice["logo"] ?? "";
            $counter++;
            ?>
            <div class="__option_grid">
                <div class="-item">
                    <div class="-sub-item">
                        <label for="<?php echo esc_attr( "sms-ws-$id" ); ?>" class="clickable">
                            <span><?php echo esc_html( $name ); ?></span>
                            <br>
                            <span class="tiny-text waiting"><?php echo esc_html( $desc ); ?></span>
                        </label>
                        <?php if( $logo ): ?>
                            <div style="padding:4px 0;pointer-events:none;height:32px">
                                <img src="<?php echo esc_url( $logo ); ?>" alt="" style="height:100%;width:auto;max-width:120px;display:block">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="radio" role="switch" id="<?php echo esc_attr( "sms-ws-$id" ); ?>" name="sms_active_webservice" value="<?php echo esc_attr( $id ); ?>">
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="amd-admin-card --sms-ws-conf" id="<?php echo esc_attr( "sms-ws-config-$id" ); ?>">
                <h4 class="color-primary"><?php echo esc_html_x( "Configuration", "Admin", "material-dashboard" ); ?></h4>
                <?php
                /**
                 * SMS webservice configuration hook
                 * @since 1.1.2
                 */
                do_action( "adp_sms_webservice_{$id}_configuration" );
                ?>
            </div>
            <?php if( count( $sms_webservices ) > 1 AND count( $sms_webservices ) > $counter ): ?>
            <div class="hr"></div>
            <?php endif; ?>
        <?php endforeach; ?>
            <div class="__option_grid">
                <div class="-item">
                    <div class="-sub-item">
                        <label for="sms-ws-none" class="clickable">
                            <span><?php esc_html_e( "Disable", "material-dashboard" ); ?></span>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <label class="hb-switch">
                            <input type="radio" role="switch" id="sms-ws-none" name="sms_active_webservice" value="">
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>
        </form>
        <?php if( $counter <= 0 ): ?>
            <p class="text-center"><?php echo esc_html_x( "There is no webservice available.", "Admin", "material-dashboard" ); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
    (function(){
        var network = new AMDNetwork();
        var unsaved = false;

        $(window).bind("beforeunload", function(e){
            if(unsaved){
                e.returnValue = "Changes you made may not be saved.";
                return "Changes you made may not be saved.";
            }
        });
        $("input, textarea, select").on("change", function(e){
            if(e.originalEvent || null) unsaved = true;
        });

        network.setAction(amd_conf.ajax.private);
        $("#amd-save-settings").click(function(e){
            // [SETTINGS_SAVE_HANDLER]
            let $btn = $(this);
            $btn.blur();
            let v = $amd.doEvent("on_settings_saved", {e});
            let allowed = true;
            if(typeof e.isDefaultPrevented !== "undefined")
                allowed = !e.isDefaultPrevented();
            else if(typeof e.defaultPrevented !== "undefined")
                allowed = !e.defaultPrevented;
            if(!allowed)
                return;
            $btn.setWaiting();
            network.clean();
            network.put("save_options", v);
            network.on.start = () => $btn.html(_t("wait_td"));
            network.on.end = (resp, error) => {
                $btn.html(_t("save"));
                $btn.setWaiting(false);
                if(!error){
                    $amd.alert(_t("settings"), resp.data.msg, {
                        icon: resp.success ? "success" : "error"
                    });
                    if(resp.success) unsaved = false;
                }
                else{
                    $amd.alert(_t("settings"), _t("error"), {
                        icon: "error"
                    });
                }
            }
            network.post();
        });

        $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)

        // SMS webservice
        let $ws_inputs = $('input[name="sms_active_webservice"]'), selected_ws = "";
        function recheck_ws_conf(a=false){
            let $i = $('input[name="sms_active_webservice"]:is(:checked)');
            $(".amd-admin-card.--sms-ws-conf").fadeOut(0);
            if($i.length) {
                let v = $i.val();
                selected_ws = v;
                if(v) $(`#sms-ws-config-${v}`).fadeIn();
            }
            else{
                selected_ws = "";
            }
        }
        recheck_ws_conf(true);
        $ws_inputs.change(() => recheck_ws_conf());

        $amd.addEvent("on_settings_saved", () => {
            return {
                "sms_active_webservice": selected_ws,
            };
        });
    }());
</script>