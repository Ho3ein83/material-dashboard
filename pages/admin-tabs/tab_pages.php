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

?><!-- @formatter off -->
<style>.--copy-alt{display:inline-block;width:20px;height:20px}.--copy-alt>._icon_{width:20px;height:20px}</style>
<!-- @formatter on -->
<!-- Login & registration page -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "Login & registration page", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
			<?php if( count( $pages ) > 0 ): ?>
                <div class="-item">
                    <div class="-sub-item">
                        <label for="amd-login-page">
							<?php echo esc_html_x( "Select", "Admin", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <select class="amd-admin-select" id="amd-login-page">
                            <option value="" selected
                                    style="display: none;"><?php echo esc_html_x( "Select one", "Admin", "material-dashboard" ); ?></option>
							<?php foreach( $pages as $page ): ?>
								<?php
								if( in_array( $page->ID, [ $dashboardPage, $apiPage, $loginPage ] ) )
									continue;
								$loginPage_flag = true;
								?>
                                <option value="<?php echo esc_attr( "$page->ID" ); ?>" <?php echo $page->ID == $loginPage ? "selected" : ""; ?>><?php echo esc_html( wp_trim_words( $page->post_title, 5, "..." ) ); ?></option>
							<?php endforeach; ?>
                            <?php if( $loginPage_available ): ?>
                                <option value="<?php echo esc_attr( $loginPage ); ?>" selected><?php echo esc_html( $_loginPage->post_name ); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="-item">
                    <div class="-sub-item">
                        <a href="javascript: void(0)"
                           class="amd-admin-button --make-login-page"><?php echo esc_html_x( "make automatically", "Admin", "material-dashboard" ); ?></a>
                    </div>
                    <div class="-sub-item">
						<?php if( $loginPage_available ): ?>
                            <a href="<?php echo esc_url( get_permalink( $loginPage ) ); ?>"
                               class="amd-admin-button --primary --text"
                               target="_blank" data-amd-url="login"><?php echo esc_html_x( "view", "Admin", "material-dashboard" ); ?></a>
						<?php else: ?>
                            <a href="javascript: void(0)" class="amd-admin-button --primary --text" target="_blank"
                               data-amd-url="login"
                               style="display:none"><?php echo esc_html_x( "view", "Admin", "material-dashboard" ); ?></a>
						<?php endif; ?>
                    </div>
                </div>
			<?php endif; ?>
			<?php if( !$loginPage_flag ): ?>
                <p class="__login_make-flag"><?php printf( esc_html_x( "You don't have any pages right now, but you can %smake it%s automatically", "Admin", "material-dashboard" ), '<a href="javascript: void(0)" class="--make-login-page">', '</a>' ) ?></p>
			<?php endif; ?>
            <p class="color-primary"><?php printf( esc_html_x( "You can also use %s shortcode", "Admin", "material-dashboard" ), "<code>[amd-login]</code>" ); ?>
                <span class='--copy-alt clickable' data-copy="[amd-login]"
                      title="<?php esc_html_e( "Copy", "material-dashboard" ); ?>"><?php echo amd_icon( "copy" ); ?></span></p>
        </div>
    </div>
</div>

<!-- Dashboard page -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "Dashboard page", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
			<?php if( count( $pages ) > 0 ): ?>
                <div class="-item">
                    <div class="-sub-item">
                        <label for="amd-dashboard-page">
							<?php echo esc_html_x( "Select", "Admin", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <select class="amd-admin-select" id="amd-dashboard-page">
                            <option value="" selected
                                    style="display:none"><?php echo esc_html_x( "Select one", "Admin", "material-dashboard" ); ?></option>
							<?php foreach( $pages as $page ): ?>
								<?php
								if( in_array( $page->ID, [ $dashboardPage, $loginPage, $apiPage ] ) )
									continue;
								$dashboardPage_flag = true;
								?>
                                <option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo $page->ID == $dashboardPage ? "selected" : ""; ?>><?php echo esc_html( wp_trim_words( $page->post_title, 5, "..." ) ); ?></option>
							<?php endforeach; ?>
	                        <?php if( $dashboardPage_available ): ?>
                                <option value="<?php echo esc_attr( $dashboardPage ); ?>" selected><?php echo esc_html( $_dashboardPage->post_name ); ?></option>
	                        <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="-item">
                    <div class="-sub-item">
                        <a href="javascript: void(0)"
                           class="amd-admin-button --make-dashboard-page"><?php echo esc_html_x( "make automatically", "Admin", "material-dashboard" ); ?></a>
                    </div>
                    <div class="-sub-item">
						<?php if( $dashboardPage_available ): ?>
                            <a href="<?php echo esc_url( get_permalink( $dashboardPage ) ); ?>"
                               class="amd-admin-button --primary --text" target="_blank"
                               data-amd-url="dashboard"><?php echo esc_html_x( "view", "Admin", "material-dashboard" ); ?></a>
						<?php else: ?>
                            <a href="javascript: void(0)" class="amd-admin-button --primary --text" target="_blank"
                               data-amd-url="dashboard"
                               style="display:none"><?php echo esc_html_x( "view", "Admin", "material-dashboard" ); ?></a>
						<?php endif; ?>
                    </div>
                </div>
			<?php endif; ?>
			<?php if( !$dashboardPage_flag ): ?>
                <p class="__dashboard_make-flag"><?php printf( esc_html_x( "You don't have any pages right now, but you can %smake it%s automatically", "Admin", "material-dashboard" ), '<a href="javascript: void(0)" class="--make-dashboard-page">', '</a>' ) ?></p>
			<?php endif; ?>
            <p class="color-primary"><?php printf( esc_html_x( "You can also use %s shortcode", "Admin", "material-dashboard" ), "<code>[amd-dashboard]</code>" ); ?>
                <span class='--copy-alt clickable' data-copy="[amd-dashboard]"
                      title="<?php esc_html_e( "Copy", "material-dashboard" ); ?>"><?php echo amd_icon( "copy" ); ?></span></p>
        </div>
    </div>
</div>

<!-- API page -->
<div class="amd-admin-card --setting-card">
    <h3 class="--title"><?php echo esc_html_x( "API page", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <div class="__option_grid">
			<?php if( count( $pages ) > 0 ): ?>
                <div class="-item">
                    <div class="-sub-item">
                        <label for="amd-api-page">
							<?php echo esc_html_x( "Select", "Admin", "material-dashboard" ); ?>
                        </label>
                    </div>
                    <div class="-sub-item">
                        <select class="amd-admin-select" id="amd-api-page">
                            <option value="" selected
                                    style="display:none"><?php echo esc_html_x( "Select one", "Admin", "material-dashboard" ); ?></option>
							<?php foreach( $pages as $page ): ?>
								<?php
								if( in_array( $page->ID, [ $loginPage, $dashboardPage, $apiPage ] ) )
									continue;
								$apiPage_flag = true;
								?>
                                <option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo $page->ID == $apiPage ? "selected" : ""; ?>><?php echo esc_html( wp_trim_words( $page->post_title, 5, "..." ) ); ?></option>
							<?php endforeach; ?>
	                        <?php if( $apiPage_available ): ?>
                                <option value="<?php echo esc_attr( $apiPage ); ?>" selected><?php echo esc_html( $_apiPage->post_name ); ?></option>
	                        <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="-item">
                    <div class="-sub-item">
                        <a href="javascript: void(0)"
                           class="amd-admin-button --make-api-page"><?php echo esc_html_x( "make automatically", "Admin", "material-dashboard" ); ?></a>
                    </div>
                    <div class="-sub-item">
						<?php if( $dashboardPage_available ): ?>
                            <a href="<?php echo esc_url( get_permalink( $dashboardPage ) ); ?>"
                               class="amd-admin-button --primary --text" target="_blank"
                               data-amd-url="dashboard"><?php echo esc_html_x( "view", "Admin", "material-dashboard" ); ?></a>
						<?php else: ?>
                            <a href="javascript: void(0)" class="amd-admin-button --primary --text" target="_blank"
                               data-amd-url="dashboard"
                               style="display:none"><?php echo esc_html_x( "view", "Admin", "material-dashboard" ); ?></a>
						<?php endif; ?>
                    </div>
                </div>
			<?php endif; ?>
			<?php if( !$apiPage_flag ): ?>
                <p class="__login_make-flag"><?php printf( esc_html_x( "You don't have any pages right now, but you can %smake it%s automatically", "Admin", "material-dashboard" ), '<a href="javascript: void(0)" class="--make-api-page">', '</a>' ) ?></p>
			<?php endif; ?>
            <p class="color-primary"><?php printf( esc_html_x( "You can also use %s shortcode", "Admin", "material-dashboard" ), "<code>[amd-api]</code>" ); ?>
                <span class='--copy-alt clickable' data-copy="[amd-api]"
                      title="<?php esc_html_e( "Copy", "material-dashboard" ); ?>"><?php echo amd_icon( "copy" ); ?></span></p>
        </div>
    </div>
</div>

<?php
    /**
     * After admin pages settings
     * @since 1.0.4
     */
    do_action( "amd_admin_settings_after_pages_setup", $pages );
?>

<script>
    $(document).on("click", "[data-copy]", function() {
        let text = $(this).hasAttr("data-copy", true);
        if(text) $amd.copy(text, false, true);
    });
    (function() {
        let $dashboardPage = $("#amd-dashboard-page"), $loginPage = $("#amd-login-page");
        let $apiPage = $("#amd-api-page");

        $amd.addEvent("on_settings_saved", () => {
            return {
                "dashboard_page": $dashboardPage.val(),
                "login_page": $loginPage.val(),
                "api_page": $apiPage.val(),
            };
        });

        function pageCreateRequest(scope, onStart = () => {}, onEnd = () => {}, confirm = false) {
            onStart();
            network.clean();
            network.put("make_page", scope);
            if(confirm)
                network.put("_confirm", confirm);
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
                        else if(scope === "api") {
                            $apiPage.append(`<option value="${id}">${title}</option>`);
                            $apiPage.val(id);
                        }
                        else{
                            let $p = $(`[data-custom-page-select="${scope}"]`);
                            $p.append(`<option value="${id}">${title}</option>`);
                            $p.val(id);
                        }
                        if(url) {
                            let $a = $(`[data-amd-url="${scope}"]`);
                            $a.attr("href", url);
                            $a.fadeIn();
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

        $(document).on("click", ".--make-dashboard-page, .--make-login-page, .--make-api-page", function() {
            let $btn = $(this);
            let scope = "";
            if($btn.hasClass("--make-login-page"))
                scope = "login";
            else if($btn.hasClass("--make-dashboard-page"))
                scope = "dashboard";
            else if($btn.hasClass("--make-api-page"))
                scope = "api";
            if(scope.length <= 0)
                return;
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

        $(document).on("click", "[data-create-custom-page]", function() {
            let $btn = $(this);
            let scope = $(this).attr("data-create-custom-page");
            if(scope.length <= 0)
                return;
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