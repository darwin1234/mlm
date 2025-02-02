<?php
 if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'GHLCONNECTPRO_Settings_Page' ) ) {
	class GHLCONNECTPRO_Settings_Page {

        public function __construct() {
			add_action( 'admin_menu', array( $this, 'ghlconnectpro_create_menu_page' ) );
			add_action( 'admin_post_ghlconnectpro_admin_settings', array( $this, 'ghlconnectpro_save_settings' ) );
			add_filter( 'plugin_action_links_' . GHLCONNECTPRO_PLUGIN_BASENAME , array( $this , 'ghlconnectpro_add_settings_link' ) );
		
		}

        public function ghlconnectpro_create_menu_page() {
	    
			$page_title 	= __( 'GHL Connect for WooCommerce Pro', 'ghl-connect-pro' );
			$menu_title 	= __( 'GHL Connect for WooCommerce Pro', 'ghl-connect-pro' );
			$capability 	= 'manage_options';
			$menu_slug 		= 'ib-ghlconnectpro';
			$callback   	= array( $this, 'ghlconnectpro_page_content' );
			$icon_url   	= 'dashicons-admin-plugins';
			add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url );
			// add_submenu_page('ib-ghlconnectpro', 'Opportunity/Pipeline', 'Opportunity/Pipeline', 'manage_options', 'ib-ghlconnectpro-opp-pipe', array($this, 'ghlconnectpro_submenu_callback_opp_pipe'));
			
		}	
		// //for oppertunity and pipeline
		// public function ghlconnectpro_submenu_callback_opp_pipe() {
		// 	require_once plugin_dir_path( __FILE__ )."ghl-connect-pro-opp-pipe-display.php";
		// }
		//for oppertunity and pipeline
		// public function ghlconnectpro_submenu_callback_import_course() {
		// 	require_once plugin_dir_path( __FILE__ )."ghl-connect-pro-import-course.php";
		// }

        public function ghlconnectpro_page_content() {
            // check user capabilities to access the setting page.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$default_tab = null;
			$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : $default_tab;
			?>

<div class="wrap main-con">
    <div class="ghl-header">
        <!-- Logo -->
        <div class="logo">
            <img src="<?php echo esc_url(plugins_url('images/ghlconnectpro-logo.png', __DIR__)); ?>"
                alt="GHLCONNECTPRO-Logo" />

        </div>

        <h1>GHL Connect for WooCommerce Pro</h1>
    </div>
    <div class="ghl-container">

        <div class="ghl-content">
            <div class="ghl-tabs">
                <h2 class="nav-tab-wrapper-vertical">
                    <a href="?page=ib-ghlconnectpro&tab=license"
                        class="nav-tab <?php if($tab==='license'):?>nav-tab-active<?php endif; ?>">License</a>
                    <a href="?page=ib-ghlconnectpro"
                        class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Connect with GHL</a>
                    <a href="?page=ib-ghlconnectpro&tab=option"
                        class="nav-tab <?php if($tab==='option'):?>nav-tab-active<?php endif; ?>">Trigger Options</a>
                    <a href="?page=ib-ghlconnectpro&tab=sync"
                        class="nav-tab <?php if($tab==='sync'):?>nav-tab-active<?php endif; ?>">Sync Users</a>
                    <a href="?page=ib-ghlconnectpro&tab=gtag"
                        class="nav-tab <?php if($tab==='gtag'):?>nav-tab-active<?php endif; ?>">Global Tags</a>
                    <a href="?page=ib-ghlconnectpro&tab=invoice"
                        class="nav-tab <?php if($tab==='invoice'):?>nav-tab-active<?php endif; ?>">Invoice</a>
                    <a href="?page=ib-ghlconnectpro&tab=support"
                        class="nav-tab <?php if($tab==='support'):?>nav-tab-active<?php endif; ?>">Help</a>

                </h2>
            </div>


            <div class="tab-content">
                <?php switch($tab) :
								case 'option':
									require_once plugin_dir_path( __FILE__ )."/woo-trigger-form-pro.php";
									break;
								case 'sync':
									require_once plugin_dir_path( __FILE__ )."/ghl-connect-pro-sync-users.php";
									break;
								case 'gtag':
									require_once plugin_dir_path( __FILE__ )."/ghl-connect-pro-glob-tags.php";
									break;
								case 'invoice':
									require_once plugin_dir_path( __FILE__ )."/ghl-connect-pro-invoice.php";
									break;
								case 'support':
									require_once plugin_dir_path( __FILE__ )."/help-page-pro.php";
								break;
								case 'license':
									require_once plugin_dir_path( __FILE__ )."/ghl-connect-pro-licence.php";
								break;
								default:
									require_once plugin_dir_path( __FILE__ )."/settings-form-pro.php";  // HTML for general tab
									break;
							endswitch; ?>
            </div>
        </div>
    </div>
</div>

<?php	
	    		
		}
		public function ghlconnectpro_save_settings() {
			check_admin_referer( "ghl-connect-pro" );
	        $ghlconnectpro_order_status 	= sanitize_text_field( $_POST['ghlconnectpro_order_status'] );
	        $ghlconnectpro_order_status_downloadable 	= sanitize_text_field( $_POST['ghlconnectpro_order_status_downloadable'] );
	        
	        $referer = esc_url_raw(sanitize_text_field($_POST['_wp_http_referer']));

	       //save data from the trigger options for physical.
	        update_option( 'ghlconnectpro_order_status', $ghlconnectpro_order_status );
	        
	        //save data from the trigger options for downloadable.
	        update_option( 'ghlconnectpro_order_status_downloadable', $ghlconnectpro_order_status_downloadable );

			wp_redirect( $referer );
        	exit();
		}

		public function ghlconnectpro_add_settings_link( $links ) {
	        $newlink = sprintf( "<a href='%s'>%s</a>" , admin_url( 'admin.php?page=ib-ghlconnectpro' ) , __( 'Settings' , 'ghl-connect-pro' ) );
	        $links[] = $newlink;
	        return $links;
	    }

    }
    new GHLCONNECTPRO_Settings_Page();
}