<?php
/**
 * Sponsor general Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_General_Metabox' ) ) {
	/**
	 * BMLM General Meta box
	 */
	class BMLM_General_Metabox {
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
				$sponsor_obj = BMLM_Sponsor::get_instance( $sponsor_id );

				$sponsor_referrer = $sponsor_obj->bmlm_get_sponsor_referrer_user( $sponsor_id );

				$referrer_display_name = empty( $sponsor_referrer->display_name ) ? 'N/A' : $sponsor_referrer->display_name;
				$sponsor_code          = $sponsor_obj->bmlm_get_sponsor_code( $sponsor_id );
				$sponsor_code          = empty( $sponsor_code ) ? 'N/A' : strtoupper( $sponsor_code );
				$member_count          = $sponsor_obj->bmlm_sponsor_get_downline_member_count( $sponsor_id );
				$member_level          = $sponsor_obj->bmlm_get_sponsor_tree_level( $sponsor_id );
				$network_row           = $sponsor_obj->bmlm_get_network_id( $sponsor_id );
				?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th colspan="2">
							<strong>
								<?php esc_html_e( 'Sponsor', 'binary-mlm' ); ?>
							</strong>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Sponsor ID', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( strtoupper( $sponsor_code ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Referrer', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( strtoupper( $referrer_display_name ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Member Level', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( $member_level ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Downline Member', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( $member_count ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Network Row', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( $network_row ); ?></td>
					</tr>
				</tbody>
			</table>

				<?php
			}
		}
	}
}
