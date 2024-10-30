<div class="text-center" id="cart-is-empty" style="display:none">
    <h3 class="margin-0 mt-10"><?php esc_html_e( "Your cart is empty", "material-dashboard" ); ?></h3>
    <a href="?void=home" data-turtle="lazy" class="btn btn-text mt-5"><?php _e( "Back", "material-dashboard" ); ?></a>
</div>
<?php

if( $cart_items = edd_get_cart_contents() ){
	?>
    <div class="row">
        <div class="col-lg-6 margin-auto">
            <div class="amd-card-list" id="cart-card">
                <h3 class="color-primary"><?php esc_html_e( "Cart", "material-dashboard" ); ?></h3>
				<?php
                $total_price = 0;
				foreach( $cart_items as $key => $item ){
					$id = $item["id"];
					$p_name = edd_get_cart_item_name( $item );
					$price = edd_get_cart_item_price( $item['id'], $item['options'] );
					$total_price += $price;
					?>
                    <div class="--card" data-cart-item="<?php echo esc_attr( $id ); ?>">
                        <div class="-image">
							<?php
                            if( has_post_thumbnail( $item['id'] ) ):
								echo get_the_post_thumbnail( $item['id'] );
							endif;
                            ?>
                        </div>
                        <div class="-content">
                            <h4 class="-title">
                                <?php echo wp_kses( $p_name, amd_allowed_tags_with_attr( "br,span,a" ) ); ?>
                            </h4>
                            <p class="-desc">
                                <span><?php echo edd_cart_item_price( $item['id'], $item['options'] ); ?></span>
                            </p>
                        </div>
                        <div class="-side">
                            <button class="btn btn-text --red"
                                    data-remove-item="<?php echo esc_attr( $key ); ?>"
                                    data-id="<?php echo esc_attr( $id ); ?>">
                                <?php esc_html_e( "Remove", "material-dashboard" ); ?>
                            </button>
                        </div>
                    </div>
					<?php
				}
				?>
                <h4 class="text-center">
                    <?php echo esc_html_x( "Total", "Total price", "material-dashboard" ); ?>:<br>
                    <span class="small-text"><?php echo edd_currency_filter( edd_format_amount( $total_price ) ); ?></span>
                </h4>
                <div>
                    <button class="btn" id="btn-continue"><?php esc_html_e( "Continue", "material-dashboard" ); ?></button>
                    <button class="btn btn-text"
                            id="btn-empty-cart"><?php echo esc_html_x( "Empty", "Empty cart", "material-dashboard" ); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="h-100"></div>
    <script>
        var edd_set_items_separator = () => $(".--card").addClass("--separated").last().removeClass("--separated");
        edd_set_items_separator();
        (function(){
            let $card = $("#cart-card"), $continue = $("#btn-continue"), $empty = $("#btn-empty-cart");

            var cardEmpty = () => {
                let $c = $("#cart-is-empty");
                $card.fadeOut(0);
                $c.fadeIn();
                $("[data-cart-item]").remove();
            }

            var emptyCart = () => {
                $card.cardLoader();
                network.clean();
                network.put("_ajax_target", "ext_edd_private");
                network.put("remove_cart_item", "*");
                network.on.start = () => $card.cardLoader();
                network.on.end = (resp, error) => {
                    $card.cardLoader(false);
                    if(!error){
                        if(resp.success) cardEmpty();
                        else dashboard.toast(resp.data.msg);
                    }
                    else{
                        dashboard.toast(_t("error"));
                    }
                }
                network.post();
            };

            var $getItems = () => $("[data-cart-item]");

            $("[data-remove-item]").on("click", function(){
                let item = $(this).attr("data-remove-item");
                let id = $(this).attr("data-id");
                if(item){
                    var deleteItem = () => {
                        network.clean();
                        network.put("_ajax_target", "ext_edd_private");
                        network.put("remove_cart_item", item);
                        network.on.start = () => $card.cardLoader();
                        network.on.end = (resp, error) => {
                            $card.cardLoader(false);
                            if(!error){
                                if(resp.success){
                                    if($getItems().length > 1){
                                        if(id) $(`[data-cart-item="${id}"]`).removeSlow();
                                        setTimeout(() => edd_set_items_separator(), 720);
                                    }
                                    else cardEmpty();
                                }
                                else{
                                    dashboard.toast(resp.data.msg);
                                }
                            }
                            else{
                                dashboard.toast(_t("error"));
                            }
                        }
                        network.post();
                    }
                    $amd.alert(_t("cart"), _t("delete_confirm"), {
                        confirmButton: _t("yes"),
                        cancelButton: _t("no"),
                        onConfirm: () => deleteItem(item)
                    });
                }
            });

            $continue.click(function(){
                dashboard.suspend(true, 10000);
                location.href = `<?php echo edd_get_checkout_uri(); ?>`;
            });

            $empty.click(function(){
                $amd.alert(_t("cart"), _t("cart_empty_confirmation"), {
                    confirmButton: _t("yes"),
                    cancelButton: _t("no"),
                    onConfirm: () => emptyCart()
                });
            });

        }())
    </script>
	<?php
}
else{
    ?><script>$("#cart-is-empty").fadeIn(0)</script><?php
}
?>