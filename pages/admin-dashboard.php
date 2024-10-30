<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
	return;

$thisuser = amd_get_current_user();

$plugin_data = amd_plugin();
$plugin_version = $plugin_data["Version"] ?? "";
$plugin_name = $plugin_data["Name"] ?? "";
$plugin_uri = $plugin_data["PluginURI"] ?? "";
$plugin_author = $plugin_data["AuthorName"] ?? "";
$plugin_author_uri = $plugin_data["AuthorURI"] ?? "";

$dashboard_url = amd_get_dashboard_page();
$login_url = amd_get_login_page();
$api_url = amd_get_api_url();

$admin_note = amd_get_todo_list( [ "todo_key" => "admin_note" ], true );
$admin_note = amd_decrypt_aes( json_decode( $admin_note ), AMD_DIRECTORY );

$new_user_offset = apply_filters( "amd_new_user_offset", 3 );
$max_online_users = apply_filters( "amd_show_max_online_users_offset", 4 );

$all_users = amd_get_all_users();
$newest_users = amd_get_newest_users();
$online_users = amd_get_online_users();

?>
<script>var network=new AMDNetwork();network.setAction(amd_conf.ajax.private);</script>
<div class="row">
    <div class="col-lg-9">
        <h1><?php esc_html_e( "Amatris Material Dashboard", "material-dashboard" ); ?></h1>
        <div class="amd-card-columns c3">

	        <?php
                /**
                 * Dashboard cards in admin panel
                 * @since 1.0.7
                 */
                do_action( "amd_admin_dashboard_before" );
	        ?>

            <!-- Quick links -->
            <div class="amd-admin-card">
                <h3 class="--title"><?php echo esc_html_x( "Quick links", "Admin", "material-dashboard" ); ?></h3>
                <div class="--content">
                    <div class="__option_grid">
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "dashboard page", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item" <?php echo !empty( $dashboard_url ) ? '' : 'style="display:none"'; ?>>
                                <a href="<?php echo esc_url( $dashboard_url ); ?>" target="_blank"
                                   class="amd-admin-button --icon --sm"><?php esc_html_e( "Open", "material-dashboard" ); ?></a>
                                <a href="javascript:void(0)" data-copy="<?php echo esc_url( $dashboard_url ); ?>"
                                   class="amd-admin-button --sm --primary --text"><?php esc_html_e( "Copy", "material-dashboard" ); ?></a>
                            </div>
							<?php if( empty( $dashboard_url ) ): ?>
                                <div class="-sub-item --full">
                                    <p class="color-red">
										<?php esc_html_e( "This page is not set yet", "material-dashboard" ); ?>!
                                        <a href="<?php echo esc_url( admin_url( "admin.php?page=amd-settings" ) . "#pages" ); ?>"><?php esc_html_e( "Set it now", "material-dashboard" ); ?></a>
                                    </p>
                                </div>
							<?php endif; ?>
                        </div>
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "login page", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item" <?php echo !empty( $login_url ) ? '' : esc_attr( 'style="display:none"' ); ?>>
                                <a href="<?php echo esc_url( $login_url ); ?>" target="_blank"
                                   class="amd-admin-button --icon --sm"><?php esc_html_e( "Open", "material-dashboard" ); ?></a>
                                <a href="javascript:void(0)" data-copy="<?php echo esc_url( $login_url ); ?>"
                                   class="amd-admin-button --sm --primary --text"><?php esc_html_e( "Copy", "material-dashboard" ); ?></a>
                            </div>
							<?php if( empty( $login_url ) ): ?>
                                <div class="-sub-item --full">
                                    <p class="color-red">
										<?php esc_html_e( "This page is not set yet", "material-dashboard" ); ?>!
                                        <a href="<?php echo esc_url( admin_url( "admin.php?page=amd-settings" ) . "#pages" ); ?>"><?php esc_html_e( "Set it now", "material-dashboard" ); ?></a>
                                    </p>
                                </div>
							<?php endif; ?>
                        </div>
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "API page", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item">
                                <a href="<?php echo esc_url( $api_url ); ?>" target="_blank"
                                   class="amd-admin-button --icon --sm"><?php esc_html_e( "Open", "material-dashboard" ); ?></a>
                                <a href="javascript:void(0)" data-copy="<?php echo esc_url( $api_url ); ?>"
                                   class="amd-admin-button --sm --primary --text"><?php esc_html_e( "Copy", "material-dashboard" ); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <div class="amd-admin-card" id="note-card">
                <h3 class="--title"><?php echo esc_html_x( "Note", "Admin", "material-dashboard" );
					echo " (" . amd_true_date( "j F Y" ); ?>)</h3>
                <h3 class="color-low _live_clock_" style="margin:0;position:relative;top:-34px;text-align:end;"></h3>
                <div class="--content">
                    <label for="admin-note"></label>
                    <textarea id="admin-note" class="amd-admin-textarea auto-size" placeholder="<?php echo esc_html_x( "Note", "Admin", "material-dashboard" ); ?>"><?php echo is_string( $admin_note ) ? $admin_note : ""; ?></textarea>
                    <div class="__option_grid">
                        <div class="-item">
                            <div class="-sub-item">
                                <button class="amd-admin-button --sm"
                                        id="save-note"><?php esc_html_e( "Save", "material-dashboard" ); ?></button>
                            </div>
                            <div class="-sub-item">
                                <button class="amd-admin-button --sm --primary --text"
                                        id="clear-note"><?php echo esc_html_x( "Clear", "Admin", "material-dashboard" ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="amd-admin-card" id="statistics-card">
                <h3 class="--title"><?php echo esc_html_x( "Statistics", "Admin", "material-dashboard" ); ?></h3>
                <div class="--content">
                    <div class="__option_grid">
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "Total users", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item"><?php echo count( $all_users ); ?></div>
                        </div>
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "Online users", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item"><?php echo count( $online_users ) > 0 ?: 1; ?></div>
                        </div>
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "New users", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item"><?php echo count( $newest_users ); ?></div>
							<?php if( count( $newest_users ) > 0 ): ?>
                                <div class="-sub-item --full">
                                    <div class="amd-admin-badges">
										<?php foreach( $newest_users as $user ): ?>
                                            <a href="<?php echo esc_url( $user->getProfileURL() ); ?>" target="_blank"
                                               class="--badge"><?php echo esc_html( $user->fullname ); ?></a>
										<?php endforeach; ?>
                                    </div>
                                </div>
							<?php else: ?>
                                <div class="-sub-item --full">
                                    <p class="color-blue"><?php echo esc_html( sprintf( esc_html_x( "Users that registered in recent %s days", "Admin", "material-dashboard" ), $new_user_offset ) ); ?></p>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Online users -->
            <div class="amd-admin-card">
                <h3 class="--title"><?php echo esc_html_x( "Online users", "Admin", "material-dashboard" ); ?></h3>
                <div class="--content">
                    <div class="amd-user-cards">
                        <div>
                            <div class="--image"><img src="<?php echo esc_url( $thisuser->getProfile() ); ?>" alt=""></div>
                            <div class="--image --big"><img src="<?php echo esc_url( $thisuser->getProfile() ); ?>" alt=""></div>
                            <p class="--name color-primary"
                               title="<?php echo esc_attr( $thisuser->fullname ); ?>"><?php echo esc_html( $thisuser->fullname ); ?></p>
                            <span class="color-low"><?php echo esc_html( $thisuser->username ); ?></span>
                        </div>
						<?php $counter = 1; ?>
						<?php foreach( $online_users as $user ): ?>
							<?php if( $user->ID == $thisuser->ID OR $counter > $max_online_users )
								continue;
							$counter++; ?>
                            <div>
                                <div class="--image"><img src="<?php echo esc_url( $user->profile ); ?>" alt=""></div>
                                <div class="--image --big"><img src="<?php echo esc_url( $user->profile ); ?>" alt=""></div>
                                <p class="--name color-primary"
                                   title="<?php echo esc_attr( $user->fullname ); ?>"><?php echo esc_html( $user->fullname ); ?></p>
                                <span class="color-low"><?php echo esc_html( $user->username ); ?></span>
                            </div>
						<?php endforeach; ?>
                    </div>
					<?php if( $counter < count( $online_users ) ): ?>
                        <p class="text-center color-primary">+<?php echo esc_html( sprintf( esc_html_x( "more %s items", "Admin", "material-dashboard" ), count( $online_users ) - $counter ) ); ?></p>
					<?php endif; ?>
                </div>
            </div>

            <!-- Plugin info -->
            <div class="amd-admin-card">
                <h3 class="--title"><?php echo esc_html_x( "Material dashboard", "Admin", "material-dashboard" ); ?></h3>
                <div class="--content">
                    <div class="__option_grid">
						<?php if( $plugin_name AND $plugin_uri ): ?>
                            <div class="-item">
                                <div class="-sub-item"><?php echo esc_html_x( "Plugin", "Admin", "material-dashboard" ); ?></div>
                                <div class="-sub-item">
                                    <a href="<?php echo esc_url( $plugin_uri ); ?>"
                                       target="_blank"><?php echo esc_html( $plugin_name ); ?></a>
                                </div>
                            </div>
						<?php endif; ?>
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "Version", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item">
                                <a href="<?php echo amd_get_plugin_repo_url(); ?>"
                                   class="amd-admin-button --sm --primary --text"
                                   target="_blank"><?php echo esc_html( $plugin_version ); ?></a>
                            </div>
                        </div>
                        <div class="-item">
                            <div class="-sub-item"><?php echo esc_html_x( "Contact us", "Admin", "material-dashboard" ); ?></div>
                            <div class="-sub-item">
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=amd-more' ) ); ?>"
                                   class="amd-admin-button --sm --primary --text"><?php esc_html_e( "Support", "material-dashboard" ); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
                /**
                 * Dashboard cards in admin panel
                 * @since 1.0.7
                 */
                do_action( "amd_admin_dashboard_after" );
            ?>

        </div>
    </div>
</div>
<!-- @formatter off -->
<style>.amd-user-cards,.amd-user-cards>div{display:flex;flex-wrap:wrap;align-items:center;justify-content:center}  .amd-user-cards{display:flex;flex-wrap:wrap;align-items:center;justify-content:center}  .amd-user-cards>div{position:relative;flex-direction:column;background:var(--amd-wrapper-fg);padding:8px 16px;margin:4px;border-radius:10px;border:1px solid rgba(var(--amd-primary-rgb),.2)}  .amd-user-cards .--image{position:relative;width:32px;height:auto;display:flex;aspect-ratio:1}  .amd-user-cards .--image.--big{display:none}  .amd-user-cards .--image.--show+.--big{display:flex;position:absolute;width:100px;animation:amd_zoom ease .1s 1}  @keyframes amd_zoom{0%{width:32px}100%{width:100px}}  .amd-user-cards .--image:not(.--big):before{content:' ';position:absolute;display:block;width:10px;height:10px;border-radius:5px;background:var(--amd-color-green);bottom:0;left:0;animation:amd_blink ease 2s infinite}  @keyframes amd_blink{0%{opacity:1}30%{opacity:0}70%{opacity:0}100%{opacity:1}}  .amd-user-cards .--image>img{width:100%;height:100%;object-fit:cover;border-radius:50%}  .amd-user-cards .--name{margin:0;max-width:80px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}</style>
<script>let clear;$(document).on("mouseover", ".amd-user-cards .--image", function(){let $el=$(this);$el.addClass("--show")});$(document).on("mouseleave", ".amd-user-cards .--image", function(){let $el=$(this);clear=setTimeout(()=>$el.removeClass("--show"),700)});$(document).on("mouseover", ".amd-user-cards .--image.--big", function(){clearTimeout(clear)});$(document).on("mouseleave", ".amd-user-cards .--image.--big", function(){clearTimeout(clear);let $el=$(this),$prev=$(this).prev();$el.attr("style", "transition:opacity ease .2s;opacity:0");setTimeout(()=>{$el.removeAttr("style");$prev.removeClass("--show")},110)});</script>
<!-- @formatter on -->
<script>
    (function() {
        let $card = $("#note-card"), $note = $("#admin-note");
        let $save = $("#save-note"), $clear = $("#clear-note");
        let save = () => {
            network.clean();
            network.put("save_admin_note", $note.val());
            network.on.start = () => $card.cardLoader();
            network.on.end = (resp, error) => {
                $card.cardLoader(false);
                if(!error) {
                    $amd.alert(resp.data.msg, "", {
                        icon: resp.success ? "success" : "error"
                    });
                }
                else {
                    $amd.alert(_t("error"), "", {
                        icon: "error"
                    });
                }
            }
            network.post();
        }
        $save.click(() => save());
        $clear.click(() => {
            $note.val("");
            $note.trigger("change");
            save();
        });

    }());
</script>

<script>
    (function() {
        $(document).on('change keydown keyup', 'textarea.auto-size', function() {
            $(this).height(0).height(this.scrollHeight);
        }).find('textarea.auto-size').trigger("change");

        $(document).on("click", "[data-copy]", function(e) {
            e.preventDefault();
            let str = $(this).attr("data-copy");
            if(str) $amd.copy(str, false, true);
        });

        let $clocks = $("._live_clock_");

        function setClock() {
            let d = new Date();
            let hh = d.getHours().toString();
            let mm = d.getMinutes().toString();
            let ss = d.getSeconds().toString();
            $clocks.html(`${hh.addZero()}:${mm.addZero()}:${ss.addZero()}`);
        }

        setInterval(setClock, 1000);
        setClock();
    }());
</script>