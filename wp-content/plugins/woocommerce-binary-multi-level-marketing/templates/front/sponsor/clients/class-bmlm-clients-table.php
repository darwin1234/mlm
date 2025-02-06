<?php
/**
 * Dashboard Sponsor Commission Data.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Clients;

use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Clients_Table' ) ) {
	/**
	 * Sponsor Commission Data.
	 */
	class BMLM_Clients_Table {
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
			global $wpdb;

			$sponsor    = $this->sponsor->bmlm_get_sponsor();
			$sponsor_id = get_user_meta( $sponsor->ID, 'bmlm_sponsor_id', true );
			$sponsor_id = ! empty( $sponsor_id ) ? $sponsor_id : 'N/A';
			$terms_link = get_privacy_policy_url();
			$parent_id = get_current_user_id();
			
			$clients = $wpdb->get_results("SELECT * FROM "  . $wpdb->prefix .  "ds_clients WHERE  parent=" .$parent_id);
	
			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'bmlm_wc_account_menu' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="container">
						<div class="row">
							<h3>Client Lists</h3>
							<div class="col-md-12">
								<table class="table">
								<thead>
									<tr>
									<th scope="col">Order ID</th>
									<th scope="col">Product Name</th>
									<th scope="col">First Name</th>
									<th scope="col">Last Name</th>
									<th scope="col">Order Total</th>
									</tr>
								</thead>
								<tbody>
									<?php
									 foreach($clients as $client) {
										$order = wc_get_order($client->order_id);	
									?>
									<tr>
										<th scope="row"><?php echo $order->get_id();?></th>
										<th>
									 	<?php 
											 foreach ($order->get_items() as $item) {
												echo $item->get_name(); // Get product name
												
											}
										?>
										</th>
										<td><?php echo  $order->get_billing_first_name() ;?></td>
										<td><?php echo  $order->get_billing_last_name() ;?></td>
										<td><?php echo  $order->get_currency() . $order->get_total() ;?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
