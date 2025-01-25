<?php
/**
 * Sponsor badge helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Helper\Badges;

use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Badges' ) ) {
	/**
	 * Sponsor badge helper class
	 */
	class BMLM_Badges extends BMLM_Sponsor {

		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Badge id.
		 *
		 * @var integer
		 */
		protected $badge_id;

		/**
		 * Constructor
		 *
		 * @param integer $lid Badge id.
		 */
		public function __construct( $lid = '' ) {
			global $wpdb;
			$this->wpdb     = $wpdb;
			$this->badge_id = $lid;
		}

		/**
		 * Sponsor badges data
		 *
		 * @param array $data Filter data.
		 *
		 * @return array $badges
		 */
		public function bmlm_get_badges( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$badges   = array();
			$where    = '';

			if ( ! empty( $data['s'] ) ) {
				$where = $wpdb_obj->prepare( ' AND name LIKE %s', '%' . $data['s'] . '%' );
			}

			$limit = $wpdb_obj->prepare( ' ORDER BY ' . $data['orderby'] . ' ' . $data['order'] . ' LIMIT %d OFFSET %d', esc_attr( $data['limit'] ), esc_attr( $data['start'] ) );

			$sql    = "SELECT * FROM {$wpdb_obj->prefix}bmlm_sponsor_badge WHERE 1=1 $where $limit"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$badges = $wpdb_obj->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is safe, see above.

			return apply_filters( 'bmlm_sponsor_badge', $badges, $data );
		}

		/**
		 * Sponsor active badges data
		 *
		 * @param array $data Filter data.
		 * @return array $badges
		 */
		public function bmlm_get_active_badges( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$badges   = array();
			$where    = '';

			if ( ! empty( $data['status'] ) ) {
				$where = $wpdb_obj->prepare( ' AND status=%d', esc_attr( $data['status'] ) );
			}
			$sql    = "SELECT id, max_business, bonus_amt FROM {$wpdb_obj->prefix}bmlm_sponsor_badge WHERE 1=1 $where ORDER BY priority ASC";
			$badges = $wpdb_obj->get_results( $sql, ARRAY_A );

			return apply_filters( 'bmlm_sponsor_badge', $badges, $data );
		}

		/**
		 * Sponsor badges Count.
		 *
		 * @param array $data Filter data.
		 *
		 * @return int $total
		 */
		public function bmlm_get_badges_count( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT COUNT(*) FROM {$wpdb_obj->prefix}bmlm_sponsor_badge WHERE 1=1";

			if ( ! empty( $data['s'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND name LIKE %s', '%' . $data['s'] . '%' );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'bmlm_sponsor_badge_count', $total, $data );
		}

		/**
		 * Get badge.
		 *
		 * @param array $id Badge id.
		 *
		 * @return array $badge
		 */
		public function bmlm_get_badge( $id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}bmlm_sponsor_badge WHERE id=%d", $id );
			$badge    = $wpdb_obj->get_row( $sql, ARRAY_A );

			return apply_filters( 'bmlm_sponsor_badge', $badge, $id );
		}

		/**
		 * Generate badge slug.
		 *
		 * @param string $name Badge name.
		 *
		 * @return array $badge
		 */
		public function bmlm_generate_badge_slug( $name ) {
			$name = strtolower( $name );
			$slug = preg_replace( '/\s+/', '-', $name );
			return apply_filters( 'bmlm_modify_slug', $slug, $name );
		}

		/**
		 * Generate badge slug.
		 *
		 * @param string  $new_slug badge slug.
		 * @param integer $badge_id badge.
		 *
		 * @return array $badge
		 */
		public function bmlm_is_duplicate_badge( $new_slug, $badge_id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT id FROM {$wpdb_obj->prefix}bmlm_sponsor_badge WHERE slug=%s", $new_slug );
			if ( ! empty( $badge_id ) ) {
				$sql .= $wpdb_obj->prepare( ' AND id <> %d', $badge_id );
			}
			$id = $wpdb_obj->get_var( $sql );

			return apply_filters( 'bmlm_sponsor_badge_slug', $id ? true : false, $new_slug, $badge_id );
		}

		/**
		 * Get badge member assigned count.
		 *
		 * @param int $badge_id Badge id.
		 *
		 * @return int $count
		 */
		public function bmlm_get_badge_member_count( $badge_id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT COUNT( DISTINCT( user_id ) ) FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s AND meta_value=%d", 'bmlm_badge', $badge_id );
			$count    = $wpdb_obj->get_var( $sql );

			return apply_filters( 'bmlm_sponsor_badge_member_count', $count, $badge_id );
		}

		/**
		 * Map Sponsor badge.
		 *
		 * @param int $user_id User id.
		 * @param int $id Id.
		 *
		 * @return int $id badge id
		 */
		public function bmlm_map_sponsor_badge( $user_id, $id ) {
			$wpdb_obj = $this->wpdb;

			$wpdb_obj->insert(
				$wpdb_obj->prefix . 'bmlm_sponsor_badge_meta',
				array(
					'user_id'  => $user_id,
					'badge_id' => $id,
					'date'     => current_time( 'Y-m-d H:i:s' ),
				),
				array(
					'%d',
					'%d',
					'%s',
				)
			);
			return apply_filters( 'bmlm_sponsor_badge_meta', true, $user_id, $id );
		}

		/**
		 * Create Sponsor badge.
		 *
		 * @param array $data Badge data.
		 *
		 * @return bool
		 */
		public function bmlm_create_badge( $data ) {
			$wpdb_obj = $this->wpdb;
			$slug     = $this->bmlm_generate_badge_slug( $data['bmlm_badge_name'] );

			$wpdb_obj->insert(
				$wpdb_obj->prefix . 'bmlm_sponsor_badge',
				array(
					'name'         => $data['bmlm_badge_name'],
					'slug'         => $slug,
					'max_business' => wc_format_decimal( $data['bmlm_max_business'], 2 ),
					'bonus_amt'    => wc_format_decimal( $data['bmlm_bonus_amt'], 2 ),
					'priority'     => $data['bmlm_priority'],
					'image'        => $data['bmlm_badge_image'],
					'status'       => $data['bmlm_badge_status'],
					'date'         => current_time( 'Y-m-d H:i:s' ),
				),
				array(
					'%s',
					'%s',
					'%f',
					'%f',
					'%d',
					'%d',
					'%d',
					'%s',
				)
			);
			return apply_filters( 'bmlm_create_sponsor_badge', true, $data );
		}

		/**
		 * Update Sponsor badge.
		 *
		 * @param array $data Badge data.
		 *
		 * @return bool
		 */
		public function bmlm_update_badge( $data ) {
			$wpdb_obj = $this->wpdb;
			$slug     = $this->bmlm_generate_badge_slug( $data['bmlm_badge_name'] );

			$wpdb_obj->update(
				$wpdb_obj->prefix . 'bmlm_sponsor_badge',
				array(
					'name'         => $data['bmlm_badge_name'],
					'slug'         => $slug,
					'max_business' => wc_format_decimal( $data['bmlm_max_business'], 2 ),
					'bonus_amt'    => wc_format_decimal( $data['bmlm_bonus_amt'], 2 ),
					'priority'     => $data['bmlm_priority'],
					'image'        => $data['bmlm_badge_image'],
					'status'       => $data['bmlm_badge_status'],
				),
				array(
					'id' => $data['bmlm_badge_id'],
				),
				array(
					'%s',
					'%s',
					'%f',
					'%f',
					'%d',
					'%d',
					'%d',
				),
				array(
					'%d',
				)
			);
			return apply_filters( 'bmlm_update_sponsor_badge', true, $data );
		}

		/**
		 * Delete Sponsor badge
		 *
		 * @param integer $lid Badge id.
		 *
		 * @return bool
		 */
		public function bmlm_delete_badge( $lid ) {
			$wpdb_obj = $this->wpdb;

			$wpdb_obj->delete(
				$wpdb_obj->prefix . 'bmlm_sponsor_badge',
				array(
					'id' => $lid,
				),
				array(
					'%d',
				)
			);
			return apply_filters( 'bmlm_delete_sponsor_badge', true, $lid );
		}

		/**
		 * Enable Sponsor badge
		 *
		 * @param integer $lid Badge id.
		 */
		public function bmlm_enable_badge( $lid ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->update(
				$wpdb_obj->prefix . 'bmlm_sponsor_badge',
				array(
					'status' => 1,
				),
				array(
					'id' => $lid,
				),
				array(
					'%d',
				),
				array(
					'%d',
				)
			);
		}

		/**
		 * Disable Sponsor badge
		 *
		 * @param integer $lid Badge id.
		 */
		public function bmlm_disable_badge( $lid ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->update(
				$wpdb_obj->prefix . 'bmlm_sponsor_badge',
				array(
					'status' => 0,
				),
				array(
					'id' => $lid,
				),
				array(
					'%d',
				),
				array(
					'%d',
				)
			);
		}

		/**
		 * Get sponsor badge html.
		 *
		 * @param bool $status Badge status.
		 *
		 * @return string
		 */
		public function bmlm_get_badge_status_html( $status = 0 ) {
			$status_html = '<mark class="bmlm-status bmlm-status-pending tips"><span>' . esc_html__( 'Disabled', 'binary-mlm' ) . '</span></mark>';

			if ( ! empty( $status ) ) {
				$status_html = '<mark class="bmlm-status bmlm-status-completed tips"><span>' . esc_html__( 'Enabled', 'binary-mlm' ) . '</span></mark>';
			}
			return apply_filters( 'bmlm_modify_sponsor_badge_status_html', $status_html, $status );
		}
	}
}
