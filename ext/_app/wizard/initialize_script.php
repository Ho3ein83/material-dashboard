<?php

global /** @var AMD_DB $amdDB */
$amdDB;

$APIPage = intval( amd_get_site_option( "api_page" ) );
$_apiPost = get_post( $APIPage );
$API_PAGE_OK = !empty( $_apiPost ) ? $_apiPost->post_status == "publish" : null;

$db_installed = $amdDB->isInstalled();

?><script>
    let $screen = $("#screen");
    $screen.fadeOut(0);
    var install_db = `<?php echo $db_installed ? 'no' : 'yes'; ?>`,
        add_api_page = `<?php echo $API_PAGE_OK ? 'no' : 'yes'; ?>`;

    function _install_db(onComplete) {
        Hello.closeAll();
        Hello.fire({
            title: `<?php _ex( "Database", "Admin", "material-dashboard" ); ?>`,
            text: `<?php _ex( "Installing database", "Admin", "material-dashboard" ); ?>`,
            showLoader: true,
            confirmButton: false,
            cancelButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false
        });
        send_ajax({
                action: amd_conf.ajax.private,
                repair_db: true
            },
            resp => onComplete(resp, false),
            (xhr, options, error) => onComplete({xhr, options, error}, true)
        );
    }

    function _add_api_page(onComplete) {
        Hello.closeAll();
        Hello.fire({
            title: `<?php _ex( "Pages", "Admin", "material-dashboard" ); ?>`,
            text: `<?php _ex( "Creating required pages", "Admin", "material-dashboard" ); ?>`,
            showLoader: true,
            confirmButton: false,
            cancelButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false
        });
        send_ajax({
                action: amd_conf.ajax.private,
                make_page: "api",
                _confirm: true,
                _confirm_replace: true
            },
            resp => onComplete(resp, false),
            (xhr, options, error) => onComplete({xhr, options, error}, true)
        );
    }

    let queue = [], queue_flag = 0, queue_index = 0;
    if (install_db === "yes"){
        queue[queue_flag] = _install_db;
        queue_flag++;
    }
    if (add_api_page === "yes"){
        queue[queue_flag] = _add_api_page;
        queue_flag++;
    }
    var openQueue = () => {
        if (typeof queue[queue_index] !== "function"){
            Hello.closeAll();
            $screen.fadeIn();
            location.reload();
            return;
        }
        let failed = msg => {
            $amd.alert(msg, "", {
                confirmButton: false,
                cancelButton: _t("back"),
                onConfirm: () => location.reload(),
                onCancel: () => backToAdmin()
            });
        }
        queue[queue_index]((resp, error) => {
            if (!error) {
                if (resp.success) {
                    queue_index++;
                    openQueue();
                }
                else {
                    failed(resp.data.msg);
                }
            }
            else {
                failed(_t("error"));
            }
        });
    }
    openQueue();
</script>