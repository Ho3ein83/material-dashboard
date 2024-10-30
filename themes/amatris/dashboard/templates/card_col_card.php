<?php

global /** @var AMDCache $amdCache */
/** @var AMDLoader $amdLoader */
$amdCache, $amdLoader;

$card = $amdCache->cacheExists( "_extra", true, [] );
$type = $card["_type"] ?? "content_card";
$content = $card["_content"] ?? "";
if( $type )
    $card["type"] = $type;
$col = $card["col"] ?? 6;

?>
<div class="<?php echo esc_attr( "col-lg-$col" ); ?>">
    <?php if( amd_starts_with( $content, "path:" ) ): ?>
        <?php
        $path = str_replace( "path:", "", $content );
        if( file_exists( $path ) )
            require_once( $path );
        ?>
    <?php else: ?>
        <?php $amdLoader->loadTemplate( "card_$type", $card ); ?>
    <?php endif; ?>
</div>