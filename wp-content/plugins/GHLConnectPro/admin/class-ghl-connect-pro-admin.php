<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://www.ibsofts.com
 * @since      2.0.4
 *
 * @package    GHLCONNECTPRO
 * @subpackage GHLCONNECTPRO/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    GHLCONNECTPRO
 * @subpackage GHLCONNECTPRO/admin
 * @author     iB Softs <ibsofts@gmail.com>
 */
class GHLCONNECTPRO_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.4
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.4
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.4
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.4
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ghl_Connect_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ghl_Connect_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ghl-connect-pro-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2_css', plugins_url( 'css/select2.min.css', __FILE__ ), '', '1.0' );
		wp_enqueue_style( 'ghlconnectpro_admin_style', plugins_url( 'css/admin-styles.css', __FILE__ ), '', '1.0' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.4
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ghl_Connect_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ghl_Connect_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ghl-connect-pro-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script('select2', plugins_url('js/select2.min.js', __FILE__), array('jquery'), '2.0.4', true);
		wp_enqueue_script('ghlconnectpro_admin_script', plugins_url('js/admin-scripts.js', __FILE__), array('jquery'), '2.0.4', true);
		wp_localize_script($this->plugin_name, 'ghl_woo_sync', array('ajaxurl' => admin_url('admin-ajax.php')));
	}
	public function ghl_check_sync_data()
	{
		$sync_complete = get_option('sync_complete');
		if (!empty($sync_complete)) {
			wp_send_json_success(array("success" => true, 'sync_status' => $sync_complete));
		}
		wp_send_json_success(array("success" => false));
	}
}