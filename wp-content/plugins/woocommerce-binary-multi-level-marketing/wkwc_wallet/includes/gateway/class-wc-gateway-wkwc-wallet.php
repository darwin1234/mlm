<?php
/**
 * Wallet Payment Gateway.
 *
 * @package WKWC_Wallet
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * Wallet Gateway.
 *
 * Provides a Wallet Payment Gateway.
 *
 * @class    WC_Gateway_WKWC_Wallet
 * @extends  WC_Payment_Gateway
 * @package  WooCommerce/Classes/Payment
 */
class WC_Gateway_WKWC_Wallet extends \WC_Payment_Gateway {
	/**
	 * Instructions.
	 *
	 * @var string $instructions
	 */
	public $instructions;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'wkwc_wallet';
		$this->method_title       = esc_html__( 'Wallet', 'wkwc_wallet' );
		$this->method_description = esc_html__( 'Have your customers pay with wallet.', 'wkwc_wallet' );
		$this->has_fields         = false;

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions' );
		$this->supports     = array(
			'products',
			'refunds',
		);

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'wkwc_wallet_thankyou_page' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'wkwc_wallet_unset_custom_cart_content' ) );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'wkwc_wallet_email_instructions' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'wkwc_wallet_not_configured_notice' ) );

		add_action( 'wkwc_wallet_settings_migrated', array( $this, 'wkwc_wallet_maybe_migrate_otp_settings' ) );
	}

	/**
	 * Clearing custom cart contents.
	 *
	 * @return void
	 */
	public function wkwc_wallet_unset_custom_cart_content() {
		if ( ! empty( WC()->session->get( 'wkwc_wallet_cart_contents' ) ) ) {
			WC()->session->set( 'cart', WC()->session->get( 'wkwc_wallet_cart_contents' ) );
			WC()->session->__unset( 'wkwc_wallet_cart_contents' );
		}
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'             => array(
				'title'       => esc_html__( 'Enable Wallet', 'wkwc_wallet' ),
				'label'       => esc_html__( 'Enable Wallet Payment', 'wkwc_wallet' ),
				'type'        => 'checkbox',
				'description' => esc_html__( 'Enable wallet payment gateway.', 'wkwc_wallet' ),
				'desc_tip'    => true,
				'default'     => 'no',
			),
			'title'               => array(
				'title'       => esc_html__( 'Title', 'wkwc_wallet' ) . '<span class="required">*</span>',
				'type'        => 'text',
				'description' => esc_html__( 'Payment method title that the customer will see during checkout.', 'wkwc_wallet' ),
				'default'     => esc_html__( 'Pay via Wallet', 'wkwc_wallet' ),
				'desc_tip'    => true,
			),
			'description'         => array(
				'title'       => esc_html__( 'Description', 'wkwc_wallet' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Payment method description that the customer will see during checkout.', 'wkwc_wallet' ),
				'default'     => esc_html__( 'Pay with amount in your wallet.', 'wkwc_wallet' ),
				'desc_tip'    => true,
			),
			'instructions'        => array(
				'title'       => esc_html__( 'Instructions', 'wkwc_wallet' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Instructions that will be added to the thank you page.', 'wkwc_wallet' ),
				'default'     => esc_html__( 'Payment was done by amount in your wallet.', 'wkwc_wallet' ),
				'desc_tip'    => true,
			),
			'enable_for_virtual'  => array(
				'title' => esc_html__( 'Accept for virtual orders', 'wkwc_wallet' ),
				'label' => esc_html__( 'Accept Wallet if the order is virtual', 'wkwc_wallet' ),
				'type'  => 'checkbox',
			),
			'max_amount'          => array(
				'title'             => esc_html__( 'Maximum amount the customer can keep in the Wallet', 'wkwc_wallet' ) . '<span class="required">*</span>',
				'label'             => esc_html__( 'Maximum amount the customer can keep in the Wallet', 'wkwc_wallet' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'required' => 'yes',
					'min'      => '0.01',
					'step'     => '0.01',
				),
			),
			'min_credit'          => array(
				'title'             => esc_html__( 'Minimum Wallet Credit Amount', 'wkwc_wallet' ) . '<span class="required">*</span>',
				'label'             => esc_html__( 'Minimum Wallet Credit Amount', 'wkwc_wallet' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'required' => 'yes',
					'min'      => '0.01',
					'step'     => '0.01',
				),
			),
			'max_credit'          => array(
				'title'             => esc_html__( 'Maximum Wallet Credit Amount', 'wkwc_wallet' ) . '<span class="required">*</span>',
				'label'             => esc_html__( 'Maximum Wallet Credit Amount', 'wkwc_wallet' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'required' => 'yes',
					'min'      => '0.01',
					'step'     => '0.01',
				),
			),
			'max_transfer'        => array(
				'title'             => esc_html__( 'Maximum Amount Transfer From Wallet', 'wkwc_wallet' ) . '<span class="required">*</span>',
				'label'             => esc_html__( 'Maximum Amount Transfer From Wallet', 'wkwc_wallet' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'required' => 'yes',
					'min'      => '0.01',
					'step'     => '0.01',
				),
			),
			'max_debit_type'      => array(
				'title'       => esc_html__( 'Maximum Wallet Debit Type', 'wkwc_wallet' ),
				'label'       => esc_html__( 'Maximum Wallet Debit Type', 'wkwc_wallet' ),
				'type'        => 'select',
				'description' => esc_html__( 'Select maximum wallet debit type for wallet checkout.', 'wkwc_wallet' ),
				'desc_tip'    => true,
				'options'     => array( 'Fixed', 'Percentage' ),
				'default'     => '1',
			),
			'max_debit'           => array(
				'title'             => esc_html__( 'Maximum Wallet Debit Amount', 'wkwc_wallet' ) . '<span class="required">*</span>',
				'label'             => esc_html__( 'Maximum Wallet Debit Amount', 'wkwc_wallet' ),
				'type'              => 'number',
				'description'       => esc_html__( 'Maximum amount can be used from wallet on checkout.', 'wkwc_wallet' ),
				'desc_tip'          => true,
				'custom_attributes' => array(
					'required' => 'yes',
					'min'      => '0.01',
					'step'     => '0.01',
				),
			),
			'discount_applicable' => array(
				'title'       => esc_html__( 'Apply Discount', 'wkwc_wallet' ),
				'label'       => esc_html__( 'Apply Discount', 'wkwc_wallet' ),
				'type'        => 'checkbox',
				'description' => esc_html__( 'Discount coupons can be applied on recharging wallet if enabled.', 'wkwc_wallet' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'otp_verification'    => array(
				'title'       => esc_html__( 'OTP Verification', 'wkwc_wallet' ),
				'label'       => esc_html__( 'OTP Verification', 'wkwc_wallet' ),
				'type'        => 'checkbox',
				'description' => esc_html__( 'If checked, wallet checkout and wallet transfers requires OTP.', 'wkwc_wallet' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'otp_limit'           => array(
				'title'             => esc_html__( 'OTP Validation Limit in minute(s)', 'wkwc_wallet' ),
				'label'             => esc_html__( 'OTP Validation Limit in minute(s)', 'wkwc_wallet' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
				'description'       => esc_html__( 'OTP will be valid for the time after its creation.', 'wkwc_wallet' ),
				'default'           => 'no',
				'desc_tip'          => true,
			),
			'otp_method'          => array(
				'title'       => esc_html__( 'OTP Verification Method', 'wkwc_wallet' ),
				'label'       => esc_html__( 'OTP Verification Method', 'wkwc_wallet' ),
				'type'        => 'select',
				'options'     => array(
					'mail' => 'Mail',
					'sms'  => 'SMS',
				),
				'description' => esc_html__( 'If SMS is enabled, Twillio account and credentials will be required.', 'wkwc_wallet' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'twilio_sid'          => array(
				'title'             => esc_html__( 'Account SID', 'wkwc_wallet' ),
				'label'             => esc_html__( 'Account SID', 'wkwc_wallet' ),
				'type'              => 'password',
				'custom_attributes' => array(
					'pattern' => '[A-z0-9]{2,}',
				),
				'description'       => esc_html__( 'Twilio SID for sms notification.', 'wkwc_wallet' ),
				'desc_tip'          => true,
			),
			'twilio_number'       => array(
				'title'             => esc_html__( 'Twilio Number', 'wkwc_wallet' ),
				'label'             => esc_html__( 'Twilio Number', 'wkwc_wallet' ),
				'type'              => 'password',
				'description'       => esc_html__( 'Twilio Number for sms notification.', 'wkwc_wallet' ),
				'desc_tip'          => true,
				'custom_attributes' => array(
					'class' => 'wkwc_sms_hide',
				),
			),
			'auth_token'          => array(
				'title'             => esc_html__( 'Auth Token', 'wkwc_wallet' ),
				'label'             => esc_html__( 'Auth Token', 'wkwc_wallet' ),
				'custom_attributes' => array(
					'pattern' => '[A-z0-9]{2,}',
				),
				'type'              => 'password',
				'description'       => esc_html__( 'Twilio Auth Token for sms notification.', 'wkwc_wallet' ),
				'desc_tip'          => true,
			),
		);
	}

	/**
	 * Display settings page with some additional javascript for hiding conditional fields.
	 *
	 * @since 1.0.0
	 * @see WC_Settings_API::admin_options()
	 */
	public function admin_options() {
		parent::admin_options();

		if ( isset( $this->form_fields['otp_verification'] ) ) {
			// Add inline javascript to show/hide any shared settings fields as needed.
			ob_start();
			?>
			$( '#woocommerce_<?php echo $this->id; ?>_otp_verification' ).change( function() {
				var enabled = $( this ).is( ':checked' );
				if ( enabled ) {
					$( '#woocommerce_<?php echo $this->id; ?>_otp_method' ).closest( 'tr' ).show();
					$( '#woocommerce_<?php echo $this->id; ?>_otp_limit' ).closest( 'tr' ).show();
				} else {
					$( '#woocommerce_<?php echo $this->id; ?>_otp_method' ).closest( 'tr' ).hide();
					$( '#woocommerce_<?php echo $this->id; ?>_otp_limit' ).closest( 'tr' ).hide();
				}
			} ).change();

			$( '#woocommerce_<?php echo $this->id; ?>_otp_method' ).change( function() {
				var method = $( this ).val();
				if ('sms' === method ) {
					$( '#woocommerce_<?php echo $this->id; ?>_twilio_number' ).closest( 'tr' ).show();
					$( '#woocommerce_<?php echo $this->id; ?>_twilio_sid' ).closest( 'tr' ).show();
					$( '#woocommerce_<?php echo $this->id; ?>_auth_token' ).closest( 'tr' ).show();
				} else {
					$( '#woocommerce_<?php echo $this->id; ?>_twilio_number' ).closest( 'tr' ).hide();
					$( '#woocommerce_<?php echo $this->id; ?>_twilio_sid' ).closest( 'tr' ).hide();
					$( '#woocommerce_<?php echo $this->id; ?>_auth_token' ).closest( 'tr' ).hide();
				}
			} ).change();
			<?php
			wc_enqueue_js( ob_get_clean() );
		}
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$order          = null;
		$needs_shipping = false;

		// Test if shipping is needed first.
		if ( WC()->cart && WC()->cart->needs_shipping() ) {
			$needs_shipping = true;
		} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order    = wc_get_order( $order_id );

			// Test if order needs shipping.
			if ( 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item ) {
					$_product = $order->get_product_from_item( $item );
					if ( $_product && $_product->needs_shipping() ) {
						$needs_shipping = true;
						break;
					}
				}
			}
		}

		$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

		// Virtual order, with virtual disabled.
		if ( 'yes' !== $this->get_option( 'enable_for_virtual', 'no' ) && ! $needs_shipping ) {
			return false;
		}

		// Check methods.
		if ( $needs_shipping ) {
			// Only apply if all packages are being shipped via chosen methods, or order is virtual.
			$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

			if ( isset( $chosen_shipping_methods_session ) ) {
				$chosen_shipping_methods = array_unique( $chosen_shipping_methods_session );
			} else {
				$chosen_shipping_methods = array();
			}

			$check_method = false;

			if ( $order instanceof \WC_Order ) {
				if ( $order->get_shipping_method() ) {
					$check_method = $order->get_shipping_method();
				}
			} elseif ( empty( $chosen_shipping_methods ) || count( $chosen_shipping_methods ) > 1 ) {
				$check_method = false;
			} elseif ( 1 === count( $chosen_shipping_methods ) ) {
				$check_method = $chosen_shipping_methods[0];
			}

			if ( ! $check_method ) {
				return false;
			}
		}

		$is_available = parent::is_available();

		return $is_available;
	}

	/**
	 * Group payment refund process.
	 *
	 * @param int    $order_id Order id.
	 * @param float  $amount Refund amount.
	 * @param string $reason Reason for refund.
	 *
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order          = wc_get_order( $order_id );
		$payment_method = ( $order instanceof \WC_Order ) ? $order->get_payment_method() : '';

		if ( $order instanceof \WC_Order && $this->id === $payment_method ) {
			$tr_helper     = WKWC_Wallet_Transactions_Helper::get_instance();
			$user_id       = (int) $order->get_customer_id();
			$wallet_refund = \WKWC_Wallet::get_order_meta( $order, 'wallet-refund' );

			if ( empty( $wallet_refund ) ) {
				$wallet_refund = \WKWC_Wallet::get_order_meta( $order, 'wkwc_wallet_refund' );
			}

			$refunds       = $order->get_refunds();
			$refund_id     = $refunds[0]->get_id();
			$refund_amount = $refunds[0]->get_data()['amount'];

			if ( ! empty( $wallet_refund ) ) {
				$wallet_refund = array();
			}

			$wallet_refund[ $refund_id ] = $refund_amount;

			$message  = esc_html__( 'Order No.: ', 'wkwc_wallet' ) . esc_html( $order_id ) . "\n";
			$message .= esc_html__( 'Wallet Credited: ', 'wkwc_wallet' ) . get_woocommerce_currency_symbol() . esc_html( wc_format_decimal( $amount, 2 ) ) . '  ';

			$order->update_meta_data( 'wkwc_wallet_refund', $wallet_refund );
			$order->delete_meta_data( 'wallet-refund' );
			delete_post_meta( $order_id, 'wallet-refund' );
			$reference = apply_filters( 'wkwc_wallet_transaction_reference', false );
			$order->save();

			$data = array(
				'order_id'           => $order_id,
				'reference'          => $reference,
				'sender'             => get_current_user_ID(),
				'customer'           => $user_id,
				'amount'             => $amount,
				'transaction_type'   => 'refund',
				'transaction_status' => 'refunded',
				'transaction_note'   => $message,
			);

			$result_wallet = $tr_helper->create_transaction( $data );

			if ( $result_wallet ) {
				do_action( 'wkwc_wallet_add_admin_refund_note', $order_id, $amount );
				return true;
			}
		}

		return false;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order id.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->update_status( apply_filters( 'woocommerce_' . $this->id . '_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ), __( 'Payment to be made upon delivery.', 'wkwc_wallet' ) );

		// Reduce stock levels.
		wc_reduce_stock_levels( $order );

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Output for the order received page.
	 */
	public function wkwc_wallet_thankyou_page() {
		if ( ! empty( $this->instructions ) ) {
			echo esc_html( wptexturize( $this->instructions ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param \WC_Order $order WC order object.
	 * @param bool      $sent_to_admin Send to admin.
	 * @param bool      $plain_text Plain text.
	 */
	public function wkwc_wallet_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'wkwc_wallet' === $order->get_payment_method() ) {
			echo esc_html( wptexturize( $this->instructions ) ) . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Showing notices on related module's admin pages if wallet settings is not configured properly.
	 *
	 * @return bool
	 */
	public function wkwc_wallet_not_configured_notice() {
		$wkwc_wallet_pages = apply_filters( 'wkwc_wallet_admin_pages', array() );
		$wallet_page       = empty( $_GET['page'] ) ? '' : htmlspecialchars( wp_unslash( $_GET['page'] ) );

		if ( in_array( $wallet_page, $wkwc_wallet_pages, true ) ) {

			$enabled = wc_string_to_bool( $this->get_option( 'enabled' ) );

			if ( ! $enabled ) {
				?>
				<div class="error">
					<p><?php echo wp_sprintf( /* translators: %s wallet gateway settings links */ esc_html__( 'Wallet Gateway settings are not configured. %s', 'wkwc_wallet' ), '<a class="wkwc-wallet-gateway-settings-link" href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=checkout&section=wkwc_wallet' ) ) . '" >' . esc_html__( 'Configure Wallet Gateway', 'wkwc_wallet' ) . '</a>' ); ?></p>
				</div>
				<?php
				return false;
			}

			$min_credit = $this->get_option( 'min_credit', 0 );

			if ( empty( $min_credit ) || $min_credit < 0 ) {
				?>
				<div class="error">
					<p><?php echo wp_sprintf( /* translators: %s wallet gateway settings links */ esc_html__( 'Minimum wallet credit is not configured correctly, customers will not be able to add money to their wallets. %s', 'wkwc_wallet' ), '<a class="wkwc-wallet-gateway-settings-link" href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=checkout&section=wkwc_wallet' ) ) . '" >' . esc_html__( 'Configure Wallet Gateway', 'wkwc_wallet' ) . '</a>' ); ?></p>
				</div>
				<?php
				return false;
			}

			$max_debit = $this->get_option( 'max_debit', 0 );

			if ( empty( $max_debit ) || $max_debit < 0 ) {
				?>
				<div class="error">
					<p><?php echo wp_sprintf( /* translators: %s wallet gateway settings links */ esc_html__( 'Maximum wallet debit amount is not configured correctly, customers will not be able to make payments through their wallets. %s', 'wkwc_wallet' ), '<a class="wkwc-wallet-gateway-settings-link" href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=checkout&section=wkwc_wallet' ) ) . '" >' . esc_html__( 'Configure Wallet Gateway', 'wkwc_wallet' ) . '</a>' ); ?></p>
				</div>
				<?php
				return false;
			}
		}
	}

	/**
	 * Check if OTP settings can be migrated from older version modules.
	 *
	 * @return void
	 */
	public function wkwc_wallet_maybe_migrate_otp_settings() {
		if ( defined( 'WKWP_WALLET_DB_VERSION' ) && version_compare( WKWP_WALLET_DB_VERSION, '1.0.0', '>' ) ) {
			$install = WKWP_Wallet_Install::get_instance();
			$install->wkwp_wallet_maybe_migrate_gateway_settings();
		}

		if ( defined( 'WKGBUY_DB_VERSION' ) && version_compare( WKGBUY_DB_VERSION, '1.0.1', '>' ) ) {
			$install_schema = WKGBUY_Install::get_instance();
			$install_schema->wkgbuy_maybe_migrate_gateway_settings();
		}
		if ( defined( 'BMLM_DB_VERSION' ) && version_compare( BMLM_DB_VERSION, '1.0.0', '>' ) ) {
			$install_bmlm = BMLM_Install::get_instance();
			$install_bmlm->bmlm_maybe_migrate_gateway_settings();
		}
	}
}
