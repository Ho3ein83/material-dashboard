<?php

if( !empty( $_GET["report"] ) ){
    $report = sanitize_text_field( $_GET["report"] );
    if( $report == "logins" ){
	    $display_login_reports = amd_get_site_option( "display_login_reports", "true" );
	    if( $display_login_reports == "true" ){
		    require_once AMD_DASHBOARD . "/pages/page-login-reports.php";
		    return;
	    }
    }

    if( has_action( "amd_account_report" ) ){
	    /**
	     * Custom account reports
         * @since 1.0.5
	     */
        do_action( "amd_account_report" );
        return;
    }
}

?>
<div class="card-columns template-1"><?php amd_dump_cards( "acc_card" ); ?></div>
<?php amd_front_spacer( 100 ); ?>
