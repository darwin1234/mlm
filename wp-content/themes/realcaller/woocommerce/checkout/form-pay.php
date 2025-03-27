<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.2.0
 */

defined( 'ABSPATH' ) || exit;

$totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
?>
<style>
	.woocommerce-error{
		display:none;
	}
	#ds_invoice{
		border:1px solid #ccc;
		padding-top:50px;
		padding:10px;
	}
</style>
<div id="ds_invoice" class="container">
	<div class="row">
		  <div class="col-md-12">
			<h3 class="text-end">INVOICE</h3>
			<hr>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<img style="width:200px;" src="<?php echo bloginfo('template_url'); ?>/assets/images/logo.png">
		</div>
		<div class="col-md-6">
			 <p  class="text-end">Dealsdpt inc. O/A MarketingDPT</p>
			 <p  class="text-end">(416) 410-7552</p>
			 <p  class="text-end">1135 Stellar Dr</p>
			 <p  class="text-end">Newmarket, ON L3Y 7B8</p>
			 <p  class="text-end">Canada</p>
		</div>
		<div class="col-md-12">
			<hr>
		</div>
	</div>
	<div class="row">
	<div class="col-md-8">
    <table class="table">
        <tr>
            <td><strong>Billed to</strong></td>
            <td><strong>Invoice No</strong></td>
            <td><strong>Issue Date</strong></td>
        </tr>
        <tr>
            <td>
                <?php 
                // Get customer details from order
                $billing_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $billing_email = $order->get_billing_email();
                $billing_country = WC()->countries->countries[$order->get_billing_country()] ?? $order->get_billing_country();
                ?>
                <p><?php echo esc_html($billing_name); ?></p>
                <p><?php echo esc_html($billing_email); ?></p>
                <p><?php echo esc_html($billing_country); ?></p>
            </td>
            <td>
                INV-<?php echo str_pad($order->get_id(), 6, '0', STR_PAD_LEFT); ?>
            </td>
            <td>
                <?php echo date_i18n('F j, Y', strtotime($order->get_date_created())); ?>
            </td>
        </tr>
    </table>
</div>
		<div class="col-md-4">
			
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="width:100%; background-color:#2EAD2E; border-color:#2EAD2E;">
			  Pay 
			</button>

		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
		<form id="order_review" method="post">
			<table class="shop_table">
				<thead>
					<tr>
						<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th> 
						<th class="product-quantity"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
						<th class="product-total"><?php esc_html_e( 'Totals', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( count( $order->get_items() ) > 0 ) : ?>
						<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
							<?php
							if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
								continue;
							}
							?>
							<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
								<td class="product-name">
									<?php
										echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );

										do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

										wc_display_item_meta( $item );

										do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
									?>
								</td>
								<td class="product-quantity"><?php echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', esc_html( $item->get_quantity() ) ) . '</strong>', $item ); ?></td><?php // @codingStandardsIgnoreLine ?>
								<td class="product-subtotal"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td><?php // @codingStandardsIgnoreLine ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<?php if ( $totals ) : ?>
						<?php foreach ( $totals as $total ) : ?>
							<tr>
								<th scope="row" colspan="2"><?php echo $total['label']; ?></th><?php // @codingStandardsIgnoreLine ?>
								<td class="product-total"><?php echo $total['value']; ?></td><?php // @codingStandardsIgnoreLine ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tfoot>
			</table>

			<?php
			/**
			 * Triggered from within the checkout/form-pay.php template, immediately before the payment section.
			 *
			 * @since 8.2.0
			 */
			do_action( 'woocommerce_pay_order_before_payment' ); 
			?>

			<div id="payment">
				

				<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
					<div class="modal-body">
					<?php if ( $order->needs_payment() ) : ?>
					<ul class="wc_payment_methods payment_methods methods">
						<?php
						if ( ! empty( $available_gateways ) ) {
							foreach ( $available_gateways as $gateway ) {
								wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
							}
						} else {
							echo '<li>';
							wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ), 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
							echo '</li>';
						}
						?>
					</ul>
					<?php endif; ?>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="woocommerce_pay" value="1" />

						<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

						<?php echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

						<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

						<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
					</div>
					</div>
				</div>
				</div>
				<div class="form-row">
					
				
				</div>
				<?php wc_get_template( 'checkout/terms.php' ); ?>
			</div>
			</form>
		</div>
	</div>
</div>


