<?php
/**
 * Sponsor Template Controller Class.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Sponsor\Dashboard;

use WCBMLMARKETING\Templates\Front;
use WCBMLMARKETING\Helper\NetworkUsers\BMLM_Network_Users;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Dashboard' ) ) {

	/**
	 * Sponsor Dashboard functions class
	 */
	class BMLM_Dashboard {

		/**
		 * Sponsor id.
		 *
		 * @var int
		 */
		private $sponsor_id;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			$this->sponsor_id = get_current_user_id();
			BMLM_Network_Users::get_instance( $this->sponsor_id );
		}

		/**
		 * Callback method for sponsor dashboard
		 *
		 * @return void
		 */
		public function bmlm_get_dashboard() {
			$dashboard_template = new Front\Sponsor\Dashboard\BMLM_Dashboard( $this->sponsor_id );
			$dashboard_template->get_configuration();
		}
	}
}
