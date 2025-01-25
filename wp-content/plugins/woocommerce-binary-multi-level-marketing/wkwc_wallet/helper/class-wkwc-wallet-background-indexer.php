<?php
/**
 * Background Indexer.
 *
 * @package WKWC_Wallet
 *
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once __DIR__ . '/wp-async-background/class-wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once __DIR__ . '/wp-async-background/class-wp-background-process.php';
}

/**
 * WKWC_Wallet_Background_Indexer Class.
 * Based on WC_Background_Updater concept
 */
class WKWC_Wallet_Background_Indexer extends WP_Background_Process {

	const WKWC_WALLET_MAX_OFFSET_THRESHOLD = 20;

	/**
	 * DB Indexer class object.
	 *
	 * @var object
	 */
	public $db_indexer;

	/**
	 * Initiate new background process.
	 *
	 * WKWC_Wallet_Background_Indexer constructor.
	 */
	public function __construct() {
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix     = 'wkwc_wallet_' . get_current_blog_id();
		$this->action     = 'indexer';
		$this->db_indexer = WKWC_Wallet_DB_Indexer::get_instance();
		parent::__construct();
	}

	/**
	 * Is queue empty.
	 *
	 * @return bool
	 */
	protected function is_queue_empty() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$column} LIKE %s", $key ) ); // @codingStandardsIgnoreLine.

		return ! ( $count > 0 );
	}

	/**
	 * Get batch.
	 *
	 * @return stdClass Return the first batch from the queue.
	 */
	protected function get_batch() {
		global $wpdb;

		$table        = $wpdb->options;
		$column       = 'option_name';
		$key_column   = 'option_id';
		$value_column = 'option_value';

		if ( is_multisite() ) {
			$table        = $wpdb->sitemeta;
			$column       = 'meta_key';
			$key_column   = 'meta_id';
			$value_column = 'meta_value';
		}

		$key   = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';
		$query = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$column} LIKE %s ORDER BY {$key_column} ASC LIMIT 1", $key ) ); // @codingStandardsIgnoreLine.

		$batch       = new stdClass();
		$batch->key  = $query->$column;
		$batch->data = array_filter( (array) maybe_unserialize( $query->$value_column ) );

		return $batch;
	}

	/**
	 * See if the batch limit has been exceeded.
	 *
	 * @return bool
	 */
	protected function batch_limit_exceeded() {
		return $this->time_exceeded() || $this->memory_exceeded();
	}

	/**
	 * Handle.
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->batch_limit_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->batch_limit_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}
	}

	/**
	 * Get memory limit.
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || - 1 === intval( $memory_limit ) ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32G';
		}

		return wp_convert_hr_to_bytes( $memory_limit );
	}

	/**
	 * Schedule cron healthcheck.
	 *
	 * @param array $schedules Schedules.
	 *
	 * @return array
	 */
	public function schedule_cron_healthcheck( $schedules ) {
		$interval = apply_filters( $this->identifier . '_cron_interval', 5 );

		if ( property_exists( $this, 'cron_interval' ) ) {
			$interval = apply_filters( $this->identifier . '_cron_interval', $this->cron_interval );
		}

		// Adds every 5 minutes to the existing schedules.
		$schedules[ $this->identifier . '_cron_interval' ] = array(
			'interval' => MINUTE_IN_SECONDS * $interval,
			/* translators: %d: interval */
			'display'  => sprintf( __( 'Every %d minutes', 'wkwc_wallet' ), $interval ),
		);

		return $schedules;
	}


	/**
	 * Delete all batches.
	 *
	 * @return WC_Background_Process
	 */
	public function delete_all_batches() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE {$column} LIKE %s", $key ) ); // @codingStandardsIgnoreLine.

		return $this;
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {

			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			WKWC_Wallet::log( 'Scheduled event cleared as queue is empty.' );

			return;
		}

		/**
		 * We are saving the last 5 offset value, due to any specific reason if last 5 offsets are same then it might be the time to kill the process.
		 */
		$offsets = $this->get_last_offsets();
		if ( self::WKWC_WALLET_MAX_OFFSET_THRESHOLD === count( $offsets ) ) {
			$unique = array_unique( $offsets );
			if ( 1 === count( $unique ) ) {
				$this->kill_process();
				WKWC_Wallet::log( sprintf( 'Offset is stuck from last %d cron jobs, terminating the process.', self::WKWC_WALLET_MAX_OFFSET_THRESHOLD ) );

				return;
			}
		}

		$this->manage_last_offsets();

		/**
		 * Everything looks good, lets roll the indexing
		 */
		$this->handle();
	}

	/**
	 * Overriding parent protected function publicly to use outside this class
	 *
	 * @return bool
	 */
	public function is_process_running() {
		return parent::is_process_running();
	}

	/**
	 * Get lat offsets
	 *
	 * @return array
	 */
	public function get_last_offsets() {
		return get_option( '_wkwc_wallet_last_offsets', array() );
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		$this->kill_process_safe();
		$this->db_indexer->set_indexing_state( '1' );
	}

	/**
	 * Manage last 5 offsets
	 */
	public function manage_last_offsets() {
		$offsets        = $this->get_last_offsets();
		$current_offset = get_option( '_wkwc_wallet_offset', 0 );
		if ( self::WKWC_WALLET_MAX_OFFSET_THRESHOLD === count( $offsets ) ) {
			$offsets = array_map(
				function ( $key ) use ( $offsets ) {
					return isset( $offsets[ $key + 1 ] ) ? $offsets[ $key + 1 ] : 0;
				},
				array_keys( $offsets )
			);

			$offsets[ self::WKWC_WALLET_MAX_OFFSET_THRESHOLD - 1 ] = $current_offset;
		} else {
			$offsets[ count( $offsets ) ] = $current_offset;
		}

		$this->update_last_offsets( $offsets );
	}

	/**
	 * Update last offsets.
	 *
	 * @param array $offsets Offsets.
	 *
	 * @return void
	 */
	public function update_last_offsets( $offsets ) {
		update_option( '_wkwc_wallet_last_offsets', $offsets );
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Kill process safely.
	 *
	 * @return void
	 */
	public function kill_process_safe() {
		if ( ! $this->is_queue_empty() ) {
			$this->delete_all_batches();
			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}
	}

	/**
	 * Re-dispatching background process.
	 *
	 * @return void
	 */
	public function maybe_re_dispatch_background_process() {
		if ( 2 !== absint( $this->db_indexer->get_indexing_state() ) ) {
			return;
		}
		if ( $this->is_queue_empty() ) {
			return;
		}
		if ( $this->is_process_running() ) {
			return;
		}

		/**
		 * We are saving the last 5 offset value, due to any specific reason if last 5 offsets are same then it might be the time to kill the process.
		 */
		$offsets = $this->get_last_offsets();
		if ( self::WKWC_WALLET_MAX_OFFSET_THRESHOLD === count( $offsets ) ) {
			$unique = array_unique( $offsets );
			if ( 1 === count( $unique ) ) {
				$this->kill_process();
				WKWC_Wallet::log( sprintf( 'Offset is stuck from last %d attempts, terminating the process.', self::WKWC_WALLET_MAX_OFFSET_THRESHOLD ) );

				return;
			}
		}

		$this->manage_last_offsets();
		$this->dispatch();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.8; // 80% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return apply_filters( $this->identifier . '_memory_exceeded', $return );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
	 *
	 * @return string|bool
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	protected function task( $callback ) {

		$result = false;

		if ( is_callable( $callback ) ) {

			$result = (bool) call_user_func( $callback );
		}

		return $result ? $callback : false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		update_option( '_wkwc_wallet_offset', 0 );

		WKWC_Wallet::log( 'Background indexing completed for WKWC_Wallet.' );
		do_action( 'wkwc_wallet_indexing_completed' );
		parent::complete();
	}
}
