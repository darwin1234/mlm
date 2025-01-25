<?php
/**
 * Dashboard Sponsor Commission Data.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Marketing;



defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Marketing' ) ) {
	/**
	 * Sponsor Commission Data.
	 */
	class BMLM_Marketing {
		/**
		 * Sponsor class object
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Sponsor commission.
		 *
		 * @var object
		 */
		protected $commission;

		/**
		 * Constructor
		 *
		 * @param object $sponsor Sponsor class object.
		 */
		public function __construct( $sponsor ) {
			$this->sponsor    = $sponsor;
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			?>
					<div class="woocommerce-account woocommerce">
						<?php do_action( 'bmlm_wc_account_menu' ); ?>
						<div class="woocommerce-MyAccount-content">
							<div class="bmlm-commissions-wrapper">
								Marketing CRM Link
							</div>
						</div>
					</div>
			<?php 
		}
	}
}
