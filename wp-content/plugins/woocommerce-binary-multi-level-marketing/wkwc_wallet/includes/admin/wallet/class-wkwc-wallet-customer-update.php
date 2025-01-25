<?php
/**
 * Customer wallet update for manual transactions.
 *
 * @package WKWC_Wallet
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKWC_Wallet_Customer_Update' ) ) {
	/**
	 * WKWC_Wallet_Customer_Update Class.
	 */
	class WKWC_Wallet_Customer_Update {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 */
		public function __construct() {
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
		 * Show customer wallet table.
		 *
		 * @param string $setting_page Setting page.
		 * @param string $reference Reference.
		 *
		 * @return void
		 */
		public function wkwc_show_wallet_update_form( $setting_page = '', $reference = '' ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['wkwc_wallet_manual_transaction_submit'] ) ) {
				$nonce = empty( $posted_data['wkwc_wallet_manual_transaction_nonce'] ) ? '' : wp_unslash( $posted_data['wkwc_wallet_manual_transaction_nonce'] );

				if ( wp_verify_nonce( $nonce, 'wkwc_wallet_manual_transaction' ) ) {
					$posted_data['wkwc_settings_page'] = $setting_page;
					$this->wkwc_wallet_handle_manual_transaction( $posted_data );
				}
			}
			$amount    = empty( $posted_data['wallet-transaction-amount'] ) ? '' : floatval( $posted_data['wallet-transaction-amount'] );
			$type      = empty( $posted_data['wallet-action'] ) ? 'credit' : $posted_data['wallet-action'];
			$note      = empty( $posted_data['wallet-note'] ) ? '' : $posted_data['wallet-note'];
			$customers = empty( $posted_data['wkwc_wallet_customer'] ) ? array() : $posted_data['wkwc_wallet_customer'];

			?>
			<div class="wrap woocommerce">
				<form method="post" action="" enctype="multipart/form-data">
					<h1><?php esc_html_e( 'Wallet Manual Transaction', 'wkwc_wallet' ); ?></h1>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-customer"><?php esc_html_e( 'Customer Name', 'wkwc_wallet' ); ?></label>
								</th>

								<td>
									<select multiple class="wkwc-wallet-customer" required name="wkwc_wallet_customer[]" id="wkwc_wallet_customer" title="<?php esc_attr_e( 'Customer', 'wkwc_wallet' ); ?>">
									<?php
									foreach ( $customers as $customer_id ) {
										?>
										<option selected value="<?php echo esc_attr( $customer_id ); ?>"><?php echo esc_html( WKWC_Wallet::wkwc_wallet_get_user_display_name( $customer_id ) ); ?></option>
										<?php
									}
									?>
								</select>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-transaction-amount"><?php esc_html_e( 'Amount', 'wkwc_wallet' ); ?></label>
								</th>

								<td>
									<input type="number" required class="" value="<?php echo esc_attr( $amount ); ?>" name="wallet-transaction-amount" id="wallet-transaction-amount" step="0.01" min="0">
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-action"><?php esc_html_e( 'Action', 'wkwc_wallet' ); ?></label>
								</th>

								<td>
									<select class="" name="wallet-action" id="wallet-action" title="action">
										<option value="credit"><?php esc_html_e( 'Credit', 'wkwc_wallet' ); ?></option>
										<option <?php selected( $type, 'debit', true ); ?> value="debit"><?php esc_html_e( 'Debit', 'wkwc_wallet' ); ?></option>
									</select>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wallet-note"><?php esc_html_e( 'Transaction Note', 'wkwc_wallet' ); ?></label>
								</th>

								<td>
									<textarea cols="46" pattern="([A-z0-9\s]){2,}" rows="7" name="wallet-note" id="wallet-note" title="<?php esc_attr_e( 'note', 'wkwc_wallet' ); ?>"><?php echo esc_html( $note ); ?></textarea>
								</td>
							</tr>

						</tbody>
					</table>
						<?php wp_nonce_field( 'wkwc_wallet_manual_transaction', 'wkwc_wallet_manual_transaction_nonce' ); ?>
					<p class="submit">
						<input name="wkwc_wallet_reference" type="hidden" value="<?php echo esc_attr( $reference ); ?>">
						<input name="wkwc_wallet_manual_transaction_submit" class="button-primary" type="submit" value="<?php esc_attr_e( 'Update Wallet', 'wkwc_wallet' ); ?>">
						<a href="<?php echo esc_url( admin_url( '/admin.php?page=' . $setting_page ) ); ?>" class="button-secondary"><?php esc_html_e( 'Cancel', 'wkwc_wallet' ); ?></a>
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * Manual wallet transaction submit.
		 *
		 * @param array $post_data Form submitted post data.
		 *
		 * @return void
		 */
		public function wkwc_wallet_handle_manual_transaction( $post_data = array() ) {
			$wallet_customers = empty( $post_data['wkwc_wallet_customer'] ) ? array() : array_map( 'intval', $post_data['wkwc_wallet_customer'] );
			$wallet_amount    = empty( $post_data['wallet-transaction-amount'] ) ? 0 : floatval( $post_data['wallet-transaction-amount'] );
			$wallet_action    = empty( $post_data['wallet-action'] ) ? '' : $post_data['wallet-action'];
			$wallet_note      = empty( $post_data['wallet-note'] ) ? '' : $post_data['wallet-note'];
			$wallet_reference = empty( $post_data['wkwc_wallet_reference'] ) ? '' : $post_data['wkwc_wallet_reference'];

			$errmsg = '';

			if ( empty( $wallet_customers ) ) {
				$errmsg .= __( 'Select atleast one customer.', 'wkwc_wallet' );
			}

			if ( empty( $wallet_amount ) ) {
				$errmsg .= __( ' Enter a valid positive amount.', 'wkwc_wallet' );
			}

			if ( empty( $wallet_note ) ) {
				$errmsg .= __( ' Transaction note should not be empty.', 'wkwc_wallet' );
			}

			if ( empty( $errmsg ) ) {
				$tr_helper = WKWC_Wallet_Transactions_Helper::get_instance();

				foreach ( $wallet_customers as $wallet_customer ) {
					$updated    = false;
					$note       = $wallet_note;
					$old_amount = $tr_helper->wkwc_wallet_get_amount( $wallet_customer );

					if ( 'credit' === $wallet_action ) {
						$note   .= __( ' Manual Wallet Credit', 'wkwc_wallet' );
						$updated = true;
					} elseif ( 'debit' === $wallet_action && $old_amount >= $wallet_amount ) {
						$note   .= __( ' Manual Wallet Debit', 'wkwc_wallet' );
						$updated = true;
					} else {
						$errmsg = __( 'Insufficient Amount.', 'wkwc_wallet' );
					}

					if ( $updated ) {
						$data = array(
							'transaction_type'   => $wallet_action,
							'amount'             => $wallet_amount,
							'sender'             => get_current_user_ID(),
							'customer'           => $wallet_customer,
							'transaction_note'   => $note,
							'transaction_status' => 'manual',
							'reference'          => $wallet_reference,
						);

						$tr_helper->create_transaction( $data );
					}
				}

				if ( ! empty( $wallet_action ) && ! empty( $updated ) ) {
					$setting_page = empty( $post_data['wkwc_settings_page'] ) ? '' : $post_data['wkwc_settings_page'];
					wp_safe_redirect( site_url() . '/wp-admin/admin.php?page=' . $setting_page . '&transaction=' . $wallet_action );

					exit;
				}
			}

			if ( ! empty( $errmsg ) ) {
				?>
				<div class='notice notice-error is-dismissible'>
					<p><?php echo esc_html( $errmsg ); ?></p>
				</div>
				<?php
			}
		}
	}
}
