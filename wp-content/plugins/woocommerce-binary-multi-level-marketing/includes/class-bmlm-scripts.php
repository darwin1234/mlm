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
			add_action( 'wp_ajax_get_data', array($this, 'get_children_ajax_handler') );
			add_action( 'wp_ajax_nopriv_get_data', array($this, 'get_children_ajax_handler') );
			add_action( 'wp_enqueue_scripts', array($this,'ajax_dscript'  ));
		}

		function ajax_dscript() {
			// Enqueue the ds-script.js file
			wp_enqueue_script( 'ajax-script', plugin_dir_url( __FILE__ ) . 'assets/js/ds-script.js', array('jquery'), null, true );
		
			// Localize the script with AJAX URL
			wp_localize_script( 'ajax-script', 'ds_bmlm', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			));
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
				$sponsors     = $this->getChildren($sponsors,false);
				wp_enqueue_script('ds-scripts', BMLM_PLUGIN_URL . 'assets/js/ds-scripts.js', array(), BMLM_SCRIPT_VERSION, true);
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

		//Darwin Modification
		public function getChildren($sponsor,$ajax)
		{
			// Make a copy of the sponsor array
			$sponsorParent = $sponsor;
			// Check if the sponsor has children
			if (isset($sponsor['downline_member']) > 0 && $sponsor['ID'] > 0 ) 
			{
				$newChildren = [];
				
				if(array_key_exists('children', $sponsor))
				{
					
					foreach ($sponsor['children'] as $child) 
					{
						// If the child has downline members, update its data using your helper functions
						if ($child['downline_member'] > 0) {
							$jsonData    = $this->bmlm_get_sponsor_childrens($child['ID']);
							$sponsorData = $this->bmlm_get_sposnors_miscellaneous($jsonData);
							$formattedChild = $this->bmlm_format_sponsor_data($sponsorData);
							
							// Recursively process this formatted child's children too
							$formattedChild = $this->getChildren($formattedChild,false);
							$newChildren[] = $formattedChild;
						} else {
							// Append the child as is (it has already been processed for any nested children)
							$newChildren[] = $child;
						}
					}
					
				}

				if($ajax){
					$sponsorParent['children'] = $this->getChildrenData($sponsor,true);
				}else {
					$sponsorParent['children'] = $newChildren;
				}

				
				// Update the parent's children array
				
			}
			return $sponsorParent;
		}	


		public function get_children_ajax_handler(){

			$sponsor_id =  (int)$_POST['ds_sponsor_id'];
			///echo $sponsor_id;
			if($sponsor_id > 0) {
				$json_data    = $this->bmlm_get_sponsor_childrens($sponsor_id );
				$sponsor_data = $this->bmlm_get_sposnors_miscellaneous( $json_data );
				$sponsors     = $this->bmlm_format_sponsor_data( $sponsor_data );
				if($sponsors['downline_member'] > 0  && !array_key_exists('children', $sponsors))
				{
					$sponsors  = $this->getChildren($sponsors,true);
				}
				
				echo json_encode($sponsors);
			}
		
			wp_die(); 
		}


		public function getChildrenData($sponsor)
		{
			global $wpdb;
			$sponsors_data = [];
		
			// Ensure $sponsor['ID'] is properly sanitized
			$query = $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}bmlm_gtree_nodes WHERE parent = %d AND child > 0",
				$sponsor['ID']
			);
			$children = $wpdb->get_results($query);
		
			foreach ($children as $child) {
				// Ensure valid data is returned from these functions
				$json_data = $this->bmlm_get_sponsor_childrens($child->child);
				if ($json_data) {
					$sponsor_data = $this->bmlm_get_sposnors_miscellaneous($json_data);
					if ($sponsor_data) {
						$sponsors = $this->bmlm_format_sponsor_data($sponsor_data);
						// Ensure getChildren function works as expected
						$sponsors_data[] = $this->getChildren($sponsors, true);
					}
				}
			}
		
			return $sponsors_data;
		}
		
	
	}

	
}

