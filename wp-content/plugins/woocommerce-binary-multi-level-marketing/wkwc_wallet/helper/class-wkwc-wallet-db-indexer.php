<?php
/**
 * DB Indexer.
 *
 * @package WKWC_Wallet
 *
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

/**
 * Class WKWC_Wallet_DB_Indexer
 */
class WKWC_Wallet_DB_Indexer {
	/**
	 * Ins
	 *
	 * @var $ins
	 */
	public static $ins;

	/**
	 * Background Indexer.
	 *
	 * @var WKWC_Wallet_DB_Indexer $indexer
	 */
	public $indexer;

	/**
	 * Background Indexer.
	 *
	 * @var Background_Indexer $wallet_indexer
	 */
	public $wallet_indexer;

	/**
	 * Order.
	 *
	 * @var Order id in progress $product_id_in_process
	 */
	public $wallet_id_in_process;

	/**
	 * Indexing
	 *
	 * @var null Used when product indexing is running
	 */
	public static $indexing = null;

	/**
	 * WKWC_Wallet_DB_Indexer constructor.
	 */
	public function __construct() {
		/** Initiate Background Indexing on initialization */
		add_action( 'init', array( $this, 'wkwc_wallet_init_background_indexer' ), 110 );
		add_action( 'admin_init', array( $this, 'wkwc_wallet_maybe_index_transactions' ), 120 );

		add_action( 'wkwc_wallet_indexing_completed', array( $this, 'maybe_change_state_on_success' ) );
		add_action( 'admin_footer', array( $this, 'maybe_re_dispatch_background_process' ) );

		add_action( 'admin_head', array( $this, 'wkwc_wallet_show_indexing_notice' ) );
	}

	/**
	 * Show admin notice about indexing progress.
	 *
	 * @return void
	 */
	public function wkwc_wallet_show_indexing_notice() {
		$show_notice = apply_filters( 'wkwc_wallet_allow_transaction_migration_notice', false );
		if ( $show_notice ) {
			$dismiss = filter_input( INPUT_GET, 'wkwc_wallet_dismiss', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( 'yes' === $dismiss ) {
				$this->set_indexing_state( 3 );
			}

			$state   = $this->get_indexing_state();
			$message = '';

			if ( '1' === $state ) {
				$message = esc_html__( 'Your wallet transaction database table is updating to newer version in background. We\'ll update this notice once it is completed.', 'wkwc_wallet' );
			}

			if ( '2' === $state ) {
				$actual_link = empty( $_SERVER['HTTP_HOST'] ) ? ' ' : wc_clean( $_SERVER['HTTP_HOST'] );
				if ( ! empty( $actual_link ) ) {
					$actual_link .= empty( $_SERVER['REQUEST_URI'] ) ? ' ' : wc_clean( $_SERVER['REQUEST_URI'] );
				}

				$dismiss_url = $actual_link . '&wkwc_wallet_dismiss=yes';

				$message = wp_sprintf( /* translators: %1$s: Dismiss link, %2$s: Closing anchor. */ esc_html__( 'Your wallet transaction database table has been updated. %1$s Dismiss %2$s', 'wkwc_wallet' ), '<a href="' . esc_url( $dismiss_url ) . '">', '</a>' );
			}

			WKWC_Wallet::wkwc_wallet_show_admin_notice( $message, 'success' );
		}
	}

	/**
	 * Get single instance of the class.
	 *
	 * @return WKWC_Wallet_DB_Indexer.
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Set indexing state.
	 *
	 * @param int $stage Stage.
	 *
	 * @return void
	 */
	public function set_indexing_state( $stage ) {
		update_option( '_wkwc_wallet_db_index_state', $stage, true );
	}

	/**
	 * Get indexing state.
	 *
	 * @return int
	 */
	public function get_indexing_state() {
		/**
		 * 0: Indexing can be started.
		 * 1: Indexing is dispatched and running.
		 * 2: Indexing is completed
		 * 3: Indexing is completed and notice is dismissed.
		 */
		return get_option( '_wkwc_wallet_db_index_state', '0' );
	}

	/**
	 * Initiate WKWC_Wallet_Background_Indexer class
	 *
	 * @see wkwces_maybe_index_product
	 */
	public function wkwc_wallet_init_background_indexer() {
		if ( ! class_exists( 'WKWC_Wallet_Background_Indexer' ) ) {
			require_once __DIR__ . '/class-wkwc-wallet-background-indexer.php';
		}

		if ( class_exists( 'WKWC_Wallet_Background_Indexer' ) ) {
			$this->indexer = new WKWC_Wallet_Background_Indexer();
		}
	}

	/**
	 * Updating state on completed.
	 *
	 * @return void
	 */
	public function maybe_change_state_on_success() {
		delete_option( '_wkwc_wallet_last_offsets' );
		WKWC_Wallet::log( 'WKWC_Wallet indexing completed.' );

		update_option( '_wkwc_wallet_db_version', WKWC_WALLET_DB_VERSION, true );

		$this->set_indexing_state( '2' );
	}

	/**
	 * This method takes care of database updating process.
	 * Checks whether there is a need to update the database
	 * Iterates over define callbacks and passes it to background updater class
	 * Update wkwc_wallet_transactions table
	 *
	 * @hooked over `admin_head`
	 */
	public function wkwc_wallet_maybe_index_transactions() {
		if ( is_null( $this->indexer ) ) {
			return;
		}

		$get_db_version = get_option( '_wkwc_wallet_db_version', '0.0.0' );
		$start_indexing = apply_filters( 'wkwc_wallet_allow_indexing', false );

		if ( $start_indexing && version_compare( WKWC_WALLET_DB_VERSION, $get_db_version, '>' ) && '0' === $this->get_indexing_state() ) {
			$this->wkwc_wallet_start_indexing();
		}
	}

	/**
	 * Start Indexing.
	 *
	 * @return void
	 */
	public function wkwc_wallet_start_indexing() {
		$this->indexer->push_to_queue( array( $this, 'wkwc_wallet_index_wallet_transactions' ) );

		WKWC_Wallet::log( '**************START INDEXING************' );

		$this->set_indexing_state( '1' );
		$this->indexer->save()->dispatch();

		WKWC_Wallet::log( 'First Dispatch completed' );
	}

	/**
	 * Scanning old transaction table and migrating to the new.
	 *
	 * @return bool|string
	 */
	public function wkwc_wallet_index_wallet_transactions() {
		$index_ids             = array();
		$indexed_option_key    = '_wkwc_wallet_indexed_modules';
		$table_name_option_key = '_wkwc_wallet_new_transaction_table';
		$indexing_completed    = get_option( $indexed_option_key, array() );

		// Create or rename/migrate transaction table data for wallet system module.
		if ( defined( 'WKWP_WALLET_DB_VERSION' ) && version_compare( WKWP_WALLET_DB_VERSION, '1.0.0', '>' ) && ! in_array( WKWP_WALLET_REFERENCE, $indexing_completed, true ) ) {

			$wkwp_wallet_install = WKWP_Wallet_Install::get_instance();
			$index_ids           = $wkwp_wallet_install->wkwp_wallet_migrate_wallet_transactions( $table_name_option_key );

			if ( ! empty( $index_ids ) ) {
				update_option( '_wkwc_wallet_offset', end( $index_ids ) );

				return $this->wkwc_wallet_index_wallet_transactions();
			}

			$indexing_completed[] = WKWP_WALLET_REFERENCE; // wkwp_wallet.
			update_option( $indexed_option_key, $indexing_completed );
			update_option( '_wkwc_wallet_offset', 0 );
			delete_option( $table_name_option_key );
		}

		// Migrating for WC Group Buy module.
		if ( defined( 'WKGBUY_DB_VERSION' ) && version_compare( WKGBUY_DB_VERSION, '1.0.1', '>' ) && ! in_array( WKGBUY_REFERENCE, $indexing_completed, true ) ) {
			$wkgbuy_install = WKGBUY_Install::get_instance();

			$index_ids = $wkgbuy_install->wkgbuy_wallet_migrate_wallet_transactions( $table_name_option_key );

			if ( ! empty( $index_ids ) ) {
				update_option( '_wkwc_wallet_offset', end( $index_ids ) );

				return $this->wkwc_wallet_index_wallet_transactions();
			}

			$indexing_completed[] = WKGBUY_REFERENCE; // wkgbuy_wallet.
			update_option( $indexed_option_key, $indexing_completed );
			delete_option( $table_name_option_key );
		}

		// Migrating for WC Group Buy module.
		if ( defined( 'BMLM_DB_VERSION' ) && version_compare( BMLM_DB_VERSION, '1.0.0', '>' ) && ! in_array( BMLM_REFERENCE, $indexing_completed, true ) ) {
			$bmlm_install = BMLM_Install::get_instance();

			$index_ids = $bmlm_install->bmlm_wallet_migrate_wallet_transactions( $table_name_option_key );

			if ( ! empty( $index_ids ) ) {
				update_option( '_wkwc_wallet_offset', end( $index_ids ) );

				return $this->wkwc_wallet_index_wallet_transactions();
			}

			$indexing_completed[] = BMLM_REFERENCE; // bmlm_wallet.
			update_option( $indexed_option_key, $indexing_completed );
			delete_option( $table_name_option_key );
		}

		return empty( $index_ids ) ? false : $this->wkwc_wallet_index_wallet_transactions();
	}

	/**
	 * Capture fatal error during indexing.
	 *
	 * @return void
	 */
	public function capture_fatal_error() {
		$error = error_get_last();
		if ( ! empty( $error ) ) {
			if ( is_array( $error ) && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {

				if ( $this->is_ignorable_error( $error['message'] ) ) {
					return;
				}

				$current_offset = get_option( '_wkwc_wallet_offset', 0 );
				++$current_offset;
				update_option( '_wkwc_wallet_offset', $current_offset );

				$wallet_id = $this->indexer->get_wallet_id_process();
				update_option( 'wkwc_wallet_id_in_progress', $wallet_id );
			}
		}
	}

	/**
	 * Get if ignorable error.
	 *
	 * @param string $str String.
	 *
	 * @return boolean
	 */
	private function is_ignorable_error( $str ) {
		$get_all_ignorable_regex = $this->ignorable_errors();

		foreach ( $get_all_ignorable_regex as $re ) {
			$matches = array();
			preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Ignorable errors.
	 *
	 * @return array
	 */
	private function ignorable_errors() {
		return array( '/Maximum execution time of/m', '/Allowed memory size of/m' );
	}

	/**
	 * Set wallet id in process.
	 *
	 * @param int $wallet_id Wallet id.
	 *
	 * @return void
	 */
	public function set_wallet_id_in_process( $wallet_id ) {
		$this->wallet_id_in_process = $wallet_id;
	}

	/**
	 * Get wallet id in process.
	 *
	 * @return int
	 */
	public function get_wallet_id_process() {
		return $this->wallet_id_in_process;
	}

	/**
	 * Maybe re-dispatch background process.
	 *
	 * @return void
	 */
	public function maybe_re_dispatch_background_process() {
		$this->indexer->maybe_re_dispatch_background_process();
	}
}
