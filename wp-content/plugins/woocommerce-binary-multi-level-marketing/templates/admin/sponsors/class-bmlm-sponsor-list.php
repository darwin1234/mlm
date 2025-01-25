<?php
/**
 * Sponsor List template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors;

use WCBMLMARKETING\Inc\BMLM_Errors;
use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'BMLM_Sponsor_List' ) ) {
	/**
	 * Sponsor list class
	 */
	class BMLM_Sponsor_List extends \WP_List_Table {

		/**
		 * Sponsor Helper Variable
		 *
		 * @var object
		 */
		protected $sponsor_helper;

		/**
		 * Error Helper Variable
		 *
		 * @var object
		 */
		protected $error_helper;

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->sponsor_helper = BMLM_Sponsor::get_instance();
			$this->error_helper   = new BMLM_Errors();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Sponsor List', 'binary-mlm' ),
					'plural'   => esc_html__( 'Sponsor List', 'binary-mlm' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare Items.
		 *
		 * @return void
		 */
		public function prepare_items() {

			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();
			$screen   = get_current_screen();

			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'bmlm_per_page', 20 );
			$current_page = $this->get_pagenum();
			$offset       = ( $current_page - 1 ) * $per_page;

			$args = array(
				'per_page' => $per_page,
				'offset'   => $offset,
			);

			$sponsors_query = $this->sponsor_helper->bmlm_get_all_sponsors( $args );
			$total_items    = $sponsors_query->get_total();
			$sponsors       = $sponsors_query->get_results();
			$data           = $this->bmlm_get_sponsors( $sponsors );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$this->items = $data;
		}

		/**
		 * Process bulk actions.
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			$nonce = empty( $_GET['bmlm_sponsor_list_nonce'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm_sponsor_list_nonce'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'bmlm_sponsor_list_nonce_action' ) ) {
				if ( 'delete' === $this->current_action() ) {
					$sponsor_ids = empty( $_GET['sponsor_id'] ) ? array() : wp_unslash( wc_clean( $_GET['sponsor_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$sponsor_ids = ( ! empty( $sponsor_ids ) && ! is_array( $sponsor_ids ) ) ? array( intval( $sponsor_ids ) ) : array_map( 'intval', $sponsor_ids );

					if ( ! empty( $sponsor_ids ) && is_array( $sponsor_ids ) ) {
						foreach ( $sponsor_ids as $sponsor_id ) {
							$response = $this->sponsor_helper->bmlm_delete_sponsor( $sponsor_id );
						}
						$message = count( $sponsor_ids ) . ' ' . esc_html__( 'Sponsor(s) deleted successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_print_notification( $message );
					}
				}
			}
		}

		/**
		 * Fetch sponsors
		 *
		 * @param array $sponsors Sponsors.
		 * @return array $sponsors
		 */
		public function bmlm_get_sponsors( $sponsors ) {
			$data = array();

			if ( ! empty( $sponsors ) ) {
				foreach ( $sponsors as $user ) {
					$user_id         = $user->ID;
					$user_login      = $user->display_name;
					$email           = $user->user_email;
					$downline_member = $this->sponsor_helper->bmlm_sponsor_get_downline_member_count( $user_id );

					$args = array(
						'user_id' => $user_id,
						'paid'    => 1,
					);

					$gross_business = $this->sponsor_helper->bmlm_sponsor_get_gross_business( $args );
					$html           = $this->sponsor_helper->bmlm_get_status_html( $user_id );
					$wallet_amount  = get_user_meta( $user_id, 'wkwc_wallet_amount', true );

					$data[] = array(
						'id'              => $user_id,
						'name'            => $user_login,
						'email'           => '<a href="mailto:' . $email . '">' . $email . '</a>',
						'downline_member' => $downline_member,
						'gross_business'  => wc_price( $gross_business ),
						'balance'         => wc_price( $wallet_amount ),
						'status'          => $html,
					);
				}
			}

			return apply_filters( 'bmlm_sponsor_list_data', $data );
		}

		/**
		 * No items.
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No sponsor available.', 'binary-mlm' );
		}

		/**
		 * Hidden Columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'              => '<input type="checkbox" />',
				'name'            => esc_html__( 'Name', 'binary-mlm' ),
				'email'           => esc_html__( 'Email', 'binary-mlm' ),
				'downline_member' => esc_html__( 'Downline Member', 'binary-mlm' ),
				'gross_business'  => esc_html__( 'Gross Business', 'binary-mlm' ),
				'balance'         => esc_html__( 'Wallet Balance', 'binary-mlm' ),
				'status'          => esc_html__( 'Status', 'binary-mlm' ),
			);

			return apply_filters( 'bmlm_sponsor_list_columns', $columns );
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'name':
				case 'email':
				case 'downline_member':
				case 'gross_business':
				case 'balance':
				case 'status':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Column actions
		 *
		 * @param array $item Items.
		 * @return array $actions
		 */
		public function column_name( $item ) {
			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$click = "return confirm('" . esc_html__( 'Are you sure to delete sponsors? Deleting a sponsor will affect the commission calculation and also, no child can be added below this sponsor as the referral id become invalid.', 'binary-mlm' ) . "')";

			$actions = array(
				'edit'   => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( '/user-edit.php?user_id=' . $item['id'] ) ), esc_html__( 'Edit', 'binary-mlm' ) ),
				'manage' => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $page_name . '&section=sponsor-general&action=manage&sponsor_id=' . $item['id'] ) ), esc_html__( 'Manage', 'binary-mlm' ) ),
				'delete' => sprintf( '<a onclick="' . $click . '" href="%s">%s</a>', wp_nonce_url( 'admin.php?page=' . $page_name . '&action=delete&sponsor_id=' . esc_attr( $item['id'] ), 'bmlm_sponsor_list_nonce_action', 'bmlm_sponsor_list_nonce' ), esc_html__( 'Delete', 'binary-mlm' ) ),
			);
			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( apply_filters( 'bmlm_sponsor_list_line_actions', $actions ) ) );
		}

		/**
		 * Render the bulk edit checkbox.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="sponsor_id[]" value="%d" />', esc_attr( $item['id'] ) );
		}

		/**
		 * Bulk actions on list.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			return apply_filters(
				'bmlm_modify_bulk_actions_in_sponsor',
				array(
					'delete' => esc_html__( 'Delete', 'binary-mlm' ),
				)
			);
		}
	}
}
