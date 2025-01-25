<?php
/**
 * Dashboard Sponsor Commission Data.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Commission;

use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Commission_Table' ) ) {
	/**
	 * Sponsor Commission Data.
	 */
	class BMLM_Commission_Table {
		/**
		 * Sponsor class object
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Sponsor commission.
		 *
		 * @var object
		 */
		protected $commission;

		/**
		 * Constructor
		 *
		 * @param object $sponsor Sponsor class object.
		 */
		public function __construct( $sponsor ) {
			$this->sponsor    = $sponsor;
			$this->commission = BMLM_Commission_Helper::get_instance();
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$sponsor      = $this->sponsor->bmlm_get_sponsor();
			$sponsor_code = $this->sponsor->bmlm_get_sponsor_code( $sponsor->ID );
			$sponsor_code = empty( $sponsor_code ) ? 'N/A' : $sponsor_code;

			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'bmlm_wc_account_menu' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="bmlm-commissions-wrapper">
						<div class="bmlm-table-header">
							<h3><?php esc_html_e( 'Commissions', 'binary-mlm' ); ?></h3>
						</div>
						<div class="bmlm-table-body">
							<?php
							$paged   = ! empty( get_query_var( 'leaf' ) ) ? intval( get_query_var( 'leaf' ) ) : 1;
							$pagenum = 0 === $paged || $paged <= 0 ? 1 : $paged;
							$limit   = 10;
							$offset  = ( 1 === $pagenum ) ? 0 : ( $pagenum - 1 ) * $limit;

							$args = array(
								'start'   => $offset,
								'limit'   => $limit,
								'orderby' => 'id',
								'order'   => 'DESC',
								'user_id' => get_current_user_id(),
							);

							$customer_commissions = $this->commission->bmlm_get_all_commission( $args );
							$count                = $this->commission->bmlm_get_all_commission_count( $args );
							$count                = intval( $count );
							if ( $count > 0 ) :

								?>
								<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'ID', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Amount', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Type', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Description', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Status', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Date', 'binary-mlm' ); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php
									foreach ( $customer_commissions as $commission ) :
										?>
											<tr>
												<td><?php echo esc_html( $commission['id'] ); ?></td>
												<td><?php echo wp_kses_post( wc_price( $commission['commission'] ) ); ?></td>
												<td><?php echo esc_html( ucfirst( $commission['type'] ) ); ?></td>
												<td><?php echo esc_html( $commission['description'] ); ?></td>
												<td><?php echo $commission['paid'] ? esc_html__( 'Paid', 'binary-mlm' ) : esc_html__( 'Unpaid', 'binary-mlm' ); ?></td>
												<td><?php echo esc_html( gmdate( 'M d, Y', strtotime( $commission['date'] ) ) ); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php else : ?>
								<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
									<a class="woocommerce-button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
										<?php esc_html_e( 'Go shop', 'binary-mlm' ); ?>
									</a>
									<?php esc_html_e( 'No commissions has been made yet.', 'binary-mlm' ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<?php
					if ( 1 < $count ) :
						?>
						<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination wallet-pagination" style="margin-top:10px;">

							<?php if ( 1 !== $paged && $paged > 1 ) : ?>
								<a class="woocommerce-button woocommerce-button--previous woocommerce-button woocommerce-button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'wallet/page', $paged - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'binary-mlm' ); ?></a>
							<?php endif; ?>
								<?php if ( ceil( $count / 10 ) > $paged ) : ?>
								<a class="woocommerce-button woocommerce-button--next woocommerce-button woocommerce-button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'wallet/page', $paged + 1 ) ); ?>"><?php esc_html_e( 'Next', 'binary-mlm' ); ?></a>
							<?php endif; ?>

						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}
}
