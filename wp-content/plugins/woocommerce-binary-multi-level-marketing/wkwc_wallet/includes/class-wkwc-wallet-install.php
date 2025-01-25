<?php
/**
 * Schema create on Activation
 *
 * @package WKWC_Wallet
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WKWC_Wallet_Install' ) ) {
	/**
	 * Install Wallet
	 */
	class WKWC_Wallet_Install {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Functions Construct
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init_schema' ) );
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}

		/**
		 * Function initialization.
		 */
		public function init_schema() {
			$get_db_version = get_option( '_wkwc_wallet_db_version', '0.0.0' );
			$migrated       = false;

			if ( version_compare( WKWC_WALLET_DB_VERSION, $get_db_version, '>' ) ) {
				$new_wallet_table      = 'wkwc_wallet_transactions';
				$table_name_option_key = '_wkwc_wallet_new_transaction_table';

				// Create or rename/migrate transaction table data for wallet system module.
				if ( defined( 'WKWP_WALLET_DB_VERSION' ) && version_compare( WKWP_WALLET_DB_VERSION, '1.0.0', '>' ) ) {
					$wkwp_wallet_install = WKWP_Wallet_Install::get_instance();

					$new_wallet_key = 'wkwc_wallet_amount';
					$new_phone_key  = 'wkwc_wallet_phone_number';

					$wkwp_wallet_install->wkwp_wallet_migrate_to_wkwc_wallet( $new_wallet_key, $new_phone_key, $new_wallet_table, $table_name_option_key );
					$migrated = true;
				}

				// Migrating for WC Group Buy module.
				if ( defined( 'WKGBUY_DB_VERSION' ) && version_compare( WKGBUY_DB_VERSION, '1.0.1', '>' ) ) {
					$wkgbuy_install = WKGBUY_Install::get_instance();

					$new_wallet_key = 'wkwc_wallet_amount';
					$new_phone_key  = 'wkwc_wallet_phone_number';

					$wkgbuy_install->wkgbuy_migrate_to_wkwc_wallet( $new_wallet_key, $new_phone_key, $new_wallet_table, $table_name_option_key );
					$migrated = true;
				}

				// Migrating for WC Group Buy module.
				if ( defined( 'BMLM_DB_VERSION' ) && version_compare( BMLM_DB_VERSION, '1.0.0', '>' ) ) {
					$bmlm_install   = BMLM_Install::get_instance();
					$new_wallet_key = 'wkwc_wallet_amount';
					$new_phone_key  = 'wkwc_wallet_phone_number';

					$bmlm_install->bmlm_migrate_to_wkwc_wallet( $new_wallet_key, $new_phone_key, $new_wallet_table, $table_name_option_key );
					$migrated = true;
				}
			}

			if ( $migrated ) {
				do_action( 'wkwc_wallet_settings_migrated' );
			}
		}
	}
}
