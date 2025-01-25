<?php
/**
 * Global Class.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

use WCBMLMARKETING\Helper;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM' ) ) {
	/**
	 * BMLM Main Class
	*/
	final class BMLM {
		/**
		 * Instance variable
		 *
		 * @var object Mlm object.
		 */

		protected static $bmlm = null;

		/**
		 * Sponsor page slug
		 *
		 * @var string
		 */

		public $sponsor_page_slug;

		/**
		 * General query helper
		 *
		 * @var object
		 */

		protected $sponsor;

		/**
		 * The object is created from within the class itself only if the class has no instance.
		 *
		 * @return $bmlm
		 */
		public static function instance() {
			if ( is_null( self::$bmlm ) ) {
				self::$bmlm = new self();
			}

			return self::$bmlm;
		}

		/**
		 * Marketing Constructor.
		 */
		public function __construct() {

			$this->bmlm_init_hooks();
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @return void
		 */
		public function bmlm_init_hooks() {
			require_once BMLM_PLUGIN_FILE . 'includes/class-bmlm-install.php';
			$schema_handler = \BMLM_Install::get_instance();

			register_activation_hook( BMLM_FILE, array( $schema_handler, 'init' ) );
			add_action( 'plugins_loaded', array( $this, 'bmlm_load_plugin' ) );

			require_once BMLM_PLUGIN_FILE . '/class-wk-caching-core-loader.php';
			require_once BMLM_PLUGIN_FILE . '/class-wk-wallet-core-loader.php';

			add_action( 'plugins_loaded', array( 'WK_Caching_Core_Loader', 'include_core' ), - 1 );
			add_action( 'plugins_loaded', array( 'WK_Wallet_Core_Loader', 'include_core' ), - 1 );
		}

		/**
		 * Encryption
		 *
		 * @param string $input_string Input string.
		 *
		 * @param string $action Action.
		 *
		 * @return string
		 */
		public function encrypt_decrypt( $input_string, $action ) {
			// You may change these values to your own.
			$secret_key     = 'wk';
			$secret_iv      = 'bmlm';
			$output         = false;
			$encrypt_method = 'AES-256-CBC';
			$key            = hash( 'sha256', $secret_key );
			$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

			if ( 'e' === $action ) {
				$output = base64_encode( openssl_encrypt( $input_string, $encrypt_method, $key, 0, $iv ) );
			} elseif ( 'd' === $action ) {
				$output = openssl_decrypt( base64_decode( $input_string ), $encrypt_method, $key, 0, $iv );
			}

			return $output;
		}

		/**
		 * Process membership form.
		 *
		 * @return void
		 */
		public function bmlm_process_membership_form() {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$nonce = empty( $posted_data['bmlm_sponsor_membership_nonce_field'] ) ? '' : $posted_data['bmlm_sponsor_membership_nonce_field'];

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'bmlm_sponsor_membership_nonce_action' ) ) {
				$membership    = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
				$membership_id = empty( $membership->ID ) ? 0 : $membership->ID;

				if ( ! empty( $membership_id ) ) {
					WC()->cart->add_to_cart( $membership_id );
					wp_safe_redirect( wc_get_page_permalink( 'cart' ) );
					exit;
				}
			}
		}

		/**
		 * Load plugin
		 *
		 * @return void
		 */
		public function bmlm_load_plugin() {
			load_plugin_textdomain( 'binary-mlm', false, basename( __DIR__ ) . '/languages' );
			if ( ! function_exists( 'WC' ) ) {
				add_action(
					'admin_notices',
					function () {
						?>
						<div class="error">
							<?php /* translators: %s: plugin check */ ?>
							<p><?php echo wp_sprintf( esc_html__( 'WooCommerce Binary Multi Level Marketing depends on the latest version of %s or later to work!', 'binary-mlm' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . esc_html__( 'WooCommerce', 'binary-mlm' ) . '</a>' ); ?></p>
						</div>
						<?php
					}
				);
			} else {
				new BMLM_File_Handler();
				add_action( 'wp_loaded', array( $this, 'bmlm_process_membership_form' ) );

				$this->sponsor           = Helper\Sponsor\BMLM_Sponsor::get_instance();
				$this->sponsor_page_slug = $this->sponsor->bmlm_get_sponsor_page_slug();

			}
		}


		/**
		 * Check user is sponsor.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return bool
		 */
		public function bmlm_user_is_sponsor( $user_id ) {
			// / Get the user object.
			$user     = get_userdata( $user_id );
			$response = false;
			// Get all the user roles as an array.
			$user_roles = ( $user instanceof \WP_User ) ? $user->roles : array();
			$status     = $this->sponsor->bmlm_get_sponsor_status( $user_id );

			if ( ! empty( $user_roles ) && in_array( 'bmlm_sponsor', $user_roles, true ) && 1 === intval( $status ) ) {
				$response = true;
			}

			return $response;
		}

		/**
		 * Check current page is sponsor
		 *
		 * @return bool
		 */
		public function bmlm_is_sponsor_page() {
			$response = false;
			if ( is_page( $this->sponsor_page_slug ) ) {
				$response = true;
			}
			return $response;
		}

		/**
		 * Get the Pagination
		 *
		 * @param int $total Total items.
		 *
		 * @param int $page Which page.
		 *
		 * @param int $limit How many items display on single page.
		 *
		 * @param int $url Page URL.
		 *
		 * @return array $data Pagination info
		 */
		public function bmlm_get_pagination( $total, $page, $limit, $url ) {
			$url              .= '/page/{page}';
			$pagination        = new BMLM_Pagination();
			$pagination->total = $total;
			$pagination->page  = $page;
			$pagination->limit = $limit;
			$pagination->url   = $url;

			$data = array();

			$data['pagination'] = $pagination->bmlm_render();

			$data['results'] = '<p class="woocommerce-result-count">' . sprintf( 'Showing %d to %d of %d (%d Pages)', ( $total ) ? ( ( $page - 1 ) * $limit ) + 1 : 0, ( ( ( $page - 1 ) * $limit ) > ( $total - $limit ) ) ? $total : ( ( ( $page - 1 ) * $limit ) + $limit ), $total, ceil( $total / $limit ) ) . '</p>';

			return $data;
		}

		/**
		 * To get first admin user id. It will return smallest admin user id on the site.
		 *
		 * @return int
		 */
		public function bmlm_get_first_admin_user_id() {
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
	}
}
