<?php
/**
 * Ajax Hooks.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Ajax;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Ajax_Hooks' ) ) {
	/**
	 * Ajax hooks class.
	 */
	class BMLM_Ajax_Hooks extends BMLM_Ajax_Functions {
		/**
		 * Ajax hooks construct.
		 */
		public function __construct() {

			// Get sponsors list.
			add_action( 'wp_ajax_bmlm_wallet_get_sponsors_list', array( $this, 'bmlm_wallet_get_sponsors_list' ) );
			add_action( 'wp_ajax_nopriv_bmlm_wallet_get_sponsors_list', array( $this, 'bmlm_wallet_get_sponsors_list' ) );

			// Pay commission.
			add_action( 'wp_ajax_bmlm_pay_commission', array( $this, 'bmlm_pay_commission' ) );
			add_action( 'wp_ajax_nopriv_bmlm_pay_commission', array( $this, 'bmlm_pay_commission' ) );
		}
	}
}
