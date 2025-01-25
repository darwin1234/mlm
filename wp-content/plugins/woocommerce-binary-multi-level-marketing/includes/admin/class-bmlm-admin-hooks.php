<?php
/**
 * Admin End Hooks.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Admin_Hooks' ) ) {
	/**
	 * Admin hooks class.
	 */
	class BMLM_Admin_Hooks {
		/**
		 *
		 * Admin end hooks construct.
		 */
		public function __construct() {

			$handler = new BMLM_Admin_Functions();

			add_action( 'admin_init', array( $handler, 'bmlm_register_settings' ) );
			add_action( 'admin_menu', array( $handler, 'bmlm_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $handler, 'bmlm_enqueue_admin_scripts' ) );

			add_action( 'user_register', array( $handler, 'bmlm_save_sponsor_custom_data' ) );
			add_action( 'user_new_form', array( $handler, 'bmlm_add_custom_template' ) );
			add_filter( 'user_profile_update_errors', array( $handler, 'bmlm_validate_user_fields' ) );
			add_action( 'delete_user', array( $handler, 'bmlm_network_delete_user' ), 10, 3 );

			add_filter( 'plugin_row_meta', array( $handler, 'bmlm_add_plugin_setting_links' ), 10, 2 );
			add_filter( 'plugin_action_links_' . BMLM_PLUGIN_BASENAME, array( $handler, 'bmlm_plugin_action_links' ), 10, 2 );

			add_action( 'bmlm_sponsors_tabs_content', array( $handler, 'bmlm_sponsors' ) );
			add_action( 'bmlm_commissions_tabs_content', array( $handler, 'bmlm_commission' ) );
			add_action( 'bmlm_genealogy_tabs_content', array( $handler, 'bmlm_genealogy' ) );
			add_action( 'bmlm_wallet_tabs_content', array( $handler, 'bmlm_manage_wallet' ) );
			add_action( 'bmlm_badges_tabs_content', array( $handler, 'bmlm_badges' ) );
			add_action( 'bmlm_transaction_tabs_content', array( $handler, 'bmlm_payout' ) );
			add_action( 'bmlm_settings_tabs_content', array( $handler, 'bmlm_configuration' ) );
			add_filter( 'set-screen-option', array( $handler, 'bmlm_set_option' ), 10, 3 );
			add_filter( 'woocommerce_screen_ids', array( $handler, 'bmlm_set_wc_screen_ids' ), 10, 1 );

			add_filter( 'wkwc_wallet_allow_indexing', array( $handler, 'bmlm_allow_indexing_from_group_product_page' ) );

			add_filter( 'wkwc_wallet_allow_transaction_migration_notice', array( $handler, 'bmlm_allow_indexing_progress_notice' ) );
		}
	}
}
