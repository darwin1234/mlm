<?php
/**
 * Sponsor Badge History Meta box Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Badge_History_Metabox' ) ) {
	/**
	 * BMLM Badge History Meta box
	 */
	class BMLM_Badge_History_Metabox {
		/**
		 * Badges.
		 *
		 * @var object Badges.
		 */
		protected $badges;

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Construct
		 *
		 * @param  array $badges badges.
		 */
		public function __construct( $badges ) {
			$this->badges = $badges;
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @param object $badges Badge object.
		 *
		 * @return object
		 */
		public static function get_instance( $badges ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $badges );
			}

			return static::$instance;
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Badge', 'binary-mlm' ); ?></th>
						<th><?php esc_html_e( 'Name', 'binary-mlm' ); ?></th>
						<th><?php esc_html_e( 'Bonus Amount', 'binary-mlm' ); ?></th>
						<th><?php esc_html_e( 'Date', 'binary-mlm' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( ! empty( $this->badges ) ) :
						foreach ( $this->badges as $badge ) :
							$badge_image = wp_get_attachment_image_src( $badge['image'] );
							?>
							<tr>
								<td>
									<img src="<?php echo esc_url( $badge_image[0] ); ?>" height="50" width="50" />
								</td>
								<td><?php echo esc_html( $badge['name'] ); ?></td>
								<td><?php echo wp_kses_post( wc_price( $badge['bonus_amt'] ) ); ?></td>
								<td><?php echo esc_html( $badge['date'] ); ?></td>
							</tr>
							<?php
						endforeach;
					else :
						?>
						<tr>
							<td colspan="4"><?php esc_html_e( 'No badges added yet', 'binary-mlm' ); ?></td>
						</tr>
						<?php
					endif;
					?>
				</tbody>
			</table>
			<?php
		}
	}
}
