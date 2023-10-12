<?php

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
$maxInPage = apply_filters( "amd_ext_wc_max_orders_in_home_page", 4 );
$chunk = array_chunk( $all_orders, $maxInPage );
$orders = $chunk[0] ?? [];

?>
<?php ob_start(); ?>
<?php if( empty( $orders ) ): ?>
    <h4 class="text-center"><?php esc_html_e( "You don't have any purchase to see", "material-dashboard" ); ?></h4>
<?php else: ?>
    <div class="amd-card-list no-shadow">
		<?php
		foreach( $orders as $item ){
			$order = wc_get_order( $item );
			$id = $order->get_id();
			$date = amd_true_date( amd_ext_wc_get_date_format(), strtotime( $order->get_date_created() ) );
			$status = $order->get_status();
			?>
            <div class="--card" data-wc="<?php echo esc_attr( $id ); ?>">
                <div class="-image" style="color:var(--amd-text-color)">
					<?php echo amd_ext_wc_purchase_svg() ?>
                </div>
                <div class="-content">
                    <h4 class="-title">
						<?php echo esc_html( $date ); ?>
						<?php amd_ext_wc_esc_status_html( $status ) ?>
                    </h4>
                    <p class="-desc">
                        <span><?php echo $order->get_formatted_order_total(); ?></span>
                    </p>
                </div>
                <div class="-side">
                    <button class="btn btn-text --low"
                            data-wc-purchase="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( "View", "material-dashboard" ); ?></button>
                </div>
            </div>
			<?php
		}
		?>
        <?php if( count( $all_orders ) > $maxInPage ): ?>
            <a href="?void=wc_purchases" data-turtle="lazy" class="btn btn-text"><?php esc_html_e( "More", "material-dashboard" ); ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php $purchases_card_content = ob_get_clean(); ?>
<?php amd_dump_single_card( array(
    "title" => esc_html__( "Purchases", "material-dashboard" ),
    "content" => $purchases_card_content,
    "type" => "title_card",
    "color" => "purple"
) ); ?>
<script>
    (function(){
        $(document).on("click", "[data-wc-purchase]", function() {
            let item = $(this).attr("data-wc-purchase");
            if(item) dashboard.lazyOpen($amd.mergeCurrentQuery("void=wc_purchases&view=" + item));
        });
    }());
</script>