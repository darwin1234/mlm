<?php
/**
 * This class is a main loader class for all core files.
 *
 * @package WKWC_Wallet
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKWC_Wallet' ) ) {
	/**
	 * WKWC_Wallet Class.
	 */
	class WKWC_Wallet {
		/**
		 * Constructor.
		 */
		public function __construct() {
		}

		/**
		 * Init function hooked on `admin_init`
		 * Set the required variables and register some important hooks
		 */
		public static function init() {
			self::define_constants();
			add_action( 'init', array( __CLASS__, 'localization' ) );
			add_action( 'plugins_loaded', array( __CLASS__, 'initialze' ) );
			add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'wkwc_wallet_add_payment_gateway' ), 11 );
		}

		/**
		 * Define constants.
		 */
		public static function define_constants() {
			defined( 'WKWC_WALLET_VERSION' ) || define( 'WKWC_WALLET_VERSION', '1.0.6' );
			defined( 'WKWC_WALLET_DB_VERSION' ) || define( 'WKWC_WALLET_DB_VERSION', '1.0.6' );
			defined( 'WKWC_WALLET_SCRIPT_VERSION' ) || define( 'WKWC_WALLET_SCRIPT_VERSION', '1.0.6' );
			defined( 'WKWC_WALLET_SUBMODULE_URL' ) || define( 'WKWC_WALLET_SUBMODULE_URL', plugin_dir_url( __DIR__ ) );
			defined( 'WKWC_WALLET_SUBMODULE_PATH' ) || define( 'WKWC_WALLET_SUBMODULE_PATH', plugin_dir_path( __DIR__ ) );
		}


		/**
		 * Plugin url.
		 *
		 * @return string
		 */
		public static function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Plugin url.
		 *
		 * @return string
		 */
		public static function plugin_abspath() {
			return trailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public static function includes() {
			if ( self::is_request( 'admin' ) ) {
				WKWC_Wallet_Install::get_instance();
				WKWC_Wallet_Admin_Hooks::get_instance();
				WKWC_Wallet_Admin_Ajax_Hooks::get_instance();
				WKWC_Wallet_Product::get_instance();
			}
			WKWC_Wallet_Front_Hooks::get_instance();
			WKWC_Wallet_Hooks::get_instance();
			WKWC_Wallet_Front_Filter_Hooks::get_instance();
			WKWC_Wallet_Front_Ajax_Hooks::get_instance();
			WKWC_Wallet_Email_Handler::get_instance();
			WKWC_Wallet_DB_Indexer::get_instance();
		}

		/**
		 * Localization.
		 *
		 * @return void
		 */
		public static function localization() {
			load_plugin_textdomain( 'wkwc_wallet', false, plugin_basename( __DIR__ ) . '/languages' );
		}

		/**
		 * Initialization.
		 *
		 * @return void
		 */
		public static function initialze() {
			// Load core auto-loader.
			require dirname( __DIR__ ) . '/inc/class-wkwc-wallet-autoload.php';

			$wallet_setting = get_option( 'woocommerce_wkwc_wallet_settings', array() );
			$otp_method     = empty( $wallet_setting['otp_method'] ) ? 'mail' : $wallet_setting['otp_method'];

			if ( 'mail' !== $otp_method ) {
				if ( ! file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
					add_action( 'admin_notices', array( __CLASS__, 'wkwc_wallet_twilio_not_installed_notice' ) );
				}
			}

			add_action( 'admin_notices', array( __CLASS__, 'wkwc_wallet_maybe_show_wallet_gateway_block_incompatibility_notice' ) );

			self::includes();
		}

		/**
		 * Twilio library not installed notice.
		 *
		 * @return void
		 */
		public static function wkwc_wallet_twilio_not_installed_notice() {
			$configuration = WK_Caching_Core_Loader::get_the_latest();

			if ( ! empty( $configuration['plugin_path'] ) ) {
				?>
			<div class="error">
				<p>
					<?php
					esc_html_e( 'Please run the command "composer install" at following path to install Twilio library library for SMS feature.', 'wkwc_wallet' );
					?>
				</p>
				<p><?php echo esc_html( $configuration['plugin_path'] ) . 'wkwc_wallet'; ?></p>
			</div>
				<?php
			}
		}

		/**
		 * Show notice for wallet gateway incompatibility if checkout is built using block.
		 *
		 * @return void
		 */
		public static function wkwc_wallet_maybe_show_wallet_gateway_block_incompatibility_notice() {
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '5.0.0', '>=' ) && class_exists( 'WC_Blocks_Utils' ) ) {
				$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
				$checkout_edit    = get_edit_post_link( $checkout_page_id );
				$block_present    = \WC_Blocks_Utils::has_block_in_page( $checkout_page_id, 'woocommerce/checkout' );

				if ( $block_present ) {
					$message = wp_sprintf( /* Translators: %1$s: Checkout edit URL, %2$s: Closing anchor. */ esc_html__( 'WooCommerce blocks are enabled on checkout. Wallet gateway will not work! Please %1$s Switch to classic checkout. %2$s', 'wkwc_wallet' ), '<a href="' . esc_url( $checkout_edit ) . '">', '</a>' );
					self::wkwc_wallet_show_admin_notice( $message, 'error' );
				}
			}
		}

		/**
		 * Which type of request is this?
		 *
		 * @param string $type admin, ajax, cron or frontend.
		 *
		 * @return bool
		 */
		private static function is_request( $type ) {
			if ( 'admin' === $type ) {
				return is_admin();
			}

			return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		/**
		 * Add the gateway to woocommerce.
		 *
		 * @param array $methods All payment methods.
		 */
		public static function wkwc_wallet_add_payment_gateway( $methods ) {
			$methods[] = 'WC_Gateway_WKWC_Wallet';

			return $methods;
		}

		/**
		 * To get first admin user id. It will return smallest admin user id on the site.
		 *
		 * @return int
		 */
		public static function wkwc_wallet_get_first_admin_user_id() {
			// Find and return first admin user id.
			$first_admin_user_id = 0;
			$admin_users         = get_users(
				array(
					'role'    => 'administrator',
					'orderby' => 'ID',
					'order'   => 'ASC',
					'number'  => 1,
				)
			);

			if ( count( $admin_users ) > 0 && $admin_users[0] instanceof \WP_User ) {
				$first_admin_user_id = $admin_users[0]->ID;
			}

			return $first_admin_user_id;
		}

		/**
		 * Log function for debugging.
		 *
		 * @param mixed  $message Message string or array.
		 * @param array  $context Additional parameter, like file name 'source'.
		 * @param string $level One of the following:
		 *     'emergency': System is unusable.
		 *     'alert': Action must be taken immediately.
		 *     'critical': Critical conditions.
		 *     'error': Error conditions.
		 *     'warning': Warning conditions.
		 *     'notice': Normal but significant condition.
		 *     'info': Informational messages.
		 *     'debug': Debug-level messages.
		 */
		public static function log( $message, $context = array(), $level = 'info' ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				$log_enabled = apply_filters( 'wkwc_wallet_is_log_enabled', true );

				if ( $log_enabled ) {
					$source            = ( is_array( $context ) && ! empty( $context['source'] ) ) ? $context['source'] : 'wkwc_wallet';
					$context['source'] = $source;
					$logger            = wc_get_logger();
					$current_user_id   = get_current_user_id();

					$in_action = sprintf( ( /* translators: %s current user id */ esc_html__( 'User in action: %s: ', 'wkwc_wallet' ) ), $current_user_id );
					$message   = $in_action . $message;

					$logger->log( $level, $message, $context );
				}
			}
		}

		/**
		 * Get display name of a user.
		 *
		 * @param int           $user_id User id.
		 * @param object|string $user User objct.
		 * @param object|string $display_type Name display type e.g. 'full'|'nick.
		 *
		 * @return string
		 */
		public static function wkwc_wallet_get_user_display_name( $user_id = 0, $user = '', $display_type = 'full' ) {
			$display_name = __( 'Anonymous User', 'wkwc_wallet' );

			if ( ! $user instanceof \WP_User && $user_id > 0 ) {
				$user = get_user_by( 'ID', $user_id );
			}

			if ( is_a( $user, 'WP_User' ) ) {
				if ( 'nick' === $display_type ) {
					$display_name = empty( $user->user_nicename ) ? $user->display_name : $user->user_nicename;
				} else {
					$display_name  = empty( $user->first_name ) ? get_user_meta( $user_id, 'first_name', true ) : $user->first_name;
					$display_name .= empty( $display_name ) ? '' : ( empty( $user->last_name ) ? ' ' . get_user_meta( $user_id, 'last_name', true ) : ' ' . $user->last_name );
					$display_name  = empty( $display_name ) ? $user->display_name : $display_name;
					$display_name  = empty( $display_name ) ? $user->user_nicename : $display_name;
				}
			}

			return apply_filters( 'wkwc_wallet_get_user_display_name', $display_name, $user_id );
		}

		/**
		 * Declare plugin is compatible with HPOS.
		 *
		 * @param string $file Plugin main file path.
		 * @param bool   $status Plugin compatiblity status.
		 */
		public static function wkwc_wallet_declare_hpos_compatible( $file = '', $status = true ) {
			add_action(
				'before_woocommerce_init',
				function () use ( $file, $status ) {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $file, $status );
					}
				}
			);
		}

		/**
		 * Declare plugin is incompatible with WC Cart and Checkout blocks.
		 *
		 * @param string $file Plugin main file path.
		 * @param bool   $status Compatiblity status.
		 *
		 * @return void
		 */
		public static function wkwc_addon_declare_cart_checkout_block_compatibility_status( $file = '', $status = false ) {
			add_action(
				'before_woocommerce_init',
				function () use ( $file, $status ) {
					if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $file, $status );
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
			if ( ! $order instanceof WC_Abstract_Order && ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( ! $order instanceof WC_Abstract_Order ) {
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
		 * To convert gmt date time saved in db into Admin's configured date and time under WordPress general settings.
		 *
		 * @param string $gmt_datetime GMT date time saved in db.
		 * @param string $format Display format.
		 *
		 * @return string
		 */
		public static function wkwc_wallet_display_gmt_to_wp_timezone( $gmt_datetime = '', $format = '' ) {
			if ( empty( $format ) ) {
				$format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
			}

			if ( empty( $gmt_datetime ) ) {
				$gmt_datetime = gmdate( $format );
			}

			$date = new DateTime( $gmt_datetime, new DateTimeZone( 'GMT' ) );
			$date->setTimezone( new DateTimeZone( wp_timezone_string() ) );

			return $date->format( $format );
		}

		/**
		 * To convert User's date time into gmt for storing in db.
		 *
		 * @param string $local_datetime Local datetime from user input.
		 * @param string $format Display format.
		 *
		 * @return string
		 */
		public static function wkwc_wallet_convert_wp_timezone_to_gmt( $local_datetime = '', $format = 'Y-m-d H:i:s' ) {
			if ( empty( $local_datetime ) ) {
				return gmdate( $format );
			}

			$date = new DateTime( $local_datetime, new DateTimeZone( wp_timezone_string() ) );
			$date->setTimezone( new DateTimeZone( 'UTC' ) );

			return $date->format( $format );
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
		public static function wkwc_wallet_show_admin_notice( $message = '', $type = 'error', $args = array() ) {
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
	}
	WKWC_Wallet::init();
}
