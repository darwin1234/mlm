<?php
/**
 * Dashboard Sponsor General Data.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Refferal;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Refferal' ) ) {

	/**
	 * Dashboard Manifest
	 */
	class BMLM_Refferal {

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
					<div class="container" style="margin-top:200px; width:95%;">
						<div class="row">
							<div class="col-md-6">
								<h1>Referral</h1>
								<p>By clicking to view the Terms and Conditions, you acknowledge that you have familiarized yourself with and understand the stated terms.</p>
							
								<h1>Terms and conditions</h1>
								<p>Our Terms and Conditions outline the rules, policies, and legal obligations that govern the use of our services. By accessing or using our platform, you acknowledge that you have read, understood, and agreed to these terms. We encourage you to review them carefully to ensure compliance and awareness of your rights and responsibilities.</p>
							
							</div>
							<div class="col-md-6 m-auto">
									<div class="wrap bmlm-wrapper bmlm-refferal">
										<div class="bmlm-graph-card">
											<div class="bmlm-graph-card-body">
												<table class="form-class bmlm-sponsor-refferal" style="width:100%;">
													<tbody>
														<tr>
									
															<td>
																<?php  
																$user_id = get_current_user_id();
																$profile_url = site_url('/dealer/dealers-form/?sponsor=' . $user_id); // Generate profile UR ?>
																<label><?php esc_html_e( 'Sponsor ID', 'binary-mlm' ); ?></label>
																<input type="text" value="<?php echo $profile_url ?>" class="bmlm-input form-control">
																<div class="bmlm-tooltip w-100 mt-3">
																	<button id="copy_url"  class="bmlm-tooltip-btn w-100" type="button" style="width:100%; display:block; padding:10px; border-radius:10px; border-color:#cccccc;">
																		<span class="bmlm-tooltiptext">
																			
																			<?php esc_html_e( 'Copy Affiliate link', 'binary-mlm' ); ?>
																		</span>
																		<img src="<?php echo bloginfo('template_url'); ?>/assets/icons/copy.png">
																		<?php esc_html_e( 'Copy Affiliate link', 'binary-mlm' ); ?>
																	</button>
																</div>

																<div class="bmlm-tooltip w-100 mt-3">
																	<button class="bmlm-tooltip-btn w-100" data-bs-toggle="modal" data-bs-target="#staticBackdrop" type="button" style="width:100%; display:block; padding:10px; background-color:#8c52ff; color:#ffffff; border-color:#8c52ff; border-radius:10px;">
																		<span class="bmlm-tooltiptext">
																			<?php esc_html_e( 'Invite via Email', 'binary-mlm' ); ?>
																		</span>
																		<?php esc_html_e( 'Invite via Email', 'binary-mlm' ); ?>
																	</button>
																</div>
																<!-- Modal -->
																<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
																<div class="modal-dialog modal-dialog-centered">
																	<div class="modal-content">
																	<div class="modal-header">
																		<h5 class="modal-title" id="staticBackdropLabel">Invite via Email</h5>
																		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																	</div>
																	<div class="modal-body">
																	<form>
																		<div class="mb-3">
																			<label for="exampleInputEmail1" class="form-label">Email address</label>
																			<input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
																		</div>
																		
																	</form>
																	</div>
																	<div class="modal-footer">
																		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
																		<button type="button" class="btn btn-primary" style="background-color:#8c52ff; border-color:#8c52ff; color:#ffffff;">Send</button>
																	</div>
																	</div>
																</div>
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
					</div>
				</div>
			</div>

			<?php
		}
	}
}
