<?php
/**
 * Sponsor account Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Account_Metabox' ) ) {
	/**
	 * BMLM Account Metabox
	 */
	class BMLM_Account_Metabox {
		/**
		 * Sponsor.
		 *
		 * @var object Sponsor.
		 */
		protected $sponsor_id;

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Construct
		 *
		 * @param int $sponsor_id Sponsor Id.
		 */
		public function __construct( $sponsor_id ) {
			$this->sponsor_id = $sponsor_id;
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @param int $sponsor_id Sponsor ID.
		 *
		 * @return object
		 */
		public static function get_instance( $sponsor_id = 0 ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $sponsor_id );
			}

			return static::$instance;
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$sponsor_id = empty( $this->sponsor_id ) ? 0 : intval( $this->sponsor_id );

			if ( $sponsor_id > 0 ) {
				$sponsor_obj  = BMLM_Sponsor::get_instance( $sponsor_id );
				$sponsor_data = $sponsor_obj->bmlm_get_sponsor( $sponsor_id );
				$status_html  = $sponsor_obj->bmlm_get_status_html( $sponsor_id );
				?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th colspan="2"><strong><?php esc_html_e( 'Account', 'binary-mlm' ); ?></strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Joining Date', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( date_i18n( 'F d, Y g:i:s A', strtotime( $sponsor_data->user_registered ) ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Username', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( strtoupper( $sponsor_data->user_login ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Email', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( $sponsor_data->user_email ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Display Name', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( strtoupper( $sponsor_data->display_name ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Status', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( $status_html ); ?></td>
					</tr>
				</tbody>
			</table>
				<?php
			}
		}
	}
}
