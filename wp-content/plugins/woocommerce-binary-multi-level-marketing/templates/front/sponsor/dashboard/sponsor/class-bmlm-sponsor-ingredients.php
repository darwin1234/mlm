<?php
/**
 * Dashboard Sales Sponsor Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Dashboard\Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Sponsor_Ingredients' ) ) {
	/**
	 * BMLM Dashboard Sponsor Ingredients
	 */
	class BMLM_Sponsor_Ingredients {
		/**
		 * Sponsor class object.
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Construct
		 *
		 * @param object $sponsor Sponsor object.
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
			$sponsor               = $this->sponsor->bmlm_get_sponsor();
			$sponsor_referrer      = $this->sponsor->bmlm_get_sponsor_referrer_user( $sponsor->ID );
			$referrer_display_name = empty( $sponsor_referrer->display_name ) ? 'N/A' : $sponsor_referrer->display_name;
			$referrer_email        = empty( $sponsor_referrer->user_email ) ? 'N/A' : $sponsor_referrer->user_email;

			$sponsor_code  = $this->sponsor->bmlm_get_sponsor_code( $sponsor->ID );
			$sponsor_code  = empty( $sponsor_code ) ? 'N/A' : $sponsor_code;
			$referrer_code = $this->sponsor->bmlm_get_sponsor_code( $sponsor_referrer->ID );
			?>
			<div class="bmlm-sponsor-ingredients">
				<div class="bmlm-row">
					<div class="bmlm-col">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3>
									<?php esc_html_e( 'Sponsor', 'binary-mlm' ); ?>
								</h3>
							</div>
							<div class="bmlm-graph-card-body bmlm-no-padding">
								<table class="form-class">
									<tbody>
										<tr>
											<th>
												<label><?php esc_html_e( 'Sponsor ID', 'binary-mlm' ); ?></label>
											</th>
											<td><?php echo esc_html( $sponsor_code ); ?></td>
										</tr>
										<tr>
											<th>
												<label><?php esc_html_e( 'Email', 'binary-mlm' ); ?></label>
											</th>
											<td><?php echo esc_html( $sponsor->user_email ); ?></td>
										</tr>
										<tr>
											<th>
												<label><?php esc_html_e( 'Joining Date', 'binary-mlm' ); ?></label>
											</th>
											<td><?php echo esc_html( $sponsor->user_registered ); ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="bmlm-col">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3>
									<?php esc_html_e( 'Sponsor Referral', 'binary-mlm' ); ?>
								</h3>
							</div>
							<div class="bmlm-graph-card-body bmlm-no-padding">
								<table class="form-class">
									<tbody>
										<tr>
											<th>
												<label><?php esc_html_e( 'Referral Sponsor ID', 'binary-mlm' ); ?></label>
											</th>
											<td><?php echo esc_html( $referrer_code ); ?></td>
										</tr>
										<tr>
											<th>
												<label><?php esc_html_e( 'Name', 'binary-mlm' ); ?></label>
											</th>
											<td><?php echo esc_html( $referrer_display_name ); ?></td>
										</tr>
										<tr>
											<th>
												<label><?php esc_html_e( 'Email', 'binary-mlm' ); ?></label>
											</th>
											<td><?php echo esc_html( $referrer_email ); ?></td>
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
