<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>
		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
		<div class="container">
			<div class="row mt-5 mb-5">
				<div class="col-md-6">
					<img id="checkoutLogo" src="<?php echo bloginfo('template_url');?>/assets/images/checkout-logo.png">
				</div>
				<div class="col-md-6"></div>
			</div>
			<div class="row mb-5">
				<div class="col-md-7">
					<h3 class="align-top"><?php esc_html_e( 'Billing Information', 'woocommerce' ); ?></h3>
					<p class='desc' style="display:none">Pellentesque vitae consequat ut mattis curabitur pellentesque. Integer porttitor iaculis vivamus nec nunc ipsum. Id facilisi lacinia mi sed. Commodo consequat sed sem eleifend convallis. Risus at in non maecenas. Vel viverra integer massa velit. Nulla.</p>
				</div>
				<div class="col-md-5">
					<?php
						$fields = $checkout->get_checkout_fields( 'billing' );

						foreach ( $fields as $key => $field ) {
							woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
						}
					?>
				</div>
			</div>
			<div class="row mb-5">
				<div class="col-md-7">
					<h3 class="align-top"><?php esc_html_e( 'Billing Address', 'woocommerce' ); ?></h3>
					<p class="desc" style="display:none">Pellentesque vitae consequat ut mattis curabitur pellentesque. Integer porttitor iaculis vivamus nec nunc ipsum. Id facilisi lacinia mi sed. Commodo consequat sed sem eleifend convallis. Risus at in non maecenas. Vel viverra integer massa velit. Nulla.</p>
				</div>
				<div class="col-md-5">
					<?php
						$fields = $checkout->get_checkout_fields( 'shipping' );

						foreach ( $fields as $key => $field ) {
							woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
						}
					?>
				</div>
			</div>
		</div>
		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
		
		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
		
		<h3 id="order_review_heading"><?php esc_html_e( 'Product Invoice', 'woocommerce' ); ?></h3>
		
		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		</div>

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
	<?php endif; ?>
	
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
