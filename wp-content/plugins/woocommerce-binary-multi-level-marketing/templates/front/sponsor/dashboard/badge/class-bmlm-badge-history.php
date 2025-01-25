<?php
/**
 * Dashboard Sponsor badge history Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Dashboard\Badge;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Badge_History' ) ) {
	/**
	 * BMLM Dashboard Badge History
	 */
	class BMLM_Badge_History {
		/**
		 * Sponsor class object.
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Construct
		 *
		 * @param object $sponsor sponsor.
		 */
		public function __construct( $sponsor ) {
			$this->sponsor = $sponsor;
		}

		/**
		 * Template.
		 *
		 * @return void
		 */
		public function get_template() {
			$user_id = $this->sponsor->bmlm_get_id();
			$badges  = $this->sponsor->bmlm_get_sponsor_badge_list( $user_id );
			?>
			<div class="bmlm-badge-history">
				<div class="bmlm-graph-card">
					<div class="bmlm-graph-card-header">
						<h3><?php esc_html_e( 'Badge History', 'binary-mlm' ); ?></h3>
					</div>
					<div class="bmlm-graph-card-body bmlm-no-padding">
						<table class="form-class">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Badge', 'binary-mlm' ); ?></th>
									<th><?php esc_html_e( 'Name', 'binary-mlm' ); ?></th>
									<th><?php esc_html_e( 'Bonus Amount', 'binary-mlm' ); ?></th>
									<th><?php esc_html_e( 'Date', 'binary-mlm' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( ! empty( $badges ) ) :
									foreach ( $badges as $badge ) :
										$badge_image = wp_get_attachment_image_src( $badge['image'] );
										?>
										<tr>
											<td>
												<img src="<?php echo esc_url( $badge_image[0] ); ?>" height="50" width="50" />
											</td>
											<td><?php echo esc_html( $badge['name'] ); ?></td>
											<td><?php echo wp_kses_post( wc_price( $badge['bonus_amt'] ) ); ?></td>
											<td><?php echo esc_html( $badge['date'] ); ?></td>
										</tr>
										<?php
									endforeach;
								else :
									?>
									<tr>
										<td colspan="4"><?php esc_html_e( 'No badges added yet', 'binary-mlm' ); ?></td>
									</tr>
									<?php
								endif;
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
