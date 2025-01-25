<?php
/**
 * This file handles the Webkul's WC related addons menu registrations.
 *
 * @version 1.0.0
 *
 * @package WKWC Addons
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'WKWC_Addons' ) ) {
	/**
	 * WKWC_Addons class.
	 */
	class WKWC_Addons {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Autoload constructor.
		 */
		public function __construct() {
			defined( 'WKWC_ADDONS_VERSION' ) || define( 'WKWC_ADDONS_VERSION', '1.1.7' );
			defined( 'WKWC_ADDONS_DIR_URL' ) || define( 'WKWC_ADDONS_DIR_URL', plugin_dir_url( __FILE__ ) );
			add_action( 'init', array( $this, 'wkwc_addons_load_textdomain' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wkwc_addons_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( $this, 'wkwc_add_addon_menu' ) );
			add_action( 'activated_plugin', array( $this, 'wkwc_redirect_to_addons_page_on_activation' ), 9 );
			add_action( 'admin_menu', array( $this, 'wkwc_add_addon_submenu' ), 999 );
			add_filter( 'extra_plugin_headers', array( $this, 'wkwc_addons_extra_headers' ) );
			add_action( 'wp_footer', array( $this, 'wkwc_addons_front_footer_info' ) );
		}

		/**
		 * Adding extra plugin headers.
		 *
		 * @param array $headers Plugin headers.
		 *
		 * @hooked 'extra_plugin_headers' filter hook.
		 *
		 * @return array
		 */
		public function wkwc_addons_extra_headers( $headers ) {
			array_push( $headers, 'WKWC_Addons' );

			return $headers;
		}

		/**
		 * Redirect to addons page.
		 *
		 * @hooked 'activated_plugin' action hook.
		 *
		 * @return void
		 */
		public function wkwc_redirect_to_addons_page_on_activation() {
			$redirect_to = filter_input( INPUT_GET, 'redirect_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( 'wkwc-addons' === $redirect_to ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wkwc-addons', 'admin' ) );
				exit();
			}
		}

		/**
		 * Loading WKWC Addons text domain.
		 *
		 * @return void
		 */
		public function wkwc_addons_load_textdomain() {
			load_plugin_textdomain( 'wkwc_addons', false, plugin_basename( __DIR__ ) . '/languages' );
		}

		/**
		 * Loading WKWC Addons Extenstions and Support & Serverics js.
		 *
		 * @return void
		 */
		public function wkwc_addons_admin_enqueue_scripts() {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			wp_enqueue_style( 'wkwc-addons-tabs-style', WKWC_ADDONS_DIR_URL . 'assets/css/wkwc-addons-tabs.css', array(), WKWC_ADDONS_VERSION, 'all' );

			if ( 'wkwc-addons' === $page_name ) {
				wp_enqueue_style( 'wkwc-addons-style', WKWC_ADDONS_DIR_URL . 'assets/css/wkwc-addons.css', array(), WKWC_ADDONS_VERSION, 'all' );
			}
			if ( 'wkwc-addons-support-services' === $page_name ) {
				wp_enqueue_script( 'wkwc-addons-support-services', 'https://webkul.com/common/modules/wksas.bundle.js', array(), WKWC_ADDONS_VERSION, true );
			}
			if ( 'wkwc-addons-extensions' === $page_name ) {
				wp_enqueue_script( 'wkwc-addons-extensions', 'https://wpdemo.webkul.com/wk-extensions/client/wk.ext.js', array(), WKWC_ADDONS_VERSION, true );
				wp_enqueue_script( 'wkwc-addons-select', WKWC_ADDONS_DIR_URL . '/assets/js/wkwc-addons.js', array(), WKWC_ADDONS_VERSION, true );
			}
		}

		/**
		 * Adding a common 'Webkul Addons' menu.
		 *
		 * @return void
		 */
		public function wkwc_add_addon_menu() {
			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_options' );

			add_menu_page(
				esc_html__( 'Webkul WC Addons', 'wkwc_addons' ),
				esc_html__( 'Webkul WC Addons', 'wkwc_addons' ),
				$capability,
				'wkwc-addons',
				array( $this, 'wkwc_addons_output' ),
				'dashicons-editor-paste-word',
				54.978 // Just keeping arbitrary fractions number to avoid collision with other plugin like WCFM also sets at 55.
			);
		}

		/**
		 * Showing list of all WC Addons by Webkul installed.
		 *
		 * @return void
		 */
		public function wkwc_addons_output() {
			$all_plugins = get_plugins();

			$this->wkwc_show_addon_header();

			foreach ( $all_plugins as $plugin_file => $headers ) {

				if ( ! empty( $headers['WKWC_Addons'] ) ) {
					$plugin_data = get_file_data(
						WP_PLUGIN_DIR . '/' . $plugin_file,
						array(
							'name'          => 'Plugin Name',
							'description'   => 'Description',
							'settings_page' => 'WKWC_Settings',
							'icon_url'      => 'WKWC_Icon_URL',
							'blog_url'      => 'Blog URI',
						),
						'plugin'
					);

					$this->wkwc_show_addon_card( $plugin_file, $plugin_data );
				}
			}

			$this->wkwc_show_addon_footer();
		}

		/**
		 * Show addon header.
		 *
		 * @return void
		 */
		public function wkwc_show_addon_header() {
			?>
			<div class="wkwc-addons-wrap wrap">
				<h2 class="wp-heading-inline"><?php esc_html_e( 'Webkul Addons', 'wkwc_addons' ); ?></h1>
				<p class="wkwc_addon_desc"><?php esc_html_e( 'Showing the list of all installed Webkul WooCommerce Addons.', 'wkwc_addons' ); ?></p>
				<div class="wkwc-addons-list">
			<?php
		}

		/**
		 * Show Addon card
		 *
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin data.
		 *
		 * @return void
		 */
		public function wkwc_show_addon_card( $plugin_file, $plugin_data ) {
			$setting_page = empty( $plugin_data['settings_page'] ) ? '' : $plugin_data['settings_page'];

			$link         = empty( $setting_page ) ? '#' : admin_url( 'admin.php?page=' . $setting_page );
			$text         = esc_html__( 'Settings', 'wkwc_addons' );
			$text_title   = esc_html__( 'Open Settings', 'wkwc_addons' );
			$card_class   = '';
			$button_class = 'button-primary';

			if ( ! is_plugin_active( $plugin_file ) ) {
				$link         = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file );
				$link        .= '&redirect_to=wkwc-addons';
				$text         = esc_html__( 'Activate', 'wkwc_addons' );
				$text_title   = esc_html__( 'Click to activate and use.', 'wkwc_addons' );
				$card_class   = 'disable';
				$button_class = 'button-secondary';
			}

			$plugin_name = empty( $plugin_data['name'] ) ? '' : $plugin_data['name'];
			$icon_url    = empty( $plugin_data['icon_url'] ) ? 'https://webkul.com/wp-content/themes/webkul-2020/images/brand-kit/square-logo/primary-sq.png' : $plugin_data['icon_url'];
			$blog_url    = empty( $plugin_data['blog_url'] ) ? 'https://webkul.com/blog/?s=' . $plugin_name . '&cat=WordPress' : $plugin_data['blog_url'];
			$description = empty( $plugin_data['description'] ) ? '' : wp_trim_words( $plugin_data['description'], 35, '...' );
			?>
			<div class="wkwc-addon-container <?php echo esc_attr( $card_class ); ?>">
				<div class="wkwc-addon-sub-cont">
					<img src="<?php echo esc_url( $icon_url ); ?>" alt="" class="wkwc-addon-img">
					<p class="wkwc-addon-cont-heading"><?php echo esc_html( $plugin_name ); ?></p>
				</div>
				<p class="wkwc-addon-cont-content"><?php echo esc_html( $description ); ?></p>
				<div class="wkwc-addon-btn-container">
					<a title="<?php echo esc_attr( $text_title ); ?>" href="<?php echo esc_url( $link ); ?>" class="wkwc-addons-cta button <?php echo esc_attr( $button_class ); ?>"><?php echo esc_html( $text ); ?></a>
					<a title="<?php esc_attr_e( 'Click to see the Blog', 'wkwc_addons' ); ?>" target="_blank" href="<?php echo esc_url( $blog_url ); ?>"><span class="wkwc-addon-info-btn dashicons dashicons-info-outline"></span></a>
				</div>
			</div>
			<?php
		}

		/**
		 * Show addon footer.
		 *
		 * @return void
		 */
		public function wkwc_show_addon_footer() {
			?>
			</div> <!-- wkwc-addons-row -->
			</div> <!-- wkwc-addons-wrap -->
			<?php
		}

		/**
		 * Display All settings tabs.
		 *
		 * @param array  $tabs Setting tabs.
		 * @param string $title Page title.
		 * @param string $icon Module icon.
		 */
		public function create_settings_tabs( $tabs = array(), $title = '', $icon = '' ) {
			$submenu_name = ( is_array( $tabs ) && count( $tabs ) > 0 ) ? array_keys( $tabs )[0] : '';
			$submenu_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( ! empty( $submenu_name ) && ! empty( $submenu_page ) && $submenu_name === $submenu_page ) {
				$tab         = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$current_tab = empty( $tab ) ? $submenu_name : $tab;
				if ( ! empty( $tab ) ) {
					$submenu_page .= '_' . $tab;
				}
				$title = empty( $title ) ? array_values( $tabs )[0] : $title;
				?>
			<div class="wkwc-addons-tabs-wrap">
				<nav class="nav-tab-wrapper wkwc-admin-addon-list-manage-nav">
					<div class="wkwc-addons-page-header">
						<div class="module-icon">
							<?php echo wp_kses_post( $icon ); ?>
						</div>
						<p class="page-title"><?php echo esc_html( $title ); ?></p>
					</div>
					<div class="wkwc-addons-nav-link">
				<?php
				foreach ( $tabs as $name => $label ) {
					$tab_url  = admin_url( 'admin.php?page=' . esc_attr( $submenu_name ) );
					$tab_url .= ( $name === $submenu_name ) ? '' : '&tab=' . $name;
					echo wp_sprintf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( $tab_url ), ( $current_tab === $name ? 'nav-tab-active' : '' ), esc_html( $label ) );
				}
				?>
					</div>
				</nav>
				<?php
				do_action( $submenu_page . '_content', $submenu_name );
				?>
			</div>
				<?php
			}
		}

		/**
		 * Adding a common 'Webkul Addons' menu.
		 *
		 * @return void
		 */
		public function wkwc_add_addon_submenu() {
			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_options' );

			add_submenu_page(
				'wkwc-addons',
				esc_html__( 'Extensions', 'wkwc_addons' ),
				esc_html__( 'Extensions', 'wkwc_addons' ),
				$capability,
				'wkwc-addons-extensions',
				array(
					$this,
					'wkwc_addons_extensions',
				)
			);

			add_submenu_page(
				'wkwc-addons',
				esc_html__( 'Support & Services', 'wkwc_addons' ) . ' | ' . esc_html__( 'Webkul', 'wkwc_addons' ),
				esc_html__( 'Support & Services', 'wkwc_addons' ),
				$capability,
				'wkwc-addons-support-services',
				array(
					$this,
					'wkwc_addons_support_services',
				)
			);
		}

		/**
		 * WKWC Addons extensions callback.
		 *
		 * @return void
		 */
		public function wkwc_addons_extensions() {
			?>
			<webkul-extensions></webkul-extensions>
			<?php
		}

		/**
		 * WKWC Addons support and services menu.
		 *
		 * @return void
		 */
		public function wkwc_addons_support_services() {
			?>
			<wk-area></wk-area>
			<?php
		}

		/**
		 * Declare plugin is compatible with HPOS.
		 *
		 * @param string $file Plugin main file path.
		 */
		public static function wkwc_addon_declare_hpos_compatible( $file = '' ) {
			add_action(
				'before_woocommerce_init',
				function () use ( $file ) {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $file, true );
					}
				}
			);
		}

		/**
		 * Check WooCommerce HPOS is enabled.
		 *
		 * @return bool
		 */
		public static function wkwc_is_hpos_enabled() {
			return ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && method_exists( '\Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() );
		}

		/**
		 * Get Order meta.
		 *
		 * @param object $order Order object.
		 * @param string $key Meta key.
		 * @param int    $order_id Order id.
		 *
		 * @return mixed $meta_data Meta data.
		 */
		public static function get_order_meta( $order = '', $key = '', $order_id = 0 ) {
			if ( empty( $key ) ) {
				return '';
			}
			if ( ! $order instanceof \WC_Abstract_Order && ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( ! $order instanceof \WC_Abstract_Order ) {
				return '';
			}

			$order_id = empty( $order_id ) ? $order->get_id() : $order_id;

			$meta_value = $order->get_meta( $key );

			if ( ! empty( $meta_value ) ) {
				return $meta_value;
			}

			if ( true === self::wkwc_is_hpos_enabled() ) {
				global $wpdb;
				$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT `meta_value` FROM `{$wpdb->prefix}wc_orders_meta` WHERE `meta_key`=%s AND `order_id`=%d", $key, $order_id ) );

				if ( ! empty( $meta_value ) ) {
					return $meta_value;
				}
			}

			return get_post_meta( $order_id, $key, true );
		}

		/**
		 * Get order edit URL.
		 *
		 * @param int $order_id Order id.
		 *
		 * @return string $order_url Order edit url.
		 */
		public static function get_order_edit_url( $order_id = 0 ) {
			$order_url = OrderUtil::get_order_admin_edit_url( $order_id );
			return esc_url( $order_url );
		}

		/**
		 * Show current plugin version and last working date and time on front end.
		 *
		 * @hooked wp_footer Action hook.
		 *
		 * @return void
		 */
		public static function wkwc_addons_front_footer_info() {
			$show_info = filter_input( INPUT_GET, 'wkmodule_info', FILTER_SANITIZE_NUMBER_INT );
			$show_info = empty( $show_info ) ? 0 : intval( $show_info );
			if ( 200 === $show_info ) {
				?>
			<input type="hidden" data-lwd="202401111345" data-wkwc_addon_version="<?php echo esc_attr( WKWC_ADDONS_VERSION ); ?>">
				<?php
			}
		}

		/**
		 * Wrapper for admin notice.
		 *
		 * @param  string $message The notice message.
		 * @param  string $type Notice type like info, error, success.
		 * @param  array  $args Additional arguments for wp-6.4.
		 *
		 * @return void
		 */
		public static function wk_show_notice_on_admin( $message = '', $type = 'error', $args = array() ) {
			if ( ! empty( $message ) ) {

				if ( function_exists( 'wp_admin_notice' ) ) {
					$args         = is_array( $args ) ? $args : array();
					$args['type'] = empty( $args['type'] ) ? $type : $args['type'];

					wp_admin_notice( $message, $args );
				} else {
					?>
				<div class="<?php echo esc_attr( $type ); ?>"><p><?php echo wp_kses_post( $message ); ?></p></div>
					<?php
				}
			}
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
	WKWC_Addons::get_instance();
}
