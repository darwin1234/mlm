<?php
/**
 * Dashboard Sponsor General Data.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Refferal;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_ClientRefferal' ) ) {

	/**
	 * Dashboard Manifest
	 */
	class BMLM_ClientRefferal {

		/**
		 * Sponsor class object
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Constructor
		 *
		 * @param object $sponsor Sponsor class object.
		 */
		public function __construct( $sponsor ) {
			$this->sponsor = $sponsor;
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$sponsor    = $this->sponsor->bmlm_get_sponsor();
			$sponsor_id = get_user_meta( $sponsor->ID, 'bmlm_sponsor_id', true );
			$sponsor_id = ! empty( $sponsor_id ) ? $sponsor_id : 'N/A';
			$terms_link = get_privacy_policy_url();
		
			?>


				<div class="woocommerce-account woocommerce">
				<?php do_action( 'bmlm_wc_account_menu' ); ?>
				<div class="woocommerce-MyAccount-content">
				<?php echo 	do_action('ds_woocommerce_products'); ?>	
				<div class="container" style="display:none;">
						
					<div class="row">
						
							<div class="col-md-6">
								<div class="card" style="width:300px;">
								<img src="https://karanzi.websites.co.in/obaju-turquoise/img/product-placeholder.png" class="card-img-top" style="width:100%;" alt="...">
								<div class="card-body">
									<h5 class="card-title">RealCallerAI: Your All-in-One AI-Driven Call and Chat Support for Every Business.</h5>
									<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
									<table class="">
									<tbody>
									<tr>
										<td>
											<?php  
												$user_id = get_current_user_id();
												$profile_url = site_url('/client/clients-form/?sponsor=' . $user_id); 
											?>
											<label><?php esc_html_e( 'Sponsor ID', 'binary-mlm' ); ?></label>
											<input type="text" value="<?php echo $profile_url ?>" class="bmlm-input form-control">
											<div class="bmlm-tooltip w-100 mt-3">
											<button class="btn btn-primary bmlm-tooltip-btn w-100" type="button">
												<span class="bmlm-tooltiptext">
													<?php esc_html_e( 'Copy to clipboard', 'binary-mlm' ); ?>
												</span>
												<?php esc_html_e( 'Copy Affiliate LINK', 'binary-mlm' ); ?>
											</button>
											</div>
										</td>
									</tr>
		
									</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php 
		}
	}

}
