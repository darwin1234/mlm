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
					<div class="wrap bmlm-wrapper bmlm-refferal">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3>
									<?php esc_html_e( 'Referral', 'binary-mlm' ); ?>
								</h3>
							</div>
							<div class="bmlm-graph-card-body">
								<table class="form-class bmlm-sponsor-refferal">
									<tbody>
										<tr>
											<th>
												<label><?php esc_html_e( 'Sponsor ID', 'binary-mlm' ); ?></label>
											</th>
											<td>
												<?php  
												$user_id = get_current_user_id();
												$profile_url = site_url('/sponsor/?sponsor=' . $user_id); // Generate profile UR ?>
												<!--<input type="text" value="<?php echo esc_attr( $sponsor_id ); ?>" class="bmlm-input">-->
												<input type="text" value="<?php echo $profile_url ?>" class="bmlm-input">
												<div class="bmlm-tooltip">
													<button class="bmlm-tooltip-btn" type="button">
														<span class="bmlm-tooltiptext">
															<?php esc_html_e( 'Copy to clipboard', 'binary-mlm' ); ?>
														</span>
														<?php esc_html_e( 'Copy Affiliate LINK', 'binary-mlm' ); ?>
													</button>
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<i class="bmlm-info">
													<abbr class="required" title="required">*</abbr>&nbsp;&nbsp;<?php esc_html_e( 'You can only add upto 2 sponsors under you, further you can also read ', 'binary-mlm' ); ?> <a href="<?php echo esc_url( $terms_link ); ?>" class="woocommerce-terms-and-conditions-link" target="_blank"><?php esc_html_e( 'terms and conditions', 'binary-mlm' ); ?></a>
												</i>
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
