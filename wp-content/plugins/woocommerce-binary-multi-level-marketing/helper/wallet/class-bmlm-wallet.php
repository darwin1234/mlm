<?php
/**
 * Sponsor wallet helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Helper\Wallet;

use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Wallet' ) ) {
	/**
	 * Sponsor wallet helper class
	 */
	class BMLM_Wallet {

		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * User id.
		 *
		 * @var integer
		 */
		protected $user_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor
		 *
		 * @param integer $id user id.
		 */
		public function __construct( $id = 0 ) {
			global $wpdb;
			$this->wpdb    = $wpdb;
			$this->user_id = $id;
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @param int $user_id User id.
		 *
		 * @return object
		 */
		public static function get_instance( $user_id = 0 ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $user_id );
			}
			return static::$instance;
		}

		/**
		 * Get Wallet product.
		 *
		 * @return object $wallet_product wallet product
		 */
		public function bmlm_get_wallet_product() {
			$wallet_product = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
			return apply_filters( 'bmlm_modify_wallet_product', $wallet_product );
		}

		/**
		 * Get user wallet money.
		 *
		 * @param string $id User id.
		 *
		 * @return string $amount
		 */
		public function bmlm_get_wallet_money( $id ) {
			$amount = get_user_meta( $id, 'wkwc_wallet_amount', true );
			return apply_filters( 'bmlm_modify_wallet_amount', $amount, $id );
		}

		/**
		 * Update customer wallet amount order completed for wallet product.
		 *
		 * @param int       $order_id Order id.
		 *
		 * @param \WC_Order $order Order object.
		 *
		 * @param array     $order_items Order Items.
		 *
		 * @return void
		 */
		public function bmlm_update_wallet_amount( $order_id, $order, $order_items ) {

			$new_amount       = ( $order instanceof \WC_Order ) ? $order->get_total() : 0;
			$order_items      = $order_items;
			$transaction_data = array(
				'sender'    => get_current_user_id(),
				'customer'  => $this->user_id,
				'amount'    => floatval( $new_amount ),
				'type'      => 'credit',
				'date'      => gmdate( 'Y-m-d H:i:s' ),
				'reference' => esc_html__( 'Wallet Credited', 'binary-mlm' ),
				'note'      => wp_sprintf( /* Translators: %d: order id. */ esc_html__( 'Wallet product purchase order %d has been completed.', 'binary-mlm' ), $order_id ),
			);

			$transaction = BMLM_Transaction::get_instance();

			$transaction->bmlm_create_transaction( $transaction_data );

			$old_amount   = get_user_meta( $this->user_id, 'wkwc_wallet_amount', true );
			$final_amount = floatval( $old_amount ) + floatval( $new_amount );

			update_user_meta( $this->user_id, 'wkwc_wallet_amount', $final_amount );
		}
	}
}
