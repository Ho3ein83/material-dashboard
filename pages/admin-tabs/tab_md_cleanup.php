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
			<div class="-item">
				<div class="-sub-item">
					<label for="opt-database">
						<?php echo esc_html_x( "Database", "Admin", "material-dashboard" ); ?>
                        <span class="color-red">*</span>
					</label>
				</div>
				<div class="-sub-item">
					<label class="hb-switch">
						<input type="checkbox" class="_opt_delete" role="switch" id="opt-database"
						       name="database" value="true">
						<span></span>
					</label>
				</div>
			</div>
            <div class="-item">
				<div class="-sub-item">
					<label for="opt-files">
						<?php echo esc_html_x( "Files", "Admin", "material-dashboard" ); ?>
					</label>
				</div>
				<div class="-sub-item">
					<label class="hb-switch">
						<input type="checkbox" class="_opt_delete" role="switch" id="opt-files"
						       name="files" value="true">
						<span></span>
					</label>
				</div>
			</div>
		</div>
		<button class="amd-admin-button --primary --text" id="cleanup"><?php echo esc_html_x( "Cleanup", "Admin", "material-dashboard" ); ?></button>
	</div>
</div>

<script>
    let _clean_up_str = `<?php echo esc_html_x( "Cleanup", "Admin", "material-dashboard" ) ?>`;
    (function () {
        let $card = $("#card-cleanup"), $btn = $("#cleanup");
        let $all = $("#opt-all");
        $all.change(function () {
            $card.find('[id*="opt-"]').prop("checked", $(this).is(":checked"));
        });
        $("._opt_delete").on("change", function(){
            $all.prop("checked", $("._opt_delete:not(:checked)").length <= 0);
        });

        $btn.click(function () {
            let lastHtml = $btn.html();
            $btn.html(_t("wait_td"));
            $btn.blur();
            $card.cardLoader();
            let options = [];
            $("._opt_delete").each(function () {
                let $input = $(this);
                if ($input.is(":checked"))
                    options.push($input.attr("name") || null);
            });
            network.clean();
            network.put("_cleanup", options.join(","));
            network.on.end = (resp, error) => {
                $card.cardLoader(false);
                $btn.html(lastHtml);
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
        });
    }());
</script>
