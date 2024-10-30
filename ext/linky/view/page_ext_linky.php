<?php

$friends = amd_ext_linky_get_friends_list();

$referral_code = amd_ext_linky_get_user_referral_code();
$referral_url = amd_merge_url_query( amd_get_login_page(), "auth=register&referral=" . urlencode( $referral_code ) );

/**
 * Referral URL
 * @since 1.2.0
 */
$referral_url = apply_filters( "amd_ext_linky_referral_url", $referral_url, $referral_code );

?>
<div class="row">
    <div class="col-lg-6 margin-auto">
        <div class="h-50"></div>
        <div class="invite-section text-center" id="linky-invite-section">
            <div class="_tabs">
                <span class="_tab is-code"><?php echo esc_html_x( "Invite code", "linky", "material-dashboard" ); ?></span>
                <span class="_tab is-link"><?php echo esc_html_x( "Invite link", "linky", "material-dashboard" ); ?></span>
            </div>
            <div class="_qr _qr_code"></div>
            <div class="_qr _qr_link"></div>
            <div class="flex-center">
                <label class="ht-input _input_label">
                    <input type="text" placeholder="" readonly required>
                    <span class="_label"><?php echo esc_html_x( "Invite link", "linky", "material-dashboard" ); ?></span>
                    <?php _amd_icon( "link" ) ?>
                </label>
                <button type="button" class="btn _copy_button" data-text=""><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
            </div>
        </div>
        <script>
            (function(){
                const _c = JSON.parse(`<?php echo wp_json_encode( $referral_code ); ?>`), _l = JSON.parse(`<?php echo wp_json_encode( $referral_url ); ?>`);
                const $section = $("#linky-invite-section"), $qr_code = $section.find("._qr_code"),
                    $qr_link = $section.find("._qr_link"), $tabs = $section.find("._tabs > ._tab"),
                    $label = $section.find("._input_label"), $input = $label.find("input"),
                    $input_label = $label.find("._label"), $copy_button = $section.find("._copy_button");
                $tabs.click(function(){
                    const $e = $(this);
                    const is_link = $e.hasClass("is-link");
                    const text = is_link ? _l : _c;
                    $tabs.removeClass("active");
                    $e.addClass("active");
                    $input.val(text);
                    $label.find("._icon_").replaceWith(_i(is_link ? "link" : "verification_code"));
                    $input_label.html($e.html());
                    $copy_button.attr("data-text", $amd.escapeAttr(text));
                    is_link ? $qr_code.fadeOut(0) && $qr_link.fadeIn(300) : $qr_link.fadeOut(0) && $qr_code.fadeIn(300);
                });
                setTimeout(() => {
                    $tabs.first().click();
                }, 100);
                $copy_button.click(function(){
                    const v = $(this).hasAttr("data-text", true);
                    if(v) $amd.copy(v, false, true);
                });
                $tabs.first().click();
                const make_qr = ($e, d) => {
                    const q = $amd.generateQR({
                        width: 300,
                        height: 300,
                        data: d
                    }, null, "--amd-wrapper-fg");
                    if(q) {
                        $e.html("");
                        q.append($e.get(0));
                    }
                }
                make_qr($qr_code, _c);
                make_qr($qr_link, _l);
                $amd.addPageEvent("change_theme_mode", () => {
                    make_qr($qr_code, _c);
                    make_qr($qr_link, _l);
                });
            }());
        </script>
        <!-- @formatter off -->
        <style>.invite-section ._tabs{display:flex;align-items:center;justify-content:center}.invite-section{max-width:500px;margin:auto}.invite-section ._qr{display:none;width:200px;height:200px;background:var(--amd-wrapper-fg);padding:12px;border-radius:22px;margin:16px auto}.invite-section ._qr>canvas{width:100%;height:100%}.invite-section ._tabs{border-radius:12px;overflow:hidden;width:max-content;margin:auto}.invite-section ._tabs>._tab{background:#fff;color:var(--amd-primary);padding:12px 16px;cursor:pointer;transition:all ease .2s}.invite-section ._tabs>._tab.active{background:var(--amd-primary);color:#fff}.invite-section ._copy_button{min-width:unset;height:50px;margin:0 0 6px}</style>
        <!-- @formatter on -->
        <?php if( empty( $friends ) ): ?>
            <div class="text-center">
                <h3><?php echo esc_html_x( "You haven't invited anyone yet", "linky", "material-dashboard" ); ?>.</h3>
            </div>
        <?php endif; ?>
    </div>
    <?php if( !empty( $friends ) ): ?>
    <div class="col-lg-6 margin-auto">
        <?php $counter = 1; ?>
        <div class="amd-table" style="max-width: 600px">
            <table class="amd-table">
                <thead>
                <tr>
                    <th><?php esc_html_e( "Number", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Fullname", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Registration date", "material-dashboard" ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach( $friends as $friend ): ?>
                    <tr>
                        <td><?php echo esc_html( $counter ); ?></td>
                        <td>
                            <?php echo esc_html( $friend->fullname ); ?>
                            <?php if( amd_is_admin() ): ?>
                                <br><a href="<?php echo esc_url( $friend->getProfileURL() ); ?>" class="tiny-text" target="_blank"><?php esc_html_e( "View", "material-dashboard" ); ?></a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( amd_true_date( "j F Y", $friend->getRegistrationTime() ) ); ?></td>
                    </tr>
                    <?php $counter++; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
