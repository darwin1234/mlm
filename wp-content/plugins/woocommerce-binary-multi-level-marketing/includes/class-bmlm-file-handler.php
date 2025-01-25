<?php
/**
 * File Handler.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

use WCBMLMARKETING\Helper\Wallet\BMLM_Wallet;
use WCBMLMARKETING\Helper\NetworkUsers\BMLM_Network_Users;
use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;
use WCBMLMARKETING\Helper\Commission as comm;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * File handler class.
 */
class BMLM_File_Handler {

	/**
	 * File handler construct.
	 */
	public function __construct() {
		if ( is_admin() ) {
			new BMLM_Virtual_Products();
			new Admin\BMLM_Admin_Hooks();
		} else {
			new Cart\BMLM_Cart();
			new Account\BMLM_Account();
		}
		new BMLM_Emails();
		new BMLM_Scripts();
		new BMLM_Query_Vars();
		new Ajax\BMLM_Ajax_Hooks();
		new Commission\BMLM_Commission();
		add_action( 'woocommerce_order_status_completed', array( $this, 'bmlm_on_checkout_order_complete' ), 10, 1 );
		add_action( 'after_setup_theme', array( $this, 'bmlm_remove_admin_bar_from_front_end' ) );
	}

	/**
	 * On order create.
	 *
	 * @param int $order_id order id.
	 *
	 * @hooked 'woocommerce_order_status_completed' action hook.
	 *
	 * @return void|bool
	 */
	public function bmlm_on_checkout_order_complete( $order_id ) {

		$order = wc_get_order( $order_id );

		$wallet_credited = $order->get_meta( '_bmlm_wallet_credited', true );
		$is_calculated   = $order->get_meta( '_commission_calculated', true );

		if ( ! empty( $wallet_credited ) && ! empty( $is_calculated ) ) {
			return false;
		}

		$order       = wc_get_order( $order_id );
		$order_items = ( $order instanceof \WC_Order ) ? $order->get_items() : array();

		if ( empty( $order_items ) ) {
			return false;
		}

		$membership     = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
		$membership_id  = empty( $membership->ID ) ? 0 : $membership->ID;
		$wallet_product = get_page_by_path( 'wkwc_wallet', OBJECT, 'product' );
		$wallet_id      = empty( $wallet_product->ID ) ? 0 : $wallet_product->ID;
		$is_membership  = false;
		$is_wallet      = false;

		foreach ( $order_items as $item ) {
			$product_id = $item->get_product_id();
			if ( intval( $product_id ) === intval( $membership_id ) ) {
				$is_membership = true;
			}

			if ( intval( $product_id ) === intval( $wallet_id ) ) {
				$is_wallet = true;
			}
		}
		$customer_id = $order->get_customer_id();
		update_user_meta( $customer_id, '_approved', true );
		bmlm_wc_log( "Order id: $order_id, Customer id: $customer_id, Is membership: $is_membership, Is wallet: $is_wallet, Wallet id: $wallet_id, Membership id: $membership_id" );

		if ( ! empty( $customer_id ) && $is_membership ) {
			$network_user = BMLM_Network_Users::get_instance( $customer_id );
			$network_user->bmlm_update_network_user_status( $customer_id, 1 );
		}

		if ( empty( $is_calculated ) ) {
			do_action( 'bmlm_sponsor_load_sale_commission', $order_items, $order );
			$order->update_meta_data( '_commission_calculated', true );
			if ( ! empty( $customer_id ) && $is_membership ) {
				do_action( 'bmlm_upgrade_parent_sponsor_levels', $customer_id );
				do_action( 'bmlm_sponsor_load_joining_commission', $customer_id ); // Based on the complete tree.
				$this->bmlm_mange_joining_level( $customer_id );
				do_action( 'bmlm_sponsor_load_after_joining_commission', $customer_id );
			}
		}
		$order->save();
	}

	/**
	 * Joining level manage
	 *
	 * @param int $customer_id Customer id.
	 * @return void
	 */
	public function bmlm_mange_joining_level( $customer_id ) {
		global $wpdb;
		$wpdb_obj    = $wpdb;
		$obj         = new BMLM_Sponsor();
		$is_approved = $obj->bmlm_approve_sponsor( $customer_id );
		if ( $is_approved ) {
			$referral_id = $obj->bmlm_get_referral_code( $customer_id );
			update_user_meta( $customer_id, 'bmlm_join_level', 0 );
			if ( $referral_id ) {
				$parent_id = $this->bmlm_get_sponsor_parent( $referral_id );
				$table     = $wpdb_obj->prefix . 'bmlm_gtree_nodes';
				$data      = $wpdb_obj->get_results( "select * from $table where parent ='$parent_id' ", ARRAY_A );
				$total     = ! empty( count( $data ) ) ? count( $data ) : 0;

				$get_parent = $this->get_parent( $customer_id );
				foreach ( $get_parent as $key => $parent_data ) {
					$join_level = get_user_meta( $parent_data['user_id'], 'bmlm_join_level', true );
					$join_level = (int) ( count( $get_parent ) - $key );
					update_user_meta( $parent_data['user_id'], 'bmlm_join_level', $join_level );
				}
			}
			$this->bmlm_update_joining_commission( $customer_id );
		}
	}

	/**
	 * Update commission
	 *
	 * @param int $customer_id customer id.
	 * @return void
	 */
	public function bmlm_update_joining_commission( $customer_id ) {

		$object = new comm\BMLM_Commission_Helper();

		$list = $this->get_parent( $customer_id );

		$rules = $object->bmlm_get_commission_rules();

		$args = array(
			'user_id' => $customer_id,
			'price'   => $rules['joining']['initial'],
		);

		$joining_commisison = $object->bmlm_calculate_commission( $list, $rules['joining'], $args );

		if ( ! empty( $joining_commisison ) ) {

			$formatted_joining_commisison = $object->bmlm_format_commission( $joining_commisison, 'joining', $args );

			$object->bmlm_add_commission( $formatted_joining_commisison );
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
	 * Get sponsor parent id.
	 *
	 * @param string $referral_id referral id.
	 *
	 * @return array
	 */
	public function bmlm_get_sponsor_parent( $referral_id ) {
		global $wpdb;
		$wpdb_obj = $wpdb;
		$table    = $wpdb_obj->prefix . 'usermeta';
		$get      = $wpdb_obj->get_row( "select * from $table where meta_key = 'bmlm_sponsor_id' && meta_value = '$referral_id' ", ARRAY_A );
		return ! empty( $get['user_id'] ) ? $get['user_id'] : 0;
	}

	/**
	 * Remove the admin bar on the front end for all users
	 */
	public function bmlm_remove_admin_bar_from_front_end() {

		$user_id = get_current_user_id();

		// Get user data based on the user ID.
		$user_data = get_userdata( $user_id );

		// Check if user data is available.
		if ( $user_data ) {
			// Get the user roles.
			$user_roles = $user_data->roles;
			// Check if the 'administrator' role is present.
			if ( ! empty( $user_roles ) && in_array( 'bmlm_sponsor', $user_roles, true ) ) {
				show_admin_bar( false );
			}
		}
	}
}
