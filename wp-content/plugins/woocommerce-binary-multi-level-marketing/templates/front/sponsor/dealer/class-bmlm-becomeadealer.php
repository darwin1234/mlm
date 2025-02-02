<?php
/**
 * Dashboard Sponsor Commission Data.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\dealer;



defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_BecomeADealer' ) ) {
	/**
	 * Sponsor Commission Data.
	 */
	class BMLM_BecomeADealer {
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
			$user_id =  get_current_user_id();
			$first_name = get_user_meta($user_id,'first_name', true);
			?>
					<div class="woocommerce-account woocommerce">
						<?php do_action( 'bmlm_wc_account_menu' ); ?>
						<div class="woocommerce-MyAccount-content">
							<div class="bmlm-commissions-wrapper">
								<div id="becomeAmemberWrap" class="container">
									<div class="row">
										<div class="col-md-5 m-auto">
											<h3>Welcome Back, <?php echo $first_name; ?></h3>
											<p>Lorem ipsum dolor sit amet consectetur. Mi dui molestie turpis blandit elit magnis sit. Amet dui laoreet quis.</p>
											<a href="#" id="becomeAmemberbtn" class="btn btn-primary w-100">Become a Dealer</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
			<?php 
		}
	}
}
