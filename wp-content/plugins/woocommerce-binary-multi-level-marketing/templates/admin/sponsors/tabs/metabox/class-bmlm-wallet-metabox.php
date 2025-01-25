<?php
/**
 * Sponsor Wallet Meta box Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

use WCBMLMARKETING\Helper\Wallet\BMLM_Wallet;
use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Wallet_Metabox' ) ) {
	/**
	 * BMLM Wallet Meta box
	 */
	class BMLM_Wallet_Metabox {

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
		 * Template.
		 *
		 * @return void
		 */
		public function get_template() {
			$sponsor_id = empty( $this->sponsor_id ) ? 0 : intval( $this->sponsor_id );

			if ( $sponsor_id > 0 ) {
				$sponsor_obj = BMLM_Sponsor::get_instance( $sponsor_id );
				$wallet_obj  = BMLM_Wallet::get_instance( $sponsor_id );

				$args = array(
					'user_id' => $sponsor_id,
					'paid'    => 0,
				);

				$wallet_amount   = $wallet_obj->bmlm_get_wallet_money( $sponsor_id );
				$pending_balance = $sponsor_obj->bmlm_sponsor_get_gross_business( $args );
				?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th colspan="2">
							<strong>
							<?php esc_html_e( 'Wallet', 'binary-mlm' ); ?>
							</strong>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Wallet Balance', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $wallet_amount ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Pending Amount', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $pending_balance ) ); ?></td>
					</tr>
				</tbody>
			</table>
				<?php
			}
		}
	}
}
