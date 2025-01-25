<?php
/**
 * Sponsor commission records.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs;

use WCBMLMARKETING\Inc\BMLM_Errors;
use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Sponsor_Commission' ) ) {
	/**
	 * Sponsor commission class
	 */
	class BMLM_Sponsor_Commission extends \WP_List_Table {

		/**
		 * Sponsor Helper Variable
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
		 * Sponsor id.
		 *
		 * @var int Sponsor id.
		 */
		protected $sponsor_id;

		/**
		 * Class constructor
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function __construct( $sponsor_id ) {
			$this->sponsor_id   = $sponsor_id;
			$this->helper       = BMLM_Commission_Helper::get_instance();
			$this->error_helper = new BMLM_Errors();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Sponsor Commission List', 'binary-mlm' ),
					'plural'   => esc_html__( 'Sponsor commission List', 'binary-mlm' ),
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
			$this->process_row_action();

			$commission_from_date = empty( $_GET['bmlm-commission-from-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm-commission-from-date'] ) ) );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$commission_to_date   = empty( $_GET['bmlm-commission-to-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm-commission-to-date'] ) ) );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$commission_type      = empty( $_GET['bmlm-commission-type'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['bmlm-commission-type'] ) ) );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order                = empty( $_GET['order'] ) ? 'DESC' : htmlspecialchars( wp_unslash( wc_clean( $_GET['order'] ) ) );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby              = empty( $_GET['orderby'] ) ? 'id' : htmlspecialchars( wp_unslash( wc_clean( $_GET['orderby'] ) ) );//phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$current_page = $this->get_pagenum();
			$per_page     = $this->get_items_per_page( 'bmlm_per_page', 10 );
			$offset       = ( $current_page - 1 ) * $per_page;

			$args = array(
				'start'   => $offset,
				'limit'   => $per_page,
				'type'    => $commission_type,
				'user_id' => $this->sponsor_id,
				'from'    => $commission_from_date,
				'to'      => $commission_to_date,
				'orderby' => $orderby,
				'order'   => $order,
			);

			$total_items = $this->helper->bmlm_get_all_commission_count( $args );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$data = $this->bmlm_get_sponsor_commission( $args );

			usort( $data, array( $this, 'bmlm_usort_reorder' ) );
			$this->items = $data;
		}

		/**
		 * User sorting.
		 *
		 * @param array $a First argument.
		 * @param array $b Second argument.
		 *
		 * @return float|int
		 */
		public function bmlm_usort_reorder( $a, $b ) {
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = empty( $request_data['orderby'] ) ? 'id' : $request_data['orderby']; // If no sort, default to date.
			$order        = empty( $request_data['order'] ) ? 'DESC' : $request_data['order']; // If no order, default to asc.
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort.
		}

		/**
		 * Fetch commission.
		 *
		 * @param array $args Arguments.
		 *
		 * @return array $commission
		 */
		public function bmlm_get_sponsor_commission( $args ) {
			$data = array();

			$commissions = $this->helper->bmlm_get_all_commission( $args );

			if ( ! empty( $commissions ) ) {
				foreach ( $commissions as  $commission ) {
					$commission_id = $commission['id'];
					$customer_id   = $commission['user_id'];
					$customer      = get_user_by( 'ID', $customer_id );

					if ( ! empty( $customer ) ) {
						if ( ! empty( $commission['paid'] ) ) {
							$paid = '<a href="#" class="button" disabled>' . esc_html__( 'Paid', 'binary-mlm' ) . '</a>';
						} else {
							$paid = '<a href="#" class="button">' . esc_html__( 'Unpaid', 'binary-mlm' ) . '</a>';
						}

						$data[] = array(
							'id'          => $commission_id,
							'type'        => ucfirst( $commission['type'] ),
							'description' => $commission['description'],
							'date'        => date_i18n( 'F d, Y g:i:s A', strtotime( $commission['date'] ) ),
							'commission'  => wc_price( $commission['commission'] ),
							'paid'        => $paid,
						);
					}
				}
			}

			return apply_filters( 'bmlm_sponsor_commission_list_data', $data );
		}

		/**
		 * No items.
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No commission avaliable.', 'binary-mlm' );
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
		 * Associative array of columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'          => '',
				'type'        => esc_html__( 'Commission Type', 'binary-mlm' ),
				'description' => esc_html__( 'Description', 'binary-mlm' ),
				'date'        => esc_html__( 'Date', 'binary-mlm' ),
				'commission'  => esc_html__( 'Commission', 'binary-mlm' ),
				'paid'        => esc_html__( 'Action', 'binary-mlm' ),
			);
			return apply_filters( 'bmlm_sponsor_commission_list_columns', $columns );
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
			$response = '-';
			if ( array_key_exists( $column_name, $item ) ) {
				$response = $item[ $column_name ];
			}

			return $response;
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'id'         => array( 'id', true ),
				'commission' => array( 'commission', true ),
				'date'       => array( 'date', true ),
				'type'       => array( 'type', true ),
			);

			return apply_filters( 'bmlm_sponsor_commission_list_sortable_columns', $sortable_columns );
		}


		/**
		 * Render the bulk edit checkbox.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return '';
		}

		/**
		 * Sponsor commission List Filters
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			$all_commission_types = array(
				''        => esc_html__( 'Commission Type', 'binary-mlm' ),
				'sale'    => esc_html__( 'Sales', 'binary-mlm' ),
				'joining' => esc_html__( 'Joining', 'binary-mlm' ),
				'levelup' => esc_html__( 'Levelup', 'binary-mlm' ),
			);

			$all_commission_types = apply_filters( 'bmlm_modify_commission_types_for_filter', $all_commission_types );

			if ( 'top' === $which ) {
				$get_data             = isset( $_GET ) ? wc_clean( $_GET ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$commission_from_date = empty( $get_data['bmlm-commission-from-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['bmlm-commission-from-date'] ) ) );
				$commission_to_date   = empty( $get_data['bmlm-commission-to-date'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['bmlm-commission-to-date'] ) ) );
				$commission_type      = empty( $get_data['bmlm-commission-type'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $get_data['bmlm-commission-type'] ) ) );
				?>
				<div class="alignleft actions bulkactions bmlm-commission-bulk-actions">
					<select name="bmlm-commission-type" class="bmlm-commission-type">
						<?php
						if ( ! empty( $all_commission_types ) ) {
							foreach ( $all_commission_types as $key => $value ) {
								$selected = $key === $commission_type ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $value ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<label for="bmlm-commission-from-date"><?php esc_html_e( 'From:', 'binary-mlm' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $commission_from_date ); ?>" name="bmlm-commission-from-date" id="bmlm-commission-from-date" placeholder="yyyy-mm-dd" autocomplete="off" max=<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>/>

					<label for="bmlm-commission-to-date"><?php esc_html_e( 'To:', 'binary-mlm' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $commission_to_date ); ?>" name="bmlm-commission-to-date" id="bmlm-commission-to-date" placeholder="yyyy-mm-dd" autocomplete="off" max=<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>/>
					<input type="submit" value="<?php esc_attr_e( 'Filter', 'binary-mlm' ); ?>" name="commission" class="button" />
				</div>
				<?php
			}
		}
	}
}
