<?php
/**
 * Order again button
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-again.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;
?>

<p class="order-again">
	<a id="gotoyourdashboard" href="<?php echo esc_url( site_url() . '/my-account' ); ?>" class="button<?php echo esc_attr( $wp_button_class ); ?>"><?php esc_html_e( 'Go to your Dashboard', 'woocommerce' ); ?></a>
</p>
