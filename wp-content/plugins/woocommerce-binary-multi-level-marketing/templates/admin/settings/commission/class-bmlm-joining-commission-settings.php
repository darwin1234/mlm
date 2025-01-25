<?php
/**
 * New Sponsor  Joining Commission Controller Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Settings\Commission;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Joining_Commission_Settings' ) ) {
	/**
	 * BMLM Joining Commission Settings
	 */
	class BMLM_Joining_Commission_Settings {
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

			$membership                       = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$membership_id                    = $membership->ID;
			$product                          = wc_get_product( $membership_id );
			$bmlm_joining_commission          = get_option( 'bmlm_joining_commission_admin', 0 );
			$bmlm_joining_commission          = ! empty( $bmlm_joining_commission ) ? $bmlm_joining_commission : 0;
			$bmlm_joining_commission_other    = get_option( 'bmlm_joining_commission_other' );
			$bmlm_joining_remaining_commision = 0;

			if ( intval( $bmlm_joining_commission ) >= 0 ) {
				$bmlm_joining_remaining_commision = 100 - $bmlm_joining_commission;
			}
			$data                                = ! empty( $bmlm_joining_commission_other ) ? $bmlm_joining_commission_other : $default_args;
			$bmlm_joining_commission_alot_amount = $product->get_price();
			$bmlm_joining_amount_settings_enable = get_option( 'bmlm_joining_amount_settings_enable' );
			$class                               = empty( $bmlm_joining_amount_settings_enable ) ? 'bmlm-visiblity hide' : 'bmlm-visiblity';
			?>
			<div class="wrap">
				<?php settings_errors(); ?>
				<form action="options.php" method="POST">
					<?php settings_fields( 'bmlm-joining-commission-settings-group' ); ?>
					<h3><?php esc_html_e( 'Sponsors Joining Amount', 'binary-mlm' ); ?></h3>
					<p><?php esc_html_e( 'Here, commission settings are configured for the event when a new sponsor registered.', 'binary-mlm' ); ?></p>

					<table class="form-table">
						<tr>
							<th>
								<label for="bmlm-sponsor-joining-amount-settings-enable"><?php esc_html_e( 'Joining Amount Enable', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<select name="bmlm_joining_amount_settings_enable" id="bmlm-sponsor-joining-amount-settings-enable" class="regular-text visiblity-action">
									<?php
									$selected = ! empty( $bmlm_joining_amount_settings_enable ) ? $bmlm_joining_amount_settings_enable : '';
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
								<label for="bmlm-sponsor-joining-alot-amount"><?php esc_html_e( 'Total Amount on joining Alot (Fixed Amount)', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<input type="text" name="bmlm_joining_commission_alot_amount" id="bmlm-sponsor-joining-alot-amount" class="regular-text" readonly value="<?php echo esc_attr( $bmlm_joining_commission_alot_amount ); ?>" />
								<p class="description">
									<?php echo wp_sprintf( /* translators: %s: Product link.*/ esc_html__( 'To change membership amount follow this %s', 'binary-mlm' ), '<a href="' . esc_url( get_edit_post_link( $product->get_id() ) ) . '" target="_blank">' . esc_html__( 'link', 'binary-mlm' ) . '</a>' ); ?>
								</p>
							</td>
						</tr>
						<tr class="<?php echo esc_attr( $class ); ?>">
							<th>
								<label for="bmlm-joining-commission-admin"><?php esc_html_e( 'Commission for Sponsor (Admin) Joining (%)', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<input type="number" step="any" min ="0" max="100" name="bmlm_joining_commission_admin" id="bmlm-joining-commission-admin" class="regular-text" value="<?php echo esc_attr( $bmlm_joining_commission ); ?>" />
								<p class="description"><?php esc_html_e( 'Enter a value between 0 and 100', 'binary-mlm' ); ?></p>
							</td>
						</tr>
						<tr class="<?php echo esc_attr( $class ); ?>">
							<th>
								<label for="bmlm-joining-commisssion-other"><?php esc_html_e( 'Commission Remaining (%)', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<input type="text" name="bmlm_joining_commission_other" id="bmlm-joining-commisssion-other" class="regular-text" value="<?php echo esc_attr( $bmlm_joining_remaining_commision ); ?>" disabled />
							</td>
						</tr>
						<tr class="<?php echo esc_attr( $class ); ?>">
							<th>
								<label for="bmlm-commission-levels"><?php esc_html_e( 'Levels', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<table class="wp-list-table bmlm-widefat sponsor-commission-rule-table" id="bmlm_joining_commission_level_rate">
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
													<input type="number" step="0.01" min="1" max="100" name="bmlm_joining_commission_other[<?php echo esc_attr( $key ); ?>][level]" value="<?php echo esc_attr( $value['level'] ); ?>">
												</td>
												<td>
													<input type="number" step="0.01" min="1" max="100" name="bmlm_joining_commission_other[<?php echo esc_attr( $key ); ?>][rate]" value="<?php echo esc_attr( $value['rate'] ); ?>" class="required-entry validate-range">
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
					<td><input type="number" min="1" max="100" name="bmlm_joining_commission_other[{{{data.key}}}][level]" required value=""></td>
					<td><input type="number" min="1" max="100" name="bmlm_joining_commission_other[{{{data.key}}}][rate]" required value="" class="required-entry validate-range"></td>
					<td class="col-actions"><button class="action-delete button-primary" type="button"><span><?php esc_html_e( 'Delete', 'binary-mlm' ); ?></span></button></td>
				</tr>
			</script>
			<?php
		}
	}
}
