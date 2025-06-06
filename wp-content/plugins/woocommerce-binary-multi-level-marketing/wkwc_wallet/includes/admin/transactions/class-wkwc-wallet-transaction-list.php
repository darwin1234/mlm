<?php
/**
 * Wallet Transactions list at admin end.
 *
 * @package WKWC_Wallet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if Accessed Directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKWC_Wallet_Transaction_List' ) ) {
	/**
	 * Wallet Transaction List.
	 */
	class WKWC_Wallet_Transaction_List extends WP_List_Table {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => __( 'Wallet Transaction List', 'wkwc_wallet' ),
					'plural'   => __( 'Wallet Transactions List', 'wkwc_wallet' ),
					'ajax'     => false,
				)
			);
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
		 * Prepare items.
		 *
		 *  @param string $setting_page Setting page.
		 *  @param string $reference Reference.
		 *
		 * @return void
		 */
		public function prepare_items( $setting_page = '', $reference = '' ) {
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();
			$screen   = get_current_screen();
			$per_page = $this->get_items_per_page( 'transaction_per_page', 20 );

			$this->_column_headers = array( $columns, $hidden, $sortable );

			if ( empty( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}

			$current_page = $this->get_pagenum();

			$get_data = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$export = empty( $_POST['export_wallet_transaction_details_csv'] ) ? false : wc_clean( $_POST['export_wallet_transaction_details_csv'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = empty( $request_data['orderby'] ) ? 'id' : $request_data['orderby'];
			$order        = empty( $request_data['order'] ) ? 'desc' : $request_data['order'];
			$orderby      = ( 'transaction_id' === $orderby ) ? 'id' : $orderby;
			$orderby      = ( 'date' === $orderby ) ? 'transaction_date' : $orderby;

			$args = array(
				'export'                => $export,
				'orderby'               => $orderby,
				'order'                 => $order,
				'reference'             => $reference,
				'transaction_type'      => empty( $get_data['transaction-type'] ) ? '' : $get_data['transaction-type'],
				'transaction_from_date' => empty( $get_data['transaction-from-date'] ) ? '' : $get_data['transaction-from-date'],
				'transaction_to_date'   => empty( $get_data['transaction-to-date'] ) ? '' : $get_data['transaction-to-date'],
			);

			$total_items = count( $this->table_data( $args ) );

			$args['limit']        = $per_page;
			$args['setting_page'] = $setting_page;
			$args['offset']       = ( $current_page - 1 ) * $per_page;

			$data = $this->table_data( $args );

			$total_pages = ceil( $total_items / $per_page );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'total_pages' => $total_pages,
					'per_page'    => $per_page,
				)
			);
			$this->items = $data;
		}

		/**
		 * Getting data from database.
		 *
		 * @param array $args Arguments array.
		 *
		 * @return array
		 */
		public function table_data( $args = array() ) {
			$data         = array();
			$tr_helper    = WKWC_Wallet_Transactions_Helper::get_instance();
			$transactions = $tr_helper->get_transactions( $args );
			$export       = empty( $args['export'] ) ? false : $args['export'];

			if ( $export ) {
				if ( ! empty( $transactions ) ) {
					$wallet_exporter = WKWC_Wallet_Exporter::get_instance();

					$wallet_exporter->wkwcwallet_process_exporting_wallet_transaction_details( $transactions );

				} else {
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php esc_html_e( 'No Transactions to export.', 'wkwc_wallet' ); ?></p>
					</div>
					<?php
				}
			}

			if ( ! empty( $transactions ) ) {
				foreach ( $transactions as $transaction ) {
					$id          = $transaction['id'];
					$customer_id = ! empty( $transaction['customer'] ) ? $transaction['customer'] : $transaction['sender'];
					$customer    = get_user_by( 'ID', $customer_id );

					if ( ! empty( $customer ) && is_object( $customer ) ) {
						$email        = $customer->user_email . ' (#' . $customer_id . ')';
						$setting_page = empty( $args['setting_page'] ) ? '' : $args['setting_page'];

						$data[] = array(
							'id'               => $id,
							'transaction_id'   => '<a href = "' . admin_url( 'admin.php?page=' . $setting_page . "&transaction_id=$id" ) . '" >#' . $id . '</a>',
							'customer'         => $email,
							'amount'           => wc_price( $transaction['amount'] ),
							'transaction_type' => ucfirst( $transaction['transaction_type'] ),
							'date'             => WKWC_Wallet::wkwc_wallet_display_gmt_to_wp_timezone( $transaction['transaction_date'] ),
						);
					}
				}
			}

			return $data;
		}

		/**
		 * Defining Columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'transaction_id'   => __( 'Transaction ID', 'wkwc_wallet' ),
				'customer'         => __( 'Customer', 'wkwc_wallet' ),
				'amount'           => __( 'Amount', 'wkwc_wallet' ),
				'transaction_type' => __( 'Transaction Type', 'wkwc_wallet' ),
				'date'             => __( 'Date', 'wkwc_wallet' ),
			);

			return $columns;
		}

		/**
		 * Get Default Columns.
		 *
		 * @param array  $item List columns.
		 * @param string $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'transaction_id':
				case 'customer':
				case 'amount':
				case 'transaction_type':
				case 'date':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Defining Hidden Columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Column checkbox.
		 *
		 * @param array $item List columns.
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="transaction_id_%s" name="transaction_id[]" value="%s" />', $item['transaction_id'], $item['transaction_id'] );
		}

		/**
		 * List Filters.
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			$transaction_type      = '';
			$transaction_from_date = '';
			$transaction_to_date   = '';
			$all_transaction_types = array(
				''         => __( 'Transaction Type', 'wkwc_wallet' ),
				'credit'   => __( 'Credit', 'wkwc_wallet' ),
				'debit'    => __( 'Debit', 'wkwc_wallet' ),
				'refund'   => __( 'Refund', 'wkwc_wallet' ),
				'transfer' => __( 'Transfer', 'wkwc_wallet' ),
			);

			$get_data              = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$all_transaction_types = apply_filters( 'wkwcwallet_modify_transaction_types_for_filter', $all_transaction_types );

			if ( 'top' === $which ) {
				if ( isset( $get_data['transaction-type'] ) ) {
					$transaction_type = $get_data['transaction-type'];
				}
				if ( isset( $get_data['transaction-from-date'] ) ) {
					$transaction_from_date = $get_data['transaction-from-date'];
				}
				if ( isset( $get_data['transaction-to-date'] ) ) {
					$transaction_to_date = $get_data['transaction-to-date'];
				}
				$to_date = gmdate( 'Y-m-d' );
				?>
				<div class="alignleft actions bulkactions">
					<select name="transaction-type" class="transaction-type">
						<?php
						if ( ! empty( $all_transaction_types ) ) {
							foreach ( $all_transaction_types as $key => $value ) {
								$selected = ( $key === $transaction_type ) ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $value ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<label for="transaction-from-date"><?php esc_html_e( 'From:', 'wkwc_wallet' ); ?></label><input type="date" value="<?php echo esc_attr( $transaction_from_date ); ?>" name="transaction-from-date" id="transaction-from-date" placeholder="yyyy-mm-dd" max="<?php echo esc_attr( $to_date ); ?>" class="transaction-from-datepicker" autocomplete="off" />
					<label for="transaction-to-date"><?php esc_html_e( 'To:', 'wkwc_wallet' ); ?></label><input type="date" value="<?php echo esc_attr( $transaction_to_date ); ?>" name="transaction-to-date" id="transaction-to-date" placeholder="yyyy-mm-dd" max="<?php echo esc_attr( $to_date ); ?>" class="transaction-to-datepicker" autocomplete="off" />
					<input type="hidden" value="transaction" name="tab" />
					<input type="submit" value="<?php esc_attr_e( 'Filter', 'wkwc_wallet' ); ?>" name="transaction" class="button" />
				</div>
				<?php
			}
		}

		/**
		 * Get sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'transaction_id'   => array( 'transaction_id', true ),
				'transaction_type' => array( 'transaction_type', true ),
				'amount'           => array( 'amount', true ),
				'date'             => array( 'date', true ),
			);

			return $sortable_columns;
		}
	}
}
