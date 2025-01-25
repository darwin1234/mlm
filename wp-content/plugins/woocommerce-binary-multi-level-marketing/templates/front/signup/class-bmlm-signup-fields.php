<?php
/**
 * Signup custom fields.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Signup;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * Signup fields class.
 */
class BMLM_Signup_Fields {
	/**
	 * Signup fields construct.
	 */
	public function __construct() {}

	/**
	 * Template
	 *
	 * @return void
	 */
	public function get_template() {
		$postdata    = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$refferal_id = ! empty( $postdata['bmlm_refferal_id'] ) ? $postdata['bmlm_refferal_id'] : '';
		$terms_link  = get_privacy_policy_url();
		if(isset($_GET['sponsor'])){
			$refferal_id = get_user_meta((int)$_GET['sponsor'], 'bmlm_sponsor_id', true );
		}
		?>
		<div class="bmlm-sponsor-registration-fields">
			<div>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="bmlm-refferal_id"><?php esc_html_e( 'Sponsor Referral ID', 'binary-mlm' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text form-control" name="bmlm_refferal_id" id="bmlm-refferal_id" value="<?php echo $refferal_id; ?>" />
				</p>
				<?php do_action( 'bmlm_sponsor_add_registeration_field' ); ?>
			</div>
			<div class="woocommerce-sponsor-terms-text">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="sponsor_terms" id="terms">
					<span class="woocommerce-terms-and-conditions-checkbox-text"><span class="required">*</span>&nbsp;<?php esc_html_e( 'I accept website', 'binary-mlm' ); ?>&nbsp;<a href="<?php echo esc_url( $terms_link ); ?>" class="woocommerce-terms-and-conditions-link" target="_blank"><?php esc_html_e( 'terms and conditions', 'binary-mlm' ); ?></a>&nbsp;<?php esc_html_e( 'for becoming a sponsor', 'binary-mlm' ); ?></span>
				</label>
			</div>
			<input type="hidden" name="role" value="bmlm_sponsor">
		</div>
		<?php
	}
}
