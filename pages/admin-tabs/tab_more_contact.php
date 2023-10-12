<?php

const business_email = "contact@amatris.ir", developer_email = "ho3ein.b.83@gmail.com";

?>
<div class="amd-admin-card --setting-card">
	<h3 class="--title text-center"><?php echo esc_html_x( "Contact us", "Admin", "material-dashboard" ); ?></h3>
	<div class="--content">
		<p class="color-primary text-center"><?php esc_html_e( "If you have any suggestion or comment or you need help with plugin, contact us with links below, we'll reply as soon as possible. Please feel free to ask anything related anytime you want.", "material-dashboard" ); ?></p>
		<div class="__option_grid">
			<div class="-item">
				<div class="-sub-item">
					<span><?php esc_html_e( "Our email", "material-dashboard" ); ?></span>
				</div>
				<div class="-sub-item">
					<a href="mailto:<?php echo business_email; ?>" class="amd-admin-button --sm --primary --text"><?php echo esc_html( business_email ); ?></a>
                    <button data-copy="<?php echo business_email; ?>" class="amd-admin-button --sm --primary"><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
				</div>
			</div>
			<div class="-item">
				<div class="-sub-item">
					<span><?php esc_html_e( "Developer email", "material-dashboard" ); ?></span>
				</div>
				<div class="-sub-item">
					<a href="<?php echo esc_attr( 'mailto:' . developer_email ); ?>" class="amd-admin-button --sm --primary --text"><?php echo esc_html( developer_email ); ?></a>
					<button data-copy="<?php echo esc_attr( developer_email ); ?>" class="amd-admin-button --sm --primary"><?php esc_html_e( "Copy", "material-dashboard" ); ?></button>
				</div>
				<div class="-sub-item --full">
					<p class="color-blue text-center"><?php esc_html_e( "If you need help with anything related to plugin (like license, modules, codes, etc.) you can contact the plugin developer to get help directly.", "material-dashboard" ); ?></p>
				</div>
			</div>
            <div class="-item">
				<div class="-sub-item">
					<span><?php esc_html_e( "1 Minute Of Your Time", "material-dashboard" ); ?></span>
				</div>
				<div class="-sub-item">
                    <a href="<?php echo esc_url( amd_get_survey_url() ); ?>" class="amd-admin-button --sm --primary --text" target="_blank"><?php esc_html_e( "Open survey", "material-dashboard" ); ?></a>
				</div>
				<div class="-sub-item --full">
                    <p class="color-blue text-center"><?php printf( esc_html__( "Please give us 1 minute of your time and participate in %sOur survey%s to improve this plugin", "material-dashboard" ), "<b>", "</b>" ); ?></p>
				</div>
			</div>
			<div class="-item">
				<div class="-sub-item --full">
					<p class="text-center"><strong><?php esc_html_e( "Suggestions and improvements", "material-dashboard" ); ?></strong></p>
				</div>
				<div class="-sub-item --full">
					<p class="color-low text-center"><?php esc_html_e( "We are trying to give the best features with best performance to you, so if you have any suggestion, improvements or you want us to add your language to our plugins contact us with one of the mentioned ways.", "material-dashboard" ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
    $(document).on("click", "[data-copy]", function(){
        let text = $(this).attr("data-copy");
        if(text) $amd.copy(text, false, true);
    });
</script>