<?php

$thisuser = amd_simple_user();

?><div class="--user">
	<div class="circle-image s-100 margin-auto">
		<img class="--avatar" src="" alt="" loading="lazy">
	</div>
	<span class="--welcome"><?php esc_html_e( "welcome", "material-dashboard" ); ?></span>
	<span class="--user-name"><?php echo esc_html( $thisuser->firstname ); ?></span>
</div>
<div class="--quick-options"></div>
<ul class="amd-sidebar-menu"></ul>