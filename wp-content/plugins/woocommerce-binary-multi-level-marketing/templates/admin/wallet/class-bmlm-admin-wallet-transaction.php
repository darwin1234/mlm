<?php
/**
 * Wallet Transaction.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Wallet;

use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;
use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;
use WCBMLMARKETING\Inc\BMLM_Errors;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Admin_Wallet_Transaction' ) ) {
	/**
	 * Wallet Transaction
	 */
	class BMLM_Admin_Wallet_Transaction extends BMLM_Errors {


		/**
		 * Sponsor.
		 *
		 * @var object Sponsor.
		 */
		protected $sponsor;

		/**
		 * Commission.
		 *
		 * @var array Commission.
		 */
		protected $commission = array(
			'id'       => '',
			'note'     => '',
			'customer' => '',
			'amount'   => '',
			'type'     => 'credit',
		);

		/**
		 * Commission helper.
		 *
		 * @var object Commission helper.
		 */
		protected $commission_helper;

		/**
		 * Transaction class object.
		 *
		 * @var object  $helper Transaction class object.
		 */
		protected $helper;

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 *
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Construct
		 */
		public function __construct() {
			$get_data      = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$commission_id = empty( $get_data['cid'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['cid'] ) ) );

			if ( ! empty( $commission_id ) ) {
				$this->commission_helper = BMLM_Commission_Helper::get_instance();
				$commission              = $this->commission_helper->bmlm_get_commission( $commission_id );
				if ( ! empty( $commission ) ) {
					$this->commission['customer'] = $commission['user_id'];
					$this->commission['amount']   = $commission['commission'];
					$this->commission['note']     = $commission['description'];
					$this->commission['id']       = $commission['id'];
				}
			}
			$this->helper = BMLM_Transaction::get_instance();
			$this->bmlm_create_transaction();
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @param int $sponsor_id Sponsor ID.
		 *
		 * @return object
		 */
		public static function get_instance( $sponsor_id = 0 ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $sponsor_id );
			}

			return static::$instance;
		}

		/**
		 * Create transaction function.
		 *
		 * @return void
		 */
		public function bmlm_create_transaction() {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ! empty( $posted_data['wallet-manual-transaction-submit'] ) && ! empty( $posted_data['bmlm_wallet_nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['bmlm_wallet_nonce'] ), 'bmlm_wallet_nonce_action' ) ) {
				$this->bmlm_validate_request( $posted_data );
			}
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$note      = $this->commission['note'];
			$amount    = $this->commission['amount'];
			$customers = empty( $this->commission['customer'] ) ? array() : array( $this->commission['customer'] );
			$attribute = ! empty( $this->commission['id'] ) ? 'disabled' : '';

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$amount      = ( empty( $amount ) && ! empty( $posted_data['wallet-transaction-amount'] ) ) ? $posted_data['wallet-transaction-amount'] : $amount;
			$customers   = ( empty( $customers ) && ! empty( $posted_data['wallet-customer'] ) ) ? $posted_data['wallet-customer'] : $customers;
			$action      = empty( $posted_data['wallet-action'] ) ? 'credit' : $posted_data['wallet-action'];
			$note        = ( empty( $note ) && ! empty( $posted_data['wallet-note'] ) ) ? $posted_data['wallet-note'] : $note;
			?>
			<div class="wrap">

				<form method="post" id="bmlm-wallet-manual-transaction">

					<h1><?php esc_html_e( 'Wallet Transaction', 'binary-mlm' ); ?></h1>
					<p><?php esc_html_e( 'Admin can debit or credit wallet amount manually to different users.', 'binary-mlm' ); ?></p>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-customer"><?php esc_html_e( 'Sponsor name', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label>
								</th>
								<td>
									<?php echo wc_help_tip( esc_html__( 'Search and Select Sponsors name', 'binary-mlm' ), false ); ?>
									<select <?php echo esc_attr( $attribute ); ?> class="regular-text" name="wallet-customer[]" id="wallet-customer" title="<?php esc_attr_e( 'Customer', 'binary-mlm' ); ?>" multiple>
										<?php
										foreach ( $customers as $customer_id ) {
											$customer = get_userdata( $customer_id );
											if ( $customer instanceof \WP_User ) {
												$username = $customer->user_email;
												?>
													<option value="<?php echo esc_attr( $customer_id ); ?>" selected="selected"><?php echo esc_html( $username ); ?>
													<option>
												<?php
											}
										}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-transaction-amount"><?php esc_html_e( 'Amount', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label>
								</th>
								<td>
									<?php echo wc_help_tip( esc_html__( 'Add Wallet Amount', 'binary-mlm' ), false ); ?>
									<input type="number" class="regular-text" name="wallet-transaction-amount" value="<?php echo esc_attr( $amount ); ?>" id="wallet-transaction-amount" step="0.01" min="0" <?php echo esc_attr( $attribute ); ?>>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-action"><?php esc_html_e( 'Action', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label>
								</th>
								<td>
									<?php echo wc_help_tip( esc_html__( 'Select Wallet Transaction Type', 'binary-mlm' ), false ); ?>
									<select class="regular-text" name="wallet-action" id="wallet-action" title="action" <?php echo esc_attr( $attribute ); ?>>
										<option <?php selected( $action, 'credit', true ); ?> value="credit"><?php esc_html_e( 'Credit', 'binary-mlm' ); ?></option>
										<option <?php selected( $action, 'debit', true ); ?> value="debit"><?php esc_html_e( 'Debit', 'binary-mlm' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-note"><?php esc_html_e( 'Transaction Note', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label>
								</th>
								<td>
									<?php echo wc_help_tip( esc_html__( 'Add Transaction Note', 'binary-mlm' ), false ); ?>
									<textarea cols="46" class="regular-text" rows="7" name="wallet-note" id="wallet-note" title="<?php esc_attr_e( 'note', 'binary-mlm' ); ?>" <?php echo esc_attr( $attribute ); ?>><?php echo esc_textarea( $note ); ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="action-submit">
						<?php
							wp_nonce_field( 'bmlm_wallet_nonce_action', 'bmlm_wallet_nonce' );
							submit_button( esc_html__( 'Create transaction', 'binary-mlm' ), 'primary', 'wallet-manual-transaction-submit' );
						?>
						<!-- <a href="<?php echo esc_url( admin_url( 'admin.php?page=bmlm-menu' ) ); ?>"  class="button"><?php esc_html_e( 'Cancel', 'binary-mlm' ); ?></a> -->
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Validate wallet function
		 *
		 * @param array $data Wallet form data.
		 * @return void
		 */
		public function bmlm_validate_request( $data ) {
			$commission_id = '';
			if ( ! empty( $this->commission['id'] ) ) {
				$wallet_action    = 'credit';
				$wallet_note      = $this->commission['note'];
				$commission_id    = $this->commission['id'];
				$wallet_amount    = $this->commission['amount'];
				$wallet_customers = array( $this->commission['customer'] );
				$reference        = esc_html__( 'Wallet Credit', 'binary-mlm' );
			} else {
				// wallet args.
				$wallet_customers = isset( $data['wallet-customer'] ) ? $data['wallet-customer'] : '';
				$wallet_amount    = isset( $data['wallet-transaction-amount'] ) ? sanitize_text_field( $data['wallet-transaction-amount'] ) : '';
				$wallet_action    = isset( $data['wallet-action'] ) ? sanitize_text_field( $data['wallet-action'] ) : 'credit';
				$wallet_note      = isset( $data['wallet-note'] ) ? sanitize_text_field( $data['wallet-note'] ) : '';

				if ( empty( $wallet_customers ) ) {
					$message = esc_html__( 'Customer must be selected for transaction to be fulfilled', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
				}
				if ( empty( $wallet_amount ) ) {
					$message = esc_html__( 'Transaction amount is mandatory', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
				}
				if ( empty( $wallet_action ) || ( ! empty( $wallet_action ) && 'debit' !== $wallet_action && 'credit' !== $wallet_action ) ) {
					$message = esc_html__( 'Transaction type must be selected', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
				}
				if ( empty( $wallet_note ) ) {
					$message = esc_html__( 'Transaction note is mandatory', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
				}
			}

			if ( 0 === parent::bmlm_get_error_code() ) {

				$text = 'debit' === $wallet_action ? esc_html__( 'debited from', 'binary-mlm' ) : esc_html__( 'credited to', 'binary-mlm' );

				$data = array(
					'order_id'         => '',
					'reference'        => BMLM_REFERENCE,
					'sender'           => get_current_user_id(),
					'customer'         => '',
					'amount'           => $wallet_amount,
					'transaction_type' => $wallet_action,
					'transaction_date' => current_time( 'Y-m-d H:i:s' ),
					'transaction_note' => $wallet_note,
				);

				$wallet_setting       = get_option( 'woocommerce_wkwc_wallet_settings', array() );
				$maximum_store_amount = empty( $wallet_setting['max_amount'] ) ? 0 : $wallet_setting['max_amount'];

				if ( ! class_exists( 'WKWC_Wallet_Transactions_Helper' ) ) {
					require_once BMLM_PLUGIN_FILE . 'wkwc_wallet/helper/class-wkwc-wallet-transactions-helper.php';
				}
				$tr_helper = \WKWC_Wallet_Transactions_Helper::get_instance();

				foreach ( $wallet_customers as $customer ) {

					$errmsg           = '';
					$data['customer'] = $customer;
					$old_amount       = $tr_helper->wkwc_wallet_get_amount( $customer );

					$validator = 0;

					if ( 'credit' === $wallet_action ) {
						$new_amount = $old_amount + $wallet_amount;
						if ( $new_amount <= $maximum_store_amount ) {
							$check_val = 'updated';
						} else {
							$errmsg = wp_sprintf( /* translators: %s: Maximum amount. */ esc_html__( 'You Could not store more than %s', 'binary-mlm' ), $maximum_store_amount );
						}
					} elseif ( 'debit' === $wallet_action && $old_amount >= $wallet_amount ) {
						$new_amount = $old_amount - $wallet_amount;
						$check_val  = 'updated';
					} else {
						$errmsg = __( 'Insufficient Amount.', 'binary-mlm' );
					}

					if ( empty( $errmsg ) ) {

						parent::bmlm_set_error_code( 0 );
						$status = $tr_helper->create_transaction( $data );

						if ( ! empty( $commission_id ) && ! empty( $this->commission ) ) {
							$this->commission_helper->bmlm_update_commission_status( $commission_id );
							wp_safe_redirect( admin_url( 'admin.php?page=bmlm_sponsors&tab=bmlm_wallet' ) );
							exit();
						}
						/* translators: %s is amount string*/
						$message     = sprintf( esc_html__( 'Wallet amount %s account successfully', 'binary-mlm' ), $text );
						$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
						unset( $_POST );
					} else {
						parent::bmlm_set_error_code( 1 );
						$message = $errmsg;
					}
					parent::bmlm_print_notification( $message );
				}
			} else {
				$message = esc_html__( 'Please fill up all the required fields ', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
		}
	}
}
