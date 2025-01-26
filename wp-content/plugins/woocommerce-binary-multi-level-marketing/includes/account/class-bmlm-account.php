<?php
/**
 * Account hooks.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Account;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;
use WCBMLMARKETING\Includes\Sponsor\BMLM_Template_Controller;
use WCBMLMARKETING\Templates\Front\Dashboard\BMLM_Become_Sponsor;
use WCBMLMARKETING\Templates\Front\Dashboard\BMLM_Sponsor_Membership;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Account' ) ) {
	/**
	 * Front hooks class.
	 */
	class BMLM_Account extends BMLM_Sponsor {
		/**
		 * Sponsor template handler
		 *
		 * @var object Sponsor template object.
		 */
		protected $sponsor_template;

		/**
		 * User meta table variable
		 *
		 * @var string
		 */
		protected $usermeta_table;

		/**
		 * Genealogy tree table variable
		 *
		 * @var string
		 */
		protected $gtree_table;

		/**
		 * Sponsor id.
		 *
		 * @var int Sponsor id.
		 */
		protected $sponsor_id;
		/**
		 * Account hooks
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb             = $wpdb;
			$this->usermeta_table   = $this->wpdb->prefix . 'usermeta';
			$this->gtree_table      = $this->wpdb->prefix . 'bmlm_gtree_nodes';
			$this->sponsor_id       = get_current_user_id();
			$this->sponsor_template = new BMLM_Template_Controller( $this );

			add_action( 'wp', array( $this, 'bmlm_sponsor_shortcode_controller' ), 10 );
			add_action( 'woocommerce_register_form', array( $this, 'bmlm_sponsor_custom_registration_form' ) );
			add_filter( 'woocommerce_process_registration_errors', array( $this, 'bmlm_sponsor_custom_registration_errors' ) );
			add_filter( 'registration_errors', array( $this, 'bmlm_sponsor_custom_registration_errors' ) );
			add_filter( 'woocommerce_new_customer_data', array( $this, 'bmlm_sponsor_custom_data' ) );
			add_action( 'woocommerce_created_customer', array( $this, 'bmlm_sponsor_process_registration' ), 10, 2 );
			add_filter( 'the_title', array( $this, 'bmlm_modify_sponsor_page_title' ) );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'bmlm_add_sponsor_custom_menu_items' ) );
			add_action( 'woocommerce_account_dashboard', array( $this, 'bmlm_add_sponsor_information' ), 10 );
			add_filter( 'woocommerce_get_endpoint_url', array( $this, 'bmlm_decode_sponsor_links' ), 10, 1 );
		}

		/**
		 * Call sponsor sub pages in sponsor page shortcode
		 *
		 * @return void
		 */
		public function bmlm_sponsor_shortcode_controller() {
			global $bmlm;

			$is_sponsor = $bmlm->bmlm_user_is_sponsor( $this->sponsor_id );
			$pagename   = get_query_var( 'pagename' );
			$main_page  = get_query_var( 'main_page' );

			if ( is_user_logged_in() && ! empty( $pagename ) && $is_sponsor && $pagename === $bmlm->sponsor_page_slug ) {
				switch ( $main_page ) {
					case 'dashboard':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_dashboard' ), 'dashboard' );
						break;
					case 'ads':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_ads' ), 'ads' );
						break;
					case 'genealogy':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_genealogy_tree' ), 'gtree' );
						break;
					case 'refferal':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_refferal_links' ), 'refferal' );
						break;
					case 'client-refferal':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_client_refferal_links' ), 'clientrefferal' );
						break;
					case 'invoice':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_client_invoice' ), 'invoice' );
						break;	
					case 'marketing-crm-link':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_marketing_crm_link' ), 'marketingcrmlink' );
						break;
					case 'social-media-kit':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_social_media_kit' ), 'socialmediakit' );
						break;	
					case 'training-resources':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_training_resources' ), 'trainingresources' );
						break;		
					case 'commission':
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_commissions' ), 'wallet' );
						break;
					default:
						add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_dashboard' ), 'dashboard' );
						break;
				}
			} elseif ( $pagename === $bmlm->sponsor_page_slug && ! is_user_logged_in() ) {
				add_shortcode( 'sponsor', array( $this->sponsor_template, 'bmlm_sponsor_registration' ), 'dashboard' );
			} elseif ( $pagename === $bmlm->sponsor_page_slug && is_user_logged_in() ) {
				wp_safe_redirect( esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) );
				exit();
			}
		}

		/**
		 * Sponsor link decode.
		 *
		 * @param string $url URL.
		 *
		 * @return string
		 */
		public function bmlm_decode_sponsor_links( $url ) {
			return urldecode( $url );
		}

		/**
		 * Sponsor related fields in registration fields
		 *
		 * @return void
		 */
		public function bmlm_sponsor_custom_registration_form() {
			global $bmlm;
			if ( ! is_user_logged_in() && $bmlm->bmlm_is_sponsor_page() && ! is_account_page() ) {
				$this->sponsor_template->bmlm_sponsor_custom_registration_form();
			}
		}

		/**
		 * Validates sponsor registration form
		 *
		 * @param WP_Error $error Error.
		 * @return \WP_Error
		 */
		public function bmlm_sponsor_custom_registration_errors( $error ) {
			$error = $this->bmlm_validate_sponsor_registration_fields( $error );
			return $error;
		}

		/**
		 * Inject sponsor data into form data
		 *
		 * @param array $data Data.
		 *
		 * @return $data
		 */
		public function bmlm_sponsor_custom_data( $data ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$allowed_roles = array( 'customer', 'bmlm_sponsor' );
			$role          = ( isset( $posted_data['role'] ) && in_array( wp_unslash( $posted_data['role'] ), $allowed_roles, true ) ) ? wp_unslash( $posted_data['role'] ) : 'customer';

			if ( 'bmlm_sponsor' === $role ) {
				$data['role']        = $role;
				$data['refferal_id'] = trim( wp_unslash( $posted_data['bmlm_refferal_id'] ) );
				$data['register']    = sanitize_title( $posted_data['register'] );
			}
			return $data;
		}

		/**
		 * Process sponsor registration
		 *
		 * @param int   $user_id New User ID.
		 * @param array $data Data Array.
		 * @return void
		 * @throws Exception Success Message.
		 */
		public function bmlm_sponsor_process_registration( $user_id, $data ) {
			if ( ! empty( $data['register'] ) && ! empty( $data['refferal_id'] ) && ! empty( $data['user_login'] ) && ! empty( $data['refferal_id'] ) && ! empty( $data['user_login'] ) ) {
				$this->bmlm_process_sponsor_registration( $user_id, $data );
			}
		}

		/**
		 * Set page title as per the template requested
		 *
		 * @param string $title Title of page.
		 * @return $title
		 */
		public function bmlm_modify_sponsor_page_title( $title ) {
			global $bmlm;
			$page_name = $bmlm->sponsor_page_slug;
			if ( in_the_loop() && is_page( $page_name ) && ! empty( get_query_var( 'main_page' ) ) ) {
				$main_page = get_query_var( 'main_page' );
				switch ( $main_page ) {
					case 'dashboard':
						$title = esc_html__( 'Dashboard', 'binary-mlm' );
						break;
					case 'ads':
						$title = esc_html__( 'Sponsor ads', 'binary-mlm' );
						break;
					case 'genealogy':
						$title = esc_html__( 'Genealogy tree', 'binary-mlm' );
						break;
					case 'refferal':
						$title = esc_html__( "Dealer's Affiliate Link", 'binary-mlm' );
						break;
					case 'wallet':
						$title = esc_html__( 'Wallet', 'binary-mlm' );
						break;
					case 'commission':
						$title = esc_html__( 'Commissions', 'binary-mlm' );
						break;
					default:
						break;
				}
			}

			return $title;
		}

		/**
		 *  Add custom menu items in sponsor menu
		 *
		 * @param  array $items items array.
		 *
		 * @return array $new_items Item array with sponsor options if sponsor.
		 */
		public function bmlm_add_sponsor_custom_menu_items( $items ) {
			global $bmlm;
			$user_id   = get_current_user_id();
			$new_items = array();

			if ( $user_id ) {
				$is_sponsor = $bmlm->bmlm_user_is_sponsor( $user_id );
				$page_name  = $bmlm->sponsor_page_slug;

				if ( $is_sponsor ) {
					$new_items = array(
						'../' . $page_name . '/dashboard'  => esc_html__( 'Dashboard', 'binary-mlm' ),
						'../' . $page_name . '/ads'        => esc_html__( 'Sponsor Ads', 'binary-mlm' ),
						'../' . $page_name . '/genealogy'  => esc_html__( 'Genealogy Tree', 'binary-mlm' ),
						'../' . $page_name . '/commission' => esc_html__( 'Commissions', 'binary-mlm' ),
						'../' . $page_name . '/refferal'   => esc_html__( "Dealer's Affiliate Link", 'binary-mlm' ),
						'../' . $page_name . '/client-refferal'   => esc_html__( "Client Affiliate Link", 'binary-mlm' ),
						'../' . $page_name . '/marketing-crm-link'   => esc_html__( "Marketing CRM Link", 'binary-mlm' ),
						'../' . $page_name . '/social-media-kit'   => esc_html__( "Social Media Kit", 'binary-mlm' ),
						'../' . $page_name . '/training-resources'   => esc_html__( "Training Resources", 'binary-mlm' ),
					);

					$new_items = apply_filters( 'bmlm_woocommerce_account_menu_options', $new_items );

				}
				$new_items += $items;
			}

			return $new_items;
		}

		/**
		 *  Become sponsor information
		 */
		public function bmlm_add_sponsor_information() {
			$bobj       = new BMLM_Become_Sponsor( $this );
			$sponsor    = get_userdata( $this->sponsor_id );
			$user_roles = ! empty( $sponsor ) ? $sponsor->roles : array();

			if ( ! empty( $user_roles ) && ! in_array( 'bmlm_sponsor', $user_roles, true ) ) {
				$bobj->bmlm_process_form();
			}

			$sponsor    = get_userdata( $this->sponsor_id );
			$user_roles = ! empty( $sponsor ) ? $sponsor->roles : array();
			$status     = $this->bmlm_get_sponsor_status( $this->sponsor_id );

			$is_approved = ( 1 === intval( $status ) );

			if ( ! empty( $user_roles ) && ! in_array( 'bmlm_sponsor', $user_roles, true ) && ! current_user_can( 'administrator' ) && ! $is_approved ) {
				$bobj->get_template();
			} elseif ( ! empty( $user_roles ) && in_array( 'bmlm_sponsor', $user_roles, true ) && ! $is_approved && ! current_user_can( 'administrator' ) ) {
				$obj = new BMLM_Sponsor_Membership();
				$obj->get_template();
			}
		}
	}
}
