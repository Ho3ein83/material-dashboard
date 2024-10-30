<?php

if( !function_exists( "woo_wallet" ) )
	return;

# add_filter( "woo_wallet_is_enable_top_up", "__return_false" );
add_filter( "woo_wallet_is_enable_transfer", "__return_false" );
# add_filter( "woo_wallet_is_enable_transaction_details", "__return_false" );

$action = sanitize_text_field( $_GET["wallet_action"] ?? "add" );
$_GET["wallet_action"] = $action;

?>
<div class="row">
	<div class="col-lg-7 margin-auto">
		<?php ob_start(); ?>

        <div>
            <a href="?void=wc_purchases&wallet=tera&wallet_action=add" data-turtle="lazy" class="btn <?php echo $action == 'add' ? '' : 'btn-text --low'; ?>"><?php esc_html_e( 'Wallet topup', 'woo-wallet' ) ?></a>
            <a href="?void=wc_purchases&wallet=tera&wallet_action=transaction" data-turtle="lazy" class="btn <?php echo $action == 'transaction' ? '' : 'btn-text --low'; ?>"><?php esc_html_e( 'Transactions', 'woo-wallet' ) ?></a>
            <div class="h-20"></div>
        </div>

        <?php if( $action != "transaction" ): ?>
            <?php woo_wallet()->get_template( "wc-endpoint-wallet.php" ); ?>
        <?php else: ?>
	        <?php $transactions = get_wallet_transactions( array( 'limit' => apply_filters( 'woo_wallet_transactions_count', 10 ) ) ); ?>
	        <?php
	        if( !empty( $transactions ) ){ ?>
                <div class="amd-table" id="tera-wallet-transaction">
                    <table>
                        <thead>
                        <tr>
                            <th><?php esc_html_e( "Date", "woo-wallet" ); ?></th>
                            <th><?php esc_html_e( "Amount", "woo-wallet" ); ?></th>
                            <th><?php esc_html_e( "Details", "woo-wallet" ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="-table-items">
				        <?php foreach( $transactions as $transaction ) : ?>
                            <tr>
                                <td><?php echo wc_string_to_datetime( $transaction->date )->date_i18n( wc_date_format() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                                <td>
							        <?php
							        echo wc_price( apply_filters( 'woo_wallet_amount', $transaction->amount, $transaction->currency, $transaction->user_id ), woo_wallet_wc_price_args( $transaction->user_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							        ?>
                                    <br>
                                    <?php if( $transaction->type == "credit" ): ?>
                                    <span class="tiny-text color-green"><?php esc_html_e( 'Credit', 'woo-wallet' ); ?></span>
                                    <?php else: ?>
                                    <span class="tiny-text color-red"><?php esc_html_e( 'Debit', 'woo-wallet' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $transaction->details; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                            </tr>
				        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
		        <?php
	        }
	        else{
                ?><h4><?php esc_html_e( 'No transactions found', 'woo-wallet' ); ?></h4><?php
	        }
	        ?>
        <?php endif; ?>
        <?php $wallet_content = ob_get_clean(); ?>
		<?php amd_dump_single_card( array(
			"title" => esc_html__( "Wallet", "woo-wallet" ),
			"content" => $wallet_content,
			"type" => "content_card",
			"color" => "primary",
			"_id" => "tera-wallet-card"
		) ); ?>
	</div>
</div>
<style>
    .woo-wallet-content-heading { display: none }
    .woo-wallet-sidebar { display: none }
    .woo-wallet-content hr { display: none }
    input.woo-wallet-balance-to-add {
        border: none;
        outline: none;
        background: var(--amd-input-bg);
        font-family: var(--amd-font-family);
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 15px;
    }
    .woo-add-to-wallet {
	    background: var(--amd-primary);
	    color: #fff;
    }
    .woo-wallet-add-amount {
	    display: flex;
	    flex-wrap: wrap;
	    align-items: start;
	    justify-content: start;
	    flex-direction: column;
    }
    .woo-wallet-add-amount > * {
	    margin: 8px;
        border: none;
        outline: none;
        font-family: var(--amd-font-family);
        font-size: 15px;
        padding: 8px 16px;
        border-radius: 8px;
	    cursor: pointer;
    }
</style>

<?php if( 2 == 3 ): ?>
    <style>
        /*.woo-wallet-my-wallet-container {
            position: relative;
            background: var(--amd-wrapper-fg);
            padding: 6px 16px;
            border-radius: 16px;
        }
        .woo-wallet-sidebar-heading > * {
            text-decoration: none;
            color: var(--amd-title-color);
        }
        .woo-wallet-sidebar ul {
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: start;
            margin: 0;
            padding: 0;
        }
        .woo-wallet-sidebar li.card a {
            display: none;
            text-decoration: none;
            color: var(--amd-primary);
            background: rgba(var(--amd-primary-rgb), .2);
            width: max-content;
            line-height: 6px;
            padding: 4px 14px;
            margin: 4px;
            border-radius: 12px;
        }*/
    </style>
<?php endif; ?>
