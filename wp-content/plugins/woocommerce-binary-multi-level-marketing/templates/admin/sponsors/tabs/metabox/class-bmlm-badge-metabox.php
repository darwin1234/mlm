<?php
/**
 * Sponsor Badge Meta box Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Badge_Metabox' ) ) {
	/**
	 * BMLM Badge Metabox
	 */
	class BMLM_Badge_Metabox {
		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Badge.
		 *
		 * @var object Badge.
		 */
		protected $badge;

		/**
		 * Construct
		 *
		 * @param object $badge Badge object.
		 */
		public function __construct( $badge ) {
			$this->badge = $badge;
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @param object $badge Badge object.
		 *
		 * @return object
		 */
		public static function get_instance( $badge ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $badge );
			}

			return static::$instance;
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$badge_name   = 'N/A';
			$bonus_amount = 'N/A';

			if ( ! empty( $this->badge ) ) {
				$badge_name   = $this->badge['name'];
				$bonus_amount = $this->badge['bonus_amt'];
			}
			?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th colspan="2"><strong><?php esc_html_e( 'Badge', 'binary-mlm' ); ?></strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Badge', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo esc_html( $badge_name ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Bonus Amount', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $bonus_amount ) ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php
		}
	}
}
