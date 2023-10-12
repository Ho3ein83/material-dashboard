<?php

$zipAvailable = class_exists( "ZipArchive" );

global $amdDB;

$export_variants = $amdDB->getExportVariants();

?><!-- Backup / Export -->
<div class="amd-admin-card text-center --setting-card" id="export-card">
    <h3 class="--title"><?php echo esc_html_x( "Backup", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <p class="color-primary margin-0"><?php echo esc_html_x( "All of these options are only related to this plugin and will not export other information from your site.", "Admin", "material-dashboard" ); ?></p>
        <div class="__option_grid">
            <div class="-item">
                <div class="-sub-item">
                    <label for="exp-all">
						<?php echo esc_html_x( "Select all", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" id="exp-all">
                        <span></span>
                    </label>
                </div>
            </div>
			<?php foreach( $export_variants as $id => $variant ): ?>
				<?php
                $requirements = $variant["requirements"] ?? [];
                $has_requirements = amd_check_requirements( $requirements );
                ?>
                <div class="-item">
                    <div class="-sub-item<?php echo !$has_requirements ? ' waiting' : ''; ?>">
                        <label for="<?php echo esc_attr( "exp-$id" ); ?>"><?php echo $variant["title"] ?? ""; ?></label>
                    </div>
                    <div class="-sub-item">
                        <?php if( $has_requirements ): ?>
                            <label class="hb-switch">
                                <input type="checkbox" class="_export_opt" role="switch" id="<?php echo esc_attr( "exp-$id" ); ?>"
                                       name="<?php echo esc_attr( "$id" ); ?>" value="true" checked>
                                <span></span>
                            </label>
                        <?php endif; ?>
                    </div>
					<?php if( !$has_requirements ): ?>
                        <div class="-sub-item --full">
                            <p class="color-red ma-8"><?php echo esc_html_x( "There are some requirements for this variant that you have to install them to use this item", "Admin", "material-dashboard" ); ?></p>
							<?php foreach( $requirements as $name => $requirement ): ?>
								<?php if( !amd_check_requirements( $requirement ) ): ?>
                                    <p class="margin-0"><?php echo esc_html( $name ); ?></p>
								<?php endif; ?>
							<?php endforeach; ?>
                        </div>
					<?php endif; ?>
                </div>
			<?php endforeach; ?>
        </div>
        <button class="amd-admin-button --primary --text" id="export"><?php echo esc_html_x( "Submit", "Admin", "material-dashboard" ); ?></button>
    </div>
</div>

<!-- Backup files -->
<div class="amd-admin-card text-center --setting-card" id="backups-card">
    <h3 class="--title">
		<?php echo esc_html_x( "Backup files", "Admin", "material-dashboard" ); ?>
        <button class="amd-admin-button --primary --text --sm __reload_backups"><?php echo esc_html_x( "Refresh", "Admin", "material-dashboard" ); ?></button>
    </h3>
    <div class="--content">
        <p class="margin-0"
           id="no-backup"><?php echo esc_html_x( "There is no backup file in backup directory", "Admin", "material-dashboard" ); ?></p>
        <div class="__option_grid" id="backups-items"></div>
    </div>
</div>

<script>
    (function() {
        let $card = $("#export-card"), $btn = $("#export");
        let $nb = $("#no-backup"), $list = $("#backups-items");
        let $backupCard = $("#backups-card"), $all = $("#exp-all");
        $all.change(function() {
            $card.find('[id*="exp-"]').prop("checked", $(this).is(":checked"));
        });
        $("._export_opt").on("change", function() {
            $all.prop("checked", $("._export_opt:not(:checked)").length <= 0);
        });
        $("._export_opt:first-child").trigger("change");

        let makeFileTemplate = (url, filename, size) => {
            return `<div class="-item" data-backup-file="${filename}"><div class="-sub-item"><span class="_file_" style="background:rgba(var(--amd-text-color-rgb),.2);padding:4px 8px;border-radius:6px" dir="auto">${filename}</span></div><div class="-sub-item"><span class="_size_">${size}</span></div><div class="-sub-item"><a href="${url}" class="amd-admin-button --sm --primary --text" download="${filename}">${_t("download")}</a><a href="javascript:void(0)" class="amd-admin-button mlr-10 --sm --red --text" data-remove-backup="${filename}">${_t("delete")}</a></div></div>`;
        }

        $list.fadeOut(0);
        let reloadBackups = () => {
            $backupCard.cardLoader();
            network.clean();
            network.put("get_backups", "");
            network.on.end = (resp, error) => {
                $backupCard.cardLoader(false);
                if(!error) {
                    if(resp.success) {
                        let data = resp.data;
                        let files = data.files || {};
                        let filesArray = Object.entries(files);
                        if(filesArray.length > 0) {
                            $nb.fadeOut(0);
                            for(let [filename, d] of filesArray) {
                                let $existing = $(`[data-backup-file="${filename}"]`);
                                if($existing.length > 0)
                                    $existing.remove();
                                $list.prepend(makeFileTemplate(d.url || "#", filename, d.size || ""));
                            }
                            $list.fadeIn();
                        }
                        else {
                            $list.fadeOut(0);
                            $nb.fadeIn();
                        }
                    }
                    else {
                        $amd.alert(_t("backup_files"), resp.data.msg, {
                            icon: "error"
                        });
                    }
                }
                else {
                    $amd.alert(_t("backup_files"), _t("error"), {
                        icon: "error"
                    });
                }
            }
            network.post();
        }
        reloadBackups();
        $(".__reload_backups").click(() => reloadBackups());

        $(document).on("click", "[data-remove-backup]", function() {
            let $el = $(this);
            let file = $el.hasAttr("data-remove-backup", true);
            if(file) {
                let del = () => {
                    $backupCard.cardLoader();
                    network.clean();
                    network.put("remove_backup", file);
                    network.on.end = (resp, error) => {
                        $backupCard.cardLoader(false);
                        if(!error) {
                            if(resp.success) {
                                $(`[data-backup-file="${file}"]`).removeSlow();
                                setTimeout(() => {
                                    if($("[data-backup-file]").length <= 0) {
                                        $list.fadeOut(0);
                                        $nb.fadeIn();
                                    }
                                }, 800);
                            }
                            else {
                                $amd.alert(_t("backup_files"), resp.data.msg, {
                                    icon: "error"
                                });
                            }
                        }
                        else {
                            $amd.alert(_t("delete"), _t("error"), {
                                icon: "error"
                            });
                        }
                    }
                    network.post();
                }
                $amd.alert(_t("delete_backup_files"), _t("delete_confirm"), {
                    confirmButton: _t("yes"),
                    cancelButton: _t("no"),
                    onConfirm: () => del()
                });
            }
        });

        $btn.click(function() {

            let lastHtml = $btn.html();
            $btn.html(_t("wait_td"));
            $btn.blur();
            $card.cardLoader();

            let options = [];
            $("._export_opt").each(function() {
                let $input = $(this);
                if($input.is(":checked"))
                    options.push($input.attr("name") || null);
            });

            network.clean();
            network.put("_export", options.join(","));
            network.on.end = (resp, error) => {
                $card.cardLoader(false);
                $btn.html(lastHtml);
                if(!error) {
                    if(resp.success) {
                        let data = resp.data;
                        let url = data.url || "javascript:void(0)";
                        let filename = data.filename || "***";
                        let size = data.size || "";
                        $nb.fadeOut();
                        reloadBackups();
                        $amd.alert(_t("backup"), resp.data.msg, {
                            icon: "success",
                            cancelButton: _t("close"),
                            confirmButton: _t("download"),
                            onConfirm: () => $amd.download(url, filename)
                        });
                    }
                    else {
                        $amd.alert(_t("backup"), resp.data.msg, {
                            icon: "error"
                        });
                    }
                }
                else {
                    $amd.alert(_t("backup"), _t("error"), {
                        icon: "error"
                    });
                    console.log(resp.xhr);
                }
            }
            network.post();
        });
    }());
</script>
