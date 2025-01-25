<?php
/**
 * Sponsor Transaction helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */
namespace WCBMLMARKETING\Helper\Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Transaction' ) ) {
	/**
	 * Sponsor transaction helper class
	 */
	class BMLM_Transaction {
		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Transaction id
		 *
		 * @var integer
		 */
		protected $transaction_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * @param integer $tid transaction id.
		 */
		public function __construct( $tid = '' ) {
			global $wpdb;
			$this->wpdb           = $wpdb;
			$this->transaction_id = $tid;
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @param string $id Transaction id.
		 *
		 * @return object
		 */
		public static function get_instance( $id = '' ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $id );
			}
			return static::$instance;
		}

		/**
		 * Sponsor transactions data.
		 *
		 * @param array $data Filter data.
		 *
		 * @return array $transactions
		 */
		public function bmlm_get_transactions( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$where    = '';
			$date     = gmdate( 'Y-m-d H:i:s' );
			$search   = ! empty( $data['s'] ) ? $data['s'] : '';
			$from     = ! empty( $data['from'] ) ? $data['from'] : '';
			$from     = ! empty( $from ) ? gmdate( 'Y-m-d H:i:s', strtotime( $from ) ) : '';
			$to       = ! empty( $data['to'] ) ? $data['to'] : '';
			$to       = ! empty( $to ) ? gmdate( 'Y-m-d H:i:s', strtotime( $to ) ) : '';
			$order    = ! empty( $data['order'] ) ? $data['order'] : 'desc';
			$orderby  = ! empty( $data['orderby'] ) ? $data['orderby'] : 'id';
			$customer = ! empty( $data['customer'] ) ? $data['customer'] : '';
			$date     = gmdate( 'Y-m-d H:i:s' );

			if ( ! empty( $search ) ) {
				$where .= $wpdb_obj->prepare( ' AND id LIKE %s', $search . '%' );
			}

			if ( ! empty( $customer ) ) {
				$where .= $wpdb_obj->prepare( ' AND ( customer=%d OR sender=%d )', $customer, $customer );
			}

			if ( ! empty( $data['type'] ) ) {
				$where .= $wpdb_obj->prepare( ' AND transaction_type=%s', $data['type'] );
			}

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$where .= $wpdb_obj->prepare( ' AND transaction_date BETWEEN %s AND %s', $from, $to );
			} elseif ( ! empty( $from ) ) {
				$where .= $wpdb_obj->prepare( ' AND transaction_date BETWEEN %s AND %s', $from, $date );
			} elseif ( ! empty( $to ) ) {
				$where .= $wpdb_obj->prepare( ' AND transaction_date BETWEEN %s AND %s', $date, $to );
			}

			$order_by = " ORDER BY $orderby $order";
			$limit    = $wpdb_obj->prepare( ' LIMIT %d OFFSET %d', $data['limit'], $data['start'] );
			$sql      = "SELECT id, order_id as transaction_id, reference, sender, customer, amount, transaction_type as `type`, transaction_date as `date`, transaction_status, transaction_note as note FROM {$wpdb_obj->prefix}wkwc_wallet_transactions WHERE 1=1 $where $order_by $limit"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$transactions = $wpdb_obj->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is safe, see above.

			return apply_filters( 'bmlm_sponsor_transaction', $transactions, $data );
		}

		/**
		 * Sponsor transactions Count
		 *
		 * @param array $data Filter data.
		 *
		 * @return int $total
		 */
		public function bmlm_get_transactions_count( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$date     = gmdate( 'Y-m-d H:i:s' );
			$from     = ! empty( $data['from'] ) ? $data['from'] : '';
			$from     = ! empty( $from ) ? gmdate( 'Y-m-d H:i:s', strtotime( $from ) ) : '';
			$to       = ! empty( $data['to'] ) ? $data['to'] : '';
			$to       = ! empty( $to ) ? gmdate( 'Y-m-d H:i:s', strtotime( $to ) ) : '';

			$search   = ! empty( $data['s'] ) ? $data['s'] : '';
			$customer = ! empty( $data['customer'] ) ? $data['customer'] : '';

			$sql = "SELECT COUNT(*) FROM {$wpdb_obj->prefix}wkwc_wallet_transactions WHERE 1=1";
			if ( ! empty( $search ) ) {
				$sql .= $wpdb_obj->prepare( ' AND order_id LIKE %s', $search . '%' );
			}

			if ( ! empty( $customer ) ) {
				$sql .= $wpdb_obj->prepare( ' AND ( customer=%d OR sender=%d )', $customer, $customer );
			}

			if ( ! empty( $data['type'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_type=%s', $data['type'] );
			}

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_date BETWEEN %s AND %s', $from, $to );
			} elseif ( ! empty( $from ) ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_date BETWEEN %s AND %s', $from, $date );
			} elseif ( ! empty( $to ) ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_date BETWEEN %s AND %s', $date, $to );
			}
			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'bmlm_sponsor_transaction_count', $total, $data );
		}

		/**
		 * Get transaction by user id
		 *
		 * @param int $id transaction id.
		 * @param int $user_id user id.
		 * @return array $transaction
		 */
		public function bmlm_get_transaction( $id, $user_id ) {
			$wpdb_obj    = $this->wpdb;
			$sql         = $wpdb_obj->prepare( "SELECT id, order_id as transaction_id, reference, sender, customer, amount, transaction_type as `type`, transaction_date as `date`, transaction_status, transaction_note as note FROM {$wpdb_obj->prefix}wkwc_wallet_transactions WHERE order_id=%s AND ( customer=%d OR sender=%d )", $id, $user_id, $user_id );
			$transaction = $wpdb_obj->get_row( $sql, ARRAY_A );

			return apply_filters( 'bmlm_sponsor_transaction', $transaction, $id );
		}

		/**
		 * Get transaction by id
		 *
		 * @param int $id transaction index id.
		 * @return array $transaction
		 */
		public function bmlm_get_transaction_by_id( $id ) {
			$wpdb_obj    = $this->wpdb;
			$sql         = $wpdb_obj->prepare( "SELECT id, order_id as transaction_id, reference, sender, customer, amount, transaction_type as `type`, transaction_date as `date`, transaction_status, transaction_note as note FROM {$wpdb_obj->prefix}wkwc_wallet_transactions WHERE id=%d", $id );
			$transaction = $wpdb_obj->get_row( $sql, ARRAY_A );

			return apply_filters( 'bmlm_get_transaction_by_id_filter', $transaction, $id );
		}

		/**
		 * Get customer transaction
		 *
		 * @param int $cid customer id.
		 * @return array $transaction
		 */
		public function bmlm_get_customer_transaction( $cid ) {
			$wpdb_obj    = $this->wpdb;
			$sql         = $wpdb_obj->prepare( "SELECT id, order_id as transaction_id, reference, sender, customer, amount, transaction_type as `type`, transaction_date as `date`, transaction_status, transaction_note as note FROM {$wpdb_obj->prefix}wkwc_wallet_transactions WHERE customer=%d ORDER BY id DESC limit 15 offset 0", $cid );
			$transaction = $wpdb_obj->get_results( $sql, ARRAY_A );

			return apply_filters( 'bmlm_sponsor_transactions_by_id', $transaction, $cid );
		}

		/**
		 * Create Sponsor transaction.
		 *
		 * @param array $data transaction data.
		 * @return bool
		 */
		public function bmlm_create_transaction( $data ) {
			$wpdb_obj = $this->wpdb;

			$status = $wpdb_obj->insert(
				$wpdb_obj->prefix . 'wkwc_wallet_transactions',
				array(
					'order_id'         => '',
					'reference'        => BMLM_REFERENCE,
					'sender'           => $data['sender'],
					'customer'         => $data['customer'],
					'amount'           => $data['amount'],
					'transaction_type' => $data['type'],
					'transaction_date' => $data['date'],
					'transaction_note' => $data['note'],
				),
				array( '%s', '%s', '%d', '%d', '%f', '%s', '%s', '%s' )
			);

			if ( class_exists( 'WK_Caching_Core' ) ) {
				$cache_obj = \WK_Caching_Core::get_instance();
				$cache_obj->reset( '', 'wkwc_wallet_transactions', false );
			}

			return apply_filters( 'bmlm_create_sponsor_transaction', $wpdb_obj->insert_id, $data );
		}

		/**
		 * Delete Sponsor transaction.
		 *
		 * @param integer $lid transaction id.
		 *
		 * @return bool
		 */
		public function bmlm_delete_transaction( $lid ) {
			$wpdb_obj = $this->wpdb;

			$wpdb_obj->delete(
				$wpdb_obj->prefix . 'wkwc_wallet_transactions',
				array(
					'id' => $lid,
				),
				array(
					'%d',
				)
			);
			return apply_filters( 'bmlm_delete_sponsor_transaction', true, $lid );
		}

		/**
		 * Get transaction type html.
		 *
		 * @param string $type Transaction type.
		 *
		 * @return string
		 */
		public function bmlm_get_transaction_type_html( $type ) {
			if ( ! empty( $type ) && 'credit' === $type ) {
				$html = '<mark class="bmlm-status bmlm-status-completed tips"><span>' . ucfirst( $type ) . '</span></mark>';
			} else {
				$html = '<mark class="bmlm-status bmlm-status-danger tips"><span>' . ucfirst( $type ) . '</span></mark>';
			}
			return apply_filters( 'bmlm_modify_transaction_type_html', $html, $type );
		}

		/**
		 * Setup Sponsor transaction.
		 *
		 * @param array  $commission Commission.
		 * @param object $helper Commission helper.
		 *
		 * @return bool
		 */
		public function bmlm_setup_transaction( $commission, $helper ) {
			$wallet_customers = array( $commission['user_id'] );
			$wallet_amount    = $commission['commission'];
			$wallet_note      = $commission['description'];
			$commission_id    = $commission['id'];
			$wallet_action    = 'credit';
			$reference        = esc_html__( 'Wallet Credit', 'binary-mlm' );
			$text             = esc_html__( 'credited to', 'binary-mlm' );

			$data = array(
				'sender'    => get_current_user_id(),
				'customer'  => '',
				'amount'    => $wallet_amount,
				'type'      => $wallet_action,
				'reference' => $reference,
				'note'      => $wallet_note,
				'date'      => current_time( 'Y-m-d H:i:s' ),
				'email'     => array(
					'subject' => '',
					'message' => '',
				),
			);

			foreach ( $wallet_customers as $customer ) {
				$data['customer'] = $customer;
				$old_amount       = (float) get_user_meta( $customer, 'wkwc_wallet_amount', true );
				$new_amount       = $old_amount + $data['amount'];
				update_user_meta( $customer, 'wkwc_wallet_amount', $new_amount );
				$this->bmlm_create_transaction( $data );
				$helper->bmlm_update_commission_status( $commission_id );
				$message = wp_sprintf( /* Translators: %s: Credit text. */ esc_html__( 'Wallet amount %s account successfully', 'binary-mlm' ), $text );

			}
			return apply_filters( 'bmlm_alter_sponsor_transaction_data', $message, $commission );
		}
	}
}
