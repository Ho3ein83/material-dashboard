<?php

$optimizer = amd_get_site_option( "optimizer", "true" );
$indexing = amd_get_site_option( "indexing", "no-index" );
$forceSSL = amd_get_site_option( "force_ssl", "false" );
$avatarUpload = amd_get_site_option( "avatar_upload_allowed", "true" );
$changeEmail = amd_get_site_option( "change_email_allowed", "false" );
$useLazyLoading = amd_get_site_option( "use_lazy_loading", "true" );
$tera_wallet_support = amd_get_site_option( "tera_wallet_support", "true" );

$tera_wallet_available = function_exists( "woo_wallet" );
if( !$tera_wallet_available )
    $tera_wallet_support = "false";

$defaultSettings = array(
	"optimizer" => $optimizer,
	"indexing" => $indexing,
	"force_ssl" => $forceSSL,
	"change_email_allowed" => $changeEmail,
	"avatar_upload" => $avatarUpload,
	"use_lazy_loading" => $useLazyLoading,
	"tera_wallet_support" => $tera_wallet_support,
);

?>

<?php
/** @since 1.2.1 */
do_action( "amd_settings_before_all_cards" );
?>

<!-- Optimizer -->
<div class="amd-admin-card --setting-card" data-ts="optimizer">
    <?php amd_dump_admin_card_keywords( ["optimize", "بهینه", "بهینه‌ساز"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Optimizer", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_optimizer_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_optimizer_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="optimizer">
						<?php echo esc_html_x( "Enable dashboard optimizer", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="optimizer" value="true" id="optimizer">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "By enabling this item, WordPress header won't be printed in dashboard pages. This can make dashboard performance better but your custom scripts, theme styles, page builders resources, etc. will not work in dashboard anymore.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_optimizer_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_optimizer_content" );
        ?>
    </div>
</div>

<!-- Seo -->
<div class="amd-admin-card --setting-card" data-ts="seo">
    <?php amd_dump_admin_card_keywords( ["سیو", "google", "گوگل", "search engine", "موتور جستجو"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Seo", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_seo_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_seo_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="indexing">
						<?php echo esc_html_x( "No index", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="indexing" value="no-index" id="indexing">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "If you enable this item dashboard page will not show in search engines results, however it's not recommended to let dashboard page be in search results.", "Admin", "material-dashboard" ); ?></p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_seo_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_seo_content" );
        ?>
    </div>
</div>

<!-- URL & addresses -->
<div class="amd-admin-card --setting-card" data-ts="ssl">
    <?php amd_dump_admin_card_keywords( ["secure", "امن", "امنیت", "security"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "URL and addresses", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_url_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_url_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="force-ssl">
						<?php echo esc_html_x( "Force SSL", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="force_ssl" value="true" id="force-ssl">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "Force clients to use https instead of http. Please consider that this plugin will not change your .htaccess file ant it's only a WordPress redirect", "Admin", "material-dashboard" ); ?>.</p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_url_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_url_content" );
        ?>
    </div>
</div>

<!-- Allow avatar image upload -->
<div class="amd-admin-card --setting-card" data-ts="avatar">
    <?php amd_dump_admin_card_keywords( ["پروفایل", "profile", "picture"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Avatar image", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_avatar_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_avatar_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="avatar-upload">
						<?php echo esc_html_x( "Allow avatar image upload", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="avatar_upload" value="true" id="avatar-upload">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "Let users to upload their custom avatar picture. Avatar pictures will be cropped in 1:1 ratio (square image) and they will be converted to png to prevent users to upload harmful or broken files", "Admin", "material-dashboard" ); ?>.</p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_avatar_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_avatar_content" );
        ?>
    </div>
</div>

<!-- Content settings -->
<div class="amd-admin-card --setting-card" data-ts="lazyload">
    <?php amd_dump_admin_card_keywords( ["load", "lazy loading", "بارگیری", "optimize", "optimizer", "بهینه", "بهینه ساز"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Content", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_content_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_content_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="fast-loading">
						<?php echo esc_html_x( "Use fast loading", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="use_lazy_loading" value="true" id="fast-loading">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "By enabling fast loading dashboard pages will not be refreshed and contents will be loaded with AJAX. This item can reduce your site traffic and users data usage, also makes your dashboard faster", "Admin", "material-dashboard" ); ?>.</p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_content_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_content_content" );
        ?>
    </div>
</div>

<!-- Change email -->
<div class="amd-admin-card --setting-card" data-ts="change_email">
    <?php amd_dump_admin_card_keywords( ["email"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Change email", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_email_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_email_items" );
            ?>
            <div class="-item">
                <div class="-sub-item">
                    <label for="change-email">
						<?php echo esc_html_x( "Allow users to change their email", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="change_email_allowed" value="true" id="change-email">
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-primary text-justify"><?php echo esc_html_x( "By changing email, users downloads and purchases won't be lost. If you are using 'Easy Digital Downloads' or 'WooCommerce' plugins users data won't be lost, however we can't say that about your other plugins, please make sure that your site operations are not depended on their emails.", "Admin", "material-dashboard" ); ?>.</p>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_email_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_email_content" );
        ?>
    </div>
</div>

<!-- Woocommerce -->
<div class="amd-admin-card --setting-card" data-ts="woocommerce">
    <?php amd_dump_admin_card_keywords( ["woocommerce", "shop", "فروشگاه"] ); ?>
    <h3 class="--title"><?php echo esc_html_x( "Woocommerce", "Admin", "material-dashboard" ); ?></h3>
    <div class="--content">
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_before_woocommerce_content" );
        ?>
        <div class="__option_grid">
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_before_woocommerce_items" );
            ?>
            <div class="-item">
                <div class="-sub-item <?php echo $tera_wallet_available ? '' : 'waiting'; ?>">
                    <label for="terawallet-support">
						<?php echo esc_html_x( "Tera wallet support", "Admin", "material-dashboard" ); ?>
                    </label>
                </div>
                <div class="-sub-item <?php echo $tera_wallet_available ? '' : 'waiting'; ?>">
                    <label class="hb-switch">
                        <input type="checkbox" role="switch" name="tera_wallet_support" value="true" id="terawallet-support" <?php echo $tera_wallet_support == "true" ? 'checked' : ''; ?>>
                        <span></span>
                    </label>
                </div>
                <div class="-sub-item --full">
                    <p class="color-blue"><?php echo esc_html_x( "By enabling this item, Tera wallet balance and deposit their account.", "Admin", "material-dashboard" ); ?></p>
                    <?php if( !$tera_wallet_available ): ?>
                        <p class="color-red"><?php printf( esc_html_x( "You need to install %sTera wallet%s plugin to use this option.", "Admin", "material-dashboard" ), '<a href="https://wordpress.org/plugins/woo-wallet/" target="_blank">', '</a>' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
                /** @since 1.2.0 */
                do_action( "amd_settings_after_woocommerce_items" );
            ?>
        </div>
        <?php
            /** @since 1.2.0 */
            do_action( "amd_settings_after_woocommerce_content" );
        ?>
    </div>
</div>

<?php
/** @since 1.2.1 */
do_action( "amd_settings_after_all_cards" );
?>

<script>
    $amd.applyInputsDefault(<?php echo json_encode( $defaultSettings ); ?>)
    $amd.addEvent("on_settings_saved", () => {
        return {
            "optimizer": $("#optimizer").is(":checked") ? "true" : "false",
            "indexing": $("#indexing").is(":checked") ? "no-index" : "index",
            "force_ssl": $("#force-ssl").is(":checked") ? "true" : "false",
            "change_email_allowed": $('#change-email').is(":checked") ? "true" : "false",
            "avatar_upload_allowed": $('#avatar-upload').is(":checked") ? "true" : "false",
            "use_lazy_loading": $('#fast-loading').is(":checked") ? "true" : "false",
            "tera_wallet_support": $("#terawallet-support").is(":checked") ? "true" : "false",
        }
    });
</script>