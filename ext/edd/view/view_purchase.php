<?php

$_get = amd_sanitize_get_fields( $_GET );
$order_id = !empty( $_get["view"] ) ? $_get["view"] : 0;

$order = edd_get_order( $order_id );

$order_items = [];
if( $order ) $order_items = $order->get_items();

if( !$order OR empty( $order_items ) ){
	?>
    <div class="text-center">
        <h2 class="margin-0 mt-10"><?php esc_html_e( "This order is not available", "material-dashboard" ); ?>.</h2>
        <a href="javascript: void(0)" data-lazy-query="" data-query-toRemove="view"
           class="btn btn-lg btn-text mt-5"><?php esc_html_e( "Back", "material-dashboard" ); ?></a>
    </div>
	<?php
	return;
}

$_date = $order->date_created;
$_time = strtotime( $_date );
$date = amd_true_date( amd_ext_edd_get_date_format(), $_time );

?>
<div class="row">
    <div class="col-lg-6 margin-auto">
        <div class="amd-table" style="max-width:800px;min-width:300px;margin:auto">
            <table>
                <tr>
                    <th><?php esc_html_e( "Order ID", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Order status", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Date", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Total price", "material-dashboard" ); ?></th>
                </tr>
                <tr>
                    <td><?php echo esc_html( $order->id ); ?></td>
                    <td>
						<?php if( $order->status == "complete" ): ?>
                            <span class="color-green"><?php echo esc_html_x( "completed", "Purchase status", "material-dashboard" ); ?></span>
						<?php elseif( $order->status == "failed" ): ?>
                            <span class="color-red"><?php echo esc_html_x( "failed", "Purchase status", "material-dashboard" ); ?></span>
						<?php else: ?>
                            <span><?php echo edd_get_status_label( $order->status ); ?></span>
						<?php endif; ?>
                    </td>
                    <td><?php echo esc_html( $date ); ?></td>
                    <td><?php echo amd_ext_edd_format_amount( $order->total ); ?></td>
                </tr>
            </table>
        </div>
        <div class="h-20"></div>
        <div class="amd-card-list">
            <h3 class="color-primary"><?php esc_html_e( "Files", "material-dashboard" ); ?></h3>
			<?php foreach( $order_items as $key => $item ): ?>
				<?php
				if( !apply_filters( 'edd_user_can_view_receipt_item', true, $item ) )
					continue;
				$st = false;
				$download_files = edd_get_download_files( $item->product_id, $item->price_id );
				?>
                <div class="--card">
                    <div class="-image">
						<?php echo amd_ext_edd_download_svg(); ?>
                    </div>
                    <div class="-content">
                        <h4 class="-title">
							<?php echo $item->get_order_item_name(); ?>
							<?php if( $item->status == "complete" ): ?>
								<?php $st = true; ?>
                                <span class="tiny-text color-green">(<?php echo esc_html_x( "completed", "Purchase status", "material-dashboard" ); ?>)</span>
							<?php elseif( $item->status == "failed" ): ?>
                                <span class="tiny-text color-red">(<?php echo esc_html_x( "failed", "Purchase status", "material-dashboard" ); ?>)</span>
							<?php else: ?>
                                <span class="tiny-text">(<?php echo edd_get_status_label( $order->status ); ?>)</span>
							<?php endif; ?>
                        </h4>
                        <p class="-desc">
                            <span><?php echo amd_ext_edd_format_amount( $item->total ); ?></span>
                        </p>
                    </div>
                </div>
                <div class="--card">
					<?php if( !edd_no_redownload() ): ?>
						<?php if( $item->is_deliverable() AND $download_files ): ?>
                            <ul class="amd-list">
								<?php foreach( $download_files as $fileKey => $file ): ?>
                                    <li>
                                        <a href="<?php echo esc_url( edd_get_download_file_url( $order->payment_key, $order->email, $fileKey, $item->product_id, $item->price_id ) ); ?>"><?php echo esc_html( edd_get_file_name( $file ) ); ?></a>
                                    </li>
								<?php endforeach; ?>
                            </ul>
						<?php endif; ?>
					<?php endif; ?>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
</div>

<div class="h-100"></div>
<!-- @formatter off -->
<script>(function(){dashboard.clearFloatingButtons();dashboard.addFloatingButtons("back",{text:_t("back"),args:["white", "full"],attrs:{"data-lazy-query":"","data-query-toRemove":"view"}});dashboard.renderFloatingButtons()}());</script>
<!-- @formatter on -->