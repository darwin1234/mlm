<?php
/**
 * Dashboard Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Dashboard;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;
use WCBMLMARKETING\Templates\Front\Sponsor\Dashboard;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Dashboard' ) ) {
	/**
	 * BMLM_Dashboard
	 */
	class BMLM_Dashboard extends BMLM_Sponsor {
		/**
		 * Construct.
		 *
		 * @param int $sponsor_id Sponsor Id.
		 */
		public function __construct( $sponsor_id ) {
			parent::__construct();
			add_action( 'bmlm_dashboard-graph', array( $this, 'bmlm_dashboard_graph_earning' ) );
			add_action( 'bmlm_dashboard-statistics', array( $this, 'bmlm_dashboard_statistics' ) );
			add_action( 'bmlm_dashboard-badge-history', array( $this, 'bmlm_dashboard_badge_history' ) );
			add_action( 'bmlm_dashboard-member-ingredient', array( $this, 'bmlm_dashboard_member_ingredient' ) );
			add_action( 'bmlm_dashboard-sponsor-ingredient', array( $this, 'bmlm_dashboard_sponsor_ingredient' ) );
		}

		/**
		 * Dashboard template controller
		 *
		 * @return void
		 */
		public function get_configuration() {
			$bmlm_dashboard_items = apply_filters(
				'wooml_dashboard_items',
				array(
					'dashboard-statistics',
					'dashboard-graph',
					'dashboard-sponsor-ingredient',
					'dashboard-badge-history',
					'dashboard-member-ingredient',
				)
			);
			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'bmlm_wc_account_menu' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="wrap bmlm-wrapper">
						<div class="bmlm-dashboard-wrapper">
							<?php
							foreach ( $bmlm_dashboard_items as $item ) {
								do_action( 'bmlm_' . esc_attr( $item ), $this );
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Dashboard statistics template controller
		 *
		 * @return void
		 */
		public function bmlm_dashboard_statistics() {
			$stats = new Dashboard\Statistics\BMLM_Statistics( $this );
			$stats->get_template();
		}

		/**
		 * Dashboard graph template controller
		 *
		 * @return void
		 */
		public function bmlm_dashboard_graph_earning() {
			$earning = new Dashboard\Graph\BMLM_Graph( $this );
			$earning->get_template();
		}

		/**
		 * Dashboard member badge history template controller
		 *
		 * @return void
		 */
		public function bmlm_dashboard_badge_history() {
			$badge = new Dashboard\Badge\BMLM_Badge_History( $this );
			$badge->get_template();
		}

		


		/**
		 * Dashboard member ingredient template controller
		 *
		 * @return void
		 */
		public function bmlm_dashboard_member_ingredient() {
			$member = new Dashboard\Member\BMLM_Member_Ingredients( $this );
			$member->get_template();
		}

		/**
		 * Dashboard sponsor ingredient template controller
		 *
		 * @return void
		 */
		public function bmlm_dashboard_sponsor_ingredient() {
			$object = new Dashboard\Sponsor\BMLM_Sponsor_Ingredients( $this );
			$object->get_template();
		}
	}
}
