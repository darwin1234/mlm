<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://www.ibsofts.com
 * @since      2.0.4
 *
 * @package    GHLCONNECTPRO
 * @subpackage GHLCONNECTPRO/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.4
 * @package    GHLCONNECTPRO
 * @subpackage GHLCONNECTPRO/includes
 * @author     iB Softs <ibsofts@gmail.com>
 */
class GHLCONNECTPRO {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.4
	 * @access   protected
	 * @var      GHLCONNECTPRO_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.4
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.4
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.0.4
	 */
	public function __construct() {
		if ( defined( 'GHLCONNECTPRO_VERSION' ) ) {
			$this->version = GHLCONNECTPRO_VERSION;
		} else {
			$this->version = '2.0.4';
		}
		$this->plugin_name = 'ghl-connect-pro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - GHLCONNECTPRO_Loader. Orchestrates the hooks of the plugin.
	 * - GHLCONNECTPRO_i18n. Defines internationalization functionality.
	 * - GHLCONNECTPRO_Admin. Defines all hooks for the admin area.
	 * - GHLCONNECTPRO_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.4
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ghl-connect-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ghl-connect-pro-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ghl-connect-pro-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ghl-connect-pro-public.php';

		//Include Required Files for plugins.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/settings-page-pro.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/woo-product-page-settings-pro.php';


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'ghlpro_api/ghlpro-all-apis.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/woo-ghl-connect-pro.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ghl-connect-pro-updater.php';
		
		$this->loader = new GHLCONNECTPRO_Loader();


	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the GHLCONNECTPRO_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.4
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new GHLCONNECTPRO_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.4
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new GHLCONNECTPRO_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$updater = new GHLConnectPro_Updater();


		$this->loader->add_filter('site_transient_update_plugins', $updater, 'ghlconnectpro_update');
		$this->loader->add_action('in_plugin_update_message-' . GHLCONNECTPRO_PATH, $updater, 'ghlconnectpro_update_message', 10, 2);
		$this->loader->add_action('wp_ajax_ghl_check_sync_data', $plugin_admin, 'ghl_check_sync_data');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.4
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new GHLCONNECTPRO_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.4
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.4
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.4
	 * @return    GHLCONNECTPRO_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.4
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}