<?php
/**
 * Cart Hooks.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Cart;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Cart' ) ) {
	/**
	 * Front hooks class.
	 */
	class BMLM_Cart {
		/**
		 * Cart Hooks.
		 */
		public function __construct() {
			// Product page hooks.
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'bmlm_add_sponsor_refferal_id_field' ), 9 );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'bmlm_cart_validate_refferal_id' ), 10, 3 );
			// Add item data to the cart.
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'bmlm_add_refferal_id_to_cart' ), 10, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'bmlm_get_cart_items_from_session' ), 1, 3 );

			// Add order item meta.
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'bmlm_add_order_item_meta' ), 10, 4 );

			add_action( 'template_redirect', array( $this, 'bmlm_wallet_template_redirect' ) );
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'bmlm_wallet_payment_gateway_handler' ), 10, 1 );

			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'bmlm_validate_product_in_cart' ) );
			add_filter( 'woocommerce_before_cart', array( $this, 'bmlm_validate_product_in_cart' ), 10, 1 );
		}

		/**
		 * Validate product in cart.
		 *
		 * @param array $cart_item_data Cart item data.
		 */
		public function bmlm_validate_product_in_cart( $cart_item_data ) {
			$page            = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
			$membership_page = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$wallet_id       = $page->ID;
			$membership_id   = $membership_page->ID;
			$cart            = WC()->cart->get_cart();

			if ( ! empty( $cart ) ) {
				foreach ( $cart as $value ) {
					$product_id = $value['product_id'];
					if ( intval( $product_id ) === intval( $wallet_id ) ) {
						WC()->cart->empty_cart();
						WC()->cart->add_to_cart( $wallet_id );
						return false;
					}
				}
			}
			if ( ! empty( $cart ) ) {
				foreach ( $cart as $value ) {
					$product_id = $value['product_id'];
					if ( intval( $product_id ) === intval( $membership_id ) ) {
						WC()->cart->empty_cart();
						WC()->cart->add_to_cart( $membership_id );
						return false;
					}
				}
			}

			return $cart_item_data;
		}

		/**
		 * Add custom field.
		 */
		public function bmlm_add_sponsor_refferal_id_field() {
			$value = isset( $_GET['ref_id'] ) ? sanitize_text_field( wp_unslash( $_GET['ref_id'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $value ) ) {
				echo '<input name="refferal_id" type="hidden" value="' . esc_attr( $value ) . '" />';
			}
		}

		/**
		 * Validate referral Id.
		 *
		 * @param boolean $is_true is valid.
		 * @param integer $product_id product id.
		 * @param integer $qty product quantity.
		 * @return boolean
		 */
		public function bmlm_cart_validate_refferal_id( $is_true, $product_id, $qty ) {
			global $bmlm;
			$product_id  = $product_id;
			$qty         = $qty;
			$refferal_id = isset( $_POST['refferal_id'] ) ? sanitize_text_field( wp_unslash( $_POST['refferal_id'] ) ) : '';  //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ! empty( $refferal_id ) ) {
				$sponsor     = BMLM_Sponsor::get_instance();
				$refferal_id = $bmlm->encrypt_decrypt( trim( $refferal_id ), 'd' );
				$is_exists   = $sponsor->bmlm_sponsor_id_exists( $refferal_id );

				if ( ! $is_exists ) {
					$is_true       = false;
					$error_message = esc_html__( 'Referral id does not exist .', 'binary-mlm' );
					wc_add_notice( $error_message, 'error' );
				}
			}

			return $is_true;
		}

		/**
		 * Add referral id to cart item
		 *
		 * @param array $cart_item cart item.
		 * @param int   $product_id product id.
		 * @return array
		 */
		public function bmlm_add_refferal_id_to_cart( $cart_item, $product_id ) {
			global $bmlm;
			$product_id  = $product_id;
			$refferal_id = ! empty( $_POST['refferal_id'] ) ? sanitize_text_field( wp_unslash( $_POST['refferal_id'] ) ) : '';  //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$refferal_id = $bmlm->encrypt_decrypt( trim( $refferal_id ), 'd' );

			if ( ! empty( $refferal_id ) ) {
				$cart_item['refferal_id'] = $refferal_id;
			}

			return $cart_item;
		}

		/**
		 * Insert custom data into cart object
		 *
		 * @param object $item cart item object.
		 * @param array  $values cart values.
		 * @param string $key cart key.
		 *
		 * @return object
		 */
		public function bmlm_get_cart_items_from_session( $item, $values, $key ) {
			$key = $key;
			if ( array_key_exists( 'refferal_id', $values ) ) {
				$item['refferal_id'] = $values['refferal_id'];
			}
			return $item;
		}

		/**
		 * Add referral id to order item.
		 *
		 * @param object $item Item.
		 * @param string $cart_item_key cart item key.
		 * @param array  $values values.
		 * @param object $order order.
		 * @return void
		 */
		public function bmlm_add_order_item_meta( $item, $cart_item_key, $values, $order ) {
			$order         = $order;
			$cart_item_key = $cart_item_key;
			if ( ! empty( $values['refferal_id'] ) ) {
				$item->update_meta_data( '_refferal_id', $values['refferal_id'] );
			}
		}

		/**
		 * Restrict other product on cart.
		 *
		 * @return void
		 */
		public function bmlm_wallet_template_redirect() {
			$page = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
			if ( $page ) {
				$wallet_id = $page->ID;

				if ( is_shop() || ( get_post_type() === 'product' && is_single() ) ) {
					$get_cart = WC()->cart->cart_contents;
					if ( ! empty( $get_cart ) ) {
						foreach ( $get_cart as $value ) {
							$product_id = $value['product_id'];
							if ( $product_id === $wallet_id ) {
								wc_add_notice( __( 'Cannot add new product now. Either empty cart or process it first.', 'binary-mlm' ) );
							}
						}
					}
				}
			}
		}

		/**
		 * This function handles checkout payment gateways.
		 *
		 * @param array $available_gateways All available gateways.
		 */
		public function bmlm_wallet_payment_gateway_handler( $available_gateways ) {
			$count           = 0;
			$user_id         = get_current_user_id();
			$user            = ! empty( $user_id ) ? get_userdata( $user_id ) : array();
			$cart_items      = empty( WC()->cart ) ? array() : WC()->cart->cart_contents;
			$membership_page = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$membership_id   = $membership_page->ID;
			$page            = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
			$wallet_id       = $page->ID;

			if ( ! empty( $cart_items ) ) {
				foreach ( $cart_items as $value ) {
					$product_id = $value['product_id'];
					if ( intval( $product_id ) === intval( $wallet_id ) ) {
						$count = 1;
						unset( $available_gateways['wallet'] );
					} elseif ( intval( $product_id ) === intval( $membership_id ) ) {
						$count = 1;
						unset( $available_gateways['wallet'] );
					}
				}
			}

			if ( 0 === intval( $count ) ) {
				if ( ! empty( $user ) && in_array( 'bmlm_sponsor', $user->roles, true ) && ! is_admin() ) {
					$wallet_amount = get_user_meta( $user_id, 'wkwc_wallet_amount', true );
					$total         = WC()->cart->get_totals()['total'];
					if ( $wallet_amount < $total ) {
						unset( $available_gateways['wallet'] );
					}
				} else {
					unset( $available_gateways['wallet'] );
				}
			} else {
				unset( $available_gateways['cod'] );
				unset( $available_gateways['cheque'] );
			}
			return $available_gateways;
		}
	}
}
