<?php
/**
 * Dashboard Sales Member Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Dashboard\Member;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Member_Ingredients' ) ) {

	/**
	 * Member Ingredients
	 */
	class BMLM_Member_Ingredients {
		/**
		 * Sponsor class object.
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Construct
		 *
		 * @param object $sponsor Sponsor.
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
			$user_id  = $this->sponsor->bmlm_get_id();
			$sponsors = $this->sponsor->bmlm_get_sponsor_childrens( $user_id );
			$members  = array();

			if ( ! empty( $sponsors ) ) {
				$users   = wp_list_pluck( $sponsors, 'id' );
				$args    = array(
					'per_page' => 200,
					'offset'   => 0,
					'users'    => $users,
					'fields'   => array( 'ID', 'display_name', 'user_email', 'user_registered' ),
				);
				$result  = $this->sponsor->bmlm_get_all_sponsors( $args );
				$members = $result->get_results();
			}
			?>
			<div class="bmlm-sales-stats-n-members">
				<div class="bmlm-member-activities">
					<div class="bmlm-graph-card">
						<div class="bmlm-graph-card-header">
							<h3>
								<?php esc_html_e( 'Member Activities', 'binary-mlm' ); ?>
							</h3>
						</div>
						<div class="bmlm-graph-card-body bmlm-no-padding">
							<table class="form-class">
								<thead>
									<tr>
										<th><label><?php esc_html_e( 'Name', 'binary-mlm' ); ?></label></th>
										<th><label><?php esc_html_e( 'Email', 'binary-mlm' ); ?></label></th>
										<th><label><?php esc_html_e( 'Joining Date', 'binary-mlm' ); ?></label></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ( ! empty( $members ) ) :
										foreach ( $members as $member ) :
											?>
											<tr>
												<td><?php echo esc_html( $member->display_name ); ?></td>
												<td><?php echo esc_html( $member->user_email ); ?></td>
												<td><?php echo esc_html( $member->user_registered ); ?></td>
											</tr>
											<?php
										endforeach;
									else :
										?>
										<tr>
											<td colspan="3"><?php esc_html_e( 'No members added yet', 'binary-mlm' ); ?></td>
										</tr>
										<?php
									endif;
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
