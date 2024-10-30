<?php

$doc_url = trim( amd_doc_url( "" ), "/" );

?>
<div class="amd-admin-card --setting-card">
	<h3 class="--title text-center"><?php echo esc_html_x( "Documentation", "Admin", "material-dashboard" ); ?></h3>
	<div class="--content">
		<p class="color-primary text-center"><?php esc_html_e( "By using our documentation you can develop your own extension or theme for this plugin or customize your dashboard with your own taste. At this moment documentation may not completed, we are trying to finish it as soon as possible", "material-dashboard" ); ?></p>
		<div class="__option_grid">
			<div class="-item">
				<div class="-sub-item">
					<span><?php esc_html_e( "Plugin documentation", "material-dashboard" ); ?></span>
				</div>
				<div class="-sub-item">
					<a href="<?php echo esc_url( $doc_url ); ?>" class="amd-admin-button --sm --primary --text" target="_blank"><?php echo esc_url( $doc_url ); ?></a>
					<button data-copy="<?php echo esc_url( $doc_url ); ?>" class="amd-admin-button --sm --primary"><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>