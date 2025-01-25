<?php
/**
 * Installation related functions and actions.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Install' ) ) {

	/**
	 * BMLM Install Class.
	 */
	class BMLM_Install {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		public $wpdb;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
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
		 * Hook in tabs.
		 */
		public function init() {
			$this->bmlm_create_admin_sponsor_id();
			$this->bmlm_create_sponsor_page();
			$this->bmlm_create_sponsor_role();
			$this->bmlm_create_tables();
		}

		/**
		 * Generate Admin sponsor id.
		 */
		public function bmlm_create_admin_sponsor_id() {
			global $bmlm;

			$first_admin_id = $bmlm->bmlm_get_first_admin_user_id();
			$obj            = BMLM_Sponsor::get_instance( $first_admin_id );

			if ( empty( get_user_meta( $first_admin_id, 'bmlm_sponsor_id', true ) ) ) {
				$obj->bmlm_generate_sponsor_id( $first_admin_id );
				$obj->bmlm_upgrade_network_id( $first_admin_id, 0 );
				$obj->bmlm_upgrade_sponsor_level( $first_admin_id, 0 );
			}
		}

		/**
		 * Create Sponsor Page on activation
		 *
		 * @return void
		 */
		public function bmlm_create_sponsor_page() {
			$pages = apply_filters(
				'bmlm_sponsor_pages_data',
				array(
					'sponsor' => array(
						'title'   => esc_html__( 'Sponsor', 'binary-mlm' ),
						'content' => '[sponsor]',
					),
				)
			);

			foreach ( $pages as $key => $value ) {
				$page = get_page_by_path( $key );
				if ( ! $page ) {
					$page_data = array(
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'post_author'    => get_current_user_id(),
						'post_name'      => $key,
						'post_title'     => $value['title'],
						'post_content'   => $value['content'],
						'comment_status' => 'closed',
					);

					$page_id = wp_insert_post( $page_data );
				} else {
					$page_id = $page->ID;
				}

				update_option( 'bmlm_sponsor_page_id', $page_id, 'no' );
				update_option( 'bmlm_joining_amount_settings_enable', 1 );
				update_option( 'bmlm_levelup_amount_settings_enable', 1 );
				update_option( 'bmlm_refferal_code_length', 8 );
			}
		}

		/**
		 * Create new sponsor role on activation
		 *
		 * @return void
		 */
		public function bmlm_create_sponsor_role() {

			add_role(
				'bmlm_sponsor',
				'Sponsor',
				array(
					'read'                   => true, // True allows that capability.
					'manage_mlm'             => true,
					'edit_posts'             => true,
					'delete_posts'           => true, // Use false to explicitly deny.
					'publish_posts'          => true,
					'edit_published_posts'   => false,
					'upload_files'           => true,
					'delete_published_posts' => true,
				)
			);
		}

		/**
		 * Create marketplace tables.
		 */
		public function bmlm_create_tables() {
			$wpdb_obj   = $this->wpdb;
			$db_version = get_option( 'bmlm_db_version', '0.0.0' );

			if ( version_compare( BMLM_DB_VERSION, $db_version, '>' ) ) {
				$charset_collate = $wpdb_obj->get_charset_collate();

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';

				$sponsor_badge       = $wpdb_obj->prefix . 'bmlm_sponsor_badge';
				$sponsor_badge_table = "CREATE TABLE $sponsor_badge (
					`id`           BIGINT(20) NOT NULL AUTO_INCREMENT,
					`name`         VARCHAR(255) NOT NULL,
					`slug`         VARCHAR(255) NOT NULL,
					`max_business` DOUBLE NOT NULL,
					`bonus_amt`    DOUBLE NOT NULL,
					`priority`     INT NOT NULL,
					`image`        INT NOT NULL,
					`status`       TINYINT NOT NULL,
					`date`         DATETIME NOT NULL,
					PRIMARY KEY (id)
				) $charset_collate;";
				dbDelta( $sponsor_badge_table );

				$sponsor_badge_meta       = $wpdb_obj->prefix . 'bmlm_sponsor_badge_meta';
				$sponsor_badge_meta_table = "CREATE TABLE $sponsor_badge_meta (
					`id`       BIGINT(20) NOT NULL AUTO_INCREMENT,
					`user_id`  BIGINT(20) NOT NULL,
					`badge_id` BIGINT(20) NOT NULL,
					`date`     datetime NOT NULL,
					PRIMARY KEY (id)
				) $charset_collate;";
				dbDelta( $sponsor_badge_meta_table );

				$gtree_nodes       = $wpdb_obj->prefix . 'bmlm_gtree_nodes';
				$gtree_nodes_table = "CREATE TABLE IF NOT EXISTS $gtree_nodes (
					`id`     BIGINT(20) NOT NULL AUTO_INCREMENT,
					`child`  BIGINT,
					`parent` BIGINT,
					`nrow`   BIGINT,
					PRIMARY KEY (id)
				) $charset_collate;";
				dbDelta( $gtree_nodes_table );

				$commission       = $wpdb_obj->prefix . 'bmlm_commission';
				$commission_table = "CREATE TABLE IF NOT EXISTS $commission (
					id          BIGINT(20) NOT NULL AUTO_INCREMENT,
					user_id     INT NOT NULL,
					type        VARCHAR(50) NOT NULL,
					description VARCHAR(250) NOT NULL,
					commission  DOUBLE NOT NULL,
					date        DATETIME NOT NULL,
					paid        BOOLEAN NOT NULL,
					PRIMARY KEY (id)
				) $charset_collate;";
				dbDelta( $commission_table );

				$network_users       = $wpdb_obj->prefix . 'bmlm_network_users';
				$network_users_table = "CREATE TABLE IF NOT EXISTS $network_users (
					`id`        BIGINT(20) NOT NULL AUTO_INCREMENT,
					`user_id`   BIGINT,
					`user_data` LONGTEXT NOT NULL,
					`status`    INT(2) comment '0 => Pending, 1 => Enabled, 2 => Disabled',
					PRIMARY KEY (id)
				) $charset_collate;";
				dbDelta( $network_users_table );

				update_option( '_bmlm_db_prev_version', $db_version, true );
				update_option( 'bmlm_db_version', BMLM_DB_VERSION );
			}
		}

		/**
		 * Migrate old wallet settings to the new key.
		 *
		 * @param string $new_wallet_key New wallet key.
		 * @param string $new_phone_key New phone key.
		 * @param string $new_wallet_table New wallet table name.
		 * @param string $table_name_option_key New wallet table name option key.
		 *
		 * @return void
		 */
		public function bmlm_migrate_to_wkwc_wallet( $new_wallet_key, $new_phone_key, $new_wallet_table, $table_name_option_key ) {
			$get_bmlm_prev_db_version = get_option( '_bmlm_db_prev_version', '0.0.0' );

			if ( version_compare( $get_bmlm_prev_db_version, '1.0.0', '<' ) ) {
				// 1. Migrating wallet amount and phone number.
				$old_wallet_key = 'mlmwallet';
				$old_phone_key  = 'wp_user_phone';

				$wallet_users = get_users(
					array(
						'fields'   => 'ID',
						'meta_key' => array( $old_wallet_key, $old_phone_key ),
					)
				);
				foreach ( $wallet_users as $user_id ) {
					$new_amount = get_user_meta( $user_id, $new_wallet_key, true );
					$new_amount = empty( $new_amount ) ? 0 : floatval( $new_amount );
					$old_amount = get_user_meta( $user_id, $old_wallet_key, true );
					$old_amount = empty( $old_amount ) ? 0 : floatval( $old_amount );

					$new_amount += $old_amount;
					update_user_meta( $user_id, $new_wallet_key, $new_amount );
					// Migrating phone numbers.
					$old_phone = get_user_meta( $user_id, $old_phone_key, false );
					$new_phone = get_user_meta( $user_id, $new_phone_key, false );

					if ( empty( $new_phone ) && ! empty( $old_phone ) ) {
						update_user_meta( $user_id, $new_phone_key, $old_phone );
					}
					delete_user_meta( $user_id, $old_phone_key );
					delete_user_meta( $user_id, $old_wallet_key );
				}
			}

			$this->bmlm_rename_old_transaction_table_column();
			$this->bmlm_create_or_rename_transaction_table( $new_wallet_table, $table_name_option_key );
		}

		/**
		 * Creating (for fresh installation) or rename (for Update plugin) wallet transaction table.
		 *
		 * @param string $new_transaction_table_name New transaction table name.
		 * @param string $table_name_option_key New transaction table option key.
		 *
		 * @return void
		 */
		public function bmlm_create_or_rename_transaction_table( $new_transaction_table_name, $table_name_option_key ) {
			$wpdb_obj        = $this->wpdb;
			$charset_collate = $wpdb_obj->get_charset_collate();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$new_transactions = $wpdb_obj->prefix . $new_transaction_table_name;
			$old_transactions = $wpdb_obj->prefix . 'bmlm_wallet_transactions';

			$transactions_check     = $wpdb_obj->get_var( "SHOW TABLES LIKE '$old_transactions'" ); // If old transactions table exists.
			$new_transactions_check = $wpdb_obj->get_var( "SHOW TABLES LIKE '$new_transactions'" ); // If new transactions table exists.
			if ( $transactions_check !== $old_transactions && $new_transactions_check !== $new_transactions ) { // Wallet transactions table in not created yet.
				$transactions_sql = "CREATE TABLE IF NOT EXISTS $new_transactions (
								id bigint(20) NOT NULL AUTO_INCREMENT,
								order_id varchar(250),
								reference varchar(100) NOT NULL,
								sender int(10) NOT NULL,
								customer int(10) NOT NULL,
								amount varchar(50) NOT NULL,
								transaction_type varchar(10) NOT NULL,
								transaction_date datetime NOT NULL,
								transaction_status varchar(10) DEFAULT 'completed',
								transaction_note varchar(250),
							PRIMARY KEY (`id`)
				) $charset_collate;";

				dbDelta( $transactions_sql );
			} elseif ( $transactions_check === $old_transactions && empty( $new_transactions_check ) ) { // Old transactions table is there, need to rename it.
				$rename_transactions_sql = "RENAME TABLE $old_transactions to $new_transactions";
				$wpdb_obj->query( $rename_transactions_sql );

				$this->bmlm_maybe_replace_reference();

			} elseif ( $new_transactions_check === $new_transactions ) {

				$this->bmlm_maybe_replace_reference();

				// Update option to migrate data from old transaction table to new transaction table via background process.
				update_option( $table_name_option_key, $new_transaction_table_name );
			}
		}

		/**
		 * Replace reference to 'bmlm_wallet' for renamed table in previous version.
		 *
		 * @return void
		 */
		public function bmlm_maybe_replace_reference() {
			$get_bmlm_prev_db_version = get_option( '_bmlm_db_prev_version', '0.0.0' );

			if ( version_compare( $get_bmlm_prev_db_version, '1.0.0', '<' ) ) {
				$wpdb_obj = $this->wpdb;

				$sql = "UPDATE {$wpdb_obj->prefix}wkwc_wallet_transactions SET `reference`='" . BMLM_REFERENCE . "' WHERE `reference` NOT IN ( 'wkwp_wallet', 'wkgbuy_wallet', 'bmlm_wallet', 'wkmp_gbuy_wallet' )";
				$wpdb_obj->query( $sql );
			}
		}

		/**
		 * Migrate Wallet gateway settings.
		 *
		 * @return void
		 */
		public function bmlm_maybe_migrate_gateway_settings() {
			// Migrating wallet gateway settings from binary module 1.2.0 or older to wkwc_wallet_db_version - 1.0.1.
			$bmlm_prev_db_version = get_option( 'bmlm_db_version', '0.0.0' );

			if ( version_compare( $bmlm_prev_db_version, '1.0.0', '<' ) ) {
				$wkwc_wallet_setting = get_option( 'woocommerce_wkwc_wallet_settings', array() );
				$wallet_setting      = get_option( 'woocommerce_wallet_settings', array() );

				if ( empty( $wkwc_wallet_setting ) && ! empty( $wallet_setting ) ) {
					update_option( 'woocommerce_wkwc_wallet_settings', $wallet_setting );
					delete_option( 'woocommerce_wallet_settings' );
				}
			}
		}

		/**
		 * Migrate from old table to new table.
		 *
		 * @param string $table_name_option_key Table name option key.
		 *
		 * @return bool|array
		 */
		public function bmlm_wallet_migrate_wallet_transactions( $table_name_option_key ) {
			$wpdb_obj    = $this->wpdb;
			$_table_name = get_option( $table_name_option_key, '' );
			$index_ids   = array();

			if ( ! empty( $_table_name ) ) {
				$batch_size  = apply_filters( 'bmlm_migrate_batch', 20 );
				$last_offset = get_option( '_wkwc_wallet_offset', 0 );

				$wallet_transactions = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * from {$wpdb_obj->prefix}bmlm_wallet_transactions WHERE id > %d LIMIT %d", $last_offset, $batch_size ), ARRAY_A );
				$new_data            = array();
				foreach ( $wallet_transactions as $value ) {
					if ( ! empty( $value['id'] ) ) {
						$index_ids[] = $value['id'];

						$new_data['order_id']           = $value['transaction_id'];
						$new_data['reference']          = BMLM_REFERENCE;
						$new_data['sender']             = $value['sender'];
						$new_data['customer']           = $value['customer'];
						$new_data['amount']             = $value['amount'];
						$new_data['transaction_type']   = $value['type'];
						$new_data['transaction_date']   = $value['date'];
						$new_data['transaction_status'] = '';
						$new_data['transaction_note']   = $value['note'];
					}

					if ( ! empty( $index_ids ) ) {
						$wpdb_obj->insert( "{$wpdb_obj->prefix}" . $_table_name, $new_data );
					} else {
						$wpdb_obj->query( "DROP TABLE IF EXISTS {$wpdb_obj->prefix}bmlm_wallet_transactions" );
					}
				}
			}

			return $index_ids;
		}

		/**
		 * Rename Old table column name with new table
		 */
		public function bmlm_rename_old_transaction_table_column() {
				$wpdb_obj         = $this->wpdb;
				$old_transactions = $wpdb_obj->prefix . 'bmlm_wallet_transactions';
				// Check if the table exists.
				$table_exists = $wpdb_obj->get_var( "SHOW TABLES LIKE '$old_transactions'" ) === $old_transactions;

			if ( $table_exists ) {
				$result = $wpdb_obj->query( "SHOW COLUMNS FROM {$wpdb_obj->prefix}bmlm_wallet_transactions LIKE 'transaction_id'" );

				if ( $result ) {
					$wpdb_obj->query( "ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions RENAME COLUMN `transaction_id` TO `order_id`" );
					$wpdb_obj->query( "ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions RENAME COLUMN `type` TO `transaction_type`" );
					$wpdb_obj->query( "ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions RENAME COLUMN `date` TO `transaction_date`" );
					$wpdb_obj->query( "ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions RENAME COLUMN `note` TO `transaction_note`" );
					$wpdb_obj->query( "ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions DROP `reference`" );
				}

				$reference = $wpdb_obj->query( "SHOW COLUMNS FROM {$wpdb_obj->prefix}bmlm_wallet_transactions LIKE 'reference'" );
				if ( ! $reference ) {
					$wpdb_obj->query(
						"ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions ADD reference VARCHAR( 100 ) NOT NULL AFTER order_id"
					);
						$wpdb_obj->query(
							"ALTER TABLE {$wpdb_obj->prefix}bmlm_wallet_transactions ADD transaction_status VARCHAR( 100 ) DEFAULT 'completed' AFTER transaction_date"
						);
						$this->bmlm_create_tables();
				}
			}
		}
	}
}
