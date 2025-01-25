<?php
/**
 * Sponsor Product helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Helper\Sponsor\Products;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Products' ) ) {
	/**
	 * Sponsor helper class
	 */
	class BMLM_Products {

		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Sponsor table Variable
		 *
		 * @var string
		 */

		/**
		 * Construct
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Sponsor Products data.
		 *
		 * @param array $data Filter data.
		 *
		 * @return array $products
		 */
		public function bmlm_get_sponsor_products( $data = array() ) {
			$wpdb_obj       = $this->wpdb;
			$membership     = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$wallet_product = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
			$wallet_id      = $wallet_product->ID;
			$membership_id  = $membership->ID;
			$products       = array();
			$ids            = array( $wallet_id, $membership_id );
			$sql            = $wpdb_obj->prepare( "SELECT ID, post_title, post_status FROM {$wpdb_obj->prefix}posts WHERE post_type = %s AND post_status = %s AND ID NOT IN (" . implode( ',', array_filter( $ids, 'strlen' ) ) . ')', 'product', 'publish' );
			if ( ! empty( $data['filter_name'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND post_title LIKE %s', '%' . $data['filter_name'] . '%' );
			}

			$sql     .= $wpdb_obj->prepare( ' LIMIT %d OFFSET %d', esc_attr( $data['limit'] ), esc_attr( $data['start'] ) );
			$products = $wpdb_obj->get_results( $sql, ARRAY_A );

			return apply_filters( 'bmlm_get_sponsor_products', $products );
		}

		/**
		 * Sponsor Products Count
		 *
		 * @param array $data Filter data.
		 *
		 * @return int $total
		 */
		public function bmlm_get_sponsor_product_count( $data = array() ) {
			$wpdb_obj       = $this->wpdb;
			$membership     = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$wallet_product = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
			$wallet_id      = $wallet_product->ID;
			$membership_id  = $membership->ID;
			$ids            = array( $wallet_id, $membership_id );
			$sql            = $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$wpdb_obj->prefix}posts WHERE post_type = %s AND post_status = %s AND ID NOT IN (" . implode( ',', array_filter( $ids, 'strlen' ) ) . ')', 'product', 'publish' );

			if ( ! empty( $data['filter_name'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND post_title LIKE %s', '%' . $data['filter_name'] . '%' );
			}
			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'bmlm_get_sponsor_total_products', $total );
		}
	}
}
