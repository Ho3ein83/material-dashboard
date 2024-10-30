<?php

$thisuser = amd_simple_user();

$avatars = apply_filters( "amd_get_avatars", [] );

$uploadAllowed = apply_filters( "amd_is_avatar_upload_allowed", true );
$isCustomAvatar = $thisuser->profileKey == $thisuser->secretKey;

# [AVATAR_IMAGE_FORMAT]
$hasCustomAvatar = file_exists( amd_get_avatars_path() . "/" . $thisuser->secretKey . ".png" );

/**
 * Enable or disable default avatar pack
 * @since 1.2.0
 */
$enable_default_avatars = apply_filters( "amd_enable_default_avatars", true );

/**
 * Custom avatars
 * @since 1.2.0
 */
$custom_avatars = apply_filters( "amd_custom_avatars", [] );

/**
 * Placeholder avtar URL
 * @since 1.2.0
 */
$placeholder_avatar = apply_filters( "amd_placeholder_avatar_url", amd_avatar_url( "placeholder" ) );

?>

<?php ob_start(); ?>
<div class="amd-avatars-gallery" id="avatars-gallery">
	<?php if( $uploadAllowed ): ?>
        <div class="--item _upload_avatar_">
            <div class="--icon --dashed">
				<?php _amd_icon( "cloud_upload" ); ?>
            </div>
        </div>
	<?php endif; ?>
    <div class="--item --self <?php echo $isCustomAvatar ? 'active' : ''; ?>"
         data-avatar="<?php echo esc_attr( $thisuser->secretKey ); ?>" <?php echo ( $isCustomAvatar || $hasCustomAvatar ) ? '' : 'style="display:none"'; ?>>
        <div class="--image">
            <img src="<?php echo esc_url( amd_merge_url_query( amd_avatar_url( $thisuser->secretKey ), "cache=" . time() ) ); ?>"
                 alt="" loading="lazy">
        </div>
    </div>
    <div class="--item <?php echo esc_attr( $thisuser->profileKey == 'placeholder' ? 'active' : '' ); ?>"
         data-avatar="placeholder">
        <div class="--image">
            <img src="<?php echo esc_url( $placeholder_avatar ); ?>" alt="" loading="lazy">
        </div>
    </div>
    <?php
        /**
         * Before avatars gallery
         * @since 1.2.0
         */
        do_action( "amd_before_avatars_gallery" );
    ?>
    <?php foreach( $custom_avatars as $avatar_id => $avatar ): ?>
        <?php
            $url = $avatar["url"] ?? null;
            if( empty( $url ) )
                continue;
            $avatarKey = $url;
        ?>
        <div class="--item <?php echo esc_attr( $thisuser->profileKey == $avatarKey ? 'active' : '' ); ?>"
             data-avatar="<?php echo esc_attr( $avatarKey ); ?>">
            <div class="--image">
                <img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy">
            </div>
        </div>
    <?php endforeach; ?>
    <?php if( $enable_default_avatars ): ?>
        <?php foreach( $avatars as $path_id => $avatar ): ?>
            <?php

            if( !file_exists( $avatar ) )
                continue;
            $files = [];
            if( is_dir( $avatar ) )
                $files = glob( rtrim( $avatar, "/\\" ) . "/*.{png,svg,jpg,jpeg}", GLOB_BRACE );
            else
                $files = [ $avatar ];
            foreach( $files as $file ){
                $filename = pathinfo( $file, PATHINFO_BASENAME );
                if( amd_starts_with( $filename, "_" ) )
                    continue;
                $avatarKey = "$path_id:$filename";
                $url = amd_get_api_url( "_avatar=$avatarKey" );
            ?>
                <div class="--item <?php echo esc_attr( $thisuser->profileKey == $avatarKey ? 'active' : '' ); ?>"
                     data-avatar="<?php echo esc_attr( "$path_id:$filename" ); ?>">
                    <div class="--image">
                        <img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy">
                    </div>
                </div>
                <?php
            }
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
        /**
         * After avatars gallery
         * @since 1.2.0
         */
        do_action( "amd_after_avatars_gallery" );
    ?>
</div>
<div id="cropper" class="amd-cropper">
    <img src="" alt="" id="cropper-img">
</div>
<div class="force-center" id="cropper-footer" style="display:none">
    <button type="button" class="btn" id="cropper-crop"><?php esc_html_e( "Crop", "material-dashboard" ); ?></button>
    <button type="button" class="btn btn-text" id="cropper-dismiss"><?php esc_html_e( "Dismiss", "material-dashboard" ); ?></button>
</div>
<label style="display:none">
    <input type="file" id="custom-avatar" accept=".png,.svg,.jpg,.jpeg">
</label>
<?php $avatar_card_content = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php if( $uploadAllowed ): ?>
    <div class="amd-drop-zone" style="display:none">
        <div>
            <h2><?php esc_html_e( "Drop files here", "material-dashboard" ); ?></h2>
            <div><?php _amd_icon( "upload" ); ?></div>
        </div>
    </div>
<?php endif; ?>
<?php $avatar_dropzone_content = ob_get_clean(); ?>

<?php amd_dump_single_card( array(
	"type" => "content_card",
	"title" => esc_html__( "Avatar picture", "material-dashboard" ),
	"content" => $avatar_card_content,
	"_before" => $avatar_dropzone_content,
	"_class" => "_avatar_dropzone_",
	"_id" => "avatar-card"
) ); ?>

<script>
    (function() {
        let $card = $("#avatar-card"), $dz = $card.find(".amd-drop-zone");
        let $input = $("#custom-avatar"), $gallery = $("#avatars-gallery");
        let $drop_zones = $("._avatar_dropzone_");
        let cancelDrag;

        let $img = $("#cropper-img");
        let $footer = $("#cropper-footer");
        let $crop = $("#cropper-crop"), $dismiss = $("#cropper-dismiss");

        function openCropper(file) {
            let done = url => {
                $input.val("");
                $img.attr("src", url);
                $footer.fadeIn(0);
                cropper = new Cropper($img.get()[0], {
                    aspectRatio: 1,
                    viewMode: 3
                });
            }
            reader = new FileReader();
            reader.onloadend = () => done(reader.result);
            reader.readAsDataURL(file);
        }

        function cancel_crop() {
            $input.val("");
            $img.attr("src", "");
            $footer.fadeOut(0);
            if(cropper && typeof cropper.destroy === "function")
                cropper.destroy();
            cropper = null;
        }

        function complete_crop() {
            if(!cropper || typeof cropper === 'undefined') return;

            let canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
            });

            canvas.toBlob(function(blob) {
                url = URL.createObjectURL(blob);
                let reader = new FileReader();
                reader.onloadend = function() {
                    let base64data = reader.result;
                    let _engine = dashboard.getApiEngine();
                    _engine.clean();
                    _engine.put("upload_avatar", base64data);
                    _engine.on.start = () => {
                        $card.cardLoader();
                        Hello.clearQueue();
                        dashboard.toast(_t("uploading_td"), 2000);
                    }
                    _engine.on.end = (resp, error) => {
                        $card.cardLoader(false);
                        if(!error) {
                            if(resp.success) {
                                let url = "";
                                if(typeof resp.data.url === "string") url = resp.data.url;
                                if(url) {
                                    dashboard.setAvatar(url);
                                    $gallery.find(".--item.--self").find("img").attr("src", url);
                                }
                                $gallery.find(".--item").removeClass("active");
                                $gallery.find(".--item.--self").addClass("active").fadeIn();
                                Hello.clearQueue();
                                dashboard.toast(resp.data.msg);
                                cancel_crop();
                            }
                            else {
                                $amd.alert(_t("avatar"), resp.data.msg, {
                                    icon: "error"
                                });
                            }
                        }
                        else {
                            $amd.alert(_t("avatar"), _t("error"), {
                                icon: "error"
                            });
                        }
                    }
                    _engine.post();
                };
                reader.readAsDataURL(blob);
            });

        }

        $crop.click(() => complete_crop());
        $dismiss.click(() => cancel_crop());
        $input.on("change", function() {
            if(cropper && typeof cropper.destroy === "function")
                cropper.destroy();
            let files = this.files;
            if(files.length > 0) {
                let f = files[0];
                let mime = f.type || "";
                if(mime.regex("^image\/(png|svg|svg[\+]xml|jpg|jpeg)$")) openCropper(f);
            }
        });
        $drop_zones.on("dragenter dragover", function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dz.addClass("--dragging");
            $dz.fadeIn(100);
            clearInterval(cancelDrag);
        });
        $drop_zones.on("dragleave", function(e) {
            e.preventDefault();
            e.stopPropagation();
            cancelDrag = setInterval(() => {
                $dz.removeClass("--dragging");
                $dz.fadeOut(100);
            }, 700);
        });
        $drop_zones.on("mouseleave", function(e) {
            e.preventDefault();
            e.stopPropagation();
            clearInterval(cancelDrag);
            $dz.removeClass("--dragging");
            $dz.fadeOut(100);
        });
        $drop_zones.on("drop", function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dz.fadeOut(100);
            if(e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length >= 1) {
                let file = e.originalEvent.dataTransfer.files[0];
                let mime = file.type || "";
                if(mime.regex("^image\/(png|svg|svg[\+]xml|jpg|jpeg)$")) openCropper(file);
            }
        });
        $("._upload_avatar_").click(() => $input.click());
        $("[data-avatar]").click(function() {
            let avatar = $(this).attr("data-avatar");
            let isSelf = $(this).hasClass("--self");
            if(avatar) {
                let _engine = dashboard.getApiEngine();
                _engine.clean();
                _engine.put("change_avatar", avatar);
                _engine.on.start = () => {
                    $card.cardLoader();
                    dashboard.toast(_t("wait_td"), 2000);
                }
                _engine.on.end = (resp, error) => {
                    $card.cardLoader(false);
                    if(!error) {
                        if(resp.success) {
                            let url = "";
                            if(typeof resp.data.url === "string") url = resp.data.url;
                            if(url) dashboard.setAvatar(url);
                            $gallery.find(".--item").removeClass("active");
                            if(isSelf) $gallery.find(".--item.--self").addClass("active");
                            else $(`[data-avatar="${avatar}"]`).addClass("active");
                            Hello.clearQueue();
                            dashboard.toast(resp.data.msg);
                            cancel_crop();
                        }
                        else {
                            $amd.alert(_t("avatar"), resp.data.msg, {
                                icon: "error"
                            });
                        }
                    }
                    else {
                        $amd.alert(_t("avatar"), _t("error"), {
                            icon: "error"
                        });
                    }
                }
                _engine.post();
            }
        });
    }());
</script>

<!-- @formatter off -->
<style>.amd-drop-zone{position:absolute;top:0;left:0;width:100%;height:100%;border-radius:inherit;background:rgba(var(--amd-primary-rgb),.7);z-index:10}.amd-drop-zone>div{display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;margin:32px;width:calc(100% - 64px);height:calc(100% - 64px);border-radius:inherit;border:3px dashed #fff}.amd-drop-zone>div>*{color:#fff}
.amd-cropper{width:90%;max-width:300px;margin:20px auto;text-align:center}.amd-cropper>img{display:block;max-width:100%}
#avatar-card>.--content{overflow:auto;-ms-overflow-style:none;scrollbar-width:none}#avatar-card>.--content::-webkit-scrollbar{display:none}.amd-avatars-gallery{display:flex;flex-wrap:wrap;align-items:start;justify-content:center}.amd-avatars-gallery>.--item{display:flex;width:100%;height:auto;aspect-ratio:1;flex:0 0 85px;background:var(--amd-primary-x-low);color:var(--amd-primary);padding:16px;margin:8px;border-radius:10px;transition:background-color ease .2s;cursor:var(--amd-pointer)}.amd-avatars-gallery>.--item:hover{background:rgba(var(--amd-primary-rgb),.7);color:#fff}.amd-avatars-gallery>.--item.active{background:var(--amd-primary);color:#fff}.amd-avatars-gallery>.--item>.--image{border-radius:6px;overflow:hidden}.amd-avatars-gallery>.--item>.--image>img{object-fit:cover;user-select:none;pointer-events:none}.amd-avatars-gallery>.--item>.--image>img,.amd-avatars-gallery>.--item>.--icon{width:100%;height:100%;border-radius:10px}.amd-avatars-gallery>.--item>.--icon.--dashed{border:3px dashed currentColor}.amd-avatars-gallery>.--item>.--icon{display:flex;align-items:center;justify-content:center}.amd-avatars-gallery>.--item>.--icon>._icon_{color:currentColor;font-size:28px}.amd-avatars-gallery>.--item>.--icon>svg._icon_{width:auto;height:30px}</style>
<!-- @formatter on -->