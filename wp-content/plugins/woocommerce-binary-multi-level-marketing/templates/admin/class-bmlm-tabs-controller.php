<?php
/**
 * Configuration Controller Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;
use WCBMLMARKETING\Templates\Admin\Settings\Commission\BMLM_Joining_Commission_Settings;
use WCBMLMARKETING\Templates\Admin\Settings\Commission\BMLM_Level_Commission_Settings;
use WCBMLMARKETING\Templates\Admin\Settings\Commission\BMLM_Sales_Commission_Settings;
use WCBMLMARKETING\Templates\Admin\Settings\BMLM_General_Settings;
use WCBMLMARKETING\Templates\Admin\Settings\BMLM_Refferal_Settings;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\BMLM_Sponsor_Commission;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\BMLM_Sponsor_Genealogy_Tree;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\BMLM_Sponsor_Manifesto;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Tabs_Controller' ) ) {
	/**
	 * BMLM_Tabs_Controller
	 */
	class BMLM_Tabs_Controller {
		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'bmlm_general', array( $this, 'bmlm_general_settings' ), 10, 1 );
			add_action( 'bmlm_commission', array( $this, 'bmlm_commission_settings' ), 10, 1 );
			add_action( 'bmlm_refferal', array( $this, 'bmlm_refferal_settings' ), 10, 1 );
			add_action( 'bmlm_sponsor-general', array( $this, 'bmlm_sponsor_general_settings' ) );
			add_action( 'bmlm_sponsor-commission', array( $this, 'bmlm_sponsor_commission_settings' ), 10, 1 );
			add_action( 'bmlm_sponsor-downline', array( $this, 'bmlm_sponsor_downline_member_settings' ), 10, 3 );
		}

		/**
		 * Setting Tabs
		 *
		 * @return void
		 */
		public function get_setting_tabs() {
			$bmlm_tabs = array(
				'general'    => esc_html__( 'General', 'binary-mlm' ),
				'commission' => esc_html__( 'Commission', 'binary-mlm' ),
				'refferal'   => esc_html__( 'Referral Code', 'binary-mlm' ),
			);

			$current_tab = empty( $_GET['section'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			$array_keys = array_keys( $bmlm_tabs );
			?>
			<ul class="subsubsub">
					<?php
					foreach ( $bmlm_tabs as $name => $label ) {
						echo '<li> <a href="' . esc_url( admin_url( 'admin.php?page=bmlm_sponsors&tab=bmlm_settings&section=' . esc_attr( $name ) ) ) . '" class=" ' . ( $current_tab === $name ? 'current' : '' ) . '">' . esc_html( $label ) . '</a>' . ( end( $array_keys ) === $name ? '' : '|' ) . '</li>';
					}
					?>
				<?php do_action( 'bmlm_' . esc_attr( $current_tab ), $this ); ?>
			</ul>
			<?php
		}

		/**
		 * Plugin Settings.
		 * General Configuration.
		 */
		public function bmlm_general_settings() {
			$setting = new BMLM_General_Settings();
			$setting->get_template();
		}

		/**
		 * Sponsor Configuration.
		 */
		public function bmlm_commission_settings() {
			?>
			<ul class="subsubsubsub bmlm_commision_tab">
				<?php
				$sections = array(
					'sales'   => esc_html__( 'Sales', 'binary-mlm' ),
					'joining' => esc_html__( 'Joining', 'binary-mlm' ),
					'level'   => esc_html__( 'Level UP', 'binary-mlm' ),
				);

				$current_section = ( isset( $_GET['sub-section'] ) ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : 'sales'; // phpcs:ignore WordPress.Security.NonceVerification

				$array_keys = array_keys( $sections );

				foreach ( $sections as $id => $label ) {
					echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=bmlm_sponsors&tab=bmlm_settings&section=commission&sub-section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . ' </a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
				}
				?>
			</ul>
			<div class="sub-wrapper">
				<br class="clear">
				<?php
				if ( 'sales' === $current_section ) {
					$setting = new BMLM_Sales_Commission_Settings();
					$setting->get_template();
				} elseif ( 'joining' === $current_section ) {
					$setting = new BMLM_Joining_Commission_Settings();
					$setting->get_template();
				} elseif ( 'level' === $current_section ) {
					$setting = new BMLM_Level_Commission_Settings();
					$setting->get_template();
				}
				?>
			</div>
			<?php
		}

		/**
		 * Referral Configuration.
		 */
		public function bmlm_refferal_settings() {
			$setting = new BMLM_Refferal_Settings();
			$setting->get_template();
		}

		/**
		 * Sponsor Tabs
		 *
		 * @return void
		 */
		public function get_sponsor_tabs() {
			$bmlm_tabs = array(
				'sponsor-general'    => esc_html__( 'General', 'binary-mlm' ),
				'sponsor-commission' => esc_html__( 'Commission', 'binary-mlm' ),
				'sponsor-downline'   => esc_html__( 'Downline Members', 'binary-mlm' ),
			);

			$current_tab = empty( $_GET['section'] ) ? 'sponsor-general' : sanitize_title( wp_unslash( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$sponsor_id  = empty( $_GET['sponsor_id'] ) ? '' : intval( wc_clean( $_GET['sponsor_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			$sponsor_obj  = BMLM_Sponsor::get_instance( $sponsor_id );
			$sponsor_data = $sponsor_obj->bmlm_get_sponsor( $sponsor_id );
			$array_keys   = array_keys( $bmlm_tabs );
			if ( ! empty( $sponsor_data->ID ) ) {
				?>
					<ul class="subsubsub">
						<?php
						foreach ( $bmlm_tabs as $name => $label ) {
							echo '<li> <a href="' . esc_url( admin_url( 'admin.php?page=bmlm_sponsors&action=manage&section=' . esc_attr( $name ) ) ) . '&sponsor_id=' . esc_attr( $sponsor_id ) . '" class=" ' . ( $current_tab === $name ? 'current' : '' ) . '">' . esc_html( $label ) . '</a>' . ( end( $array_keys ) === $name ? '' : '|' ) . '</li>';
						}
						?>

					</ul>
					<?php do_action( 'bmlm_' . esc_attr( $current_tab ), $sponsor_id ); ?>
				<?php
			}
		}

		/**
		 * Sponsor Settings.
		 *
		 * @param integer $sponsor_id Sponsor ID.
		 *
		 * @return void
		 */
		public function bmlm_sponsor_general_settings( $sponsor_id ) {
			$setting = BMLM_Sponsor_Manifesto::get_instance( $sponsor_id );
			$setting->get_template();
		}

		/**
		 * Sponsor Commission Configuration.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return void
		 */
		public function bmlm_sponsor_commission_settings( $sponsor_id ) {
			$sobj         = new BMLM_Sponsor_Commission( $sponsor_id );
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$page   = isset( $request_data['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $request_data['page'] ) ) ) : '';
			$action = isset( $request_data['action'] ) ? esc_attr( sanitize_text_field( wp_unslash( $request_data['action'] ) ) ) : '';
			$tab    = isset( $request_data['section'] ) ? esc_attr( sanitize_text_field( wp_unslash( $request_data['section'] ) ) ) : '';
			?>
				<hr class="wp-header-end" />
				<br />
				<form method="get">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
					<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>" />
					<input type="hidden" name="section" value="<?php echo esc_attr( $tab ); ?>" />
					<input type="hidden" name="sponsor_id" value="<?php echo esc_attr( $sponsor_id ); ?>" />
					<?php
					wp_nonce_field( 'bmlm_sponsor_commission_list_nonce_action', 'bmlm_sponsor_commission_list_nonce' );
					$sobj->prepare_items();
					$sobj->display();
					?>
				</form>
			<?php
		}

		/**
		 * Sponsor Down line Member Configuration.
		 */
		public function bmlm_sponsor_downline_member_settings() {
			$setting = new BMLM_Sponsor_Genealogy_Tree();
			$setting->get_template();
		}
	}
}
