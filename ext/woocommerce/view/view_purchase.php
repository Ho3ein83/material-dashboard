<?php

$order_id = 0;

# If 'view' parameter is set and not empty
if( !empty( $_GET["view"] ) )
	$order_id = sanitize_text_field( $_GET["view"] );

$order = wc_get_order( $order_id );

if( !$order ){
	?>
    <div class="text-center">
        <h2 class="margin-0 mt-10"><?php esc_html_e( "This order is not available", "material-dashboard" ); ?>.</h2>
        <a href="javascript: void(0)" data-lazy-query="" data-query-toRemove="view"
           class="btn btn-lg btn-text mt-5"><?php esc_html_e( "Back", "material-dashboard" ); ?></a>
    </div>
	<?php
	return;
}

$status = $order->get_status();
$id = $order->get_id();
$date = amd_true_date( amd_ext_wc_get_date_format(), $order->get_date_created()->getTimestamp() );
$total_amount = intval( $order->get_total() );
$item_count = $order->get_item_count() - $order->get_item_count_refunded();

$order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array(
	'completed',
	'processing'
) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads = $order->get_downloadable_items();
$is_download_permitted = $order->is_download_permitted();
$has_downloadable_items = $order->has_downloadable_item();
$show_downloads = $has_downloadable_items && $is_download_permitted;

$discount_amount = intval( $order->get_discount_total() );
$discount_percent = (int) floor( ( $discount_amount * 101 ) / ( $total_amount + $discount_amount ) );
if( $discount_percent > 100 ) $discount_percent = 100;
else if( $discount_percent < 0 ) $discount_percent = 0;

$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array(
	'completed',
	'processing'
) ) );
$notes = $order->get_customer_order_notes();

$only_downloadable = true;
?>
<div class="row">
    <div class="col-lg-6 margin-auto">
        <div class="amd-table" style="max-width:800px;min-width:300px;margin:auto">
            <table>
                <tr>
                    <th><?php esc_html_e( "Order ID", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Order status", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Date", "material-dashboard" ); ?></th>
					<?php if( $discount_amount > 0 ): ?>
                        <th><?php esc_html_e( "Discount", "material-dashboard" ); ?></th>
					<?php endif; ?>
                    <th><?php esc_html_e( "Total price", "material-dashboard" ); ?></th>
                </tr>
                <tr>
                    <td><?php echo esc_html( $id ); ?></td>
                    <td><?php amd_ext_wc_esc_status_html( $status ) ?></td>
                    <td><?php echo esc_html( $date ); ?></td>
					<?php if( $discount_amount > 0 ): ?>
                        <td>
							<?php echo $order->get_discount_to_display(); ?>
                            <span class="tiny-text color-primary"><?php echo "($discount_percent%)"; ?></span>
                        </td>
					<?php endif; ?>
                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                </tr>
            </table>
        </div>
		<?php if( !$is_download_permitted AND $has_downloadable_items ): ?>
            <div class="h-20"></div>
			<?php amd_alert_box( array(
				"text" => esc_html__( "Download files are not available at this moment, if your order is not confirmed please wait until confirmation and try again later", "material-dashboard" ),
				"type" => "warning",
				"icon" => "downloads",
				"size" => "lg",
				"is_front" => true
			) ); ?>
		<?php endif; ?>
        <div class="h-20"></div>
        <div class="amd-card-list">
            <h3 class="color-primary"><?php esc_html_e( "Products", "material-dashboard" ); ?></h3>
			<?php foreach( $order_items as $item_id => $item ): ?>
				<?php
				if( !apply_filters( 'woocommerce_order_item_visible', true, $item ) )
					return;
				/** @var WC_Product $product */
				$product = $item->get_product();
				$is_visible = $product && $product->is_visible();
				$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

				$url = $product->get_permalink();
				$quantity = $item->get_quantity();
				$item_status = $product->get_status();
				$files = $product->get_downloads();
				if( !$product->is_downloadable() )
					$only_downloadable = false;
				?>
                <div class="--card">
                    <div class="-image">
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank">
							<?php echo $product->get_image(); ?>
                        </a>
                    </div>
                    <div class="-content">
                        <h4 class="-title">
                            <a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php echo $product->get_name(); ?></a>
                            <span class="color-blue"><?php echo esc_html( "x$quantity" ); ?></span>
                        </h4>
                        <p class="-desc">
                            <span><?php echo $order->get_formatted_line_subtotal( $item ); ?></span>
							<?php if( $show_downloads AND $product->is_downloadable() ): ?>
                                <br>
                                <span class="tiny-text color-primary"><?php esc_html_e( "You can download product files in downloads section below", "material-dashboard" ); ?>.</span>
							<?php endif; ?>
                        </p>
                    </div>
                    <div class="-side"></div>
                </div>
			<?php endforeach; ?>
        </div>
		<?php if( !$only_downloadable ): ?>
            <div class="h-20"></div>
            <div class="amd-card text-center">
                <h3 class="--title-2"><?php esc_html_e( "Order details", "material-dashboard" ); ?></h3>
                <div class="--content">
                    <div class="amd-option-grid" style="max-width:500px;margin:auto">
						<?php foreach( $order->get_order_item_totals() as $key => $total ): ?>
                            <div class="-item --center">
                                <div class="-sub-item">
                                    <h3 class="color-primary"><?php echo esc_html( $total['label'] ); ?></h3>
                                </div>
                                <div class="-sub-item"><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); ?></div>
                            </div>
						<?php endforeach; ?>
                        <div class="-item --center">
                            <div class="-sub-item">
                                <h3 class="color-primary"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h3>
                            </div>
                            <div class="-sub-item">
                                <address><?php echo $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ); ?></address>
                            </div>
                        </div>
                        <div class="-item --center">
                            <div class="-sub-item">
                                <h3 class="color-primary"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h3>
                            </div>
                            <div class="-sub-item">
                                <address><?php echo $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ); ?></address>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif; ?>
		<?php if( $show_downloads ): ?>
            <div class="h-20"></div>
            <div class="amd-card text-center">
                <h3 class="--title-2 color-primary"><?php esc_html_e( "Downloads", "material-dashboard" ); ?></h3>
                <div class="--content">
					<?php foreach( $downloads as $download ): ?>
						<?php
						$product_name = $download["product_name"];
						$product_url = $download["product_url"];
						$download_name = $download["download_name"];
						$download_url = $download["download_url"];
						?>
                        <a class="btn btn-text ma-8 --low"
                           href="<?php echo esc_url( $download_url ); ?>"><?php echo esc_html( $product_name ) . ": " . esc_html( $download_name ); ?></a>
					<?php endforeach; ?>
                </div>
            </div>
		<?php endif; ?>
		<?php if( $show_purchase_note AND $notes ): ?>
            <div class="h-20"></div>
            <h3 class="text-center"><?php esc_html_e( "Notes", "material-dashboard" ); ?></h3>
            <div class="row">
				<?php /** @var WP_Comment $note */
				foreach( $notes as $note ): ?>
					<?php
					$_date = $note->comment_date;
					$_time = strtotime( $_date );
					$date = sprintf( esc_html_x( "%s at %s", "At time", "material-dashboard" ), amd_true_date( "j F Y", $_time ), amd_true_date( "H:i", $_time ) );
					$content = $note->comment_content;
					?>
                    <div class="col-lg-6 margin-auto">
                        <div class="amd-card --title-box">
                            <h3 class="--title"><?php echo esc_html( $date ); ?></h3>
                            <div class="--content"><p class="text-justify"><?php echo esc_html( $content ); ?></p></div>
                        </div>
                    </div>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>
    </div>
</div>
<div class="h-100"></div>
<!-- @formatter off -->
<script>(function(){dashboard.clearFloatingButtons();dashboard.addFloatingButtons("back",{text:_t("back"),args:["white", "full"],attrs:{"data-lazy-query":"","data-query-toRemove":"view"}});dashboard.renderFloatingButtons()}());</script>
<!-- @formatter on -->