<?php

$maxInPage = apply_filters( "amd_ext_edd_max_orders_in_home_page", 5 );

$all_downloads = amd_ext_edd_get_downloads_count();

$orders = edd_get_orders( array(
	'user_id' => get_current_user_id(),
	'number' => $maxInPage,
	'offset' => 0,
	'type' => 'sale',
	'status__not_in' => array( 'trash' )
) );

?>
<?php ob_start(); ?>
<?php if( empty( $orders ) ): ?>
    <h4 class="text-center"><?php esc_html_e( "You don't have any purchase to see", "material-dashboard" ); ?></h4>
    <div style="margin:16px"></div>
<?php else: ?>
    <div class="amd-card-list no-shadow">
		<?php
		foreach( $orders as $key => $item ){
			$_date = $item->date_created;
			$_time = strtotime( $_date );
			$date = amd_true_date( amd_ext_edd_get_date_format(), $_time );
			?>
            <div class="--card" data-edd="<?php echo esc_attr( $item->id ); ?>">
                <div class="-image">
					<?php echo amd_ext_edd_download_svg(); ?>
                </div>
                <div class="-content">
                    <h4 class="-title">
						<?php echo esc_html( $date ); ?>
						<?php if( $item->status == "complete" ): ?>
                            <span class="tiny-text color-green">(<?php echo esc_html_x( "completed", "Purchase status", "material-dashboard" ); ?>)</span>
						<?php elseif( $item->status == "failed" ): ?>
                            <span class="tiny-text color-red">(<?php echo esc_html_x( "failed", "Purchase status", "material-dashboard" ); ?>)</span>
						<?php else: ?>
                            <span class="tiny-text">(<?php echo edd_get_status_label( $item->status ); ?>)</span>
						<?php endif; ?>
                    </h4>
                    <p class="-desc">
                        <span><?php echo amd_ext_edd_format_amount( $item->total ); ?></span>
                    </p>
                </div>
                <div class="-side">
                    <button class="btn --blue btn-text"
                            data-edd-purchase="<?php echo esc_attr( $item->id ); ?>"><?php esc_html_e( "View", "material-dashboard" ); ?></button>
                </div>
            </div>
			<?php
		}
		?>
	    <?php if( $all_downloads > $maxInPage ): ?>
            <a href="?void=edd_downloads" data-turtle="lazy" class="btn btn-text"><?php esc_html_e( "More", "material-dashboard" ); ?></a>
	    <?php endif; ?>
    </div>
<?php endif; ?>
<?php $downloads_card_content = ob_get_clean(); ?>
<?php amd_dump_single_card( array(
    "title" => esc_html__( "Downloads", "material-dashboard" ),
    "content" => $downloads_card_content,
    "type" => "title_card",
    "color" => "blue"
) ); ?>
<script>
    (function(){
        $(document).on("click", "[data-edd-purchase]", function() {
            let item = $(this).attr("data-edd-purchase");
            if(item) dashboard.lazyOpen($amd.mergeCurrentQuery("void=edd_downloads&view=" + item));
        });
    }());
</script>