<?php
/**
 * Network users helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Helper\NetworkUsers;

use stdClass;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Network_Users' ) ) {
	/**
	 * Network users helper class
	 */
	class BMLM_Network_Users {

		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Sponsor ID.
		 *
		 * @var object
		 */
		protected $sponsor_id;

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function __construct( $sponsor_id = 0 ) {
			global $wpdb;
			$this->wpdb       = $wpdb;
			$this->sponsor_id = $sponsor_id;
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @param int $sponsor_id Sponsor ID.
		 *
		 * @return object
		 */
		public static function get_instance( $sponsor_id = 0 ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $sponsor_id );
			}

			return static::$instance;
		}

		/**
		 * Add user in network users.
		 *
		 * @param int $user_id User id.
		 *
		 * @return void
		 */
		public function bmlm_add_network_user( $user_id = 0 ) {
			$sponsor_id = empty( $user_id ) ? $this->sponsor_id : $user_id;
			$wpdb_obj   = $this->wpdb;
			$user       = new \WP_User( $sponsor_id );

			if ( ! empty( $user ) && in_array( 'bmlm_sponsor', $user->roles, true ) ) {
				$user_meta = get_user_meta( $sponsor_id );

				$network_data                    = array();
				$network_data['ID']              = $sponsor_id;
				$network_data['user_login']      = $user->data->user_login;
				$network_data['display_name']    = $user->data->display_name;
				$network_data['user_email']      = $user->data->user_email;
				$network_data['user_registered'] = $user->data->user_registered;
				$network_data['sponsor_id']      = $user_meta['bmlm_sponsor_id'][0];
				$network_data['tree_level']      = $user_meta['bmlm_tree_level'][0];
				$network_data['refferal_id']     = $user_meta['bmlm_refferal_id'][0];
				$network_data['bmlm_network_id'] = $user_meta['bmlm_network_id'][0];

				$wpdb_obj->insert(
					$wpdb_obj->prefix . 'bmlm_network_users',
					array(
						'user_id'   => $sponsor_id,
						'user_data' => maybe_serialize( $network_data ),
						'status'    => 0,
					),
					array(
						'%d',
						'%s',
						'%d',
					)
				);
			}
		}

		/**
		 * Validate the users data.
		 *
		 * @param array  $users Users.
		 * @param array  $user_ids User ids.
		 * @param object $bmlm_sponsor BMLM_Sponsor object.
		 *
		 * @return array
		 */
		public function bmlm_validate_network_users( $users, $user_ids, $bmlm_sponsor ) {
			if ( ! empty( $user_ids ) ) {
				$user_column = array_column( $users, 'ID' );

				$validated_users = array_map(
					function ( $user_id ) use ( $user_column, $users, $bmlm_sponsor ) {
						if ( in_array( $user_id, $user_column, true ) ) {
							$user_data_key = array_search( $user_id, $user_column, true );
							$result        = $users[ $user_data_key ];

							$result = $this->bmlm_add_sponsor_user_data( $result, $bmlm_sponsor );

							return $result;
						}

						$result = new stdClass();

						if ( empty( $user_id ) ) {
							$result->ID              = '0';
							$result->user_login      = '';
							$result->display_name    = '';
							$result->user_email      = '';
							$result->user_registered = '';
							$result->sponsor_id      = 0;
							$result->level           = '';
							$result->refferal_id     = 0;
							$result->status          = 0;
							$result->id              = '0';

							return $result;
						}

						$user_data = $this->bmlm_get_network_user_data( $user_id );

						$result->ID              = ! empty( $user_data['ID'] ) ? strval( $user_data['ID'] ) : '';
						$result->user_login      = ! empty( $user_data['user_login'] ) ? $user_data['user_login'] : '';
						$result->display_name    = ! empty( $user_data['display_name'] ) ? $user_data['display_name'] : '';
						$result->user_email      = ! empty( $user_data['user_email'] ) ? $user_data['user_email'] : '';
						$result->user_registered = ! empty( $user_data['user_registered'] ) ? $user_data['user_registered'] : '';
						$result->sponsor_id      = ! empty( $user_data['sponsor_id'] ) ? $user_data['sponsor_id'] : '';
						$result->level           = ! empty( $user_data['tree_level'] ) ? $user_data['tree_level'] : '';
						$result->refferal_id     = ! empty( $user_data['refferal_id'] ) ? $user_data['refferal_id'] : '';
						$result->status          = $this->bmlm_get_network_user_status( $user_id );
						$result->id              = ! empty( $user_data['ID'] ) ? strval( $user_data['ID'] ) : '';

						return $result;
					},
					$user_ids
				);

				return $validated_users;
			}
		}

		/**
		 * Get network user status.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return int
		 */
		public function bmlm_get_network_user_status( $sponsor_id ) {
			$sponsor_id = empty( $sponsor_id ) ? $this->sponsor_id : $sponsor_id;
			$wpdb_obj   = $this->wpdb;
			$status     = 0;

			if ( ! empty( $sponsor_id ) ) {
				$status = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT `status` FROM {$wpdb_obj->prefix}bmlm_network_users WHERE `user_id`=%d", $sponsor_id ) );
			}
			return empty( $status ) ? $status : intval( $status );
		}

		/**
		 * Update network user status.
		 *
		 * @param int $sponsor_id Sponsor id.
		 * @param int $status Status.
		 *
		 * @return void
		 */
		public function bmlm_update_network_user_status( $sponsor_id, $status = 0 ) {
			$sponsor_id = empty( $sponsor_id ) ? $this->sponsor_id : $sponsor_id;
			$wpdb_obj   = $this->wpdb;

			if ( ! empty( $sponsor_id ) ) {
				$wpdb_obj->update(
					$wpdb_obj->prefix . 'bmlm_network_users',
					array(
						'status' => $status,
					),
					array(
						'user_id' => $sponsor_id,
					),
					array(
						'%d',
					),
					array(
						'%d',
					)
				);
			}
		}

		/**
		 * Get network user data.
		 *
		 * @param int $user_id User id.
		 *
		 * @return object
		 */
		public function bmlm_get_network_user_data( $user_id ) {
			$sponsor_id = empty( $user_id ) ? $this->sponsor_id : $user_id;
			$wpdb_obj   = $this->wpdb;
			$query      = $wpdb_obj->prepare( "SELECT `user_data` FROM {$wpdb_obj->prefix}bmlm_network_users WHERE `user_id` = %d", $sponsor_id ); // WPCS: unprepared SQL OK.
			$user_data  = $wpdb_obj->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is safe, see above.

			return empty( $user_data ) ? array() : maybe_unserialize( $user_data );
		}

		/**
		 * Update network user data.
		 *
		 * @param int    $sponsor_id Sponsor id.
		 * @param object $user_data User data.
		 */
		public function bmlm_update_network_user_data( $sponsor_id, $user_data ) {
			$wpdb_obj = $this->wpdb;
			if ( ! empty( $user_id ) ) {
				$wpdb_obj->update(
					$wpdb_obj->prefix . 'bmlm_network_users',
					array(
						'user_data' => maybe_serialize( $user_data ),
					),
					array(
						'user_id' => $sponsor_id,
					),
					array(
						'%d',
					),
					array(
						'%d',
					)
				);
			}
		}

		/**
		 * Get network id.
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function bmlm_get_network_user_id( $sponsor_id ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			return empty( $user_data['bmlm_network_id'] ) ? 0 : intval( $user_data['bmlm_network_id'] );
		}

		/**
		 * Update network id.
		 *
		 * @param int    $sponsor_id Sponsor id.
		 * @param object $network_id New network id.
		 */
		public function bmlm_update_network_user_id( $sponsor_id, $network_id ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			if ( ! empty( $user_data ) ) {
				$user_data->bmlm_network_id = $network_id;
				$this->bmlm_update_network_user_data( $sponsor_id, $user_data );
			}
		}

		/**
		 * Get network tree level.
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function bmlm_get_network_user_tree_level( $sponsor_id ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			return empty( $user_data['tree_level'] ) ? 0 : intval( $user_data['tree_level'] );
		}

		/**
		 * Update network tree level.
		 *
		 * @param int $sponsor_id Sponsor id.
		 * @param int $tree_level Network level.
		 */
		public function bmlm_update_network_user_tree_level( $sponsor_id, $tree_level ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			if ( ! empty( $user_data ) ) {
				$user_data->tree_level = $tree_level;
				$this->bmlm_update_network_user_data( $sponsor_id, $user_data );
			}
		}

		/**
		 * Get sponsor referral id.
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function bmlm_get_network_user_referral_code( $sponsor_id ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );

			return empty( $user_data['refferal_id'] ) ? '' : $user_data['refferal_id'];
		}


		/**
		 * Update network tree level.
		 *
		 * @param int $sponsor_id Sponsor id.
		 * @param int $referral_id Referral id.
		 */
		public function bmlm_update_network_user_referral_code( $sponsor_id, $referral_id ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			if ( ! empty( $user_data ) ) {
				$user_data->refferal_id = $referral_id;
				$this->bmlm_update_network_user_data( $sponsor_id, $user_data );
			}
		}

		/**
		 * Get sponsor referral id.
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function bmlm_get_network_user_sponsor_code( $sponsor_id ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			return empty( $user_data['sponsor_id'] ) ? '' : intval( $user_data['sponsor_id'] );
		}

		/**
		 * Update network tree level.
		 *
		 * @param int $sponsor_id Sponsor id.
		 * @param int $sponsor_code Sponsor code.
		 */
		public function bmlm_update_network_user_sponsor_code( $sponsor_id, $sponsor_code ) {
			$user_data = $this->bmlm_get_network_user_data( $sponsor_id );
			if ( ! empty( $user_data ) ) {
				$user_data->sponsor_id = $sponsor_code;
				$this->bmlm_update_network_user_data( $sponsor_id, $user_data );
			}
		}

		/**
		 * Update network tree level.
		 *
		 * @param String $referral_code Referral Code.
		 */
		public function bmlm_get_referral_code_network_users( $referral_code ) {
			$wpdb_obj = $this->wpdb;
			$count    = 0;

			$users_data = $wpdb_obj->get_results( "SELECT `user_data` FROM {$wpdb_obj->prefix}bmlm_network_users", ARRAY_A );

			foreach ( is_iterable( $users_data ) ? $users_data : array() as $data ) {
				$user_data = empty( $data['user_data'] ) ? 0 : $data['user_data'];

				$result = maybe_unserialize( $user_data );

				if ( empty( $result['refferal_id'] ) ) {
					continue;
				}

				if ( $referral_code === $result['refferal_id'] ) {
					++$count;
				}
			}

			return $count;
		}

		/**
		 * Adding sponsor related data to wp user data.
		 *
		 * @param object $user_data User data.
		 * @param object $bmlm_sponsor Sponsor object.
		 *
		 * @return object
		 */
		public function bmlm_add_sponsor_user_data( $user_data, $bmlm_sponsor ) {
			$user_id = empty( $user_data->ID ) ? 0 : intval( $user_data->ID );

			if ( $user_id > 0 ) {
				$user_data->status      = $bmlm_sponsor->bmlm_get_sponsor_status( $user_id );
				$user_data->level       = $bmlm_sponsor->bmlm_get_sponsor_tree_level( $user_id );
				$user_data->refferal_id = $bmlm_sponsor->bmlm_get_referral_code( $user_id );
				$user_data->sponsor_id  = $bmlm_sponsor->bmlm_get_sponsor_code( $user_id );
			}

			return $user_data;
		}
	}
}
