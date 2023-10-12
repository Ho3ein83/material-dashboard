<?php

if( !empty( $_GET["view"] ) ){
    require_once( "view_purchase.php" );
    return;
}

$_get = amd_sanitize_get_fields( $_GET );
$page = !empty( $_get["_page"] ) ? $_get["_page"] : 1;
$maxInPage = apply_filters( "amd_ext_edd_max_orders_in_page", 10 );

$all_orders = edd_get_orders( array(
	'user_id' => get_current_user_id(),
	'type' => 'sale',
	'status__not_in' => array( 'trash' )
) );

$orders = edd_get_orders( array(
	'user_id' => get_current_user_id(),
	'number' => $maxInPage,
	'offset' => $maxInPage * ( intval( $page ) - 1 ),
	'type' => 'sale',
	'status__not_in' => array( 'trash' )
) );

$orders_count = count( $all_orders );
$pagesCount = intval( ceil( $orders_count / $maxInPage ) );

if( $orders ){
	?>
    <div class="row">
        <div class="col-lg-6 margin-auto">
            <div class="amd-card-list" id="purchase-history-card">
                <h3 class="color-primary"><?php esc_html_e( "Purchase history", "material-dashboard" ); ?></h3>
				<?php
				foreach( $orders as $key => $item ){
					$_date = $item->date_created;
					$_time = strtotime( $_date );
					$date = amd_true_date( amd_ext_edd_get_date_format(), $_time );
					?>
                    <div class="--card" data-purchase="<?php echo esc_attr( $item->id ); ?>">
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
                            <button class="btn btn-text"
                                    data-edd-purchase="<?php echo esc_attr( $item->id ); ?>"><?php esc_html_e( "View", "material-dashboard" ); ?></button>
                        </div>
                    </div>
					<?php
				}
				?>
                <?php if( $pagesCount > 1 ): ?>
                    <div style="width:90%;display:flex;align-items:center;justify-content:center">
		                <?php for( $i = 1; $i <= $pagesCount; $i++ ): ?>
                            <button class="btn mlr-5 --square <?php echo esc_attr( $i == $page ? '' : 'btn-text --low' ); ?>" data-lazy-query="_page=<?php echo esc_attr( $i ); ?>"><?php echo $i; ?></button>
		                <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="h-100"></div>
    <script>
        (function() {
            let edd_set_items_separator = () => $(".--card").addClass("--separated").last().removeClass("--separated");
            edd_set_items_separator();
            $("[data-edd-purchase]").on("click", function() {
                let item = $(this).attr("data-edd-purchase");
                if(item) dashboard.lazyOpen($amd.mergeCurrentQuery("view=" + item));
            });
        }());
    </script>
	<?php
}
else{
	?>
    <div class="text-center">
        <h3 class="margin-0 mt-10"><?php esc_html_e( "You don't have any purchase to see", "material-dashboard" ); ?>.</h3>
        <a href="?void=home" data-turtle="lazy" class="btn btn-text mt-5"><?php esc_html_e( "Back", "material-dashboard" ); ?></a>
    </div>
	<?php
}
?>