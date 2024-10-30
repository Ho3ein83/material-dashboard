<?php

global /** @var AMDCache $amdCache */
$amdCache;

$card = $amdCache->cacheExists( "_extra", true, [] );
$type = $card["type"] ?? "";
$title = $card["title"] ?? "";
$content = $card["text"] ?? ( $card["content"] ?? "" );
$icon = $card["icon"] ?? "";
$color = $card["color"] ?? "";
$priority = $card["priority"] ?? 10;
$class = $card["_class"] ?? "";
$attrs = $card["_attrs"] ?? "";
$id = $card["_id"] ?? "";

?>
<div class="amd-card <?php echo esc_attr( $class ); ?>" <?php echo !empty( $id ) ? 'id="' . esc_attr( $id ) . '"' : ""; ?> <?php echo !empty( $attrs ) ? $attrs : ""; ?>>
	<?php echo $card["_before"] ?? ""; ?>
	<?php if( $title ): ?>
        <h3 class="--title-2"><?php echo wp_kses( $title, amd_allowed_tags_with_attr( "br,span,a" ) ); ?></h3>
	<?php endif; ?>
    <div class="--content">
		<?php echo do_shortcode( $content ); ?>
    </div>
	<?php if( !empty( $card["footer"] ) ): ?>
        <div class="--footer-2"><?php echo $card["footer"] ?? ""; ?></div>
	<?php endif; ?>
	<?php echo $card["_after"] ?? ""; ?>
</div>