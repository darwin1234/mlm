<?php
/**
 * Sponsor badge List template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Badge;

use WCBMLMARKETING\Inc\BMLM_Errors;
use WCBMLMARKETING\Helper\Badges\BMLM_Badges;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Badge_List' ) ) {
	/**
	 * Sponsor list class
	 */
	class BMLM_Badge_List extends \WP_List_Table {

		/**
		 * Sponsor Badge Helper Variable
		 *
		 * @var object
		 */
		protected $helper;

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
			$this->helper       = new BMLM_Badges();
			$this->error_helper = new BMLM_Errors();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Sponsor Badge List', 'binary-mlm' ),
					'plural'   => esc_html__( 'Sponsor Badge List', 'binary-mlm' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare Items
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

			$per_page     = $this->get_items_per_page( 'bmlm_per_page', 10 );
			$current_page = $this->get_pagenum();
			$offset       = ( $current_page - 1 ) * $per_page;
			$search       = empty( $_GET['s'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['s'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$orderby = ( ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'id'; // If no sort, default to title.
			$order   = ( ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'asc'; // If no order, default to asc.
			$args    = array(
				's'       => $search,
				'start'   => $offset,
				'limit'   => $per_page,
				'orderby' => $orderby,
				'order'   => $order,
			);

			$sponsors_badges = $this->helper->bmlm_get_badges( $args );
			$total_items     = $this->helper->bmlm_get_badges_count( $args );
			$data            = $this->bmlm_get_sponsor_badge_list( $sponsors_badges );
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
		 * @return void|bool
		 */
		public function process_bulk_action() {
			$nonce = empty( $_GET['bmlm_sponsor_badge_list_nonce'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm_sponsor_badge_list_nonce'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $nonce ) ) {
				if ( ! wp_verify_nonce( $nonce, 'bmlm_sponsor_badge_list_nonce_action' ) ) {
					$message = esc_html__( 'Invalid nonce. Security check failed!!!', 'binary-mlm' );
					$this->error_helper->bmlm_set_error_code( 1 );
					$this->error_helper->bmlm_print_notification( $message );
					return false;
				}

				if ( 'delete' === $this->current_action() ) {
					$lids = empty( $_GET['lid'] ) ? '' : wc_clean( $_GET['lid'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( ! empty( $lids ) && is_array( $lids ) ) {
						foreach ( $lids as $lid ) {
							$this->helper->bmlm_delete_badge( $lid );
						}
						$message = count( $lids ) . ' ' . esc_html__( 'Sponsor badge(s) deleted successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_print_notification( $message );
					} elseif ( ! empty( $lids ) && ! is_array( $lids ) ) {
						$this->helper->bmlm_delete_badge( $lids );
						$message = esc_html__( 'Sponsor badge deleted successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_print_notification( $message );
					} else {
						$message = esc_html__( 'Select sponsor badge(s) to delete.', 'binary-mlm' );
						$this->error_helper->bmlm_set_error_code( 1 );
						$this->error_helper->bmlm_print_notification( $message );
					}
				}
				if ( 'enable' === $this->current_action() ) {
					$lids = empty( $_GET['lid'] ) ? '' : wc_clean( $_GET['lid'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

					if ( ! empty( $lids ) && is_array( $lids ) ) {
						foreach ( $lids as $lid ) {
							$this->helper->bmlm_enable_badge( $lid );
						}

						$message = count( $lids ) . ' ' . esc_html__( 'Sponsor badge(s) enable successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_print_notification( $message );
					} else {
						$message = esc_html__( 'Select sponsor badge(s) to enable.', 'binary-mlm' );
						$this->error_helper->bmlm_set_error_code( 1 );
						$this->error_helper->bmlm_print_notification( $message );
					}
				}
				if ( 'disable' === $this->current_action() ) {
					$lids = empty( $_GET['lid'] ) ? '' : wc_clean( $_GET['lid'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

					if ( ! empty( $lids ) && is_array( $lids ) ) {
						foreach ( $lids as $lid ) {
							$this->helper->bmlm_disable_badge( $lid );
						}

						$message = count( $lids ) . ' ' . esc_html__( 'Sponsor badge(s) disable successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_print_notification( $message );
					} else {
						$message = esc_html__( 'Select sponsor badge(s) to disable.', 'binary-mlm' );
						$this->error_helper->bmlm_set_error_code( 1 );
						$this->error_helper->bmlm_print_notification( $message );
					}
				}
			}
		}

		/**
		 *
		 * Fetch sponsors
		 *
		 * @param array $sponsors_badges Sponsors levels.
		 * @return array $data formatted sponsor levels
		 */
		public function bmlm_get_sponsor_badge_list( $sponsors_badges ) {
			$data = array();
			if ( ! empty( $sponsors_badges ) ) {

				foreach ( $sponsors_badges as $level ) {
					$id           = $level['id'];
					$badge_name   = $level['name'];
					$date         = $level['date'];
					$html         = $this->helper->bmlm_get_badge_status_html( $level['status'] );
					$max_business = wc_price( $level['max_business'] );
					$bonus_amt    = wc_price( $level['bonus_amt'] );
					$member_count = $this->helper->bmlm_get_badge_member_count( $id );

					$data[] = array(
						'id'           => $id,
						'badge'        => $id,
						'badge_name'   => $badge_name,
						'max_business' => $max_business,
						'bonus_amt'    => $bonus_amt,
						'member_count' => $member_count,
						'date'         => $date,
						'status'       => $html,
					);
				}
			}

			return apply_filters( 'bmlm_sponsor_badge_list_data', $data );
		}

		/**
		 * Hidden Columns
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
				'cb'           => '<input type="checkbox" />',
				'badge'        => esc_html__( 'Badge', 'binary-mlm' ),
				'badge_name'   => esc_html__( 'Badge Name', 'binary-mlm' ),
				'max_business' => esc_html__( 'Max Business', 'binary-mlm' ),
				'bonus_amt'    => esc_html__( 'Bonus Amount', 'binary-mlm' ),
				'member_count' => esc_html__( 'Member Count', 'binary-mlm' ),
				'date'         => esc_html__( 'Date Created', 'binary-mlm' ),
				'status'       => esc_html__( 'Status', 'binary-mlm' ),
			);

			return apply_filters( 'bmlm_sponsor_badge_list_columns', $columns );
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
			if ( array_key_exists( $column_name, $item ) ) {
				return $item[ $column_name ];
			}
			return '-';
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'max_business' => array( 'max_business', true ),
				'bonus_amt'    => array( 'bonus_amt', true ),
				'date'         => array( 'date', true ),
				'status'       => array( 'status', true ),
			);

			return apply_filters( 'bmlm_sponsor_badge_list_sortable_columns', $sortable_columns );
		}

		/**
		 * Column actions
		 *
		 * @param array $item Items.
		 * @return array $actions
		 */
		public function column_badge( $item ) {
			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$actions = array(
				'edit'   => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $page_name . '&tab=bmlm_badges&action=edit&lid=' . $item['id'] ) ), esc_html__( 'Edit', 'binary-mlm' ) ), // phpcs:ignore
				'delete' => sprintf( '<a href="%s">%s</a>', wp_nonce_url( 'admin.php?page=' . $page_name . '&tab=bmlm_badges&action=delete&lid=' . esc_attr( $item['id'] ), 'bmlm_sponsor_badge_list_nonce_action', 'bmlm_sponsor_badge_list_nonce' ), esc_html__( 'Delete', 'binary-mlm' ) ),
			);
			return sprintf( '%1$s %2$s', $item['id'], $this->row_actions( apply_filters( 'bmlm_sponsor_badge_list_line_actions', $actions ) ) );
		}

		/**
		 * Render the bulk edit checkbox.
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="lid[]" value="%d" />', esc_attr( $item['id'] ) );
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
					'delete'  => esc_html__( 'Delete', 'binary-mlm' ),
					'enable'  => esc_html__( 'Enable', 'binary-mlm' ),
					'disable' => esc_html__( 'Disable', 'binary-mlm' ),
				)
			);
		}
	}
}
