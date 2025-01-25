<?php
/**
 * Dashboard Become Sponsor Content.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Dashboard;

use WCBMLMARKETING\Helper\NetworkUsers;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * Become sponsor content.
 */
class BMLM_Become_Sponsor {

	/**
	 * Sponsor object
	 *
	 * @var object Sponsor object.
	 */
	protected $sponsor;

	/**
	 * Sponsor id
	 *
	 * @var int Sponsor id.
	 */
	protected $sponsor_id;

	/**
	 * Become sponsor construct.
	 *
	 * @param object $sponsor Sponsor class object.
	 */
	public function __construct( $sponsor ) {
		$this->sponsor    = $sponsor;
		$this->sponsor_id = get_current_user_id();
	}

	/**
	 * Process Form
	 *
	 * @return void
	 */
	public function bmlm_process_form() {
		$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce       = empty( $posted_data['bmlm_become_sponsor_nonce_field'] ) ? '' : $posted_data['bmlm_become_sponsor_nonce_field'];

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'bmlm_become_sponsor_nonce_action' ) ) {
			$referral_id = empty( $posted_data['bmlm_refferal_id'] ) ? 0 : trim( $posted_data['bmlm_refferal_id'] );
			$is_valid    = $this->sponsor->bmlm_validate_refferal_id( $referral_id );

			if ( $is_valid ) {
				$user = $this->sponsor->bmlm_update_sponsor_details( $this->sponsor_id, $referral_id );

				$onj = new NetworkUsers\BMLM_Network_Users();
				$onj->bmlm_add_network_user( $this->sponsor_id );

				do_action( 'bmlm_new_sponsor_registration', $posted_data );
				do_action(
					'bmlm_new_sponsor_register_to_admin',
					array(
						'user_email' => $user->user_email,
						'user_name'  => $user->user_login,
					)
				);
			}
		}
	}

	/**
	 * Template
	 *
	 * @return void
	 */
	public function get_template() {

		?>
		<hr />
		<div class="bmlm-become-sponsor">
			<form method="post">
				<?php wp_nonce_field( 'bmlm_become_sponsor_nonce_action', 'bmlm_become_sponsor_nonce_field' ); ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<?php esc_html_e( 'Do you want to become a sponsor?', 'binary-mlm' ); ?>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label class="label" for="bmlm-refferal-id"><?php esc_html_e( 'Reference Sponsor Id', 'binary-mlm' ); ?></label>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<input type="text" class="required-entry" name="bmlm_refferal_id" id="bmlm-refferal-id" placeholder="<?php esc_attr_e( 'Please enter reference code', 'binary-mlm' ); ?>" aria-required="true">

				<p>
					<button type="submit" title="Submit" class="button">
					<?php esc_html_e( 'Submit', 'binary-mlm' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}
}
