<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
?>
<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
		<a href="/" class="d-flex align-items-center text-dark text-decoration-none"><img id="logo" src="<?php echo bloginfo('template_url');?>/assets/images/logo.png"></a>
		<?php 
					$user_id = get_current_user_id();
					$account_type = get_user_meta($user_id, 'account_type', true);
				?>
				<?php if($account_type==="ds_dealer") { ?>
					<h1 id="ds_dashboard" class="text-center"><a href="<?php echo site_url();?>/sponsor/dashboard/">Dealer's Dashboard</a></h1>
				<?php } else {?>
					<h1 id="ds_dashboard" class="text-center"><a href="<?php echo site_url();?>/sponsor/become-a-dealer/">Member's Dashboard</a></h1>
				<?php } ?>
		<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<span class="ds_<?php echo wc_get_account_menu_item_classes($label); ?>"></span><?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>	
		</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
