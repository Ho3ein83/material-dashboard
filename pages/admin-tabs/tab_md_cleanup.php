<?php

global $amdCore;

$variants = apply_filters( "amd_cleanup_variants", $amdCore->getCleanupVariants() );

?>
<!-- Cleanup -->
<div class="amd-admin-card text-center --setting-card" id="card-cleanup">
	<h3 class="--title"><?php echo esc_html_x( "Cleanup", "Admin", "material-dashboard" ); ?></h3>
	<div class="--content">
		<p class="color-primary margin-0"><?php echo esc_html_x( "Select which data you want to delete", "Admin", "material-dashboard" ); ?></p>
		<p class="color-red margin-0">
            <?php echo esc_html( sprintf( esc_html_x( "Items specified with %s will be regenerated automatically, if you want to delete plugin with all its data do not visit any other of plugin pages after cleanup", "Admin", "material-dashboard" ), "*" ) ); ?>.
			<?php echo amd_doc_url( "cleanup", true ); ?>
        </p>
		<div class="__option_grid">
			<div class="-item">
				<div class="-sub-item">
					<label for="opt-all">
						<?php echo esc_html_x( "Select all", "Admin", "material-dashboard" ); ?>
					</label>
				</div>
				<div class="-sub-item">
					<label class="hb-switch">
						<input type="checkbox" role="switch" id="opt-all">
						<span></span>
					</label>
				</div>
			</div>
            <?php foreach( $variants as $variant ): ?>
            <?php
                $id = $variant["id"] ?? null;
                $title = $variant["title"] ?? null;
                $auto_generates = $variant["auto_generates"] ?? false;
                if( !$id OR !$title )
                    continue;
            ?>
			<div class="-item">
				<div class="-sub-item">
					<label for="<?php echo esc_attr( "opt-$id" ); ?>">
						<?php echo esc_html( $title ); ?>
                        <?php if( $auto_generates ): ?>
                            <span class="color-red">*</span>
                        <?php endif; ?>
					</label>
				</div>
				<div class="-sub-item">
					<label class="hb-switch">
						<input type="checkbox" class="_opt_delete" role="switch" id="<?php echo esc_attr( "opt-$id" ); ?>"
						       name="<?php echo esc_attr( $id ); ?>" value="true">
						<span></span>
					</label>
				</div>
			</div>
            <?php endforeach; ?>
		</div>
		<button type="button" class="amd-admin-button --primary --text" id="amd-cleanup"><?php echo esc_html_x( "Cleanup", "Admin", "material-dashboard" ); ?></button>
	</div>
</div>

<script>
    let _clean_up_str = `<?php echo esc_html_x( "Cleanup", "Admin", "material-dashboard" ) ?>`;
    (function () {
        let $card = $("#card-cleanup"), $btn = $("#amd-cleanup");
        let $all = $("#opt-all");
        $all.change(function () {
            $card.find('[id*="opt-"]').prop("checked", $(this).is(":checked"));
        });
        $("._opt_delete").on("change", function(){
            $all.prop("checked", $("._opt_delete:not(:checked)").length <= 0);
        });

        const $l = $("#opt-login_reports"), $o = $("#opt-login_reports_old");
        $l.change(() => $l.is(":checked") ? $o.prop("checked", false) : null);
        $o.change(() => $o.is(":checked") ? $l.prop("checked", false) : null);

        const btn_html = $btn.html();
        $btn.click(function () {
            const complete = () => {
                let options = [];
                $("._opt_delete").each(function () {
                    let $input = $(this);
                    if ($input.is(":checked"))
                        options.push($input.attr("name") || null);
                });
                network.clean();
                network.put("_cleanup", options.join(","));
                network.on.start = () => {
                    $btn.blur();
                    $btn.waitHold(_t("wait_td"));
                    $card.cardLoader();
                }
                network.on.end = (resp, error) => {
                    $card.cardLoader(false);
                    $btn.waitRelease();
                    $btn.html(btn_html);
                    if (!error) {
                        $amd.alert(_clean_up_str, resp.data.html || resp.data.msg, {
                            icon: resp.success ? "success" : "error"
                        });
                    } else {
                        $amd.alert(_clean_up_str, _t("error"), {
                            icon: "error"
                        });
                    }
                }
                network.post();
            }
            const html = `<input type="text" id="cleanup-confirmation" class="amd-admin-input"/>`;
            const confirmation_phrase = _t("confirm").toLowerCase();
            $amd.alert("", `<span><?php echo esc_html_x( "Type %s on the below field to continue", "Admin", "material-dashboard" ); ?></span>`.replaceAll("%s", `<span class="color-primary w-bold">${confirmation_phrase}</span>`) + `<div class="h-10"></div>${html}`, {
                confirmButton: false,
                cancelButton: _t("cancel"),
                allowOutsideClick: false,
                buttons: [{
                    "text": _t("confirm"),
                    "attr": "data-cleanup-confirm",
                }],
                onBuild: () => {
                    const $btn = $("[data-cleanup-confirm]");
                    const $input = $("#cleanup-confirmation");
                    let confirmed = false;
                    $input.on("keydown keyup change", function(e){
                        confirmed = $input.val() === _t("confirm");
                        if(confirmed && typeof e.key !== "undefined" && e.key === "Enter"){
                            $btn.click();
                            return;
                        }
                        confirmed ? $btn.fadeIn() : $btn.fadeOut(0);
                    }).trigger("change");
                    setTimeout(() => $input.get(0).focus(), 100);
                    $btn.click(() => {
                        Hello.closeAll();
                        complete();
                    });
                }
            });
        });
    }());
</script>
