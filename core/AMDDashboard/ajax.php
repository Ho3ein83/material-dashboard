<?php

/**
 * Print fail message
 * @return string
 * @since 1.0.0
 */
function amd_hbdash_ajax_fail_message(){

	ob_start();
	?>
<div class="text-center">
	<h2 class="mbt-10">%s</h2>
	<p class="margin-0">%s</p>
	<a href="javascript: void(0)" class="btn mt-20" data-turtle="reload">%s</a>
</div>
	<?php
	$html = ob_get_clean();
	$html = apply_filters( "amd_ajax_fail_message", $html );
    return sprintf( $html, esc_html__( "An error has occurred", "material-dashboard" ), esc_html__( "This page is unavailable or your internet connection interrupted.", "material-dashboard" ), esc_html__( "try again", "material-dashboard" ) );
}

/**
 * AJAX handler
 * @param array $r
 *
 * @return void
 * @since 1.0.0
 */
function amd_hbdash_ajax_handler( $r ){

	if( !empty( $r["lazyloading"] ) ){

		do_action( "amd_before_lazy_init" );

		$data = $r["lazyloading"];
		$void = !empty( $data["_void"] ) ? $data["_void"] : "home";
		$params = !empty( $data["params"] ) ? $data["params"] : [];

        if( !amd_is_account_valid() )
            $void = "__account_validation";

		do_action( "amd_lazy_init", $void, $params );

		foreach( $params as $key => $value ){
            $safe_key = sanitize_key( $key );
			$_GET[$safe_key] = sanitize_text_field( $value );
		}

		global /** @var AMDDashboard $amdDashboard */
		$amdDashboard;

        $lazy = $amdDashboard->getLazyPage( $void, $data );
		$title = $lazy["title"];
		$html = $lazy["content"];
		$redirect = $lazy["redirect"];

        if( $redirect )
            $html = "";

		do_action( "amd_checkin", $r );

		wp_send_json_success( [ "msg" => "", "html" => $html, "title" => $title, "void" => $void, "redirect" => $redirect ] );

	}

	wp_send_json_error( [ "msg" => esc_html__( "An error has occurred", "material-dashboard" ), "html" => amd_hbdash_ajax_fail_message() ] );

}