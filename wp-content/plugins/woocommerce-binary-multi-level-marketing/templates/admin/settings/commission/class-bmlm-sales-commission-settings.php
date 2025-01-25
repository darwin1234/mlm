<?php
/**
 * Sponsor Sales Commission Controller Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Settings\Commission;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Sales_Commission_Settings' ) ) {
	/**
	 * BMLM Sales Commission Settings
	 */
	class BMLM_Sales_Commission_Settings {

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

			$bmlm_sales_commission          = get_option( 'bmlm_sales_commission_admin' );
			$bmlm_sales_commission_other    = get_option( 'bmlm_sales_commission_other' );
			$bmlm_sales_remaining_commision = 0;
			if ( intval( $bmlm_sales_commission ) >= 0 ) {
				$bmlm_sales_remaining_commision = 100 - $bmlm_sales_commission;
			}
			$data = ! empty( $bmlm_sales_commission_other ) ? $bmlm_sales_commission_other : $default_args;
			?>
			<div class="wrap">
				<?php settings_errors(); ?>
				<form action="options.php" method="POST">
					<?php settings_fields( 'bmlm-sales-commission-settings-group' ); ?>
					<h3><?php esc_html_e( 'Sponsors Sales Commission', 'binary-mlm' ); ?></h3>
					<p><?php esc_html_e( 'Here, commission settings are configured for the event when a product sale occurs.', 'binary-mlm' ); ?></p>
					<table class="form-table">
						<tr>
							<th>
								<label for="bmlm-sales-commission"><?php esc_html_e( 'Commission for MLM (Admin) sales (%)', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<input type="number" step="0.1" min="0" max="100" name="bmlm_sales_commission_admin" id="bmlm-sales-commission" class="regular-text bmlm-validate-percentage" value="<?php echo esc_attr( $bmlm_sales_commission ); ?>" /> %
								<p class="description"><?php esc_html_e( 'Enter a value between 0 and 100', 'binary-mlm' ); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-commission-remaining"><?php esc_html_e( 'Commission Remaining (%)', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<input type="text" name="bmlm_commission_mlm_remaining" id="bmlm-commission-remaining" class="regular-text" value="<?php echo esc_attr( $bmlm_sales_remaining_commision ); ?>" disabled /> %
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-commission-levels"><?php esc_html_e( 'Levels', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<table class="wp-list-table bmlm-widefat sponsor-commission-rule-table" id="bmlm_sales_commission_level_rate">
									<thead>
										<tr>
											<th class="manage-column"><?php esc_html_e( 'Level', 'binary-mlm' ); ?></th>
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
													<input type="number" step="0.01" min="1" max="100" name="bmlm_sales_commission_other[<?php echo esc_attr( $key ); ?>][level]" value="<?php echo esc_attr( $value['level'] ); ?>">
												</td>
												<td>
													<input type="number" step="0.01" min="1" max="100"  name="bmlm_sales_commission_other[<?php echo esc_attr( $key ); ?>][rate]" value="<?php echo esc_attr( $value['rate'] ); ?>" class="required-entry validate-range">
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
						<input type="number" min="1" max="100" name="bmlm_sales_commission_other[{{{data.key}}}][level]" required value="">
					</td>
					<td>
						<input type="number" min="1" max="100"  name="bmlm_sales_commission_other[{{{data.key}}}][rate]" required value="" class="required-entry validate-range">
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
