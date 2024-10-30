<?php

global /** @var AMDCache $amdCache */
$amdCache;

$card = $amdCache->cacheExists( "_extra", true, [] );
$type = $card["type"];
$title = $card["title"];
$content = $card["text"] ?? ( $card["content"] ?? "" );
$icon = $card["icon"] ?? "";
$color = $card["color"] ?? "";
$priority = $card["priority"] ?? 10;

$id = $card["id"] ?? "";
$class = $card["extra_class"] ?? "";
$footer = $card["footer"] ?? "";

?><div class="amd-card --title-box <?php echo esc_attr( $class ); ?>"<?php echo !empty( $id ) ? ' id="' . esc_attr( $id ) . '"' : ""; ?>>
	<h3 class="--title <?php echo esc_attr( "bg-$color-im" ); ?>"><?php echo $title; ?></h3>
	<div class="--content">
		<?php echo do_shortcode( $content ); ?>
	</div>
	<?php if( !empty( $footer ) ): ?>
        <div class="--footer-2"><?php echo do_shortcode( $footer ); ?></div>
	<?php endif; ?>
</div>