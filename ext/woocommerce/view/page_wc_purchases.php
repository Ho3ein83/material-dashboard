<?php

if( !empty( $_GET["view"] ) ){
	require_once( "view_purchase.php" );
	return;
}

$_get = amd_sanitize_get_fields( $_GET );
$page = !empty( $_get["_page"] ) ? $_get["_page"] : 1;
$maxInPage = apply_filters( "amd_ext_wc_max_orders_in_page", 10 );

$thisuser = amd_get_current_user();

$all_orders = get_posts(
	apply_filters(
		'woocommerce_my_account_my_orders_query',
		array(
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => wc_get_order_types( 'view-orders' ),
			'post_status' => array_keys( wc_get_order_statuses() ),
		)
	)
);

// Pagination
$chunk = array_chunk( $all_orders, $maxInPage );
$orders_count = count( $all_orders );
$pagesCount = intval( ceil( $orders_count / $maxInPage ) );

// If current page doesn't exist back to previous page,
// if there is no page left, break the loop and send 'no order' message
$p = $page;
while( empty( $chunk[$p-1] ) ){
    if( $p <= 0 ) break;
    $p--;
}
$page = $p;

$orders = $chunk[$page-1] ?? [];

if( $orders ){
	?>
    <div class="row">
        <div class="col-lg-5 margin-auto">
            <div class="amd-card-list" id="purchase-history-card">
                <h3 class="color-primary"><?php esc_html_e( "Purchase history", "material-dashboard" ); ?> (<?php echo count( $all_orders ); ?>)</h3>
				<?php foreach( $orders as $item ): ?>
					<?php
					$order = wc_get_order( $item );
                    $status = $order->get_status();
					$id = $order->get_id();
					$_date = $order->get_date_created();
					$date = amd_true_date( amd_ext_wc_get_date_format(), strtotime( $_date ) );
					$item_count = $order->get_item_count() - $order->get_item_count_refunded();
					?>
                    <div class="--card" data-purchase="<?php echo esc_attr( $id ); ?>">
                        <div class="-image">
	                        <?php echo amd_ext_wc_purchase_svg(); ?>
                        </div>
                        <div class="-content">
                            <h4 class="-title">
                                <?php printf( esc_html__( "Order #%s", "material-dashboard" ), $id ) ?>
								<?php amd_ext_wc_esc_status_html( $status ) ?>
                            </h4>
                            <p class="-desc">
                                <span class="color-title"><?php echo $order->get_formatted_order_total(); ?></span>
                                <br>
                                <span class="tiny-text margin-0 color-low"><?php echo esc_html( $date ); ?></span>
                            </p>
                        </div>
                        <div class="-side">
                            <button class="btn btn-text"
                                    data-wc-purchase="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( "View", "material-dashboard" ); ?></button>
                        </div>
                    </div>
				<?php endforeach; ?>
	            <?php if( $pagesCount > 1 ): ?>
                    <div style="width:90%;display:flex;align-items:center;justify-content:center">
			            <?php for( $i = 1; $i <= $pagesCount; $i++ ): ?>
                            <button class="btn mlr-5 --square <?php echo esc_attr( $i == $page ? '' : 'btn-text --low' ); ?>" data-lazy-query="_page=<?php echo $i; ?>"><?php echo $i; ?></button>
			            <?php endfor; ?>
                    </div>
	            <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="h-100"></div>
    <script>
        (function() {
            let wc_set_items_separator = () => $(".--card").addClass("--separated").last().removeClass("--separated");
            wc_set_items_separator();
            $("[data-wc-purchase]").on("click", function() {
                let item = $(this).attr("data-wc-purchase");
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