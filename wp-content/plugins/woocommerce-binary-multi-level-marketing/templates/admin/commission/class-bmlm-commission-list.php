<?php
/**
 * Commission List template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Commission;

use WCBMLMARKETING\Inc\BMLM_Errors;
use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;
use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Commission_List' ) ) {
	/**
	 * Commission list class
	 */
	class BMLM_Commission_List extends \WP_List_Table {

		/**
		 * Commission Helper Variable
		 *
		 * @var object
		 */
		protected $commission_helper;

		/**
		 * Commission type
		 *
		 * @var string
		 */
		protected $commssion_type;

		/**
		 * Error Helper Variable
		 *
		 * @var object
		 */
		protected $error_helper;

		/**
		 * Class constructor.
		 *
		 * @param string $type Commission type.
		 */
		public function __construct( $type ) {
			$this->commssion_type    = $type;
			$this->commission_helper = BMLM_Commission_Helper::get_instance();
			$this->error_helper      = new BMLM_Errors();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Commission List', 'binary-mlm' ),
					'plural'   => esc_html__( 'commission List', 'binary-mlm' ),
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
			$current_page = $this->get_pagenum();
			$per_page     = $this->get_items_per_page( 'bmlm_per_page', 20 );
			$offset       = ( $current_page - 1 ) * $per_page;

			$order                = empty( $_GET['order'] ) ? 'DESC' : wc_clean( $_GET['order'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby              = empty( $_GET['orderby'] ) ? 'id' : wc_clean( $_GET['orderby'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$search               = empty( $_GET['s'] ) ? '' : wc_clean( $_GET['s'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$commission_from_date = empty( $_GET['bmlm-commission-from-date'] ) ? '' : wc_clean( $_GET['bmlm-commission-from-date'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$commission_to_date   = empty( $_GET['bmlm-commission-to-date'] ) ? '' : wc_clean( $_GET['bmlm-commission-to-date'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$commission_type      = ! empty( $this->commssion_type ) && 'all' === $this->commssion_type ? '' : $this->commssion_type;

			$args = array(
				'start'   => $offset,
				'limit'   => $per_page,
				'type'    => $commission_type,
				'from'    => $commission_from_date,
				'to'      => $commission_to_date,
				'orderby' => $orderby,
				'order'   => $order,
			);

			if ( ! empty( $search ) ) {
				$user            = get_user_by( 'email', $search );
				$args['user_id'] = -1;

				if ( ! empty( $user ) ) {
					$args['user_id'] = $user->ID;
				}
			}

			$total_items = $this->commission_helper->bmlm_get_all_commission_count( $args );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);
			$data        = $this->bmlm_get_commission( $args );
			$this->items = $data;
		}

		/**
		 * Process bulk actions.
		 *
		 * @return void|bool
		 */
		public function process_bulk_action() {
			$nonce = empty( $_GET['bmlm_commission_list_nonce'] ) ? '' : wc_clean( $_GET['bmlm_commission_list_nonce'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $nonce ) ) {
				if ( ! wp_verify_nonce( $nonce, 'bmlm_commission_list_nonce_action' ) ) {
					$message = esc_html__( 'Invalid nonce. Security check failed!!!', 'binary-mlm' );
					$this->error_helper->bmlm_set_error_code( 1 );
					$this->error_helper->bmlm_print_notification( $message );
					return false;
				}

				$commission_ids = empty( $_GET['bmlm-commission-id'] ) ? array() : wp_unslash( wc_clean( $_GET['bmlm-commission-id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$commission_ids = ( ! empty( $commission_ids ) && ! is_array( $commission_ids ) && intval( $commission_ids ) > 0 ) ? intval( $commission_ids ) : array_map( 'intval', $commission_ids );

				if ( 'delete' === $this->current_action() ) {
					if ( ! empty( $commission_ids ) && is_array( $commission_ids ) ) {

						foreach ( $commission_ids as $commission_id ) {
							$response = $this->commission_helper->bmlm_delete_commission( $commission_id );
						}

						$message = count( $commission_ids ) . ' ' . esc_html__( 'Commission(s) deleted successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_print_notification( $message );
					}
				} elseif ( 'pay' === $this->current_action() ) {
					$transaction = BMLM_Transaction::get_instance();

					if ( ! empty( $commission_ids ) ) {
						$users = $this->commission_helper->bmlm_get_commission_users( $commission_ids );

						foreach ( $commission_ids as $commission_id ) {
							$commission = $this->commission_helper->bmlm_get_commission( $commission_id );
							if ( ! empty( $commission ) ) {
								$transaction->bmlm_setup_transaction( $commission, $this->commission_helper );
							}
						}

						do_action( 'bmlm_sponsor_load_badge_commission', $users );
						$message = esc_html__( 'Commission(s) paid successfully.', 'binary-mlm' );
						$this->error_helper->bmlm_set_error_code( 0 );
						$this->error_helper->bmlm_print_notification( $message );
					} else {
						$message = esc_html__( 'Select commission(s) to pay.', 'binary-mlm' );
						$this->error_helper->bmlm_set_error_code( 1 );
						$this->error_helper->bmlm_print_notification( $message );
					}
				}
			}
		}

		/**
		 * Fetch commission.
		 *
		 * @param array $args Arguments.
		 *
		 * @return array
		 */
		public function bmlm_get_commission( $args ) {
			$data        = array();
			$commissions = $this->commission_helper->bmlm_get_all_commission( $args );

			if ( ! empty( $commissions ) ) {
				foreach ( $commissions as $commission ) {
					$commission_id = $commission['id'];
					$customer_id   = $commission['user_id'];
					$customer      = get_user_by( 'ID', $customer_id );
					if ( ! empty( $customer ) ) {
						$customer_url = 'mailto:' . $customer->user_email;
						$email        = $customer->user_email . ' (#' . $customer_id . ' )';
						if ( ! empty( $commission['paid'] ) ) {
							$paid = '<a href="#" class="button" disabled>' . esc_html__( 'Paid', 'binary-mlm' ) . '</a>';
						} else {
							$paid = '<a href="#" class="button bmlm-pay-commission" data-cid="' . esc_attr( $commission_id ) . '">' . esc_html__( 'Pay', 'binary-mlm' ) . '</a>';
						}
						$html   = $this->commission_helper->bmlm_get_commission_type_html( $commission['type'] );
						$data[] = array(
							'id'          => $commission_id,
							'sid'         => $customer_id,
							'user_id'     => '<a href="' . esc_url( $customer_url ) . '">' . esc_html( $email ) . '</a>',
							'type'        => $html,
							'description' => $commission['description'],
							'date'        => date_i18n( 'F d, Y g:i:s A', strtotime( $commission['date'] ) ),
							'commission'  => wc_price( $commission['commission'] ),
							'paid'        => $paid,
						);
					}
				}
			}

			return apply_filters( 'bmlm_commission_list_data', $data );
		}

		/**
		 * No items.
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No commission available.', 'binary-mlm' );
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
		 *  Associative array of columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'          => '<input type="checkbox" />',
				'user_id'     => esc_html__( 'Sponsor', 'binary-mlm' ),
				'type'        => esc_html__( 'Commission Type', 'binary-mlm' ),
				'description' => esc_html__( 'Description', 'binary-mlm' ),
				'date'        => esc_html__( 'Date', 'binary-mlm' ),
				'commission'  => esc_html__( 'Commission', 'binary-mlm' ),
				'paid'        => esc_html__( 'Action', 'binary-mlm' ),
			);

			return apply_filters( 'bmlm_commission_list_columns', $columns );
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
				'paid' => array( 'paid', true ),
			);

			return apply_filters( 'bmlm_commission_list_sortable_columns', $sortable_columns );
		}

		/**
		 * Column actions
		 *
		 * @param array $item Items.
		 *
		 * @return array $actions
		 */
		public function column_user_id( $item ) {
			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$actions = array(
				'view'   => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=bmlm_sponsors&action=manage&section=sponsor-general&sponsor_id=' . $item['sid'] ) ), esc_html__( 'View', 'binary-mlm' ) ),
				'delete' => sprintf( '<a href="%s">%s</a>', wp_nonce_url( 'admin.php?page=' . $page_name . '&action=delete&bmlm-commission-id=' . esc_attr( $item['id'] ), 'bmlm_commission_list_nonce_action', 'bmlm_commission_list_nonce' ), esc_html__( 'Delete', 'binary-mlm' ) ),
			);
			return sprintf( '%1$s %2$s', $item['user_id'], $this->row_actions( apply_filters( 'bmlm_commission_list_line_actions', $actions ) ) );
		}

		/**
		 * Render the bulk edit checkbox.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="bmlm-commission-id[]" value="%d" />', esc_attr( $item['id'] ) );
		}

		/**
		 * Bulk actions on list.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			return apply_filters(
				'bmlm_modify_bulk_actions_in_commission',
				array(
					'pay'    => esc_html__( 'Pay', 'binary-mlm' ),
					'delete' => esc_html__( 'Delete', 'binary-mlm' ),
				)
			);
		}

		/**
		 * Commission List Filters
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				$commission_from_date = empty( $_GET['bmlm-commission-from-date'] ) ? '' : wc_clean( $_GET['bmlm-commission-from-date'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$commission_to_date   = empty( $_GET['bmlm-commission-to-date'] ) ? '' : wc_clean( $_GET['bmlm-commission-to-date'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="alignleft actions bulkactions bmlm-commission-bulk-actions">
					<label for="bmlm-commission-from-date"><?php esc_html_e( 'From:', 'binary-mlm' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $commission_from_date ); ?>" name="bmlm-commission-from-date" id="bmlm-commission-from-date" placeholder="yyyy-mm-dd" autocomplete="off" max=<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?> />

					<label for="bmlm-commission-to-date"><?php esc_html_e( 'To:', 'binary-mlm' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $commission_to_date ); ?>" name="bmlm-commission-to-date" id="bmlm-commission-to-date" placeholder="yyyy-mm-dd" autocomplete="off"  max=<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>/>

					<input type="submit" value="<?php esc_attr_e( 'Filter', 'binary-mlm' ); ?>" name="commission" class="button"  />
				</div>
				<?php
			}
		}
	}
}
