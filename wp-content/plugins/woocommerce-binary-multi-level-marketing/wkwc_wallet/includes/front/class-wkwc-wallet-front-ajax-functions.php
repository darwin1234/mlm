<?php
/**
 * Front End Ajax Functions
 *
 * @package WKWC_Wallet
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WKWC_Wallet_Front_Ajax_Functions' ) ) {
	/**
	 * Front ajax functions class
	 */
	class WKWC_Wallet_Front_Ajax_Functions {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Construct function
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
		 * Send transfer money OTP from front end.
		 */
		public function wkwc_wallet_send_transfer_money_otp() {
			if ( ! check_ajax_referer( 'wkwc-wallet-nonce', 'nonce', false ) ) {
				die( 'Busted!' );
			}

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$tr_obj = WKWC_Wallet_Transaction::get_instance();
			$result = $tr_obj->wkwc_wallet_verify_transfer( $posted_data );

			WKWC_Wallet::log( __FUNCTION__ . ': ' . __LINE__ . ' Verify OTP result: ' . print_r( $result, true ) );

			wp_send_json( $result );
		}

		/**
		 * Verify OTP for Wallet transaction.
		 */
		public function wkwc_wallet_ajax_verify_otp() {
			if ( ! check_ajax_referer( 'wkwc-wallet-nonce', 'nonce', false ) ) {
				die( 'Busted!' );
			}

			$result = array(
				'success' => false,
			);

			$user_otp           = empty( $_POST['otp'] ) ? 0 : intval( wc_clean( $_POST['otp'] ) );
			$otp_type           = empty( $_POST['otp_type'] ) ? '' : wc_clean( $_POST['otp_type'] );
			$session_data       = WC()->session->get( 'wkwc_wallet_transfer_otp' );
			$session_otp        = empty( $session_data['otp'] ) ? '' : $session_data['otp'];
			$otp_valid_upto     = empty( $session_data['otp_valid_upto'] ) ? 0 : $session_data['otp_valid_upto'];
			$current_timestamps = strtotime( gmdate( 'Y-m-d H:i:s' ) );

			$log_data = array(
				'user_otp'           => $user_otp,
				'otp_type'           => $otp_type,
				'session_data'       => $session_data,
				'current_timestamps' => $current_timestamps,
			);

			$result['message'] = apply_filters( 'wkwc_wallet_otp_success_message', esc_html__( 'Invalid OTP', 'wkwc_wallet' ), $session_otp );

			if ( $current_timestamps > $otp_valid_upto ) {
				$result['message'] = esc_html__( 'OTP Expired.', 'wkwc_wallet' );
			} elseif ( 6 === strlen( $user_otp ) && $session_otp === $user_otp ) {
				$success = false;
				WC()->session->__unset( 'wkwc_wallet_transfer_otp' );

				$tr_obj = WKWC_Wallet_Transaction::get_instance();

				if ( 'transfer' === $otp_type ) {
					$success = $tr_obj->wkwc_wallet_transfer_money( $session_data );
					if ( $success ) {
						$result['message'] = esc_html__( 'Amount has been successfully transfered.', 'wkwc_wallet' );
					}
				} elseif ( 'checkout' === $otp_type ) {
					$success = $tr_obj->wkwc_wallet_set_wallet_payment();

					if ( $success ) {
						$result['message'] = esc_html__( 'OTP has been successfully verified.', 'wkwc_wallet' );
					}

					if ( ! is_null( WC()->session ) && WC()->session->has_session() ) {
						$is_full_payment = WC()->session->get( 'wkwc_wallet_is_full_payment', false );
						if ( $is_full_payment ) {
							$result['full_payment'] = true;
						}
					}
				}

				$result['success'] = $success;
			}
			$log_data['result'] = $result;

			WKWC_Wallet::log( __FUNCTION__ . ': ' . __LINE__ . " Session OTP: $session_otp, User OTP: $user_otp, Verify OTP log data: " . print_r( $log_data, true ) );

			wp_send_json( $result );
		}

		/**
		 * Delete bulk transactions.
		 */
		public function wkwc_wallet_frontend_bulk_delete() {
			if ( ! check_ajax_referer( 'wkwc-wallet-nonce', 'nonce', false ) ) {
				die( 'Busted!' );
			}

			$result = array(
				'success' => false,
				'message' => esc_html__( 'Unable to delete.', 'wkwc_wallet' ),
			);

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$transaction_ids = empty( $posted_data['checkbox_value'] ) ? array() : array_map( 'intval', $posted_data['checkbox_value'] );

			if ( ! empty( $transaction_ids ) ) {
				$tr_helper = WKWC_Wallet_Transactions_Helper::get_instance();
				$success   = $tr_helper->wkwc_wallet_delete_transactions( $transaction_ids );

				if ( $success ) {
					$result['success'] = $success;
					$result['message'] = esc_html__( 'Selected transaction has been deleted successfully.', 'wkwc_wallet' );
				}
			}

			wp_send_json( $result );
		}

		/**
		 * Handles checkout wallet amount check.
		 *
		 * @hooked 'wp_ajax_wkwc_wallet_checkout_validate' Ajax action on check box clicked.
		 */
		public function wkwc_wallet_ajax_validate_wallet() {
			if ( ! check_ajax_referer( 'wkwc-wallet-nonce', 'nonce', false ) ) {
				die( 'Busted!' );
			}

			$result  = array( 'success' => false );
			$user_id = get_current_user_ID();

			if ( $user_id > 0 ) {
				$tr_obj  = WKWC_Wallet_Transaction::get_instance();
				$checked = empty( $_POST['checked'] ) ? false : wc_string_to_bool( sanitize_text_field( $_POST['checked'] ) );

				if ( ! $checked ) {
					$method_set                = $tr_obj->wkwc_wallet_set_wallet_payment( array(), ! $checked );
					$result['update_checkout'] = true;
					$result['success']         = true;
					$result['message']         = esc_html__( 'Wellet payment removed.', 'wkwc_wallet' );

					wp_send_json( $result );
				}

				$wallet_setting = get_option( 'woocommerce_wkwc_wallet_settings', array() );
				$otp_enabled    = empty( $wallet_setting['otp_verification'] ) ? false : ( 'yes' === $wallet_setting['otp_verification'] );

				if ( ! $otp_enabled ) {
					$method_set = $tr_obj->wkwc_wallet_set_wallet_payment( $wallet_setting );

					if ( $method_set ) {
						$result['success']         = $method_set;
						$result['update_checkout'] = $method_set;
						$result['message']         = esc_html__( 'Verification successful.', 'wkwc_wallet' );
					}

					wp_send_json( $result );
				}

				$c_user     = get_user_by( 'ID', $user_id );
				$user_email = ( $c_user instanceof \WP_User ) ? $c_user->user_email : '';

				if ( ! empty( $user_email ) ) {
					$otp_data = array(
						'sender_id'    => $user_id,
						'sender_email' => $user_email,
						'action'       => 'wallet_otp',
						'message'      => wp_sprintf( /* translators: %s: Blog title. */ esc_html__( 'You are going to make a payment for some purchase using your wallet on the site [%s].', 'wkwc_wallet' ), get_bloginfo( 'name' ) ),
					);

					$result = $tr_obj->wkwc_wallet_send_otp( $otp_data );
				}
			}
			wp_send_json( $result );
		}

		/**
		 * Validate and update phone from my-wallet endpoint.
		 *
		 * @hooked 'wp_ajax_wkwc_wallet_update_phone' Ajax action on check box clicked.
		 */
		public function wkwc_wallet_ajax_update_phone() {
			if ( ! check_ajax_referer( 'wkwc-wallet-nonce', 'nonce', false ) ) {
				die( 'Busted!' );
			}

			$result  = array( 'success' => false );
			$user_id = get_current_user_ID();

			$phone = empty( $_POST['phone'] ) ? '' : wc_clean( $_POST['phone'] );

			if ( $user_id > 0 && ! empty( $phone ) ) {
				if ( ! preg_match( '/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/', $phone ) ) {
					$result['message'] = esc_html__( 'Invalid phone number.', 'wkwc_wallet' );
				} else {
					update_user_meta( $user_id, 'wkwc_wallet_phone_number', $phone );
					$result['success'] = true;
					$result['message'] = esc_html__( 'Phone number has been successfully updated.', 'wkwc_wallet' );
				}
			}
			wp_send_json( $result );
		}
	}
}
