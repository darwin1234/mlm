<?php
/**
 * Transaction records.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Transaction;

use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'BMLM_Transactions_List' ) ) {
	/**
	 * Wallet Transaction List.
	 */
	class BMLM_Transactions_List extends \WP_List_Table {

		/**
		 * Transaction helper.
		 *
		 * @var object $helper Transaction helper.
		 */
		protected $helper = '';

		/**
		 * Transaction type
		 *
		 * @var string Transaction type
		 */
		protected $type;

		/**
		 * Constructor.
		 *
		 * @param int $type Transaction type.
		 */
		public function __construct( $type ) {

			parent::__construct(
				array(
					'singular' => esc_html__( 'Wallet Transaction List', 'binary-mlm' ),
					'plural'   => esc_html__( 'Wallet Transactions List', 'binary-mlm' ),
					'ajax'     => false,
				)
			);
			$this->type   = $type;
			$this->helper = BMLM_Transaction::get_instance();
		}

		/**
		 * Prepare items.
		 *
		 * @return void
		 */
		public function prepare_items() {

			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();
			$screen   = get_current_screen();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();
			$get_data = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$order   = empty( $get_data['order'] ) ? 'DESC' : htmlspecialchars( wp_unslash( wc_clean( $get_data['order'] ) ) );
			$orderby = empty( $get_data['orderby'] ) ? 'id' : htmlspecialchars( wp_unslash( wc_clean( $get_data['orderby'] ) ) );
			$search  = empty( $get_data['s'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['s'] ) ) );

			$transaction_from_date = empty( $get_data['bmlm-transaction-from-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['bmlm-transaction-from-date'] ) ) );
			$transaction_to_date   = empty( $get_data['bmlm-transaction-to-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['bmlm-transaction-to-date'] ) ) );

			$current_page     = $this->get_pagenum();
			$per_page         = $this->get_items_per_page( 'bmlm_per_page', 10 );
			$offset           = ( $current_page - 1 ) * $per_page;
			$transaction_type = ! empty( $this->type ) && 'all' === $this->type ? '' : $this->type;

			$args = array(
				's'       => $search,
				'start'   => $offset,
				'limit'   => $per_page,
				'type'    => $transaction_type,
				'from'    => $transaction_from_date,
				'to'      => $transaction_to_date,
				'orderby' => $orderby,
				'order'   => $order,
			);

			$total_items = $this->helper->bmlm_get_transactions_count( $args );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$data = $this->helper->bmlm_get_transactions( $args );
			$data = $this->bmlm_get_sponsor_transactions( $data );

			$this->items = $data;
		}

		/**
		 * Defining Columns.
		 */
		public function get_columns() {
			$columns = array(
				'id'        => esc_html__( 'ID', 'binary-mlm' ),
				'reference' => esc_html__( 'Reference', 'binary-mlm' ),
				'customer'  => esc_html__( 'Sponsor', 'binary-mlm' ),
				'amount'    => esc_html__( 'Amount', 'binary-mlm' ),
				'type'      => esc_html__( 'Transaction Type', 'binary-mlm' ),
				'date'      => esc_html__( 'Date', 'binary-mlm' ),
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
				case 'reference':
				case 'customer':
				case 'amount':
				case 'type':
				case 'date':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Column transaction id.
		 *
		 * @param array $item Items.
		 *
		 * @return string
		 */
		public function column_id( $item ) {
			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$actions   = array(
				'edit' => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $page_name . '&tab=bmlm_transaction&bmlm_transaction_id=' . $item['id'] ) ), esc_html__( 'View Transactions', 'binary-mlm' ) ),
			);
			return sprintf( '%1$s %2$s', $item['id'], $this->row_actions( apply_filters( 'bmlm_sponsor_transaction_list_line_actions', $actions ) ) );
		}

		/**
		 * Column transaction id.
		 *
		 * @param array $item Items.
		 *
		 * @return string
		 */
		public function column_customer( $item ) {
			$page_name = 'bmlm_sponsors&section=sponsor-general&action=manage'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$url = admin_url( 'admin.php?page=' . esc_attr( $page_name ) . '&sponsor_id=' . esc_attr( $item['customer_id'] ) );

			return sprintf( '<a class="bmlm-view-link" href="%s">%s</a>', esc_url( $url ), esc_html( $item['customer'] ) );
		}

		/**
		 * Defining Hidden Columns
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
		 * Getting data from database.
		 *
		 * @param array $transactions Transactions.
		 */
		public function bmlm_get_sponsor_transactions( $transactions ) {
			$data = array();

			if ( ! empty( $transactions ) ) {
				foreach ( $transactions as $transaction ) {
					$id             = $transaction['id'];
					$transaction_id = $transaction['transaction_id'];
					$customer_id    = ! empty( $transaction['customer'] ) ? $transaction['customer'] : $transaction['sender'];
					$customer       = get_user_by( 'ID', $customer_id );
					$sponsor_mail   = ! empty( $customer->user_email ) ? $customer->user_email : '';
					$email          = $sponsor_mail . ' (#' . $customer_id . ')';
					$html           = $this->helper->bmlm_get_transaction_type_html( $transaction['type'] );
					$data[]         = array(
						'id'             => $id,
						'transaction_id' => $id,
						'customer_id'    => $customer_id,
						'reference'      => $transaction['reference'],
						'customer'       => $email,
						'amount'         => wc_price( $transaction['amount'] ),
						'type'           => $html,
						'date'           => gmdate( 'M d, Y g:i:s A', strtotime( $transaction['date'] ) ),

					);
				}
			}

			return $data;
		}

		/**
		 * List Filters.
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			$transaction_from_date = empty( $_GET['bmlm-transaction-from-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm-transaction-from-date'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$transaction_to_date   = empty( $_GET['bmlm-transaction-to-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm-transaction-to-date'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'top' === $which ) {
				?>
				<div class="alignleft actions bulkactions">
					<label for="bmlm-transaction-from-date"><?php esc_html_e( 'From:', 'binary-mlm' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $transaction_from_date ); ?>" name="bmlm-transaction-from-date" id="bmlm-transaction-from-date" placeholder="yyyy-mm-dd" class="transaction-from-datepicker" autocomplete="off" />

					<label for="bmlm-transaction-to-date"><?php esc_html_e( 'To:', 'binary-mlm' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $transaction_to_date ); ?>" name="bmlm-transaction-to-date" id="bmlm-transaction-to-date" placeholder="yyyy-mm-dd" class="transaction-to-datepicker" autocomplete="off" />

					<input type="submit" value="<?php esc_attr_e( 'Filter', 'binary-mlm' ); ?>" name="transaction" class="button" />
				</div>
				<?php
			}
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'reference' => array( 'reference', true ),
				'type'      => array( 'type', true ),
				'date'      => array( 'date', true ),
				'customer'  => array( 'customer', true ),
			);

			return $sortable_columns;
		}
	}
}
