<?php
/**
 * Dashboard Sponsor Membership.
 *
 * @since 1.0.0
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Dashboard;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * Sponsor membership.
 */
class BMLM_Sponsor_Membership {

	/**
	 * Sponsor object
	 *
	 * @var object Sponsor object.
	 */
	protected $sponsor;

	/**
	 * Membership product id
	 *
	 * @var int Membership id.
	 */
	protected $membership_id;

	/**
	 * Become sponsor construct.
	 */
	public function __construct() {
		$membership          = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
		$this->membership_id = $membership->ID;
		//wp_redirect(wc_get_checkout_url());
	}

	/**
	 * Template.
	 *
	 * @return void
	 */
	public function get_template() {
		$product = wc_get_product( $this->membership_id );
		?>
		<script>
			// Automatically submit the form when the page loads
			document.addEventListener('DOMContentLoaded', function () {
				document.getElementById('bmlm-sponsor-membership-form').submit();
			});
		</script>

		<?php
	}
}
