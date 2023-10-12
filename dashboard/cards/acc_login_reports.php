<?php

/**
 * Max items to display in home page
 * @since 1.0.5
 */
$per_page = apply_filters( "amd_login_reports_home_max_items_to_display", 5 );

if( $per_page <= 0 )
	return;

?>

<?php ob_start(); ?>
<div class="text-center">
	<h3>
		<?php esc_html_e( "Login reports", "material-dashboard" ) ?>
		<button type="button" class="btn btn-text btn-sm --low _home_reload_login_reports_" style="display:none"><?php esc_html_e( "Refresh", "material-dashboard" ); ?></button>
	</h3>
</div>
<div class="amd-table" id="home-report-list" style="display:none">
	<table>
		<thead>
		<tr>
			<th><?php esc_html_e( "Date", "material-dashboard" ); ?></th>
			<th><?php esc_html_e( "Browser", "material-dashboard" ); ?></th>
			<th><?php esc_html_e( "IP address", "material-dashboard" ); ?></th>
		</tr>
		</thead>
		<tbody class="-table-items"></tbody>
	</table>
</div>
<h4 class="text-center" id="login-reports-empty"><?php esc_html_e( "There is no item to show", "material-dashboard" ); ?></h4>
<div class="text-center" id="login-reports-load-more" style="display:none">
	<div class="h-20"></div>
	<a href="?void=account&report=logins" data-turtle="lazy" class="btn btn-text --low"><?php esc_html_e( "See more", "material-dashboard" ); ?></a>
</div>
<?php $reports_html_content = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
	"title" => "",
	"content" => $reports_html_content,
	"type" => "content_card",
	"color" => "orange",
	"_id" => "home-login-reports-card"
) ); ?>

<script>
    (function(){
        let $list = $("#home-report-list"), $card = $("#home-login-reports-card"),
	        $empty = $("#login-reports-empty"), $more = $("#login-reports-load-more");

        let reload_reports = () => {
            let n = dashboard.createNetwork();
            n.clean();
            n.put("get_login_reports", 1);
            n.put("per_page", parseInt(`<?php echo $per_page; ?>`));
            n.on.start = () => {
                $card.cardLoader();
            }
            n.on.end = (resp, error) => {
                $card.cardLoader(false);
                Hello.closeAll();
                if(!error){
                    if(resp.success){
                        let results = resp.data.results || "";
                        let entries = Object.entries(results);
                        let hasMore = resp.data.has_more || false;
                        if(entries.length){
                            $empty.fadeOut(0);
                            $list.fadeIn(0);
                        }
                        else{
                            $list.fadeOut(0);
                            $empty.fadeIn(0);
                        }
                        if(hasMore) $more.fadeIn(0);
                        else $more.fadeOut(0);
                        let html = "";
                        for(let [key, item] of entries){
                            let {id, browser, ip, jfy, hi} = item;
                            if(id){
                                html += `<tr data-login-report="${id}">
<td>
	<span>${jfy}</span>
    <br>
	<span class="tiny-text color-low">${hi}</span>
</td>
<td>${browser.name || "-"}</td>
<td>${ip}</td>
</tr>`;
                            }
                        }
                        $list.find(".-table-items").html(html);
                    }
                    else{
                        $amd.toast(resp.data.msg);
                    }
                }
                else{
                    console.log(resp.xhr);
                }
            }
            n.post();
        }
        reload_reports();

        $card.on("click", "._home_reload_login_reports_", () => reload_reports());
    }());
</script>
