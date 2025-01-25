<?php
/**
 * Dashboard Graph Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Dashboard\Graph;

use WCBMLMARKETING\Includes\Report\BMLM_Report;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Graph' ) ) {
	/**
	 * BMLM Dashboard Graph
	 */
	class BMLM_Graph extends BMLM_Report {
		/**
		 * Sponsor class object.
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Construct
		 *
		 * @param object $sponsor sponsor.
		 */
		public function __construct( $sponsor ) {
			parent::__construct();
			$this->sponsor = $sponsor;
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$start_date = gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d' ) ) ) . ' - 1 month' ) ) . '';
			$end_date   = gmdate( 'Y-m-d' );

			$get_data       = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$start_date     = isset( $get_data['start_date'] ) ? wc_clean( $get_data['start_date'] ) : $start_date; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$end_date       = isset( $get_data['end_date'] ) ? wc_clean( $get_data['end_date'] ) : $end_date; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$type           = isset( $get_data['type'] ) ? wc_clean( $get_data['type'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$sponsor        = $this->sponsor->bmlm_get_sponsor();
			$args           = array(
				'user_id'    => $sponsor->ID,
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'type'       => $type,
			);
			$sale_report    = $this->bmlm_get_report( $args );
			$args['type']   = 'joining';
			$joining_report = $this->bmlm_get_report( $args );
			$args['type']   = 'levelup';
			$levelup_report = $this->bmlm_get_report( $args );
			$args['type']   = 'bonus';
			$bonus_report   = $this->bmlm_get_report( $args );
			$report         = array(
				'sale'    => $sale_report,
				'joining' => $joining_report,
				'levelup' => $levelup_report,
				'bonus'   => $bonus_report,
			);
			?>
			<script>
				var chart = '<?php echo wp_json_encode( $report ); ?>';
			</script>
					<div class="bmlm-sponsor-search" id="bmlm-sponsor-search">
						<div class="bmlm-graph-card-search">
							<form method="GET" action="<?php echo esc_url( home_url() . '/sponsor/dashboard#bmlm-sponsor-search' ); ?>">
								<p>
									<b><?php esc_html_e( 'Filter By Date:', 'binary-mlm' ); ?></b>
									<span><input type="date" class="bmlm-date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>"></span>
									<b><?php esc_html_e( 'To', 'binary-mlm' ); ?></b>
									<span><input type="date" class="bmlm-date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>"></span>

									<span>
										<select name="type" class="bmlm_search_drop">
											<option value="weekly" <?php selected( 'weekly' === $type ); ?>><?php esc_html_e( 'Weekly', 'binary-mlm' ); ?></option>
											<option value="monthly" <?php selected( 'monthly' === $type ); ?>><?php esc_html_e( 'Monthly', 'binary-mlm' ); ?></option>
											<option value="yearly" <?php selected( 'yearly' === $type ); ?>><?php esc_html_e( 'Yearly', 'binary-mlm' ); ?></option>
										</select>
									</span>
									<span><input type="submit" value="Search"></span>

									<a href="<?php echo esc_url( home_url() . '/sponsor/dashboard' ); ?>" class="button bmlm_search_reset"><?php esc_html_e( 'Reset', 'binary-mlm' ); ?></a>
								</p>
							</form>
						</div>
					</div>
			<div class="bmlm-sponsor-histograms">
				<div class="bmlm-mixed-histogram">
					<div class="bmlm-graph-card">
						<div class="bmlm-graph-card-header">
							<h3><?php esc_html_e( 'Commission Comparision', 'binary-mlm' ); ?></h3>
						</div>
						<div class="bmlm-graph-card-body">
							<canvas id="grosshistogram"></canvas>
						</div>
					</div>
				</div>
				<div class="bmlm-historgram">
					<div class="bmlm-sales-histogram">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3><?php esc_html_e( 'Sale Commission', 'binary-mlm' ); ?></h3>
							</div>
							<div class="bmlm-graph-card-body">
								<canvas id="bmlm-sales-graph"></canvas>
							</div>
						</div>
					</div>
					<div class="bmlm-joining-histogram">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3><?php esc_html_e( 'Joining Commission', 'binary-mlm' ); ?></h3>
							</div>
							<div class="bmlm-graph-card-body">
								<canvas id="bmlm-joining-graph"></canvas>
							</div>
						</div>
					</div>
					<div class="bmlm-levelup-histogram">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3><?php esc_html_e( 'Level Up Commission', 'binary-mlm' ); ?></h3>
							</div>
							<div class="bmlm-graph-card-body">
								<canvas id="bmlm-levelup-graph"></canvas>
							</div>
						</div>
					</div>
					<div class="bmlm-bonus-histogram">
						<div class="bmlm-graph-card">
							<div class="bmlm-graph-card-header">
								<h3><?php esc_html_e( 'Bonus Commission', 'binary-mlm' ); ?></h3>
							</div>
							<div class="bmlm-graph-card-body">
								<canvas id="bmlm-bonus-graph"></canvas>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
