<?php

$current_session = wp_get_session_token();

$thisuser = amd_get_current_user();

global $amdDB;

$reports = $amdDB->readReports( "login", $thisuser->ID );

$unknown = esc_html__( "Unknown", "material-dashboard" );

?>
<div class="amd-table" id="report-list">
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th><?php esc_html_e( "Date", "material-dashboard" ); ?></th>
				<th><?php esc_html_e( "Device", "material-dashboard" ); ?></th>
				<th><?php esc_html_e( "Browser", "material-dashboard" ); ?></th>
				<th><?php esc_html_e( "IP address", "material-dashboard" ); ?></th>
				<th><?php esc_html_e( "Session status", "material-dashboard" ); ?></th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<div class="h-20"></div>
<div class="text-center" id="reports-load-more">
    <button type="button" class="btn btn-lg"><?php esc_html_e( "Load more", "material-dashboard" ); ?></button>
</div>

<script>
    (function(){
        let $list = $("#report-list"), $more = $("#reports-load-more");
        $list.on("click", "[data-destroy-session]", function(){
            let $el = $(this);
            let hash = $el.attr("data-destroy-session");
            let id = $el.hasAttr("data-id", true);
            if(hash && id){
                let $report = $(`[data-login-report="${id}"]`);
                let n = dashboard.createNetwork();
                n.put("destroy_session_by_hash", hash);
                n.on.start = () => {
                    $el.waitHold(_t("wait_td"));
                    $el.blur();
                }
                n.on.end = (resp, error) => {
                    $el.waitRelease(_t("wait_td"));
                    if(!error){
                        if(resp.success){
                            $report.find("[data-status]").fadeOut(0);
                            $report.find('[data-status="inactive"]').fadeIn();
                            $el.remove();
                        }
                        else{
                            $amd.toast(resp.data.msg);
                        }
                    }
                    else{
                        $amd.toast(_t("error"));
                        console.log(resp.xhr);
                    }
                }
                $amd.alert(`<?php esc_html_e( "Destroy session", "material-dashboard" ); ?>`, `<?php esc_html_e( "Are you sure about closing this session? users with that session will be logged out from their account.", "material-dashboard" ); ?>`, {
                    confirmButton: _t("yes"),
                    cancelButton: _t("no"),
                    onConfirm: () => n.post()
                });
            }
        });
        let page_number = 1;

        let reload_reports = (page, onSuccess=()=>{}) => {

            let n = dashboard.createNetwork();
            n.clean();
            n.put("get_login_reports", page);
            n.put("get_html", true);
            n.on.start = () => {
                $amd.toast(_t("wait"));
                $list.cardLoader();
                $more.fadeOut(0);
            }
            n.on.end = (resp, error) => {
                $list.cardLoader(false);
                Hello.closeAll();
                if(!error){
                    if(resp.success){
                        let html = resp.data.html || "";
                        let hasMore = resp.data.has_more || false;
                        if(html)
                            $list.find("tbody").append(html);
                        if(hasMore) $more.fadeIn(0);
                        else $more.fadeOut(0);
                        onSuccess(resp);
                    }
                    else{
                        $amd.toast(resp.data.msg);
                    }
                }
                else{
                    $amd.toast(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();
        }

        $more.click(() => {
            reload_reports(page_number, () => {
                page_number++;
            });
        }).click();

    }());
</script>