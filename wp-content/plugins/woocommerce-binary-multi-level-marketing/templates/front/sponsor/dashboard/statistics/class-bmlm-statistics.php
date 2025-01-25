<?php
/**
 * Dashboard Statistics Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Dashboard\Statistics;

use WCBMLMARKETING\Helper\Badges\BMLM_Badges;
use WCBMLMARKETING\Helper\Wallet\BMLM_Wallet;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Statistics' ) ) {
	/**
	 * BMLM Dashboard Statistics
	 */
	class BMLM_Statistics {

		/**
		 * Sponsor object
		 *
		 * @var object Sponsor object.
		 */
		protected $sponsor;

		/**
		 * Construct.
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
			$sponsor          = $this->sponsor->bmlm_get_sponsor();
			$args             = array(
				'user_id' => $sponsor->ID,
			);
			$wallet_obj       = BMLM_Wallet::get_instance( $sponsor->ID );
			$gross_income     = $this->sponsor->bmlm_sponsor_get_gross_business( $args );
			$args['paid']     = 1;
			$wallet_amount    = $wallet_obj->bmlm_get_wallet_money( $sponsor->ID );
			$args['paid']     = 0;
			$pending_balance  = $this->sponsor->bmlm_sponsor_get_gross_business( $args );
			$downline_members = $this->sponsor->bmlm_sponsor_get_downline_member_count( $sponsor->ID );
			$member_level     = $this->sponsor->bmlm_get_sponsor_tree_level( $sponsor->ID );
			$badge            = array();
			$badge_image      = '';
			$badge_id         = $this->sponsor->bmlm_get_sponsor_badge( $sponsor->ID );

			if ( ! empty( $badge_id ) ) {
				$badge_obj = new BMLM_Badges();
				$badge     = $badge_obj->bmlm_get_badge( $badge_id );
				if ( ! empty( $badge ) ) {
					$badge_image = wp_get_attachment_image_src( $badge['image'] );
				}
			}
			?>
			<div class="sales-stats-n-members">
				<div class="bmlm-content-sponsor-earning">
					<div class="bmlm-card gross-earning">
						<div class="bmlm-card-wrapper">
							<div class="bmlm-card-header">
								<div class="bmlm-row">
									<div class="bmlm-col">
										<h3><?php esc_html_e( 'Gross Earnings', 'binary-mlm' ); ?></h3>
									</div>
									<div class="bmlm-col-auto">
										<div class="bmlm-earning-avatar">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign align-middle"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
										</div>
									</div>
								</div>
							</div>
							<div class="bmlm-card-body">
								<h3><b><?php echo wp_kses_post( wc_price( $gross_income ) ); ?></b></h3>
							</div>
						</div>
					</div>
					<div class="bmlm-card wallet-earning">
						<div class="bmlm-card-wrapper">
							<div class="bmlm-card-header">
								<div class="bmlm-row">
									<div class="bmlm-col">
										<h3><?php esc_html_e( 'Wallet Balance', 'binary-mlm' ); ?></h3>
									</div>
									<div class="bmlm-col-auto">
										<div class="bmlm-earning-avatar">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-briefcase align-middle"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
										</div>
									</div>
								</div>
							</div>
							<div class="bmlm-card-body">
								<h3><b><?php echo wp_kses_post( wc_price( $wallet_amount ) ); ?></b></h3>
							</div>
						</div>
					</div>
					<div class="bmlm-card bmlm-pending-balance">
						<div class="bmlm-card-wrapper">
							<div class="bmlm-card-header">
								<div class="bmlm-row">
									<div class="bmlm-col">
										<h3><?php esc_html_e( 'Pending Amount', 'binary-mlm' ); ?></h3>
									</div>
									<div class="bmlm-col-auto">
										<div class="bmlm-earning-avatar">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart align-middle"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
										</div>
									</div>
								</div>
							</div>
							<div class="bmlm-card-body">
								<h3><b><?php echo '+' . wp_kses_post( wc_price( $pending_balance ) ); ?></b></h3>
							</div>
						</div>
					</div>
					<div class="bmlm-card downline-members">
						<div class="bmlm-card-wrapper">
							<div class="bmlm-card-header">
								<div class="bmlm-row">
									<div class="bmlm-col">
										<h3><?php esc_html_e( 'Downline Members', 'binary-mlm' ); ?></h3>
									</div>
									<div class="bmlm-col-auto">
										<div class="bmlm-earning-avatar">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users align-middle"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
										</div>
									</div>
								</div>
							</div>
							<div class="bmlm-card-body">
								<h3><b><?php echo esc_html( $downline_members ); ?></b></h3>
							</div>
						</div>
					</div>
					<div class="bmlm-card member-level">
						<div class="bmlm-card-wrapper">
							<div class="bmlm-card-header">
								<div class="bmlm-row">
									<div class="bmlm-col">
										<h3><?php esc_html_e( 'Sponsor Level', 'binary-mlm' ); ?></h3>
									</div>
									<div class="bmlm-col-auto">
										<div class="bmlm-earning-avatar">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user align-middle"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
										</div>
									</div>
								</div>
							</div>
							<div class="bmlm-card-body">
								<h3><b><?php echo esc_html( $member_level ); ?></b></h3>
							</div>
						</div>
					</div>
					<?php
					if ( ! empty( $badge ) ) :
						?>
						<div class="bmlm-card member-level">
							<div class="bmlm-card-wrapper">
								<div class="bmlm-card-header">
									<div class="bmlm-row">
										<div class="bmlm-col">
											<h3><?php esc_html_e( 'Sponsor Badge', 'binary-mlm' ); ?></h3>
										</div>
										<div class="bmlm-col-auto">
											<div class="bmlm-earning-avatar">
											<?php
											if ( ! empty( $badge_image ) ) :
												?>
												<img src="<?php echo esc_url( $badge_image[0] ); ?>" height="<?php echo esc_attr( $badge_image[1] ); ?>" width="<?php echo esc_attr( $badge_image[2] ); ?>" />
												<?php
											endif;
											?>
											</div>
										</div>
									</div>
								</div>
								<div class="bmlm-card-body">
									<h3><b><?php echo esc_html( $badge['name'] ) . '(' . wp_kses_post( wc_price( $badge['bonus_amt'] ) ) . ')'; ?></b></h3>
								</div>
							</div>
						</div>
						<?php
						endif;
					?>
				</div>
			</div>
			<?php
		}
	}
}
