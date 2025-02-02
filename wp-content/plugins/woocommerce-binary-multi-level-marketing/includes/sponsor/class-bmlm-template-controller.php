<?php
/**
 * Sponsor Template Controller Class.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Sponsor;

use WCBMLMARKETING\Templates\Front;
use WCBMLMARKETING\Includes\Sponsor\Dashboard;
use WCBMLMARKETING\Templates\Front\Signup\BMLM_Signup_Fields;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Template_Controller' ) ) {

	/**
	 * Sponsor template functions class
	 */
	class BMLM_Template_Controller {

		/**
		 * Dashboard object
		 *
		 * @var object
		 */
		protected $dashboard;

		/**
		 * Sponsor class object
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Constructor of the class
		 *
		 * @param object $sponsor Sponsor class object.
		 */
		public function __construct( $sponsor ) {
			$this->sponsor   = $sponsor;
			$this->dashboard = new Dashboard\BMLM_Dashboard();
			add_action( 'bmlm_wc_account_menu', array( $this, 'bmlm_sponsor_account_menu' ) );
		}

		/**
		 * Callback method for sponsor account menu
		 *
		 * @return void
		 */
		public function bmlm_sponsor_account_menu() {
			wc_print_notices();
			global $wp;
			$url = home_url( add_query_arg( array(), $wp->request ) );
			$path = parse_url($url, PHP_URL_PATH);
			$segments = explode('/', $path);
			?>
			
			<nav class="woocommerce-MyAccount-navigation">
				<a href="/" class="d-flex align-items-center text-dark text-decoration-none"><img id="logo" src="<?php echo bloginfo('template_url');?>/assets/images/logo.png"></a>
				<?php 
					$user_id = get_current_user_id();
					$account_type = get_user_meta($user_id, 'account_type', true);
				?>
				<?php if($account_type==="ds_dealer") { ?>
					<h1 id="ds_dashboard" class="text-center"><a href="<?php echo site_url();?>/sponsor/dashboard/">Dealer's Dashboard</a></h1>
				<?php } else {?>
					<h1 id="ds_dashboard" class="text-center"><a href="<?php echo site_url();?>/sponsor/become-a-dealer/">Member's Dashboard</a></h1>
				<?php } ?>
				<ul>
					<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
						<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?> <?php  
								if (array_key_exists(3, $segments)) {
									echo "../sponsor/" . $segments[3]   ===   $endpoint ? " is-active " : "";
								}?>">
							<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
								<span class="ds_<?php echo wc_get_account_menu_item_classes($label); ?>"></span><?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				
				</ul>
			</nav>
			<?php
		}

		/**
		 * Callback method for sponsor registration and login
		 *
		 * @return void
		 */
		public function bmlm_sponsor_registration() {
			wp_enqueue_script( 'wc-password-strength-meter' );
			echo do_shortcode( '[woocommerce_my_account]' );
		}

		/**
		 * Callback method for sponsor custom registration form fields
		 *
		 * @return void
		 */
		public function bmlm_sponsor_custom_registration_form() {
			$signup = new BMLM_Signup_Fields();
			$signup->get_template();
		}

		/**
		 * Callback method for sponsor dahsboard
		 *
		 * @return void
		 */
		public function bmlm_sponsor_dashboard() {
			$this->dashboard->bmlm_get_dashboard();
		}

		/**
		 * Callback method for sponsor ads
		 *
		 * @return void
		 */
		public function bmlm_sponsor_ads() {
			$action = get_query_var( 'action' );
			if ( empty( $action ) || ( ! empty( $action ) && 'page' === $action ) ) {
				$ads = new Front\Sponsor\Ads\BMLM_Sponsor_Ads( $this->sponsor );
				$ads->get_template();
			}
		}

		/**
		 * Callback method for sponsor genealogy tree
		 *
		 * @return void
		 */
		public function bmlm_sponsor_genealogy_tree() {
			$gtree = new Front\Sponsor\Genealogy\BMLM_Genealogy_Tree();
			$gtree->get_template();
		}

		/**
		 * Callback method for sponsor referral links
		 *
		 * @return void
		 */
		public function bmlm_sponsor_refferal_links() {
			$referral = new Front\Sponsor\Refferal\BMLM_Refferal( $this->sponsor );
			$referral->get_template();
		}

		/**
		 * Callback method for sponsor commissions
		 *
		 * @return void
		 */
		public function bmlm_sponsor_commissions() {
			$commission = new Front\Sponsor\Commission\BMLM_Commission_Table( $this->sponsor );
			$commission->get_template();
		}


		public function bmlm_sponsor_marketing_crm_link(){
			$marketing = new Front\Sponsor\Marketing\BMLM_Marketing( $this->sponsor );
			$marketing->get_template();
		}

		public function bmlm_sponsor_social_media_kit(){
			$marketing = new Front\Sponsor\Social\BMLM_Social( $this->sponsor );
			$marketing->get_template();
		}
		
		public function bmlm_sponsor_training_resources(){
			$marketing = new Front\Sponsor\training\BMLM_Training( $this->sponsor );
			$marketing->get_template();
		}

		public function bmlm_sponsor_client_refferal_links(){
			$referral = new Front\Sponsor\Refferal\BMLM_ClientRefferal( $this->sponsor );
			$referral->get_template();
		}

		public function bmlm_sponsor_client_invoice(){
			$referral = new Front\Sponsor\Invoice\BMLM_Invoice( $this->sponsor );
			$referral->get_template();
		}

		public function bmlm_sponsor_become_a_member(){
			$referral = new Front\Sponsor\Dealer\BMLM_BecomeADealer( $this->sponsor );
			$referral->get_template();
		}
			
		
		
	}
}
