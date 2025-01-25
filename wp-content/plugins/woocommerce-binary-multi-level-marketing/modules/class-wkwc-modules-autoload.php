<?php
/**
 * Load all latest submodules.
 *
 * @package WKWC_MODULES
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WKWC_Modules_Autoload' ) ) {
	/**
	 * WKWC_Modules_Autoload class
	 */
	class WKWC_Modules_Autoload {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Webkul plugins data with submodules.
		 *
		 * @var $webkul_plugins.
		 */
		protected $webkul_plugins = array();

		/**
		 * Plugin headers data for get from each plugin.
		 *
		 * @var $plugin_headers.
		 */
		protected $plugin_headers = array( 'author' => 'Author' );

		/**
		 * Modules list.
		 *
		 * @var $modules.
		 */
		protected $modules = array();

		/**
		 * Autoload constructor.
		 */
		public function __construct() {

			$this->plugin_headers = apply_filters( 'wkwc_modify_plugin_headers', $this->plugin_headers );

			$this->modules = array(
				'wkwc_addons' => array(
					'version' => 'WKWC_Addons',
					'file'    => 'wkwc-addons/class-wkwc-addons.php',
				),
			);

			$this->modules = apply_filters( 'wkwc_modify_modules_list', $this->modules );
			$this->prepare_plugins_headers();
			$this->prepare_modules_list_data();
			$this->load_modules();
		}

		/**
		 * Load all submodules.
		 */
		public function load_modules() {
			$autoload_files = array();

			if ( ! empty( $this->webkul_plugins ) ) {
				foreach ( $this->webkul_plugins as $key => $module ) {
					$latest_module    = $this->get_the_latest( $module );
					$autoload_files[] = $latest_module['plugin_path'] . '/modules/' . $this->modules[ $key ]['file'];
				}
			}

			if ( ! empty( $autoload_files ) ) {
				foreach ( $autoload_files as  $file ) {
					if ( file_exists( $file ) ) {
						include_once $file;
					}
				}
			}
		}

		/**
		 * Prepare headers data.
		 */
		public function prepare_plugins_headers() {
			if ( ! empty( $this->modules ) ) {
				foreach ( $this->modules as $key => $module ) {
					if ( ! empty( $module['version'] ) ) {
						$this->plugin_headers[ $key ] = $module['version'];
					}
				}
			}
		}

		/**
		 * Prepare the all submodules list.
		 */
		public function prepare_modules_list_data() {
			$active_plugins = get_option( 'active_plugins' );

			if ( ! empty( $active_plugins ) && ! empty( $this->modules ) ) {
				foreach ( $active_plugins as $plugin_file ) {
					$plugin_data = get_file_data( WP_PLUGIN_DIR . '/' . $plugin_file, $this->plugin_headers, 'plugin' );

					if ( 'webkul' === strtolower( $plugin_data['author'] ) ) {
						$plugin_directory = WP_PLUGIN_DIR . '/' . explode( '/', $plugin_file )[0];

						foreach ( $this->modules as $key => $module ) {
							if ( ! empty( $plugin_data[ $key ] ) ) {
								$this->webkul_plugins[ $key ][] = array(
									'plugin_path' => $plugin_directory,
									'version'     => floatval( $plugin_data[ $key ] ),
								);
							}
						}
					}
				}
			}
		}

		/**
		 * Get the latest version.
		 *
		 * @param array $module_list All submodules list.
		 *
		 * @return array $latest_module Latest module.
		 */
		public function get_the_latest( $module_list = array() ) {
			uasort(
				$module_list,
				function ( $a, $b ) {
					if ( version_compare( $a['version'], $b['version'], '=' ) ) {
						return 0;
					} else {
						return ( version_compare( $a['version'], $b['version'], '<' ) ) ? - 1 : 1;
					}
				}
			);
			$latest_module = end( $module_list );

			return $latest_module;
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}

			return static::$instance;
		}
	}

	WKWC_Modules_Autoload::get_instance();
}
