<?php
/**
 * Plugin Name: WooCommerce Binary Multi Level Marketing
 * Plugin URI: https://codecanyon.net/item/woocommerce-binary-multi-level-marketing-mlm/32605042
 * Description: Allows store owners to add Binary MLM system into WooCommerce Store.
 * Version: 1.1.0
 * Author: Webkul
 * Author URI: https://webkul.com
 * Text Domain: binary-mlm
 * Domain Path: /languages
 *
 * WKWC_Addons: 1.1.7
 * WKWC_Settings: bmlm_sponsors&tab=bmlm_settings
 * WKWC_Icon_URL: https://store.webkul.com/media/catalog/product/cache/1/image/260x260/9df78eab33525d08d6e5fb8d27136e95/w/o/woocommerce-binary-multi-level-marketing-webkul.png
 *
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested upto: 8.5
 *
 * License: license.txt included with plugin
 * License URI: https://store.webkul.com/license.html
 *
 * Blog URI: https://webkul.com/blog/binary-multi-level-marketing-for-woocommerce/
 *
 * Requires Plugins: WooCommerce
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

use WCBMLMARKETING\Includes\BMLM;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

// Define Constants.
defined( 'BMLM_FILE' ) || define( 'BMLM_FILE', __FILE__ );
defined( 'BMLM_PLUGIN_FILE' ) || define( 'BMLM_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
defined( 'BMLM_PLUGIN_URL' ) || define( 'BMLM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'BMLM_PLUGIN_BASENAME' ) || define( 'BMLM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
defined( 'BMLM_PLUGIN_VERSION' ) || define( 'BMLM_PLUGIN_VERSION', '1.1.0' );
defined( 'BMLM_SCRIPT_VERSION' ) || define( 'BMLM_SCRIPT_VERSION', '1.1.0' );
defined( 'WKWP_WALLET_WKWC_WALLET_VERSION' ) || define( 'WKWP_WALLET_WKWC_WALLET_VERSION', '1.0.6' );
defined( 'WKWP_WALLET_WK_CACHING_VERSION' ) || define( 'WKWP_WALLET_WK_CACHING_VERSION', '1.0.8' );
defined( 'BMLM_REFERENCE' ) || define( 'BMLM_REFERENCE', 'bmlm_wallet' );
// Change this DB version value if updating any table structure like alter, drop, insert tables or columns.
defined( 'BMLM_DB_VERSION' ) || define( 'BMLM_DB_VERSION', '1.1.1' ); // 1.0.1 -> 1.1.1 Using WKWC_Wallet submdoule tables for transactions.

require_once __DIR__ . '/inc/class-bmlm-autoload.php';
require_once __DIR__ . '/modules/class-wkwc-modules-autoload.php';
new BMLM_Autoload();

if ( ! function_exists( 'bmlm_wc_log' ) ) {
	/**
	 * Adding log for debugging.
	 *
	 * @param string $message Message to log.
	 * @param array  $context Context.
	 * @param string $level Level.
	 *
	 * @return void
	 */
	function bmlm_wc_log( $message, $context = array(), $level = 'info' ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			$log_enabled = apply_filters( 'bmlm_is_log_enabled', true );
			if ( $log_enabled ) {
				$source            = ( is_array( $context ) && isset( $context['source'] ) && ! empty( $context['source'] ) ) ? $context['source'] : 'bmlm';
				$context['source'] = $source;
				$logger            = wc_get_logger();
				$current_user_id   = get_current_user_id();

				$in_action = wp_sprintf( ( /* translators: %s current user id */ esc_html__( 'User in action: %s: ', 'binary-mlm' ) ), $current_user_id );
				$message   = $in_action . $message;

				$logger->log( $level, $message, $context );
			}
		}
	}
}

if ( ! function_exists( 'bmlm_truncate_plugin_database_tables' ) ) {
	/**
	 * Reset plugins database
	 *
	 * @return void
	 */
	function bmlm_truncate_plugin_database_tables() {
		global $wpdb;
		$wpdb_obj = $wpdb;

		if ( isset( $_POST['reset_mlm'] ) ) {
			update_user_meta( 1, 'bmlm_tree_level', 0 );
			update_user_meta( 1, 'bmlm_network_id', 0 );
			update_user_meta( 1, 'bmlm_join_level', 0 );
			$table_array = array(
				$wpdb_obj->prefix . 'bmlm_commission',
				$wpdb_obj->prefix . 'bmlm_gtree_nodes',
				$wpdb_obj->prefix . 'bmlm_network_users',
				$wpdb_obj->prefix . 'bmlm_sponsor_badge',
				$wpdb_obj->prefix . 'bmlm_sponsor_badge_meta',
				$wpdb_obj->prefix . 'bmlm_wallet_transactions',
			);

			foreach ( $table_array as $table_name ) {
				if ( $wpdb_obj->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
					$wpdb_obj->query( "TRUNCATE TABLE {$table_name}" );
				}
			}
		}
	}
	add_action( 'init', 'bmlm_truncate_plugin_database_tables' );
}

if ( ! function_exists( 'bmlm_get_sponsor_parent_levels' ) ) {

	/**
	 * Get Parent sponsor
	 *
	 * @return void
	 */
	function bmlm_get_sponsor_parent_levels() {
		do_action( 'bmlm_upgrade_parent_sponsor_levels', 99 );
	}

	add_action( 'init', 'bmlm_get_sponsor_parent_levels' );
}

if ( ! function_exists( 'bmlm_declare_compatibility' ) ) {

	/**
	 * Declare compatibility.
	 *
	 * @return void
	 */
	function bmlm_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', BMLM_FILE, true );
		}
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', BMLM_FILE, false );
		}
	}
	add_action( 'before_woocommerce_init', 'bmlm_declare_compatibility' );
}

/**
 * Remove Old plugin on activation
 */
register_activation_hook( __FILE__, 'bmlm_remove_old_plugin' );

/**
 * Remove Old plugin on activation
 */
function bmlm_remove_old_plugin() {
	if ( is_plugin_active( 'binary-mlm/binary-mlm.php' ) ) {
		deactivate_plugins( 'binary-mlm/binary-mlm.php' );
		delete_plugins( array( 'binary-mlm/binary-mlm.php' ) );
	}
}

$GLOBALS['bmlm'] = BMLM::instance();
