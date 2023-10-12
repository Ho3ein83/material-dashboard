<?php

$indexing = amd_get_site_option( "indexing", "no-index" );
$forceSSL = amd_get_site_option( "force_ssl", "false" );
$avatarUpload = amd_get_site_option( "avatar_upload_allowed", "true" );
$changeEmail = amd_get_site_option( "change_email_allowed", "false" );
$useLazyLoading = amd_get_site_option( "use_lazy_loading", "true" );

$defaultSettings = array(
	"indexing" => $indexing,
	"force_ssl" => $forceSSL,
	"change_email_allowed" => $changeEmail,
	"avatar_upload" => $avatarUpload,
	"use_lazy_loading" => $useLazyLoading
);

?><h1 class="--title"><?php _ex( "General settings", "Admin", "material-dashboard" ); ?></h1>
<div class="row">
    <!-- Seo -->
    <div class="col-lg-9 margin-auto">
        <div class="amd-card bg-x-low-im">
            <h3 class="--title"><?php _ex( "Seo", "Admin", "material-dashboard" ); ?></h3>
            <div class="--content">
                <div class="__option_grid">
                    <div class="-item">
                        <div class="-sub-item">
                            <label for="indexing">
								<?php _ex( "No index", "Admin", "material-dashboard" ); ?>
                            </label>
                        </div>
                        <div class="-sub-item">
                            <label class="hb-switch">
                                <input type="checkbox" role="switch" name="indexing" value="no-index" id="indexing">
                                <span></span>
                            </label>
                        </div>
                        <div class="-sub-item --full">
                            <p class="color-primary text-justify"><?php _ex( "If you enable this item dashboard page will not show in search engines results, however it's not recommended to let dashboard page be in search results.", "Admin", "material-dashboard" ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Force SSL -->
    <div class="col-lg-9 margin-auto">
        <div class="amd-card bg-x-low-im">
            <h3 class="--title"><?php _ex( "URL and addresses", "Admin", "material-dashboard" ); ?></h3>
            <div class="--content">
                <div class="__option_grid">
                    <div class="-item">
                        <div class="-sub-item">
                            <label for="force-ssl">
								<?php _ex( "Force SSL", "Admin", "material-dashboard" ); ?>
                            </label>
                        </div>
                        <div class="-sub-item">
                            <label class="hb-switch">
                                <input type="checkbox" role="switch" name="force_ssl" value="true" id="force-ssl">
                                <span></span>
                            </label>
                        </div>
                        <div class="-sub-item --full">
                            <p class="color-primary text-justify"><?php _ex( "Force clients to use https instead of http. Please consider that this plugin will not change your .htaccess file ant it's only a WordPress redirect", "Admin", "material-dashboard" ); ?>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Allow avatar image upload -->
    <div class="col-lg-9 margin-auto">
        <div class="amd-card bg-x-low-im">
            <h3 class="--title"><?php _ex( "Avatar image", "Admin", "material-dashboard" ); ?></h3>
            <div class="--content">
                <div class="__option_grid">
                    <div class="-item">
                        <div class="-sub-item">
                            <label for="avatar-upload">
								<?php _ex( "Allow avatar image upload", "Admin", "material-dashboard" ); ?>
                            </label>
                        </div>
                        <div class="-sub-item">
                            <label class="hb-switch">
                                <input type="checkbox" role="switch" name="avatar_upload" value="true"
                                       id="avatar-upload">
                                <span></span>
                            </label>
                        </div>
                        <div class="-sub-item --full">
                            <p class="color-primary text-justify"><?php _ex( "Let users to upload their custom avatar picture. Avatar pictures will be cropped in 1:1 ratio (square image) and they will be converted to png to prevent users to upload harmful or broken files", "Admin", "material-dashboard" ); ?>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fast loading & content settings -->
    <div class="col-lg-9 margin-auto">
        <div class="amd-card bg-x-low-im">
            <h3 class="--title"><?php _ex( "Content", "Admin", "material-dashboard" ); ?></h3>
            <div class="--content">
                <div class="__option_grid">
                    <div class="-item">
                        <div class="-sub-item">
                            <label for="fast-loading">
								<?php _ex( "Use fast loading", "Admin", "material-dashboard" ); ?>
                            </label>
                        </div>
                        <div class="-sub-item">
                            <label class="hb-switch">
                                <input type="checkbox" role="switch" name="use_lazy_loading" value="true"
                                       id="fast-loading">
                                <span></span>
                            </label>
                        </div>
                        <div class="-sub-item --full">
                            <p class="color-primary text-justify"><?php _ex( "By enabling fast loading dashboard pages will not be refreshed and contents will be loaded with AJAX. This item can reduce your site traffic and users data usage, also makes your dashboard faster", "Admin", "material-dashboard" ); ?>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Change email -->
    <div class="col-lg-9 margin-auto">
        <div class="amd-card bg-x-low-im">
            <h3 class="--title"><?php _ex( "Change email", "Admin", "material-dashboard" ); ?></h3>
            <div class="--content">
                <div class="__option_grid">
                    <div class="-item">
                        <div class="-sub-item">
                            <label for="change-email">
								<?php _ex( "Allow users to change their email", "Admin", "material-dashboard" ); ?>
                            </label>
                        </div>
                        <div class="-sub-item">
                            <label class="hb-switch">
                                <input type="checkbox" role="switch" name="change_email_allowed" value="true"
                                       id="change-email">
                                <span></span>
                            </label>
                        </div>
                        <div class="-sub-item --full">
                            <p class="color-primary text-justify"><?php _ex( "By changing email, users downloads and purchases won't be lost. If you are using 'Easy Digital Downloads' or 'WooCommerce' plugins users data won't be lost, however we can't say that about your other plugins, please make sure that your site operations are not depended on their emails.", "Admin", "material-dashboard" ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)

    function _step_6(done) {

        network.clean();
        let $indexing = $("#indexing");
        let $forceSSL = $("#force-ssl");
        let $avatarUpload = $('#avatar-upload');
        let $changeEmail = $('#change-email');
        let $useLazyLoading = $('#fast-loading');
        network.put("save_options", {
            "indexing": $indexing.is(":checked") ? "no-index" : "index",
            "force_ssl": $forceSSL.is(":checked") ? "true" : "false",
            "change_email_allowed": $changeEmail.is(":checked") ? "true" : "false",
            "avatar_upload_allowed": $avatarUpload.is(":checked") ? "true" : "false",
            "use_lazy_loading": $useLazyLoading.is(":checked") ? "true" : "false"
        });
        network.on.end = (resp, error) => {
            if(!error) {
                if(resp.success) {
                    done();
                }
                else {
                    done(false);
                    $amd.toast(resp.data.msg);
                }
            }
            else {
                done(false);
                $amd.toast(_t("error"));
            }
        }
        network.post();
    }
</script>