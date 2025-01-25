<?php
/**
 * Sponsor Level UP Commission Controller Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Settings\Commission;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Level_Commission_Settings' ) ) {
	/**
	 * BMLM_Level Commission Settings
	 */
	class BMLM_Level_Commission_Settings {
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
			$visiblity_args = array(
				'No',
				'Yes',
			);

			$default_args = array(
				array(
					'level' => 1,
					'rate'  => '',
				),
				array(
					'level' => 2,
					'rate'  => '',
				),
				array(
					'level' => 3,
					'rate'  => '',
				),
			);

			$bmlm_levelup_commission_amount      = get_option( 'bmlm_levelup_commission_amount' );
			$bmlm_levelup_commission             = get_option( 'bmlm_levelup_commission' );
			$data                                = ! empty( $bmlm_levelup_commission ) ? $bmlm_levelup_commission : $default_args;
			$bmlm_levelup_amount_settings_enable = get_option( 'bmlm_levelup_amount_settings_enable' );
			$class                               = empty( $bmlm_levelup_amount_settings_enable ) ? 'bmlm-visiblity hide' : 'bmlm-visiblity';
			?>
			<div class="wrap">
				<?php settings_errors(); ?>
				<form action="options.php" method="POST">
					<?php settings_fields( 'bmlm-levelup-commission-settings-group' ); ?>
					<h3><?php esc_html_e( 'Sponsors Level Commission', 'binary-mlm' ); ?></h3>
					<p><?php esc_html_e( 'Here, commission settings are configured for the event when a sponsor leveled up.', 'binary-mlm' ); ?></p>
					<table class="form-table">
						<tr>
							<th>
								<label for="bmlm-sponsor-enable-level-commission"><?php esc_html_e( 'Enable Level Commission', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<select name="bmlm_levelup_amount_settings_enable" id="bmlm-sponsor-enable-level-commission" class="regular-text visiblity-action">
									<?php
									$selected = ! empty( $bmlm_levelup_amount_settings_enable ) ? $bmlm_levelup_amount_settings_enable : '';
									foreach ( $visiblity_args as $option_key => $option_value ) :
										?>
										<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_html( $option_value ); ?></option>
										<?php
									endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr class="<?php echo esc_attr( $class ); ?>">
							<th>
								<label for="bmlm-levelup-commission-admin"><?php esc_html_e( 'Commission amount on Level Up (Admin Fixed Amount)', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<input type="number" step="0.01" min="0" max="100" name="bmlm_levelup_commission_amount" id="bmlm-levelup-commission-admin" class="regular-text" value="<?php echo esc_attr( $bmlm_levelup_commission_amount ); ?>" />
							</td>
						</tr>
						<tr class="<?php echo esc_attr( $class ); ?>">
							<th>
								<label for="bmlm-commission-levels"><?php esc_html_e( 'Levels', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<table class="wp-list-table bmlm-widefat sponsor-commission-rule-table" id="bmlm_levelup_commission_level_rate">
									<thead>
										<tr>
											<th class="manage-column"><?php esc_html_e( 'Level', 'binary-mlm' ); ?></th>
											<th class="manage-column"><?php esc_html_e( 'Level Name', 'binary-mlm' ); ?></th>
											<th class="manage-column"><?php esc_html_e( 'Commission (%)', 'binary-mlm' ); ?></th>
											<th class="manage-column" colspan="2"><?php esc_html_e( 'Action', 'binary-mlm' ); ?></th>
										</tr>
									</thead>
									<tfoot>
										<tr valign="top">
											<td colspan="4" class="col-actions-add">
												<button id="addToEndBtn" class="action-add button-primary" title="Add" type="button">
													<span>
														<?php esc_html_e( 'Add Values', 'binary-mlm' ); ?>
													</span>
												</button>
											</td>
										</tr>
									</tfoot>
									<tbody>
										<?php foreach ( $data as $key => $value ) : ?>
											<tr valign="top">
												<td>
													<input type="number" step="0.01" min="1" max="100" name="bmlm_levelup_commission[<?php echo esc_attr( $key ); ?>][level]" value="<?php echo esc_attr( $value['level'] ); ?>">
												</td>
												<td>
													<input type="text" name="bmlm_levelup_commission[<?php echo esc_attr( $key ); ?>][name]" value="<?php echo ( isset( $value['name'] ) ) ? esc_attr( $value['name'] ) : ''; ?>" class="required-entry">
												</td>
												<td>
													<input type="number" step="0.01" min="1" max="100"  name="bmlm_levelup_commission[<?php echo esc_attr( $key ); ?>][rate]" value="<?php echo ( isset( $value['rate'] ) ) ? esc_attr( $value['rate'] ) : ''; ?>" class="required-entry validate-range">
												</td>

												<td class="col-actions">
													<button class="action-delete button-primary" type="button">
														<span>
															<?php esc_html_e( 'Delete', 'binary-mlm' ); ?>
														</span>
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								<p class="description"><?php esc_html_e( 'If commission allocation for any level is not specified. then it will be treated as 0, and sponsors with that level will receive no commission on referred product sale.', 'binary-mlm' ); ?></p>

							</td>
						</tr>
					</table>
					<?php submit_button( esc_html__( 'Save Changes', 'binary-mlm' ) ); ?>
				</form>
			</div>
			<script id="tmpl-table_row_template" type="text/html">
				<tr valign="top">
					<td>
						<input type="number" min="1" max="100" name="bmlm_levelup_commission[{{{data.key}}}][level]" required value="">
					</td>
					<td>
						<input type="text" name="bmlm_levelup_commission[{{{data.key}}}][name]" required value="" class="required-entry">
					</td>
					<td>
						<input type="number" min="1" max="100"  name="bmlm_levelup_commission[{{{data.key}}}][rate]" required value="" class="required-entry validate-range">
					</td>
					<td class="col-actions">
						<button class="action-delete button-primary" type="button">
							<span>
								<?php esc_html_e( 'Delete', 'binary-mlm' ); ?>
							</span>
						</button>
					</td>
				</tr>
			</script>
			<?php
		}
	}
}
