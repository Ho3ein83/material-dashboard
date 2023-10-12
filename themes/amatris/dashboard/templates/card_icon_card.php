<?php

global /** @var AMDCache $amdCache */
$amdCache;

$card = $amdCache->cacheExists( "_extra", true, [] );
$type = $card["type"] ?? "";
$title = $card["title"] ?? "";
$content = $card["text"] ?? ( $card["content"] ?? "" );
$icon = $card["icon"] ?? "";
$color = $card["color"] ?? "default";
$priority = $card["priority"] ?? 10;

$id = $card["id"] ?? "";
$class = $card["extra_class"] ?? "";

?><div class="amd-card <?php echo esc_attr( $class ); ?> --boxed" <?php echo !empty( $id ) ? 'id="' . esc_attr( $id ) . '"' : ""; ?>>
    <div class="--icon --flex-align <?php echo esc_attr( "-gr-$color" ); ?>"><?php _amd_icon( $icon ); ?></div>
    <div class="--title"><?php echo wp_kses( $title, amd_allowed_tags_with_attr( "br,span,a" ) ); ?></div>
    <div class="--content">
        <p class="mbt-5 size-lg font-title color-title"><?php echo do_shortcode( $content ); ?></p>
        <p class="mbt-5 size-sm"><?php echo $card["subtext"] ?? ""; ?></p>
    </div>
    <div class="--footer">
        <span class="size-sm color-low"><?php echo $card["footer"] ?? ""; ?></span>
    </div>
</div>