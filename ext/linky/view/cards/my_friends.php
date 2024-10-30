<?php

$referral_code = amd_ext_linky_get_user_referral_code();
$referral_url = amd_merge_url_query( amd_get_login_page(), "auth=register&referral=" . urlencode( $referral_code ) );

/**
 * Referral URL
 * @since 1.2.0
 */
$referral_url = apply_filters( "amd_ext_linky_referral_url", $referral_url, $referral_code );

?>

<?php ob_start(); ?>
<?php echo esc_html_x( "Invited list", "linky", "material-dashboard" ); ?>
<button class="btn btn-icon-square bis-sm --transparent --button mlr-8 --sm" id="linky-invite" data-tooltip="<?php echo esc_attr_x( "Invite", "linky", "material-dashboard" ); ?>">
    <?php _amd_icon( "qr_code" ); ?>
</button>
<button class="btn btn-icon-square bis-sm --transparent --button --right mlr-8 --sm" data-lazy-query="?void=ext_linky" data-tooltip="<?php esc_html_e( "Friends", "material-dashboard" ); ?>">
    <?php _amd_icon( "people" ); ?>
</button>
<?php $card_title = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="text-center" id="linky-loading">
    <progress class="hb-progress-circular"></progress>
    <p><?php esc_html_e( "Please wait", "material-dashboard" ); ?>...</p>
</div>
<h4 class="text-center" id="linky-empty"><?php esc_html_e( "You haven't invited anyone yet", "material-dashboard" ); ?></h4>
<div class="amd-table" id="linky-list-table">
    <table>
        <tbody class="-table-items" id="linky-list-items"></tbody>
    </table>
</div>
<div class="text-center" id="linky-show-more" style="display:none;margin-top:8px">
    <a href="?void=ext_linky" class="btn btn-text --low" data-turtle="lazy"><?php esc_html_e( "More", "material-dashboard" ); ?></a>
    <div class="h-10"></div>
</div>
<?php $card_content = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
    "title" => $card_title,
    "content" => $card_content,
    "color" => "blue",
    "type" => "title_card"
) ); ?>

<script>
    (function(){
        const referral_code = JSON.parse(`<?php echo wp_json_encode( $referral_code ); ?>`);
        const referral_url = JSON.parse(`<?php echo wp_json_encode( $referral_url ); ?>`);
        const $items = $("#linky-list-items"), $empty = $("#linky-empty"),
            $progress = $("#linky-loading"), $more = $("#linky-show-more"),
            $invite = $("#linky-invite");
        $progress.fadeOut(0);

        $invite.click(function(){
            if(referral_url){
                const qr = $amd.generateQR({
                    width: 300,
                    height: 300,
                    data: referral_url
                });
                if(qr){
                    $amd.alert(_t("referral_code"), `<style>._amd_linky_referral_code_>canvas{width:100%;height:100%}</style><div class="_amd_linky_referral_code_" style="width:150px;height:auto;margin:auto;aspect-ratio:1"></div><div class="h-10"><span class="badge --sm">${referral_code}</span></div>`, {
                        buttons: [{
                            text: _t("copy"),
                            attr: `data-copy="${$amd.escapeAttr(referral_url)}"`,
                            type: "primary"
                        }],
                        confirmButton: _t("close"),
                        onBuild: () => {
                            qr.append($("._amd_linky_referral_code_").get(0));
                        }
                    });
                }
            }
        });

        const _refresh = () => {
            let n = dashboard.createNetwork();
            n.clean();
            n.put("_ajax_target", "ext_linky");
            n.put("get_friends_list", "");
            n.put("limit", 5);
            n.on.start = () => {
                $progress.fadeIn(0);
                $items.html();
            }
            n.on.end = (resp, error) => {
                $progress.fadeOut(0);
                if(!error){
                    if(resp.success){
                        const friends = resp.data.friends || {};
                        let html = "";
                        for(let [, value] of Object.entries(friends)){
                            html += `<tr><td>${$amd.escapeHtml(value.fullname)}</td><td>${value.invite_date ?? ""}</td></tr>`;
                        }
                        $items.html(html);
                        if(Object.entries(friends).length) {
                            $empty.fadeOut(0);
                            $more.fadeIn();
                        }
                        else {
                            $empty.fadeIn(0);
                            $more.fadeOut();
                        }
                    }
                    else{}
                }
                else{}
            }
            n.post();
        }
        _refresh();
    }());
</script>
