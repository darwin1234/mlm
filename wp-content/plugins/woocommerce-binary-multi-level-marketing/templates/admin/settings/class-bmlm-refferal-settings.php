<?php
/**
 * Referral Controller Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Settings;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Refferal_Settings' ) ) {
	/**
	 * BMLM referral Settings
	 */
	class BMLM_Refferal_Settings {
		/**
		 * Construct
		 */
		public function __construct() { }

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$_options = array(
				'No',
				'Yes',
			);

			$refferal_code_length         = get_option( 'bmlm_refferal_code_length' );
			$refferal_code_prefix         = get_option( 'bmlm_refferal_code_prefix' );
			$refferal_code_suffix         = get_option( 'bmlm_refferal_code_suffix' );
			$sponsor_refferal_code_format = get_option( 'bmlm_sponsor_refferal_code_format' );
			$refferal_code_separator      = get_option( 'bmlm_refferal_code_separator' );
			?>
			<div class="wrap">
				<?php settings_errors(); ?>
				<form action="options.php" method="POST">
					<?php settings_fields( 'bmlm-refferal-settings-group' ); ?>
					<table class="form-table">
						<tr>
							<th>
								<label for="bmlm-reffral-code-length"><?php esc_html_e( 'Referral Code length', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'You can set the Referral Code Length between 5 and 30.', 'binary-mlm' ), false ); ?>
								<input type="number" name="bmlm_refferal_code_length" id="bmlm-reffral-code-length" value="<?php echo esc_attr( $refferal_code_length ); ?>" class="regular-text" min="5" max="30" />
								<p class="description"><?php esc_html_e( 'Code Length should be between 5 and 30.', 'binary-mlm' ); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-sponsor-refferal-code-format"><?php esc_html_e( 'Include special characters in Referral Code format', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'Select status if you want to Include special characters in Referral Code format', 'binary-mlm' ), false ); ?>
								<select name="bmlm_sponsor_refferal_code_format" id="bmlm-sponsor-refferal-code-format" class="regular-text">
									<?php
									$selected = ! empty( $sponsor_refferal_code_format ) ? $sponsor_refferal_code_format : '';
									foreach ( $_options as $option_key => $option_value ) :
										?>
										<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_html( $option_value ); ?></option>
										<?php
										endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-refferal-code-prefix"><?php esc_html_e( 'Referral Code prefix', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'You can add Referral Code prefix. EX. MLM-XXXX', 'binary-mlm' ), false ); ?>
								<input type="text" name="bmlm_refferal_code_prefix" id="bmlm-refferal-code-prefix" value="<?php echo esc_attr( $refferal_code_prefix ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-refferal-code-suffix"><?php esc_html_e( 'Referral Code suffix', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'You can add Referral Code Suffix. EX. XXXX-MLM', 'binary-mlm' ), false ); ?>
								<input type="text" name="bmlm_refferal_code_suffix" id="bmlm-refferal-code-suffix" value="<?php echo esc_attr( $refferal_code_suffix ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-refferal-code-separator"><?php esc_html_e( 'Referral Code separator', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'You can add Referral Code Separator. EX. MLM-MLM', 'binary-mlm' ), false ); ?>
								<input type="text" name="bmlm_refferal_code_separator" id="bmlm-refferal-code-separator" value="<?php echo esc_attr( $refferal_code_separator ); ?>" class="regular-text" />
							</td>
						</tr>

					</table>
					<?php submit_button( esc_html__( 'Save Changes', 'binary-mlm' ) ); ?>
				</form>
			</div>
			<?php
		}
	}
}
