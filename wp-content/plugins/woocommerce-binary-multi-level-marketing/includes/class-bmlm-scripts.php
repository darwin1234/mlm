<?php
/**
 * Scripts load.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Scripts' ) ) {
	/**
	 * Scripts class.
	 */
	class BMLM_Scripts extends BMLM_Sponsor {
		/**
		 * Scripts load
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'wp_enqueue_scripts', array( $this, 'bmlm_public_scripts' ) );
			add_action( 'wp_footer', array( $this, 'bmlm_front_footer_info' ) );
		}

		/**
		 * Public end scripts.
		 */
		public function bmlm_public_scripts() {

			global $wp_query;
			$query_vars = $wp_query->query_vars;
			$main_page  = ! empty( $query_vars ) && ! empty( $query_vars['main_page'] ) ? $query_vars['main_page'] : '';
			$page_name  = ! empty( $query_vars ) && ! empty( $query_vars['pagename'] ) ? $query_vars['pagename'] : '';
			if ( 'sponsor' === $page_name && ( '' === $main_page || 'dashboard' === $main_page ) ) {
				wp_enqueue_script( 'bmlm-moment', BMLM_PLUGIN_URL . 'assets/js/moment.min.js', array(), BMLM_SCRIPT_VERSION, true );
				wp_enqueue_script( 'bmlm-chart', BMLM_PLUGIN_URL . 'assets/js/chart.min.js', array(), BMLM_SCRIPT_VERSION, true );
				wp_enqueue_script( 'bmlm-graph', BMLM_PLUGIN_URL . 'assets/js/graph.js', array( 'jquery', 'bmlm-moment', 'bmlm-chart' ), BMLM_SCRIPT_VERSION, true );
				wp_enqueue_style( 'bmlm-graph', BMLM_PLUGIN_URL . 'assets/css/graph.css', array(), BMLM_SCRIPT_VERSION, false );
			} elseif ( 'sponsor' === $page_name && 'genealogy' === $main_page ) {
				$json_data    = $this->bmlm_get_sponsor_childrens( get_current_user_id() );
				$sponsor_data = $this->bmlm_get_sposnors_miscellaneous( $json_data );
				$sponsors     = $this->bmlm_format_sponsor_data( $sponsor_data );
				wp_enqueue_script( 'bmlm-d3', BMLM_PLUGIN_URL . 'assets/js/d3.min.js', array(), BMLM_SCRIPT_VERSION, true );
				wp_enqueue_script( 'bmlm-gtree', BMLM_PLUGIN_URL . 'assets/js/gtree.js', array( 'bmlm-d3' ), BMLM_SCRIPT_VERSION, true );
				wp_localize_script(
					'bmlm-gtree',
					'bmlm_gtree',
					array(
						'gtree'    => wp_json_encode( $sponsors ),
						'is_admin' => is_admin(),
					)
				);
				wp_enqueue_style( 'bmlm-gtree', BMLM_PLUGIN_URL . 'assets/css/gtree.css', array(), BMLM_SCRIPT_VERSION, false );
			}
			wp_enqueue_style( 'bmlm-style', BMLM_PLUGIN_URL . 'assets/css/style.css', array(), BMLM_SCRIPT_VERSION, false );
			wp_enqueue_script( 'bmlm-plugin', BMLM_PLUGIN_URL . 'assets/js/plugin.js', array( 'wp-util' ), BMLM_SCRIPT_VERSION, true );
		}

		/**
		 * Front footer info.
		 *
		 * @return void
		 */
		public function bmlm_front_footer_info() {
			$show_info = filter_input( INPUT_GET, 'wkmodule_info', FILTER_SANITIZE_NUMBER_INT );
			$show_info = empty( $show_info ) ? 0 : intval( $show_info );
			if ( 200 === $show_info ) {
				?>
			<input type="hidden" data-lwdt="202401161150" data-bmlm_version="<?php echo esc_attr( BMLM_PLUGIN_VERSION ); ?>">
				<?php
			}
		}
	}
}
