<?php
/**
 * Ajax Functions.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Ajax;

use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;
use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Ajax_Functions' ) ) {
	/**
	 * Ajax functions class.
	 */
	class BMLM_Ajax_Functions {

		/**
		 * Get sponsors list.
		 */
		public function bmlm_wallet_get_sponsors_list() {

			$response = array();
			if ( check_ajax_referer( 'bmlm-nonce', 'security', false ) ) {
				// code.
				$search = isset( $_GET['query'] ) && ! empty( $_GET['query'] ) ? (string) wc_clean( wp_unslash( $_GET['query'] ) ) : '';

				if ( ! empty( $search ) ) {

					$keyword = wc_strtolower( $search );

					$query = new \WP_User_Query(
						array(
							'search'         => '*' . esc_attr( $keyword ) . '*',
							'role__in'       => array( 'bmlm_sponsor' ),
							'search_columns' => array(
								'user_login',
								'user_nicename',
								'user_email',
								'user_url',
							),
							'fields'         => array( 'user_email', 'user_login', 'ID' ),
						)
					);

					$customers = $query->get_results();

					if ( $customers ) {

						$response = array(
							'error'   => false,
							'data'    => $customers,
							'message' => '',
						);
					} else {

						$response = array(
							'error'   => true,
							'data'    => array(),
							'message' => esc_html__( 'Invalid data provided', 'binary-mlm' ),
						);
					}
				} else {

					$response = array(
						'error'   => true,
						'data'    => array(),
						'message' => esc_html__( 'Invalid data provided!', 'binary-mlm' ),
					);
				}
			} else {
				$response = array(
					'error'   => true,
					'data'    => array(),
					'message' => esc_html__( 'Security check failed!', 'binary-mlm' ),
				);
			}

			wp_send_json( $response );
		}

		/**
		 * Pay commission to sponsor.
		 */
		public function bmlm_pay_commission() {
			$response = array();

			if ( check_ajax_referer( 'bmlm-nonce', 'nonce', false ) ) {
				// code.
				$cid = ! empty( $_POST['cid'] ) ? (int) wc_clean( wp_unslash( $_POST['cid'] ) ) : '';
				if ( ! empty( $cid ) ) {
					$commission_helper = BMLM_Commission_Helper::get_instance();
					$transaction       = BMLM_Transaction::get_instance();
					$commission        = $commission_helper->bmlm_get_commission( $cid );
					if ( ! empty( $commission ) ) {
						$users   = array();
						$users[] = array(
							'user_id' => $commission['user_id'],
						);
						$message = $transaction->bmlm_setup_transaction( $commission, $commission_helper );
						do_action( 'bmlm_sponsor_load_badge_commission', $users );
						$response = array(
							'error'   => false,
							'data'    => array(),
							'message' => $message,
						);
					} else {
						$response = array(
							'error'   => true,
							'data'    => array(),
							'message' => esc_html__( 'Invalid data provided', 'binary-mlm' ),
						);
					}
				} else {
					$response = array(
						'error'   => true,
						'data'    => array(),
						'message' => esc_html__( 'Invalid data provided!', 'binary-mlm' ),
					);
				}
			} else {
				$response = array(
					'error'   => true,
					'data'    => array(),
					'message' => esc_html__( 'Security check failed!', 'binary-mlm' ),
				);
			}

			wp_send_json( $response );
		}
	}
}
