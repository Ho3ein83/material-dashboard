<?php

$pages = get_posts( [ "post_type" => "page" ] );

$loginPage = amd_get_site_option( "login_page" );
$dashboardPage = amd_get_site_option( "dashboard_page" );
$apiPage = amd_get_site_option( "api_page" );

$_loginPage = get_post( $loginPage );
$_dashboardPage = get_post( $dashboardPage );
$_apiPage = get_post( $apiPage );

$loginPage_available = amd_post_available( $loginPage );
$dashboardPage_available = amd_post_available( $dashboardPage );
$apiPage_available = amd_post_available( $apiPage );

$loginPage_flag = false;
$dashboardPage_flag = false;
$apiPage_flag = false;

?><h1 class="--title"><?php _ex( "Pages", "Admin", "material-dashboard" ); ?></h1>
<div class="row">
    <div class="col-lg-4 _m">
        <h4><?php _ex( "Login & registration page", "Admin", "material-dashboard" ); ?></h4>
        <select class="wizard-select" id="amd-login-page">
            <option value="" selected style="display: none;"><?php _ex( "Select one", "Admin", "material-dashboard" ); ?></option>
			<?php foreach( $pages as $page ): ?>
				<?php
				if( in_array( $page->ID, [ $loginPage, $dashboardPage, $apiPage ] ) )
					continue;
                $loginPage_flag = true;
                ?>
                <option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo $page->ID == $loginPage ? "selected" : ""; ?>><?php echo wp_trim_words( $page->post_title, 5, "..." ); ?></option>
			<?php endforeach; ?>
	        <?php if( $loginPage_available ): ?>
                <option value="<?php echo esc_attr( $loginPage ); ?>" selected><?php echo esc_html( $_loginPage->post_name ); ?></option>
	        <?php endif; ?>
        </select>
		<?php if( !$loginPage_flag ): ?>
            <p class="__login_make-flag tiny-text text-justify"><?php printf( esc_html_x( "You don't have any pages right now, but you can %smake it%s automatically", "Admin", "material-dashboard" ), '<a href="javascript: void(0)" class="--make-login-page tiny-text">', '</a>' ) ?></p>
		<?php else: ?>
            <br>
            <button class="btn btn-text block mt-10 --make-login-page"><?php echo esc_html_x( "make automatically", "Admin", "material-dashboard" ); ?></button>
		<?php endif; ?>
    </div>

    <div class="col-lg-4 _m">
        <h4><?php _ex( "Dashboard page", "Admin", "material-dashboard" ); ?></h4>
        <select class="wizard-select" id="amd-dashboard-page">
            <option value="" selected style="display:none"><?php echo esc_html_x( "Select one", "Admin", "material-dashboard" ); ?></option>
			<?php foreach( $pages as $page ): ?>
				<?php
				if( in_array( $page->ID, [ $loginPage, $dashboardPage, $apiPage ] ) )
					continue;
                $dashboardPage_flag = true;
                ?>
                <option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo $page->ID == $dashboardPage ? "selected" : ""; ?>>
                    <?php echo wp_trim_words( $page->post_title, 5, "..." ); ?>
                </option>
			<?php endforeach; ?>
	        <?php if( $dashboardPage_available ): ?>
                <option value="<?php echo esc_attr( $dashboardPage ); ?>" selected>
                    <?php echo esc_html( $_dashboardPage->post_name ); ?>
                </option>
	        <?php endif; ?>
        </select>
		<?php if( !$dashboardPage_flag ): ?>
            <p class="__dashboard_make-flag tiny-text text-justify"><?php printf( esc_html_x( "You don't have any pages right now, but you can %smake it%s automatically", "Admin", "material-dashboard" ), '<a href="javascript: void(0)" class="--make-dashboard-page tiny-text">', '</a>' ) ?></p>
		<?php else: ?>
            <br>
            <button class="btn btn-text block mt-10 --make-dashboard-page"><?php echo esc_html_x( "make automatically", "Admin", "material-dashboard" ); ?></button>
		<?php endif; ?>
    </div>
</div>
<div class="h-50"></div>
<!-- @formatter off -->
<style>._m{text-align:start !important;margin:auto}._m .wizard-select{width:100%}</style>
<!-- @formatter on -->
<script>
    function _step_2(done) {
        let $dashboardPage = $("#amd-dashboard-page"), $loginPage = $("#amd-login-page");
        let dashboard = $dashboardPage.val(), login = $loginPage.val();
        if(!dashboard || !login){
            done(false);
            return _t("control_fields");
        }
        network.clean();
        network.put("save_options", {
            "dashboard_page": dashboard,
            "login_page": login
        });
        network.on.end = (resp, error) => {
            if(!error){
                if(resp.success){done();}
                else{done(false);$amd.toast(resp.data.msg);}
            }
            else{
                done(false);
                $amd.toast(_t("error"));
            }
        }
        network.post();
    }
    (function() {
        let $dashboardPage = $("#amd-dashboard-page"), $loginPage = $("#amd-login-page");
        function pageCreateRequest(scope, onStart, onEnd, confirm = false) {
            onStart();
            network.clean();
            network.put("make_page", scope);
            if(confirm) network.put("_confirm", confirm);
            network.on.end = (resp, error) => {
                onEnd();
                if(!error) {
                    if(typeof resp.data._confirm !== "undefined") {
                        $amd.alert(_t("pages"), resp.data.msg, {
                            confirmButton: _t("yes"),
                            cancelButton: _t("no"),
                            onConfirm: () => pageCreateRequest(scope, onStart, onEnd, true)
                        });
                        return;
                    }
                    $amd.alert(_t("pages"), resp.data.msg, {
                        icon: resp.success ? "success" : "error"
                    });
                    if(typeof resp.data.page_id !== "undefined" && typeof resp.data.page_title !== "undefined") {
                        let id = resp.data.page_id, title = resp.data.page_title;
                        let url = resp.data.url || null;
                        if(scope === "login") {
                            $loginPage.append(`<option value="${id}">${title}</option>`);
                            $loginPage.val(id);
                        }
                        else if(scope === "dashboard") {
                            $dashboardPage.append(`<option value="${id}">${title}</option>`);
                            $dashboardPage.val(id);
                        }
                        $(`.__${scope}_make-flag`).fadeOut();
                    }
                }
                else {
                    $amd.alert(_t("pages"), _t("error"), {
                        icon: "error"
                    });
                }
            }
            network.post();
        }

        $(document).on("click", ".--make-dashboard-page, .--make-login-page", function() {
            let $btn = $(this);
            let scope;
            if($btn.hasClass("--make-login-page"))
                scope = "login";
            else if($btn.hasClass("--make-dashboard-page"))
                scope = "dashboard";
            if(!scope) return;
            let lastHtml = $btn.html();
            pageCreateRequest(scope, () => {
                $btn.html(_t("wait_td"));
                $btn.setWaiting();
                $btn.blur();
            }, () => {
                $btn.html(lastHtml);
                $btn.setWaiting(false);
            });
        });
    }());
</script>