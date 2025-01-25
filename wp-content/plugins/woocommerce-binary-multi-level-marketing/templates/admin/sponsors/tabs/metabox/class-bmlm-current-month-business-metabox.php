<?php
/**
 * Sponsor Current Month Business Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Current_Month_Business_Metabox' ) ) {
	/**
	 * BMLM Current Month Business Metabox
	 */
	class BMLM_Current_Month_Business_Metabox {
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
				$args        = array(
					'user_id' => $sponsor_id,
					'type'    => 'sale',
					'current' => 1,
					'paid'    => 1,
				);

				$sales_commission = $sponsor_obj->bmlm_sponsor_get_gross_business( $args );

				$args['type']       = 'joining';
				$joining_commission = $sponsor_obj->bmlm_sponsor_get_gross_business( $args );

				$args['type']       = 'levelup';
				$levelup_commission = $sponsor_obj->bmlm_sponsor_get_gross_business( $args );

				$args['type']     = 'bonus';
				$bonus_commission = $sponsor_obj->bmlm_sponsor_get_gross_business( $args );
				?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th colspan="2">
							<strong>
								<?php esc_html_e( 'Current Month', 'binary-mlm' ); ?>
							</strong>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Sales Earning', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $sales_commission ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Joining Earning', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $joining_commission ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'LevelUp Earning', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $levelup_commission ) ); ?></td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Bonus Earning', 'binary-mlm' ); ?></label>
						</th>
						<td><?php echo wp_kses_post( wc_price( $bonus_commission ) ); ?></td>
					</tr>
				</tbody>
			</table>

				<?php
			}
		}
	}
}
