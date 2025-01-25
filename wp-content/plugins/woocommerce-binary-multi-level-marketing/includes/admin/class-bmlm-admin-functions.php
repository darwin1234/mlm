<?php
/**
 * Admin End Functions.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Admin;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;
use WCBMLMARKETING\Helper\NetworkUsers\BMLM_Network_Users;
use WCBMLMARKETING\Templates\Admin\Badge\BMLM_Badge_List;
use WCBMLMARKETING\Templates\Admin\Badge\BMLM_Manage_Badge;
use WCBMLMARKETING\Templates\Admin\Commission\BMLM_Commission_List;
use WCBMLMARKETING\Templates\Admin\Genealogy\BMLM_Genealogy_Tree;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\BMLM_Sponsor_Commission;
use WCBMLMARKETING\Templates\Admin\BMLM_Tabs_Controller;
use WCBMLMARKETING\Templates\Admin\Sponsors\BMLM_Sponsor_List;
use WCBMLMARKETING\Templates\Admin\Transaction\BMLM_Transactions_List;
use WCBMLMARKETING\Templates\Admin\Wallet\BMLM_Admin_Wallet_Transaction;
use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Admin_Functions' ) ) {
	/**
	 * Admin functions class.
	 */
	class BMLM_Admin_Functions extends BMLM_Sponsor {

		/**
		 * Admin Functions Construct.
		 */
		public function __construct() {
			parent::__construct();

			// Validate settings.
			// Validate levelup settings.
			add_action( 'pre_add_option_bmlm_levelup_commission', array( $this, 'bmlm_validate_fields' ), 10, 3 );
			add_action( 'pre_update_option_bmlm_levelup_commission', array( $this, 'bmlm_validate_fields' ), 10, 3 );

			// Validate sale settings.
			add_action( 'pre_add_option_bmlm_sales_commission_other', array( $this, 'bmlm_validate_fields' ), 10, 3 );
			add_action( 'pre_update_option_bmlm_sales_commission_other', array( $this, 'bmlm_validate_fields' ), 10, 3 );

			// Validate joining settings.
			add_action( 'pre_add_option_bmlm_joining_commission_other', array( $this, 'bmlm_validate_fields' ), 10, 3 );
			add_action( 'pre_update_option_bmlm_joining_commission_other', array( $this, 'bmlm_validate_fields' ), 10, 3 );

			add_filter( 'set-screen-option', array( $this, 'bmlm_set_screen_options' ), 10, 3 );
		}

		/**
		 * Validate settings.
		 *
		 * @param array  $value current value.
		 * @param array  $old_value old value.
		 * @param string $option option name.
		 *
		 * @return array
		 */
		public function bmlm_validate_fields( $value, $old_value, $option ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ! empty( $posted_data[ $option ] ) ) {
				$data      = array();
				$new_array = array();

				foreach ( $value as $key => $line ) {
					if ( ! in_array( $line['level'], $data, true ) ) {
						$data[]            = $line['level'];
						$new_array[ $key ] = $line;
					}
				}
				$value = $new_array;

				if ( ! empty( $value ) ) {
					$rates = wp_list_pluck( $value, 'rate' );
					$sum   = array_sum( $rates );
					if ( $sum > 100 ) {
						$old_value = array_map(
							function ( $val ) {
								$val['rate'] = wc_format_decimal( $val['rate'], 2 );
								return $val;
							},
							$old_value
						);
						return $old_value;
					} else {
						$value = array_map(
							function ( $val ) {
								$val['rate'] = wc_format_decimal( $val['rate'], 2 );
								return $val;
							},
							$value
						);
						return $value;
					}
				} else {
					return $old_value;
				}
			}
		}

		/**
		 * Register Option settings.
		 */
		public function bmlm_register_settings() {
			// Sponsor Commission Settings.
			register_setting( 'bmlm-sales-commission-settings-group', 'bmlm_sales_commission_admin' );
			register_setting( 'bmlm-sales-commission-settings-group', 'bmlm_sales_commission_other', array( $this, 'bmlm_sales_commission_other_validate' ) );
			register_setting( 'bmlm-joining-commission-settings-group', 'bmlm_joining_amount_settings_enable' );
			register_setting( 'bmlm-joining-commission-settings-group', 'bmlm_joining_commission_admin' );
			register_setting( 'bmlm-joining-commission-settings-group', 'bmlm_joining_commission_other', array( $this, 'bmlm_sales_commission_other_validate' ) );

			register_setting( 'bmlm-levelup-commission-settings-group', 'bmlm_levelup_amount_settings_enable' );
			register_setting( 'bmlm-levelup-commission-settings-group', 'bmlm_levelup_commission_amount' );
			register_setting( 'bmlm-levelup-commission-settings-group', 'bmlm_levelup_commission', array( $this, 'bmlm_sales_commission_other_validate' ) );

			// referral Settings.
			register_setting( 'bmlm-refferal-settings-group', 'bmlm_refferal_code_length', array( $this, 'bmlm_validate_refferal_code_length' ) );
			register_setting( 'bmlm-refferal-settings-group', 'bmlm_sponsor_refferal_code_format' );
			register_setting( 'bmlm-refferal-settings-group', 'bmlm_refferal_code_prefix' );
			register_setting( 'bmlm-refferal-settings-group', 'bmlm_refferal_code_suffix' );
			register_setting( 'bmlm-refferal-settings-group', 'bmlm_refferal_code_separator' );
		}

		/**
		 *  Validate sales commission data.
		 *
		 * @param array $data commission rate.
		 * @return array
		 */
		public function bmlm_sales_commission_other_validate( $data ) {
			$validate_data = array();
			$validate_data = 0;
			foreach ( $data as $value ) {
				$level = ! empty( $value['level'] ) ? $value['level'] : '';
				$rate  = ! empty( $value['rate'] ) ? $value['rate'] : '';
				if ( $rate < 1 ) {
					add_settings_error( 'my_option_notice', 'invalid_bmlm_sales_commission_other', esc_html__( 'Please enter the valid commission value in level ', 'binary-mlm' ) . $level . '.' );
					$validate_data = 1;
				}
			}
			$data = ( 1 !== $validate_data ) ? $data : array();
			return $data;
		}

		/**
		 * Admin menu callback.
		 *
		 * @return void
		 */
		public function bmlm_admin_menu() {

			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_options' );

			$hook_sponsors = add_submenu_page(
				'wkwc-addons',
				esc_html__( 'Binary MLM', 'binary-mlm' ),
				esc_html__( 'Binary MLM', 'binary-mlm' ),
				$capability,
				'bmlm_sponsors',
				array(
					$this,
					'bmlm_tabs_output',
				)
			);

			add_action( "load-{$hook_sponsors}", array( $this, 'bmlm_screen_option' ) );
		}

		/**
		 * Mlm cat screen option
		 *
		 * @return void
		 */
		public function bmlm_screen_option() {

			$options  = 'per_page';
			$args     = array();
			$get_data = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $get_data['page'] ) && 'bmlm_sponsors' === $get_data['page'] ) {

				$args = array(
					'label'   => __( 'Per Page', 'binary-mlm' ),
					'default' => 20,
					'option'  => 'bmlm_per_page',
				);
			}
			add_screen_option( $options, $args );
		}

		/**
		 * Set page option.
		 *
		 * @param boolean $status status.
		 * @param string  $option option.
		 * @param string  $value check val.
		 *
		 * @return boolean
		 */
		public function bmlm_set_option( $status, $option, $value ) {
			if ( 'bmlm_per_page' === $option ) {
				return $value;
			}
			return $status;
		}

		/**
		 * Display Tabs.
		 *
		 * @return void
		 */
		public function bmlm_tabs_output() {

			$tabs = array(
				'bmlm_sponsors'    => esc_html__( 'Sponsors', 'binary-mlm' ),
				'bmlm_commissions' => esc_html__( 'Commissions', 'binary-mlm' ),
				'bmlm_genealogy'   => esc_html__( 'Genealogy', 'binary-mlm' ),
				'bmlm_wallet'      => esc_html__( 'Wallet', 'binary-mlm' ),
				'bmlm_badges'      => esc_html__( 'Badges', 'binary-mlm' ),
				'bmlm_transaction' => esc_html__( 'Transaction', 'binary-mlm' ),
				'bmlm_settings'    => esc_html__( 'Settings', 'binary-mlm' ),
			);

			$this->bmlm_create_tabs( apply_filters( 'bmlm_tabs', $tabs ) );
		}

		/**
		 * Create binary tabs
		 *
		 * @param array $tabs tabs.
		 *
		 * @return void
		 */
		public function bmlm_create_tabs( $tabs ) {
			$submenu_name = ( is_array( $tabs ) && count( $tabs ) > 0 ) ? array_keys( $tabs )[0] : '';
			$submenu_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( ! empty( $submenu_name ) && ! empty( $submenu_page ) && $submenu_name === $submenu_page ) {
				$tab         = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$current_tab = empty( $tab ) ? $submenu_name : $tab;

				$current_tab = 'bmlm_sponsors';

				if ( ! empty( $tab ) ) {
					$submenu_page .= '_' . $tab;
					$current_tab   = $tab;
				}
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline wkwc-addons"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
					<nav class="nav-tab-wrapper wkwc-admin-addon-list-manage-nav">
						<?php
						foreach ( $tabs as $name => $label ) {
							$tab_url  = admin_url( 'admin.php?page=' . esc_attr( $submenu_name ) );
							$tab_url .= ( $name === $submenu_name ) ? '' : '&tab=' . $name;
							echo wp_sprintf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( $tab_url ), ( $current_tab === $name ? 'nav-tab-active' : '' ), esc_html( $label ) );
						}
						?>
					</nav>
					<?php
					do_action( $current_tab . '_tabs_content' );
					?>
				</div>
				<?php
			}
		}

		/**
		 * Validate referral code length option
		 *
		 * @param string $input Input.
		 *
		 * @return string
		 */
		public function bmlm_validate_refferal_code_length( $input ) {
			$input = intval( $input );
			if ( $input >= 5 ) {
				return $input;
			} else {
				$old_value = get_option( 'bmlm_refferal_code_length', true );
				add_settings_error(
					'requiredTextFieldEmpty',
					'empty',
					'Invalid referral code length value',
					'error'
				);
				return $old_value;
			}
		}

		/**
		 * Set screen options
		 *
		 * @param string $status status.
		 * @param string $option option.
		 * @param string $value value.
		 *
		 * @return string
		 */
		public function bmlm_set_screen_options( $status, $option, $value ) {
			$options = array( 'bmlm_per_page' );
			if ( in_array( $option, $options, true ) ) {
				return $value;
			}
			return $status;
		}

		/**
		 * Commission Handler.
		 */
		public function bmlm_commission() {
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Commission', 'binary-mlm' ); ?></h1>
				<hr class="wp-header-end" />
				<?php
				$get_data        = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$request_data    = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$current_section = ( isset( $get_data['section'] ) ) ? sanitize_text_field( wp_unslash( $get_data['section'] ) ) : 'all';
				$sublist         = array(
					'all'     => esc_html__( 'All', 'binary-mlm' ),
					'sale'    => esc_html__( 'Sale', 'binary-mlm' ),
					'joining' => esc_html__( 'Joining', 'binary-mlm' ),
					'levelup' => esc_html__( 'LevelUp', 'binary-mlm' ),
					'bonus'   => esc_html__( 'Bonus', 'binary-mlm' ),
				);
				$array_keys      = array_keys( $sublist );
				echo '<ul class="subsubsub">';

				$page_name = isset( $request_data['page'] ) ? wc_clean( $request_data['page'] ) : '';
				$section   = isset( $request_data['section'] ) ? wc_clean( $request_data['section'] ) : '';
				$tab       = isset( $request_data['tab'] ) ? wc_clean( $request_data['tab'] ) : '';

				foreach ( $sublist as $id => $label ) {
					echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=' . $page_name . '&tab=bmlm_commissions&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
				}
				echo '</ul>';
				$obj = new BMLM_Commission_List( $current_section );
				?>
					<form method="GET">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
						<input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>"/>
						<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>"/>
						<?php
						wp_nonce_field( 'bmlm_commission_list_nonce_action', 'bmlm_commission_list_nonce' );
						$obj->prepare_items();
						$obj->search_box( esc_html__( 'Search By Sponsor Email', 'binary-mlm' ), 'search-id' );
						$obj->display();
						?>
					</form>
			</div>
			<?php
		}

		/**
		 * Sponsor Handler.
		 */
		public function bmlm_sponsors() {
			?>
			<div class="wrap">
				<?php
				$get_data     = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$action       = empty( $get_data['action'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['action'] ) ) );
				$sponsor_id   = ( ! empty( $get_data['sponsor_id'] ) && ! is_array( $get_data['sponsor_id'] ) && intval( $get_data['sponsor_id'] ) > 0 ) ? wp_unslash( wc_clean( intval( htmlspecialchars( $get_data['sponsor_id'] ) ) ) ) : 0;
				$tab          = empty( $get_data['section'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['section'] ) ) );

				if ( 'manage' === $action && ! empty( $sponsor_id ) && ! empty( $tab ) ) {
					?>
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Manage Sponsor', 'binary-mlm' ); ?></h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bmlm_sponsors' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back', 'binary-mlm' ); ?></a>
					<hr class="wp-header-end" />
					<?php
					$this->bmlm_display_sponsor_tabs();
				} else {
					$obj       = new BMLM_Sponsor_List();
					$page_name = isset( $request_data['page'] ) ? wc_clean( $request_data['page'] ) : '';
					?>
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Sponsors', 'binary-mlm' ); ?></h1>
					<a href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Create', 'binary-mlm' ); ?></a>
					<hr class="wp-header-end" />
					<form method="GET">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>" />
						<?php
							wp_nonce_field( 'bmlm_sponsor_list_nonce_action', 'bmlm_sponsor_list_nonce' );
							$obj->prepare_items();
							$obj->search_box( esc_html__( 'Search By Sponsor Name', 'binary-mlm' ), 'search-id' );
							$obj->display();
						?>
					</form>
					<?php
				}
				?>
			</div>
			<?php
		}

		/**
		 * Add screen id
		 *
		 * @param array $array_screen screen id array.
		 *
		 * @return array
		 */
		public function bmlm_set_wc_screen_ids( $array_screen ) {
			array_push(
				$array_screen,
				'webkul-wc-addons_page_bmlm_sponsors'
			);

			return $array_screen;
		}

		/**
		 * Genealogy Handler.
		 */
		public function bmlm_genealogy() {
			$gtree = new BMLM_Genealogy_Tree();
			$gtree->get_template();
		}

		/**
		 * Wallet Amount.
		 */
		public function bmlm_manage_wallet() {
			$wallet = BMLM_Admin_Wallet_Transaction::get_instance();
			$wallet->get_template();
		}

		/**
		 * Payout Handler.
		 */
		public function bmlm_payout() {
			?>
			<div class="wrap">
				<?php
				$get_data       = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$request_data   = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$transaction_id = isset( $request_data['bmlm_transaction_id'] ) ? wc_clean( $request_data['bmlm_transaction_id'] ) : 0;

				if ( ! empty( $transaction_id ) ) {
					$url = admin_url( '/admin.php?page=bmlm_sponsors&tab=bmlm_transaction' );
					?>
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Transaction Details', 'binary-mlm' ); ?></h1>
					<a class="bmlm-back button primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Back', 'binary-mlm' ); ?></a>
					<hr class="wp-header-end" />
					<?php
					$this->bmlm_show_transaction_details( $transaction_id );
					return false;
				}
				?>
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Transactions', 'binary-mlm' ); ?></h1>
				<hr class="wp-header-end" />
				<?php

				$current_section = empty( $get_data['section'] ) ? 'all' : wc_clean( wp_unslash( $get_data['section'] ) );
				$page_name       = isset( $request_data['page'] ) ? wc_clean( $request_data['page'] ) : '';
				$tab             = isset( $request_data['tab'] ) ? wc_clean( $request_data['tab'] ) : '';

				$sublist    = array(
					'all'    => esc_html__( 'All', 'binary-mlm' ),
					'debit'  => esc_html__( 'Debit', 'binary-mlm' ),
					'credit' => esc_html__( 'Credit', 'binary-mlm' ),
				);
				$array_keys = array_keys( $sublist );
				echo '<ul class="subsubsub">';

				foreach ( $sublist as $id => $label ) {
					echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=' . $page_name . '&tab=bmlm_transaction&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
				}
				echo '</ul>';
				$obj = new BMLM_Transactions_List( $current_section );
				?>
				<form method="get">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
					<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>"/>
					<input type="hidden" name="section" value="<?php echo esc_attr( $current_section ); ?>"/>
					<?php
					wp_nonce_field( 'bmlm_sponsor_transaction_nonce_action', 'bmlm_sponsor_transaction_nonce' );
					$obj->prepare_items();
					$obj->search_box( esc_html__( 'Search By Transaction ID', 'binary-mlm' ), 'search-id' );
					$obj->display();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Member badge Handler.
		 */
		public function bmlm_badges() {
			?>
			<div class="wrap">
				<?php
				$get_data     = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$action       = empty( $get_data['action'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['action'] ) ) );
				$lid          = empty( $get_data['lid'] ) ? '' : wc_clean( $get_data['lid'] );

				if ( 'add' === $action || 'edit' === $action ) {
					?>
					<h1 class="wp-heading-inline">
						<?php echo esc_html( 'add' === $action ? __( 'Add New Badge', 'binary-mlm' ) : __( 'Edit Badge', 'binary-mlm' ) ); ?>
					</h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bmlm_sponsors&tab=bmlm_badges' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back', 'binary-mlm' ); ?></a>
					<hr class="wp-header-end" />
					<?php
					$obj = new BMLM_Manage_Badge( $lid );
					$obj->get_template();
				} else {
					$obj       = new BMLM_Badge_List();
					$page_name = isset( $request_data['page'] ) ? wc_clean( $request_data['page'] ) : '';
					$tab       = isset( $request_data['tab'] ) ? wc_clean( $request_data['tab'] ) : '';
					?>
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Sponsor Badges', 'binary-mlm' ); ?></h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bmlm_sponsors&tab=bmlm_badges&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Create', 'binary-mlm' ); ?></a>
					<hr class="wp-header-end" />
					<form method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
						<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>"/>
						<?php
						wp_nonce_field( 'bmlm_sponsor_badge_list_nonce_action', 'bmlm_sponsor_badge_list_nonce' );
						$obj->prepare_items();
						$obj->search_box( esc_html__( 'Search By Badge Name', 'binary-mlm' ), 'search-id' );
						$obj->display();
						?>
					</form>
					<?php
				}
				?>
			</div>
			<?php
		}

		/**
		 * Configuration Handler.
		 */
		public function bmlm_configuration() {
			$obj = new BMLM_Tabs_Controller();
			$obj->get_setting_tabs();
		}

		/**
		 * Genealogy Handler.
		 */
		public function bmlm_display_sponsor_tabs() {
			$obj = new BMLM_Tabs_Controller();
			$obj->get_sponsor_tabs();
		}

		/**
		 * Save custom data on user create.
		 *
		 * @param int $user_id user id.
		 */
		public function bmlm_save_sponsor_custom_data( $user_id ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['createuser'] ) && ! empty( $posted_data['role'] ) && 'bmlm_sponsor' === $posted_data['role'] ) {
				$refferal_id = ! empty( $posted_data['refferal_id'] ) ? trim( wp_unslash( $posted_data['refferal_id'] ) ) : '';
				$this->bmlm_update_sponsor_details( $user_id, $refferal_id );
			}
		}

		/**
		 * Validate fields
		 *
		 * @param object $errors Errors.
		 * @param string $update Update.
		 * @param object $user WP_user.
		 *
		 * @return object
		 */
		public function bmlm_validate_user_fields( &$errors, $update = null, &$user = null ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $posted_data['createuser'] ) && ! empty( $posted_data['role'] ) && 'bmlm_sponsor' === $posted_data['role'] ) {
				$refferal_id = ! empty( $posted_data['refferal_id'] ) ? trim( wp_unslash( $posted_data['refferal_id'] ) ) : '';

				if ( empty( $refferal_id ) ) {
					$errors->add( 'refferal_error', esc_html__( 'Please enter your referral id.', 'binary-mlm' ) );
				}

				if ( empty( $this->bmlm_sponsor_id_exists( $refferal_id ) ) ) {
					$errors->add( 'refferal_error', esc_html__( 'Entered referral id does not exists.', 'binary-mlm' ) );
				}

				$usage_count = $this->bmlm_get_referral_code_usage_count( $refferal_id );

				if ( ! empty( $usage_count ) && $usage_count >= 2 ) {
					$errors->add( 'refferal_count_error', esc_html__( 'referral id usage limit reached, A referral id can be used by only two users', 'binary-mlm' ) );
				}
			}

			return $errors;
		}

		/**
		 * Add script template.
		 */
		public function bmlm_add_custom_template() {
			?>
			<script id="tmpl-sponsor_template" type="text/html">
				<tr valign="top" id={{{data.id}}}>
					<th>
						<label for="refferal_id"> <?php esc_html_e( 'Referral ID (*)', 'binary-mlm' ); ?> </label>
					</th>
					<td>
						<input type="text" id="refferal_id" name="refferal_id" required value="">
					</td>
				</tr>
			</script>
			<?php
		}

		/**
		 * Admin end scripts.
		 */
		public function bmlm_enqueue_admin_scripts() {
			$request_data = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page         = ! empty( $request_data['page'] ) ? sanitize_text_field( wp_unslash( $request_data['page'] ) ) : '';

			if ( ! empty( $page ) && 'bmlm_sponsors' === $page ) {
				$user_id = ( ! empty( $request_data['sponsor_id'] ) && ! is_array( $request_data['sponsor_id'] ) && intval( $request_data['sponsor_id'] ) > 0 ) ? wp_unslash( wc_clean( intval( htmlspecialchars( $request_data['sponsor_id'] ) ) ) ) : get_current_user_id();

				$json_data = $this->bmlm_get_sponsor_childrens( $user_id );

				$sponsor_data = $this->bmlm_get_sposnors_miscellaneous( $json_data );

				$sponsors = $this->bmlm_format_sponsor_data( $sponsor_data );
				$sponsors = $this->bmlm_reformat_data( $sponsors );
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

			$ajax_vars = array(
				'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce'                 => wp_create_nonce( 'bmlm-nonce' ),
				'i18n_no_matches'           => esc_html__( 'No matches found', 'binary-mlm' ),
				'i18n_ajax_error'           => esc_html__( 'Loading failed', 'binary-mlm' ),
				'i18n_input_too_short_1'    => esc_html__( 'Please enter 1 or more characters', 'binary-mlm' ),
				'i18n_input_too_short_n'    => esc_html__( 'Please enter %qty% or more characters', 'binary-mlm' ),
				'i18n_input_too_long_1'     => esc_html__( 'Please delete 1 character', 'binary-mlm' ),
				'i18n_input_too_long_n'     => esc_html__( 'Please delete %qty% characters', 'binary-mlm' ),
				'i18n_selection_too_long_1' => esc_html__( 'You can only select 1 item', 'binary-mlm' ),
				'i18n_selection_too_long_n' => esc_html__( 'You can only select %qty% items', 'binary-mlm' ),
				'i18n_load_more'            => esc_html__( 'Loading more results&hellip;', 'binary-mlm' ),
				'i18n_searching'            => esc_html__( 'Searching&hellip;', 'binary-mlm' ),
			);

			wp_enqueue_script( 'select2', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js', array(), BMLM_SCRIPT_VERSION, true );
			wp_enqueue_style( 'select2', plugins_url() . '/woocommerce/assets/css/select2.css', array(), BMLM_SCRIPT_VERSION );
			wp_enqueue_script( 'bmlm-admin', BMLM_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'select2' ), BMLM_SCRIPT_VERSION, true );
			wp_localize_script(
				'bmlm-admin',
				'bmlm_vars',
				array(
					'ajax' => $ajax_vars,
				)
			);
			wp_enqueue_style( 'bmlm-admin', BMLM_PLUGIN_URL . 'assets/css/admin.css', array( 'select2' ), BMLM_SCRIPT_VERSION, false );
			wp_enqueue_media();
		}

		/**
		 * Delete user manage in network
		 *
		 * @param array $data data.
		 */
		public function bmlm_reformat_data( &$data ) {
			if ( ! empty( $data['children'] ) ) {
				$data['children'] = array_values( $data['children'] );
				array_map(
					function ( &$data ) {
						$data = $this->bmlm_reformat_data( $data );
					},
					$data['children']
				);
			}
			return $data;
		}

		/**
		 * Delete user manage in network
		 *
		 * @param int      $user_id User id.
		 *
		 * @param int|null $reassign Reassign.
		 *
		 * @param object   $user WP user.
		 *
		 * @return void
		 */
		public function bmlm_network_delete_user( $user_id, $reassign, $user ) {
			do_action( 'bmlm_before_delete_network_user', $user_id, $user );

			$bmlm_network_user = BMLM_Network_Users::get_instance( $user_id );
			$bmlm_network_user->bmlm_update_network_user_status( $user_id, 2 ); // Set network user status deleted.

			do_action( 'bmlm_after_delete_network_user', $user_id, $user );
		}

		/**
		 * Show transaction details.
		 *
		 * @param int $trans_id Transaction id.
		 *
		 * @return void
		 */
		public function bmlm_show_transaction_details( $trans_id ) {
			$helper           = BMLM_Transaction::get_instance();
			$transaction_data = $helper->bmlm_get_transaction_by_id( $trans_id );
			$customer_id      = empty( $transaction_data['customer'] ) ? 0 : intval( $transaction_data['customer'] );
			$customer         = get_user_by( 'ID', $customer_id );
			$customer_email   = ( $customer instanceof \WP_User ) ? $customer->user_email : '-';

			$sender_id    = empty( $transaction_data['sender'] ) ? 0 : intval( $transaction_data['sender'] );
			$sender       = get_user_by( 'ID', $sender_id );
			$sender_email = ( $sender instanceof \WP_User ) ? $sender->user_email : '-';
			?>
			<table class="widefat bmlm-widefat fixed">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Data Label ', 'binary-mlm' ); ?></th>
						<th><?php esc_html_e( 'Value ', 'binary-mlm' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $transaction_data['transaction_id'] ) ) { ?>
					<tr>
						<td><?php esc_html_e( 'Order ID:', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $transaction_data['transaction_id'] ); ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td><?php esc_html_e( 'Amount', 'binary-mlm' ); ?></td>
						<td><?php echo wp_kses_post( wc_price( $transaction_data['amount'] ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Type', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $transaction_data['type'] ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Action', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $transaction_data['reference'] ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Customer', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $customer_email ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Payee', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $sender_email ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Date', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $transaction_data['date'] ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Note', 'binary-mlm' ); ?></td>
						<td><?php echo esc_html( $transaction_data['note'] ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php
		}

		/**
		 * Add plugin related link on plugin list page
		 *
		 * @param array  $links Links.
		 *
		 * @param string $file File.
		 *
		 * @return array $links
		 */
		public function bmlm_add_plugin_setting_links( $links, $file ) {
			if ( plugin_basename( BMLM_FILE ) === $file ) {
				$row_meta = array(
					'docs'    => '<a target="_blank" href="' . esc_url( apply_filters( 'binary_docs_url', 'https://webkul.com/blog/binary-multi-level-marketing-for-woocommerce/' ) ) . '" aria-label="' . esc_attr__( 'View MLM documentation', 'binary-mlm' ) . '">' . esc_html__( 'Docs', 'binary-mlm' ) . '</a>',
					'support' => '<a target="_blank" href="' . esc_url( apply_filters( 'binary_mlm_support_url', 'https://webkul.uvdesk.com/' ) ) . '" aria-label="' . esc_attr__( 'Visit customer support', 'binary-mlm' ) . '">' . esc_html__( 'Support', 'binary-mlm' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Add plugin setting link on plugin list page
		 *
		 * @param array  $links Links.
		 *
		 * @param string $file File.
		 *
		 * @return array $links
		 */
		public function bmlm_plugin_action_links( $links, $file ) {
			$links   = is_array( $links ) ? $links : array();
			$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=bmlm_sponsors&tab=bmlm_settings' ) ) . '">' . esc_html__( 'Settings', 'binary-mlm' ) . '</a>';
			return $links;
		}

		/**
		 * Allowing indexing if old table if some transaction exists.
		 *
		 * @param bool $allow Allow indexing.
		 *
		 * @hooked 'wkwc_wallet_allow_indexing' filter hook.
		 *
		 * @return bool
		 */
		public function bmlm_allow_indexing_from_group_product_page( $allow ) {
			global $wpdb;
			$wpdb_obj         = $wpdb;
			$old_transactions = $wpdb_obj->prefix . 'bmlm_wallet_transactions';
			$table_exist      = $wpdb_obj->get_var( "SHOW TABLES LIKE '$old_transactions'" );

			if ( ! empty( $table_exist ) ) {
				$existing_transactions = $wpdb_obj->get_results( "SELECT * FROM $old_transactions" );
				$allow                 = ! empty( $existing_transactions );
			}

			return $allow;
		}

		/**
		 * Show indexing progress notice on group pages.
		 *
		 * @param bool $allow Allow indexing.
		 *
		 * @hooked 'wkwc_wallet_allow_indexing' filter hook.
		 *
		 * @return bool
		 */
		public function bmlm_allow_indexing_progress_notice( $allow ) {
			$group_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$tab        = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( 'bmlm_sponsors' === $group_page && 'transaction' === $tab ) {
				$allow = true;
			}

			return $allow;
		}
	}
}
