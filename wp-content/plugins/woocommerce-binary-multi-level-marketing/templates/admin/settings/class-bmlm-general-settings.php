<?php
/**
 * Configuration Controller Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Settings;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_General_Settings' ) ) {
	/**
	 * BMLM General Settings
	 */
	class BMLM_General_Settings {

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
			global $bmlm;

			$sponsor        = BMLM_Sponsor::get_instance();
			$first_admin_id = $bmlm->bmlm_get_first_admin_user_id();

			$bmlm_admin_sponsor_id = $sponsor->bmlm_get_sponsor_code( $first_admin_id );
			$level                 = $sponsor->bmlm_get_sponsor_tree_level( $first_admin_id );
			?>
			<div class="wrap">
				<?php settings_errors(); ?>
				<form action="options.php" method="POST">
					<table class="form-table">
						<tr>
							<th>
								<label for="bmlm-admin-sponsor-id"><?php esc_html_e( 'Admin Sponsor ID', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'Admin Unique Sponsor ID', 'binary-mlm' ), false ); ?>
								<input type="text" readonly name="bmlm_admin_sponsor_id" id="bmlm-admin-sponsor-id" class="regular-text" value="<?php echo esc_attr( $bmlm_admin_sponsor_id ); ?>" />
								<p class="description"><?php esc_html_e( 'This field is not editable', 'binary-mlm' ); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-admin-level"><?php esc_html_e( 'Admin Level', 'binary-mlm' ); ?></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'Admin Label', 'binary-mlm' ), false ); ?>
								<input type="text" readonly name="bmlm_admin_sponsor_level" id="bmlm-admin-level" class="regular-text" value="<?php echo esc_attr( $level ); ?>" />
								<p class="description"><?php esc_html_e( 'This field is not editable', 'binary-mlm' ); ?></p>
							</td>
						</tr>
					</table>
				</form>

				<form action='' method="post"  >
					<input type="submit" value="Reset data" class="button" name="reset_mlm" onclick="return confirm('<?php echo esc_html__( 'Are you sure You want to reset all data?', 'binary-mlm' ); ?>')" >
					<?php echo wc_help_tip( esc_html__( 'If you click on Reset data then all information like commission, sponsor, badge, and transactions data will be deleted', 'binary-mlm' ), false ); ?>
				</form>
			</div>
			<?php
		}
	}
}
