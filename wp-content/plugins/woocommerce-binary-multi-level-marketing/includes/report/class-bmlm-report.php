<?php
/**
 * Dashboard Report.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Report;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Report' ) ) {
	/**
	 * Sponsor report data class.
	 */
	class BMLM_Report {

		/**
		 * Sponsor class object.
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * DB global variable.
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Commission table variable.
		 *
		 * @var string
		 */
		protected $commission_table;

		/**
		 * Start date.
		 *
		 * @var $start_date string
		 */
		protected $start_date;

		/**
		 * End date.
		 *
		 * @var $end_date string
		 */
		protected $end_date;
		/**
		 * Construct function.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb             = $wpdb;
			$this->commission_table = $this->wpdb->prefix . 'bmlm_commission';
		}

		/**
		 * Get all delivery boys sales report.
		 *
		 * @param array $args Arguments.
		 *
		 * @return array
		 */
		public function bmlm_get_report( $args ) {
			$periods              = array();
			$range_data           = array();
			$requested_start_date = ! empty( $args['start_date'] ) ? sanitize_text_field( $args['start_date'] ) : '';
			$requested_end_date   = ! empty( $args['end_date'] ) ? sanitize_text_field( $args['end_date'] ) : '';
			$requested_date_type  = ! empty( $args['type'] ) ? sanitize_text_field( $args['type'] ) : 'monthly';
			$this->bmlm_set_dates( $requested_start_date, $requested_end_date, $requested_date_type );
			$data = $this->bmlm_sponsor_get_earning_report( $args );
			if ( ! empty( $data ) ) {
				foreach ( $data as $week_value ) {
					$key   = new \DateTime( $week_value['Date_Time'] );
					$strip = $key->format( 'Y-m-d' );
					if ( ! isset( $range_data[ $strip ] ) ) {
						$range_data[ $strip ] = wc_format_decimal( 0 );
					}
					$range_data[ $strip ] += wc_format_decimal( $week_value['commission'] );
				}
			}

			$data_value = $this->bmlm_get_dates_from_range( $this->start_date, $this->end_date );

			foreach ( $data_value as $dateval ) {

				$commission_amount = 0;
				if ( isset( $range_data[ $dateval ] ) ) {
					$commission_amount = $range_data[ $dateval ];
				}
				$periods[] = array(
					'x' => $dateval,
					'y' => wc_format_decimal( $commission_amount, 2 ),
				);
			}
			return apply_filters( 'bmlm_business_report', $periods );
		}

		/**
		 * Get date range.
		 *
		 * @param string $start Start date.
		 * @param string $end End date.
		 * @param string $format Format.
		 *
		 * @return array
		 */
		public function bmlm_get_dates_from_range( $start, $end, $format = 'Y-m-d' ) {
			// Declare an empty array.
			$array = array();

			// Variable that store the date interval of period 1 day.
			$interval = new \DateInterval( 'P1D' );

			$real_end = new \DateTime( $end );
			$real_end->add( $interval );

			$period = new \DatePeriod( new \DateTime( $start ), $interval, $real_end );

			// Use loop to store date into array.
			foreach ( $period as $date ) {
				$array[] = $date->format( $format );
			}

			// Return the array elements.
			return $array;
		}

		/**
		 * Set dates.
		 *
		 * @param string $start_date Start date.
		 * @param string $end_date End date.
		 * @param string $requested_date_type filter type.
		 *
		 * @return bool
		 */
		public function bmlm_set_dates( $start_date = '', $end_date = '', $requested_date_type ) {
			$current_timestamp = strtotime( 'now' );
			switch ( $requested_date_type ) {
				case 'weekly':
					$this->start_date = gmdate( 'Y-m-d 00:00:00', strtotime( 'midnight -7 days', $current_timestamp ) );
					$this->end_date   = gmdate( 'Y-m-d', $current_timestamp ) . ' 23:59:00';
					break;
				case 'monthly':
					$this->start_date = gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d' ) ) ) . ' - 1 month' ) ) . ' 00:00:00';
					$this->end_date   = gmdate( 'Y-m-d', $current_timestamp ) . ' 23:59:00';
					break;
				case 'yearly':
					$this->start_date = $start_date;
					$this->end_date   = $end_date;
					break;
				default:
					break;
			}
			return true;
		}

		/**
		 * Get sponsor gross business
		 *
		 * @param array $args Sponsor arguments.
		 *
		 * @return array $gross_business.
		 */
		public function bmlm_sponsor_get_earning_report( $args ) {
			$sponsor_user_id = $args['user_id'];
			$wpdb_obj        = $this->wpdb;
			$where           = '';
			$type            = ! empty( $args['type'] ) ? $args['type'] : '';
			if ( ! empty( $type ) ) {
				$where .= $wpdb_obj->prepare( ' AND type=%s', $type );
			}
			if ( ! empty( $sponsor_user_id ) ) {
				$where .= $wpdb_obj->prepare( ' AND user_id=%d', $sponsor_user_id );
			}
			if ( ! empty( $current ) ) {
				$where .= $wpdb_obj->prepare( ' AND Date_Time >= %s AND Date_Time <= %s', $this->start_date, $this->end_date );
			}

			$query          = "SELECT date as Date_Time, SUM(commission) as commission FROM $this->commission_table WHERE 1=1 $where GROUP BY Date_Time
			ORDER BY Date_Time ASC";
			$gross_business = $wpdb_obj->get_results( $query, ARRAY_A );
			return apply_filters( 'bmlm_sponsor_gross_business_report', $gross_business, $args );
		}
	}
}
