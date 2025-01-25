<?php
/**
 * Admin End Functions.
 *
 * @package WKWC_WALLET
 *
 * @since 3.6
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WKWC_Wallet_Admin_Functions' ) ) {
	/**
	 * Admin functions class
	 */
	class WKWC_Wallet_Admin_Functions {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Admin Functions Construct.
		 */
		public function __construct() {
		}

		/**
		 * Loading core scripts from loaded modules.
		 */
		public function wkwc_wallet_admin_scripts() {
			wp_enqueue_script( 'wkwc_wallet_admin', WKWC_WALLET_SUBMODULE_URL . 'assets/wkwc-wallet-admin.js', array(), WKWC_WALLET_SCRIPT_VERSION, false );
			wp_enqueue_style( 'wkwc_wallet_admin-style', WKWC_WALLET_SUBMODULE_URL . 'assets/wkwc-wallet-admin.css', array(), WKWC_WALLET_SCRIPT_VERSION, false );

			if ( defined( 'WC_VERSION' ) ) {
				wp_enqueue_style( 'wkwc_wallet-admin-wc-style', plugins_url() . '/woocommerce/assets/client/admin/chunks/8597.style.css', array(), WC_VERSION );
			}

			$ajax_obj = array(
				'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce'                 => wp_create_nonce( 'wkwc-wallet-nonce' ),
				'i18n_no_matches'           => esc_html__( 'No matches found', 'wkwc_wallet' ),
				'i18n_ajax_error'           => esc_html__( 'Loading failed', 'wkwc_wallet' ),
				'i18n_input_too_short_1'    => esc_html__( 'Please enter 1 or more characters', 'wkwc_wallet' ),
				'i18n_input_too_short_n'    => esc_html__( 'Please enter %qty% or more characters', 'wkwc_wallet' ),
				'i18n_input_too_long_1'     => esc_html__( 'Please delete 1 character', 'wkwc_wallet' ),
				'i18n_input_too_long_n'     => esc_html__( 'Please delete %qty% characters', 'wkwc_wallet' ),
				'i18n_selection_too_long_1' => esc_html__( 'You can only select 1 item', 'wkwc_wallet' ),
				'i18n_selection_too_long_n' => esc_html__( 'You can only select %qty% items', 'wkwc_wallet' ),
				'i18n_load_more'            => esc_html__( 'Loading more results&hellip;', 'wkwc_wallet' ),
				'i18n_searching'            => esc_html__( 'Searching&hellip;', 'wkwc_wallet' ),
				'i18n_delete_note'          => esc_html__( 'Are you sure you wish to delete this note? This action cannot be undone.', 'wkwc_wallet' ),
			);

			wp_localize_script( 'wkwc_wallet_admin', 'wkwc_wallet_obj', array( 'ajax' => $ajax_obj ) );
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
}
