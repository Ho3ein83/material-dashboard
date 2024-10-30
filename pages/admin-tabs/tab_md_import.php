<?php

global $amdDB;

$variants = $amdDB->getExportVariants();

?><!-- Import -->
<div class="amd-admin-card text-center --setting-card" id="import-card">
    <h3 class="--title"><?php echo esc_html_x( "Restore", "Admin", "material-dashboard" ); ?></h3>
    <label class="amd-admin-button">
        <span><?php echo esc_html_x( "Browse", "Admin", "material-dashboard" ); ?></span>
        <input type="file" id="import-file" accept=".json,.zip" style="display:none">
    </label>
    <p class="size-sm color-primary" id="selected-file"></p>
    <div style="font-size: 15px" dir="auto" id="file-log" class="color-red"></div>
    <div id="show-on-import" style="display:none" class="mt-10">
        <label class="hb-switch">
            <input type="checkbox" role="switch" name="keep_old" id="import-keep-old">
            <span><?php echo esc_html_x( "Do not overwrite existing options", "Admin", "material-dashboard" ); ?></span>
        </label>
        <div class="mt-20"></div>
        <button class="amd-admin-button" id="import"><?php echo esc_html_x( "Submit", "Admin", "material-dashboard" ); ?></button>
        <button class="amd-admin-button __clear_import --primary --text"><?php echo esc_html_x( "Dismiss", "Admin", "material-dashboard" ); ?></button>
        <div class="h-20"></div>
    </div>
</div>

<script>
    (function() {
        let $card = $("#import-card");
        let $import = $("#import"), $file = $("#import-file");
        let $state = $("#selected-file"), $log = $("#file-log");
        let $switch = $("#import-keep-old"), $showOn = $("#show-on-import");
        let importData = null;
        let clear = () => {
            $log.html("");
            $state.html(_t("no_file_selected") + ` <span dir="auto">(.json, .zip)</span>`);
            $showOn.fadeOut(0);
        }
        clear();
        $(".__clear_import").click(() => clear());
        $file.on("change", function(e) {
            let file = e.target.files;
            if(file.length <= 0)
                return;
            file = file[0];
            let fileType = file.type;
            if(fileType === "application/json") {
                let reader = new FileReader();
                $state.html(file.name);
                reader.addEventListener("load", d => {
                    let text = d.currentTarget.result;
                    importData = file;
                    $log.html(_t("wait_td"));
                    $log.removeClass("color-red");
                    try {
                        let json = JSON.parse(text);
                        let dataMap = {
                            "date": _t("date"),
                            "author": _t("backup_author"),
                            "version": _t("version"),
                            "from": _t("backup_from")
                        };
                        let optionsMap = {
							<?php
							foreach( $variants as $id => $variant )
								echo "\"$id\": `" . amd_destroy_html_tag( ( $variant["title"] ?? "" ), "a" ) . "`,";
							?>
                        };
                        let log = "";
                        let changes = [];
                        for(let [key, text] of Object.entries(dataMap)) {
                            if(typeof json[key] !== "undefined")
                                log += `<p>${text}: ${json[key]}</p>`;
                        }
                        for(let [key, text] of Object.entries(optionsMap)) {
                            if(typeof json[key] !== "undefined") changes.push(text);
                        }
                        let _changes = "<div style='background:rgba(var(--amd-primary-rgb),.2);color:var(--amd-primary);padding:8px 16px;border-radius:7px'>" + _t("backup_includes") + "<br>";
                        _changes += "<div style='display:flex;margin:8px;flex-wrap:wrap;align-items:center;justify-content:center'>";
                        for(let i = 0; i < changes.length; i++) {
                            _changes += `<span style="background:var(--amd-primary);color:#fff;padding:4px 10px;border-radius:4px;display:block;margin:8px">${changes[i]}</span>`;
                        }
                        _changes += "</div></div>";
                        $log.removeClass("color-red");
                        $log.html(log + "<br>" + _changes);
                        $showOn.fadeIn();
                    } catch(e) {
                        $log.html(_t("file_invalid") + ".");
                        $log.addClass("color-red");
                        console.log("Selected JSON file is not a valid settings file or a syntax error has occurred.", e);
                        importData = null;
                    }
                })
                reader.readAsText(file);
            }
            else {
                clear();
                $state.html(file.name);
                $log.addClass("color-red");
                $log.html(`<?php _ex( "Your selected backup file is a zip archive and contains your site backup files. However we are not sure it's a valid file, if you are sure that this file is safe upload it and if it contains your file backup will be started", "Admin", "material-dashboard" ); ?>`);
                $showOn.fadeIn();
                importData = file;
            }
        });

        $import.click(function() {

            if(importData instanceof File) {
                let lastHtml = $import.html();
                let formData = new FormData();
                formData.append("action", amd_conf.ajax.private);
                formData.append("_import_zip", importData, importData.name);
                formData.append("overwrite", !$switch.is("checked"));
                let handleUploadProgress = e => {
                    var percent = 0;
                    var position = e.loaded || e.position;
                    var total = e.total;
                    if(e.lengthComputable) percent = Math.ceil(position / total * 100);
                    $import.html(_t("uploading") + ` ${percent}%`);
                }
                $card.cardLoader();
                send_ajax_opt({
                    xhr: function() {
                        let myXhr = $.ajaxSettings.xhr();
                        if(myXhr.upload)
                            myXhr.upload.addEventListener("progress", handleUploadProgress, false);
                        return myXhr;
                    },
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: resp => {
                        console.log(resp);
                        $card.cardLoader(false);
                        $import.html(lastHtml);
                        $amd.alert(_t("import"), resp.data.html || resp.data.msg, {
                            icon: resp.success ? "success" : "error"
                        });
                        if(resp.success)
                            clear();
                    },
                    error: xhr => {
                        $card.cardLoader(false);
                        $import.html(lastHtml);
                        $amd.alert(_t("import"), _t("error"), {
                            icon: "error"
                        });
                        console.log(xhr);
                    }
                });
                return;
            }

            if(!importData) {
                $amd.alert(_t("import"), _t("failed"), {
                    icon: 'error'
                });
                return;
            }

            network.clean();
            network.put("_import", "auto");
            network.put("data", importData);
            network.put("overwrite", !$switch.is("checked"));
            network.on.start = () => $card.cardLoader()
            network.on.end = (resp, error) => {
                if(!error) {
                    $card.cardLoader(false);
                    $amd.alert(_t("import"), resp.data.msg, {
                        icon: resp.success ? 'success' : 'error'
                    });
                    if(resp.success) clear();
                }
                else {
                    $card.cardLoader(false);
                    console.log(xhr)
                }
            }

            network.post();

        });

    }())
</script>