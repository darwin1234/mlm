<?php
/**
 * Commission Hooks.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Commission;

use WCBMLMARKETING\Helper\Badges\BMLM_Badges;
use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Commission' ) ) {
	/**
	 * Commission hooks class.
	 */
	class BMLM_Commission extends BMLM_Sponsor {

		/**
		 * Badge Object
		 *
		 * @var object
		 */
		protected $helper;

		/**
		 * Commission constructor
		 */
		public function __construct() {
			parent::__construct();
			$this->helper = new BMLM_Badges();
			// Product page hooks.
			add_action( 'bmlm_sponsor_load_sale_commission', array( $this, 'bmlm_sponsor_calculate_sale_commission' ), 10, 2 );
			add_action( 'bmlm_sponsor_load_levelup_commission', array( $this, 'bmlm_sponsor_calculate_levelup_commission' ), 10, 3 );
			add_action( 'bmlm_sponsor_load_badge_commission', array( $this, 'bmlm_sponsor_calculate_badge_bonus_commission' ), 10, 1 );
			add_action( 'bmlm_upgrade_parent_sponsor_levels', array( $this, 'bmlm_upgrade_parent_sponsor_levels' ), 10, 2 );
			add_action( 'bmlm_sponsor_load_after_joining_commission', array( $this, 'bmlm_sponsor_load_after_joining_commission' ) );
		}

		/**
		 * Add joining commission.
		 *
		 * @param int $customer_id Customer id.
		 *
		 * @return void
		 */
		public function bmlm_sponsor_load_after_joining_commission( $customer_id ) {
			$is_approved = $this->bmlm_approve_sponsor( $customer_id );
			if ( $is_approved ) {
				$refferal_id = $this->bmlm_get_referral_code( $customer_id );
				if ( $refferal_id ) {
					$rules = $this->bmlm_get_commission_rules();
					$args  = array(
						'user_id' => $customer_id,
						'price'   => $rules['joining']['initial'],
					);

					$list = $this->bmlm_get_sponsor_parents( $args );
				}
			}
		}

		/**
		 * Add sale commission.
		 *
		 * @param array  $cart_items Cart items.
		 * @param object $order order.
		 *
		 * @hooked 'bmlm_sponsor_load_sale_commission' Action hook.
		 */
		public function bmlm_sponsor_calculate_sale_commission( $cart_items, $order ) {

			$customer_id  = $order->get_user_id();
			$sponsor_code = $this->bmlm_get_sponsor_code( $customer_id );
			$rules        = $this->bmlm_get_commission_rules();
			foreach ( $cart_items as $item_id => $item ) {
				$referral_code = wc_get_order_item_meta( $item_id, '_refferal_id', true );

				if ( ! empty( $referral_code ) && ( $sponsor_code !== $referral_code ) ) {

					$user_id = $this->bmlm_get_sponsor_user_id( $referral_code );
					$level   = $this->bmlm_get_sponsor_tree_level( $user_id );

					$args = array(
						'user_id' => $user_id,
						'price'   => $item->get_total(),
					);

					$list = $this->get_parent( $args['user_id'] );
					if ( ! empty( $list ) ) {
						array_push(
							$list,
							array(
								'level'   => $level,
								'user_id' => $user_id,
							)
						);
					} else {
						$list[] = array(
							'level'   => $level,
							'user_id' => $user_id,
						);
					}

					foreach ( $list as $key => $value ) {
						++$list[ $key ]['level'];
					}

					if ( ! empty( $list ) ) {
						$sale_commission = $this->bmlm_calculate_commission( $list, $rules['sale'], $args );
						if ( ! empty( $sale_commission ) ) {
							$custom_args = array(
								'user_id' => $customer_id,
								'price'   => $item->get_total(),
							);

							$formatted_sale_commission = $this->bmlm_format_commission( $sale_commission, 'sale', $custom_args );
							$this->bmlm_add_commission( $formatted_sale_commission );
						}
					}
				}
			}
		}

		/**
		 * Get parent
		 *
		 * @param int $customer_id customer id.
		 * @return array
		 */
		public function get_parent( $customer_id ) {
			global $wpdb;
			$wpdb_obj = $wpdb;
			$query    = $wpdb_obj->prepare( "SELECT umeta.meta_value as level, gtree.parent as user_id FROM {$wpdb_obj->prefix}bmlm_gtree_nodes as gtree JOIN {$wpdb_obj->base_prefix}usermeta  as umeta ON gtree.parent = umeta.user_id WHERE gtree.child=%d AND umeta.meta_key=%s  ORDER BY gtree.parent ASC", intval( $customer_id ), 'bmlm_join_level' );
			$sponsors = $wpdb_obj->get_results( $query, ARRAY_A );

			return $sponsors;
		}

		/**
		 * Add levelup commission.
		 *
		 * @param int    $user_id sponsor user id.
		 * @param double $commission_amount commission amount.
		 * @param int    $level Level.
		 *
		 * @return void
		 */
		public function bmlm_sponsor_calculate_levelup_commission( $user_id, $commission_amount, $level ) {
			$rules = $this->bmlm_get_commission_rules();

			$args = array(
				'user_id' => $user_id,
				'price'   => $commission_amount,
			);

			$list = array(
				array(
					'level'   => $level,
					'user_id' => $user_id,
				),
			);

			$levelup_commission = $this->bmlm_calculate_levelup_commission( $list, $rules['levelup'], $args );

			if ( ! empty( $levelup_commission ) ) {
				$formatted_levelup_commisison = $this->bmlm_format_commission( $levelup_commission, 'levelup', $args );
				$this->bmlm_add_commission( $formatted_levelup_commisison );
			}
		}

		/**
		 * Add Badge Bonus commission.
		 *
		 * @param array $lists List.
		 */
		public function bmlm_sponsor_calculate_badge_bonus_commission( $lists ) {
			$badge_args = array(
				'status' => 1,
			);

			$rules = $this->helper->bmlm_get_active_badges( $badge_args );
			$users = wp_list_pluck( $lists, 'user_id' );
			$users = array_map( 'intval', array_unique( $users ) );

			if ( in_array( 1, $users, true ) ) {
				unset( $users[ array_search( 1, $users, true ) ] );
				$users = ! empty( $users ) ? array_values( $users ) : array();
			}

			if ( ! empty( $users ) && ! empty( $rules ) ) {
				$sponsors         = $this->bmlm_sponsors_get_gross_business( $users, $rules );
				$badge_commission = $this->bmlm_calculate_bonus_commission( $sponsors, $rules );
				if ( ! empty( $badge_commission ) ) {
					$formatted_bonus_commisison = $this->bmlm_format_commission( $badge_commission, 'bonus', array() );
					$this->bmlm_add_commission( $formatted_bonus_commisison );
				}
			}
		}

		/**
		 * Upgrade level.
		 *
		 * @param array $sponsor_user_id sponsor user id.
		 */
		public function bmlm_upgrade_parent_sponsor_levels( $sponsor_user_id ) {
			if ( ! empty( $sponsor_user_id ) ) {
				$this->bmlm_upgrade_sponsors_level( $sponsor_user_id );
			}
		}
	}
}
