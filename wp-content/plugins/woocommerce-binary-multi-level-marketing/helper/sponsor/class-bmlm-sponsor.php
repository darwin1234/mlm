<?php
/**
 * Sponsor helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Helper\Sponsor;

use WCBMLMARKETING\Helper\Commission\BMLM_Commission_Helper;
use WCBMLMARKETING\Helper\NetworkUsers\BMLM_Network_Users;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'BMLM_Sponsor' ) ) {
	/**
	 * Sponsor helper class
	 */
	class BMLM_Sponsor extends BMLM_Commission_Helper {
		/**
		 * Sponsor id.
		 *
		 * @var int Sponsor id.
		 */
		protected $sponsor_id;

		/**
		 * Is admin end.
		 *
		 * @var boolean $is_admin Is admin.
		 */
		protected $is_admin;

		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Network user table object
		 *
		 * @var object
		 */
		protected $network_user;

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 * @since 1.1.0
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * @param int $sponsor_id Sponsor id.
		 */
		public function __construct( $sponsor_id = 0 ) {
			global $wpdb;

			parent::__construct();

			$this->wpdb     = $wpdb;
			$this->is_admin = is_admin();

			$this->sponsor_id   = empty( $sponsor_id ) ? get_current_user_id() : $sponsor_id;
			$this->network_user = BMLM_Network_Users::get_instance( $this->sponsor_id );
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
		 * Sponsor user id.
		 *
		 * @return int
		 */
		public function bmlm_get_id() {
			return $this->sponsor_id;
		}

		/**
		 * Upgrade sponsor tree node.
		 *
		 * @param int $user_id Sponsor id.
		 * @param int $node Sponsor node.
		 * @return void
		 */
		public function bmlm_upgrade_network_id( $user_id, $node ) {
			if ( get_userdata( $user_id ) instanceof \WP_User ) {
				update_user_meta( $user_id, 'bmlm_network_id', $node );
			} else {
				$this->network_user->bmlm_update_network_user_id( $user_id, $node );
			}
		}

		/**
		 * Upgrade sponsors level.
		 *
		 * @param int $parent_id Sponsor user id.
		 * @return void
		 */
		public function bmlm_upgrade_sponsors_level( $parent_id ) {

			$this->bmlm_approve_sponsor( $parent_id );
			$this->bmlm_recursive_find_parent( $parent_id );
		}

		/**
		 * Recursively find parent.
		 *
		 * @param int $child_id Sponsor user id.
		 *
		 * @return void
		 */
		public function bmlm_recursive_find_parent( $child_id ) {
			$referral_code = $this->bmlm_get_referral_code( $child_id );
			if ( ! empty( $referral_code ) ) {
				$user_id = $this->bmlm_get_sponsor_user_id( $referral_code );
				$level   = $this->bmlm_get_sponsor_tree_level( $user_id );

				$nrow = $this->bmlm_get_network_id( $user_id );
				$args = array(
					'parent'        => $user_id,
					'current_level' => $level,
					'current_row'   => $nrow,
				);

				$usage_count = $this->bmlm_is_level_upgrade_required( $args );
				if ( $usage_count ) {
					$l = $level + 1;
					bmlm_wc_log( "User comm: $this->levelup_commission, Customer id: $user_id, Is membership: $l" );
					$this->bmlm_upgrade_sponsor_level( $user_id, $level + 1 );
					if ( ! empty( $this->levelup_commission ) ) {
						do_action( 'bmlm_sponsor_load_levelup_commission', $user_id, $this->levelup_commission, $level + 1 );
					}
				}

				$this->bmlm_recursive_find_parent( $user_id );
			}
		}

		/**
		 * Upgrade sponsor tree level.
		 *
		 * @param int $user_id Sponsor id.
		 * @param int $level Sponsor level.
		 *
		 * @return void
		 */
		public function bmlm_upgrade_sponsor_level( $user_id, $level ) {
			if ( get_userdata( $user_id ) instanceof \WP_User ) {
				update_user_meta( $user_id, 'bmlm_tree_level', $level );
			} else {
				$this->network_user->bmlm_update_network_user_tree_level( $user_id, $level );
			}
		}

		/**
		 * Get sponsor badge id.
		 *
		 * @param int $user_id Sponsor user id.
		 *
		 * @return int
		 */
		public function bmlm_get_sponsor_badge( $user_id ) {
			$sponsor_badge = get_user_meta( $user_id, 'bmlm_badge', true );
			return apply_filters( 'bmlm_sponsor_badge_id', $sponsor_badge, $user_id );
		}

		/**
		 * Get sponsor badge list.
		 *
		 * @param int $user_id Sponsor user id.
		 * @return int
		 */
		public function bmlm_get_sponsor_badge_list( $user_id ) {
			$wpdb_obj       = $this->wpdb;
			$query          = $wpdb_obj->prepare( "SELECT bmeta.user_id, badge.image, bmeta.id, bmeta.date, badge.bonus_amt, badge.name FROM {$wpdb_obj->prefix}bmlm_sponsor_badge_meta as bmeta JOIN {$wpdb_obj->prefix}bmlm_sponsor_badge as badge ON bmeta.badge_id=badge.id WHERE bmeta.user_id=%d ORDER BY id desc", $user_id ); // WPCS: unprepared SQL OK.
			$sponsor_badges = $wpdb_obj->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is safe, see above.
			return apply_filters( 'bmlm_sponsor_badge_id', $sponsor_badges, $user_id );
		}

		/**
		 * Get parent sponsor tree node.
		 *
		 * @param string $sponsor_code Sponsor id.
		 *
		 * @return int
		 */
		public function bmlm_get_parent_network_id( $sponsor_code ) {
			$user_id      = $this->bmlm_get_sponsor_user_id( $sponsor_code );
			$sponsor_node = $this->bmlm_get_network_id( $user_id );

			return apply_filters( 'bmlm_parent_network_id', $sponsor_node, $sponsor_code );
		}



		/**
		 * Get sponsor user id by its sponsor code.
		 *
		 * @param string $sponsor_code Sponsor Code.
		 * @param bool   $active_check If sponsor is active. To use in registration validation.
		 *
		 * @return int
		 */
		public function bmlm_get_sponsor_user_id( $sponsor_code, $active_check = false ) {

			$wpdb_obj = $this->wpdb;

			$sponcer_query = $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key='bmlm_sponsor_id' AND meta_value=%s", $sponsor_code );

			$user_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key='bmlm_sponsor_id' AND meta_value=%s", $sponsor_code ) ); // WPCS: unprepared SQL OK.

			if ( ! empty( $user_id ) ) {
				return apply_filters( 'bmlm_sponsor_user_id', $user_id, $sponsor_code );
			}

			$users_query = "SELECT `user_data` FROM {$wpdb_obj->prefix}bmlm_network_users";

			if ( $active_check ) {
				$users_query .= $wpdb_obj->prepare( ' WHERE `status`=%d', 1 );
			}

			$users_data = $wpdb_obj->get_results( $users_query, ARRAY_A );

			foreach ( is_iterable( $users_data ) ? $users_data : array() as $data ) {
				$user_data = empty( $data['user_data'] ) ? 0 : $data['user_data'];
				$result    = maybe_unserialize( $user_data );

				if ( empty( $result['sponsor_id'] ) ) {
					continue;
				}

				if ( $sponsor_code === $result['sponsor_id'] ) {
					return apply_filters( 'bmlm_sponsor_user_id', $result['ID'], $sponsor_code );
				}
			}
			return 0;
		}

		/**
		 * Get sponsor code.
		 *
		 * @param string $user_id user id.
		 *
		 * @return int
		 */
		public function bmlm_get_sponsor_code( $user_id ) {
			$sponsor_code = '';

			if ( get_userdata( $user_id ) instanceof \WP_User ) {
				$sponsor_code = get_user_meta( $user_id, 'bmlm_sponsor_id', true );
			}
	
			if ( empty( $sponsor_code ) ) {
				if ( $this->network_user !== null ) {
					$sponsor_code = $this->network_user->bmlm_get_network_user_sponsor_code( $user_id );
				} else {
					// Handle the case where $network_user is null
					// Log an error, or set a default value for $sponsor_code
					error_log('Error: $this->network_user is null in bmlm_get_sponsor_code()');
					$sponsor_code = 'default_sponsor_code'; // Example fallback
				}
			}
	
			return apply_filters( 'bmlm_modify_sponsor_code', $sponsor_code, $user_id );
		}

		/**
		 * Generate sponsor id.
		 *
		 * @param int $user_id Sponsor id.
		 *
		 * @return void
		 */
		public function bmlm_generate_sponsor_id( $user_id ) {
			$referral_code_length         = get_option( 'bmlm_refferal_code_length', 8 );
			$length                       = empty( $referral_code_length ) ? 8 : intval( $referral_code_length );
			$referral_code_prefix         = get_option( 'bmlm_refferal_code_prefix', false );
			$referral_code_suffix         = get_option( 'bmlm_refferal_code_suffix', false );
			$sponsor_referral_code_format = get_option( 'bmlm_sponsor_refferal_code_format', 0 );
			$special_chars                = empty( $sponsor_referral_code_format ) ? false : true;
			$referral_code_separator      = get_option( 'bmlm_refferal_code_separator', false );
			$hash                         = wp_generate_password( $length, $special_chars, false );
			$hash                         = trim( $referral_code_prefix . $hash . $referral_code_suffix );
			$sponsor_code                 = empty( $referral_code_separator ) ? $hash : implode( $referral_code_separator, str_split( $hash ) );
			$sponsor_code                 = wc_clean( $sponsor_code );
			$is_exists                    = $this->bmlm_sponsor_id_exists( $sponsor_code );

			if ( ! empty( $is_exists ) ) {
				$this->bmlm_generate_sponsor_id( $user_id );
			} else {
				update_user_meta( $user_id, 'bmlm_sponsor_id', $sponsor_code );
			}
		}

		/**
		 * Check sponsor id exists in database.
		 *
		 * @param string $sponsor_code Sponsor code.
		 *
		 * @return int
		 */
		public function bmlm_sponsor_id_exists( $sponsor_code ) {
			$sponsor_id  = '';
			$sponsor_ids = get_users(
				array(
					'fields'     => 'ID',
					'meta_key'   => 'bmlm_sponsor_id',
					'meta_value' => $sponsor_code,
				)
			);

			$sponsor_id = ( is_array( $sponsor_ids ) && 1 === count( $sponsor_ids ) ) ? $sponsor_ids[0] : 0;

			if ( ! empty( $sponsor_id ) ) {
				return $sponsor_id;
			}

			return $this->bmlm_get_sponsor_user_id( $sponsor_code, true );
		}

		/**
		 * Check if sponsor level upgrade required.
		 *
		 * @param array $args arguments.
		 *
		 * @return boolean
		 */
		public function bmlm_is_level_upgrade_required( $args ) {
			$wpdb_obj = $this->wpdb;

			$current_level = (int) $args['current_level'];
			$next_level    = (int) ( $current_level + 1 );
			$parent        = (int) $args['parent'];
			$nrow          = (int) $args['current_row'];

			$required_members     = pow( 2, $next_level );
			$required_network_row = $nrow + $next_level;
			$childrens_query      = $wpdb_obj->prepare( "SELECT COUNT(child) FROM {$wpdb_obj->prefix}bmlm_gtree_nodes as gtree JOIN {$wpdb_obj->base_prefix}usermeta as umeta ON umeta.user_id=gtree.child WHERE gtree.parent=%d AND gtree.nrow=%d AND umeta.meta_key=%s AND umeta.meta_value=%s", intval( $parent ), $required_network_row, '_approved', 1 );
			$count                = $wpdb_obj->get_var( $childrens_query );
			return intval( $count ) === intval( $required_members ) ? true : false;
		}

		/**
		 * Get Referral code usage count.
		 *
		 * @param string $referral_code Referral code.
		 *
		 * @return int
		 */
		public function bmlm_get_referral_code_usage_count( $referral_code ) {
			$sponsor_id = $this->bmlm_sponsor_id_exists( $referral_code );

			$referred_user_ids = get_users(
				array(
					'fields'     => 'ID',
					'meta_key'   => 'bmlm_refferal_id',
					'meta_value' => $referral_code,
				)
			);

			if ( is_iterable( $referred_user_ids ) && count( $referred_user_ids ) > 1 ) {
				return count( $referred_user_ids );
			}

			$this->network_user = empty( $this->network_user ) ? BMLM_Network_Users::get_instance( $sponsor_id ) : $this->network_user;

			return $this->network_user->bmlm_get_referral_code_network_users( $referral_code );
		}

		/**
		 * Get sponsor.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return array Sponsor.
		 */
		public function bmlm_get_sponsor( $sponsor_id = 0 ) {
			$sponsor_id = empty( $sponsor_id ) ? $this->sponsor_id : $sponsor_id;
			$sponsor    = get_userdata( $sponsor_id );

			$this->network_user = empty( $this->network_user ) ? BMLM_Network_Users::get_instance( $sponsor_id ) : $this->network_user;

			$approved = $this->network_user->bmlm_get_network_user_status( $sponsor_id );

			if ( $sponsor instanceof \WP_User ) {
				$sponsor->approved = $approved;
				return apply_filters( 'bmlm_sponsor_data', $sponsor );
			}

			$sponsor = $this->network_user->bmlm_get_network_user_data( $sponsor_id );

			$sponsor           = (object) $sponsor;
			$sponsor->approved = $approved;

			return apply_filters( 'bmlm_sponsor_data', $sponsor );
		}

		/**
		 * Get sponsor's referrer User object.
		 *
		 * @param int $user_id sponsor user id.
		 *
		 * @return object
		 */
		public function bmlm_get_sponsor_referrer_user( $user_id ) {
			$referral_code = $this->bmlm_get_referral_code( $user_id );
			$referral_id   = $this->bmlm_get_sponsor_user_id( $referral_code );

			return apply_filters( 'bmlm_sponsor_referrer', $this->bmlm_get_sponsor( $referral_id ), $user_id );
		}

		/**
		 * Get sponsor page slug.
		 *
		 * @return string $page_name Sponsor page name.
		 */
		public function bmlm_get_sponsor_page_slug() {
			$wpdb_obj  = $this->wpdb;
			$page_id   = get_option( 'bmlm_sponsor_page_id' );
			$query     = $wpdb_obj->prepare( "SELECT post_name FROM {$wpdb_obj->prefix}posts WHERE ID=%d", intval( $page_id ) ); // WPCS: unprepared SQL OK.
			$page_name = $wpdb_obj->get_var( $query );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is safe, see above.
			return apply_filters( 'bmlm_sponsor_page_slug', $page_name, $page_id );
		}

		/**
		 * Get sponsor donwline member count
		 *
		 * @param int $sponsor_user_id sponsor user id.
		 * @return bool $count member count.
		 */
		public function bmlm_sponsor_get_downline_member_count( $sponsor_user_id ) {
			$wpdb_obj = $this->wpdb;

			$count = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(child) FROM {$wpdb_obj->prefix}bmlm_gtree_nodes WHERE parent=%d", intval( $sponsor_user_id ) ) ); // WPCS: unprepared SQL OK.

			return apply_filters( 'bmlm_sponsor_downline_member_count', intval( $count ), $sponsor_user_id );
		}

		/**
		 * Get sponsor referrer
		 *
		 * @return string $referrer
		 */
		public function bmlm_sponsor_get_refferer() {
			$referrer = get_option( 'bmlm_sponsor_page_id' );
			return apply_filters( 'bmlm_sponsor_referrer', $referrer );
		}

		/**
		 * Validates sponsor registration form
		 *
		 * @param WP_Error $error Error.
		 *
		 * @return \WP_Error
		 */
		public function bmlm_validate_sponsor_registration_fields( $error ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$role        = isset( $posted_data['role'] ) ? wp_unslash( $posted_data['role'] ) : '';

			if ( 'bmlm_sponsor' === $role ) {
				$referral_id = empty( $posted_data['bmlm_refferal_id'] ) ? '' : trim( wp_unslash( $posted_data['bmlm_refferal_id'] ) );
				$terms       = empty( $posted_data['sponsor_terms'] ) ? '' : trim( wp_unslash( $posted_data['sponsor_terms'] ) );
				$email       = empty( $posted_data['email'] ) ? '' : trim( $posted_data['email'] );

				$existing_emails = $this->bmlm_get_existing_network_user_emails();

				if ( in_array( $email, $existing_emails, true ) ) {
					return new \WP_Error( 'email-error', esc_html__( 'An sponsor with this email id already exist, please try a different email address..', 'binary-mlm' ) );
				}

				if ( empty( $referral_id ) ) {
					return new \WP_Error( 'refferal-error', esc_html__( 'Please enter your referral id.', 'binary-mlm' ) );
				}

				if ( empty( $this->bmlm_sponsor_id_exists( $referral_id ) ) ) {
					return new \WP_Error( 'refferal-error', esc_html__( 'Entered referral id does not exists.', 'binary-mlm' ) );
				}

				$usage_count = $this->bmlm_get_referral_code_usage_count( $referral_id );

				if ( ! empty( $usage_count ) && $usage_count >= 2 ) {
					return new \WP_Error( 'refferal-error', esc_html__( 'Referral id usage limit reached, A referral id can be used by only two users', 'binary-mlm' ) );
				}

				if ( empty( $terms ) ) {
					return new \WP_Error( 'terms-error', esc_html__( 'Please read and accept the terms and conditions to become a sponsor.', 'binary-mlm' ) );
				}
			}

			return $error;
		}

		/**
		 * Process sponsor registration.
		 *
		 * @param int   $user_id New User ID.
		 * @param array $data Data Array.
		 *
		 * @throws \Exception Success Message.
		 *
		 * @return void
		 */
		public function bmlm_process_sponsor_registration( $user_id, $data ) {
			$role        = $data['role'];
			$user_login  = $data['user_login'];
			$user_email  = $data['user_email'];
			$refferal_id = $data['refferal_id'];

			try {
				if ( email_exists( $user_email ) ) {
					$user_data = array(
						'display_name' => $user_login,
					);
					wp_update_user( $user_data );
				}
				$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				unset( $_POST );

				if ( ! empty( $role ) && 'customer' !== $role ) {
					$this->bmlm_update_sponsor_details( $user_id, $refferal_id );
					$network_user = BMLM_Network_Users::get_instance( $user_id );
					$network_user->bmlm_add_network_user( $user_id );
				}
				do_action( 'bmlm_new_sponsor_registration', $data );
				do_action(
					'bmlm_new_sponsor_register_to_admin',
					array(
						'user_email' => $user_email,
						'user_name'  => $user_login,
					)
				);

				throw new \Exception( 'success' );
			} catch ( \Exception $e ) {
				if ( 'success' === $e->getMessage() ) {
					wc_add_notice(
						apply_filters(
							'register_errors',
							esc_html__( 'Registration complete check your mail for password!', 'binary-mlm' )
						),
						$e->getMessage()
					);
				} else {
					wc_add_notice(
						apply_filters(
							'register_errors',
							$e->getMessage()
						),
						'error'
					);
				}
			}
		}

		/**
		 * Update sponsor details
		 *
		 * @param int    $user_id New User ID.
		 * @param string $refferal_id refferal id.
		 * @return object
		 */
		public function bmlm_update_sponsor_details( $user_id, $refferal_id ) {
			$user = new \WP_User( $user_id );
			$user->set_role( 'bmlm_sponsor' );
			$this->bmlm_generate_sponsor_id( $user_id );

			update_user_meta( $user_id, 'bmlm_refferal_id', $refferal_id );

			$parent_node   = $this->bmlm_get_parent_network_id( $refferal_id );
			$new_user_node = intval( $parent_node ) + 1;

			$this->bmlm_upgrade_network_id( $user_id, $new_user_node );
			$this->bmlm_upgrade_sponsor_level( $user_id, 0 );
			$this->bmlm_inject_sponsor_dependency( $refferal_id, $user_id, $new_user_node );

			return $user;
		}

		/**
		 * Is sponsor auto approval is ON by Admin
		 *
		 * @param int $user_id sponsor id.
		 * @return bool $is_auto_approved Is approved sponsor.
		 */
		public function bmlm_approve_sponsor( $user_id ) {
			$is_approved = $this->network_user->bmlm_get_network_user_status( $user_id );

			if ( empty( $is_approved ) ) {
				$this->network_user->bmlm_update_network_user_status( $user_id, 1 );

				do_action( 'bmlm_approve_sponsor_account', $user_id );
			}
			$status = get_user_meta( $user_id, '_approved', true );

			if ( ! empty( $status ) ) {
				update_user_meta( $user_id, '_approved', true );
				return true;
			} else {
				return true;
			}
		}

		/**
		 * Sort data.
		 *
		 * @param array $a Order By.
		 *
		 * @param array $b ASC|DESC.
		 *
		 * @return string $result
		 */
		public function usort_reorder( $a, $b ) {
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$orderby = ( ! empty( $request_data['orderby'] ) ) ? $request_data['orderby'] : 'id';
			$order   = ( ! empty( $request_data['order'] ) ) ? $request_data['order'] : 'asc';

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : -$result; // Send final sort direction to usort.
		}

		/**
		 * Get All sponsors function
		 *
		 * @param array $args Arguments.
		 * @return array
		 */
		public function bmlm_get_all_sponsors( $args ) {
			$per_page = ! empty( $args['per_page'] ) ? $args['per_page'] : '';
			$offset   = ! empty( $args['offset'] ) ? $args['offset'] : '';
			$fields   = ! empty( $args['fields'] ) ? $args['fields'] : '';
			$users    = ! empty( $args['users'] ) ? $args['users'] : '';
			$args     = array(
				'role'        => 'bmlm_sponsor',
				'orderby'     => 'ID',
				'number'      => $per_page,
				'offset'      => $offset,
				'count_total' => true,
			);
			if ( ! empty( $fields ) ) {
				$args['fields'] = $fields;
			}
			if ( ! empty( $users ) ) {
				$args['include'] = $users;
			}
			$search = empty( $_GET['s'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['s'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $search ) ) {
				$search         = '*' . $search . '*';
				$args['search'] = $search;
			}
			$sponsors_query = new \WP_User_Query( $args );
			return apply_filters( 'bmlm_modify_sponsors_list', $sponsors_query, $per_page, $offset );
		}

		/**
		 * Delete sponsor function
		 *
		 * @param int $sponsor_id Sponsor id.
		 * @return int|bool
		 */
		public function bmlm_delete_sponsor( $sponsor_id ) {
			wp_delete_user( $sponsor_id );
			return true;
		}

		/**
		 * Validates sponsor registration form
		 *
		 * @param int $refferal_id Reffreal id.
		 * @return \WP_Error
		 */
		public function bmlm_validate_refferal_id( $refferal_id ) {
			if ( empty( $refferal_id ) ) {
				wc_print_notice( esc_html__( 'Please enter your referral id.', 'binary-mlm' ), 'error' );
				return false;
			}
			if ( empty( $this->bmlm_sponsor_id_exists( $refferal_id ) ) ) {
				wc_print_notice( esc_html__( 'Entered referral id does not exists.', 'binary-mlm' ), 'error' );
				return false;
			}
			$usage_count = $this->bmlm_get_referral_code_usage_count( $refferal_id );
			if ( ! empty( $usage_count ) && $usage_count >= 2 ) {
				wc_print_notice( esc_html__( 'Referral id usage limit reached, A referral id can be used by only two users', 'binary-mlm' ), 'error' );
				return false;
			}

			return true;
		}

		/**
		 * Get all children of sponsor.
		 *
		 * @param int $sponsor_user_id parent sponsor user id.
		 * @return array
		 */
		public function bmlm_get_sponsor_childrens( $sponsor_user_id ) {
			$wpdb_obj     = $this->wpdb;

			//wp_die($sponsor_user_id);
			$current_user = array( 'id' => strval( $sponsor_user_id ) );
			$query = $wpdb_obj->prepare(
				"SELECT DISTINCT(child) as id 
				 FROM {$wpdb_obj->prefix}bmlm_gtree_nodes 
				 WHERE parent = %d AND child > 0",
				intval( $sponsor_user_id )
			);
			
			$users        = $wpdb_obj->get_results( $query, ARRAY_A );

			if ( ! empty( $users ) ) {
				array_push( $users, $current_user );
			} else {
				$users = array( $current_user );
			}
			return $users;
		}

		/**
		 * Get parent and child user ids of given sponsor user id.
		 *
		 * @param int $sponsor_user_id parent sponsor user id.
		 * @return array
		 */
		public function bmlm_get_sponsor_parent_child_relation( $sponsor_user_id ) {
			$wpdb_obj = $this->wpdb;
			$query    = $wpdb_obj->prepare( "SELECT child, parent FROM {$wpdb_obj->prefix}bmlm_gtree_nodes WHERE child=%d", intval( $sponsor_user_id ) ); // WPCS: unprepared SQL OK.
			$users    = $wpdb_obj->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is safe, see above.
			return $users;
		}

		/**
		 * Get Sponsors data.
		 *
		 * @param array $user_ids sponsor user ids.
		 * @return array
		 */
		public function bmlm_get_sposnors_miscellaneous( $user_ids ) {
			$primary_data = $this->bmlm_get_sposnors_primary( $user_ids );

			$primary_data = json_decode( wp_json_encode( $primary_data ), true );

			foreach ( $primary_data as $key => $user ) {
				$profile_image                           = md5( strtolower( trim( $user['user_email'] ) ) );
				$member_count                            = $this->bmlm_sponsor_get_downline_member_count( $user['ID'] );
				$primary_data[ $key ]['downline_member'] = $member_count;
				$primary_data[ $key ]['refferal_id']     = $this->bmlm_get_sponsor_user_id( $primary_data[ $key ]['refferal_id'] ) > 0 ?  $this->bmlm_get_sponsor_user_id( $primary_data[ $key ]['refferal_id'] ) : 143;
				$primary_data[ $key ]['profileUrl']      = $this->is_admin ? admin_url( 'admin.php?page=bmlm_sponsors&section=sponsor-general&action=manage&sponsor_id=' . $user['ID'] ) : '';
				$primary_data[ $key ]['imageUrl']        = ( $user['status'] ) ? 'https://www.gravatar.com/avatar/' . $profile_image . '?s=58' : BMLM_PLUGIN_URL . 'assets/images/ban-user.png';
				
			}

			return $primary_data;
		}

	
		/**
		 * Format Sponsors data.
		 *
		 * @param array $sponsors Sponsor.
		 * @return array
		 */
		public function bmlm_format_sponsor_data( $sponsors ) {
			usort( $sponsors, array( $this, 'bmlm_cmp' ) );
			$sponsors = $this->build_tree( $sponsors );
			$sponsors = ! empty( $sponsors ) ? array_values( $sponsors ) : array();

			return ! empty( $sponsors ) ? $sponsors[0] : array();
		}

		/**
		 * Sort array.
		 *
		 * @param array $a first index.
		 * @param array $b next index.
		 * @return array
		 */
		public function bmlm_cmp( $a, $b ) {
			if ( $a === $b ) {
				return 0;
			}
			return ( intval( $a['ID'] ) < intval( $b['ID'] ) ) ? -1 : 1;
		}


		/**
		 * Build Sponsors tree.
		 *
		 * @param array  $results sponsor items.
		 * @param string $id_field id field.
		 * @param string $parent_id_field parent id field.
		 * @param string $children_field children field.
		 *
		 * @return array
		 */
		public function build_tree( $results, $id_field = 'ID', $parent_id_field = 'refferal_id', $children_field = 'children' ) {
			$hierarchy       = array();
			$item_references = array();

			foreach ( $results as $key => $item ) {
				$id        = $item[ $id_field ];
				$parent_id = $item[ $parent_id_field ];
				if ( ! empty( $item_references[ $parent_id ] ) ) {  // Parent exists.
					$length = ! empty( $item_references[ $parent_id ][ $children_field ] ) ? count( $item_references[ $parent_id ][ $children_field ] ) : 0;
					$item_references[ $parent_id ][ $children_field ][ $length ] = $item; // Assign item to parent.
					$item_references[ $id ]                                      = & $item_references[ $parent_id ][ $children_field ][ $length ]; // Reference parent's item in. single-dimentional array.
				} elseif ( ! $parent_id || empty( $hierarchy[ $parent_id ] ) ) { // -- parent Id empty or does not exist. Add it to the root.
					$hierarchy[ $key ]      = $item;
					$item_references[ $id ] = & $hierarchy[ $key ];
				}
			}

			unset( $results, $item, $id, $parent_id );
			// -- Run through the root one more time. If any child got added before it's parent, fix it.
			foreach ( $hierarchy as $id => &$item ) {
				$parent_id = $item[ $parent_id_field ];

				if ( ! empty( $item_references[ $parent_id ] ) ) { // -- parent DOES exist
					$item_references[ $parent_id ][ $children_field ][ $id ] = $item; // -- assign it to the parent's list of children
					unset( $hierarchy[ $id ] ); // -- remove it from the root of the hierarchy
				}
			}
			unset( $item_references, $id, $item, $parent_id );

			return $hierarchy;
		}

		/**
		 * Get Sponsors primary.
		 *
		 * @param array $user_ids sponsor user ids.
		 * @return array
		 */
		public function bmlm_get_sposnors_primary( $user_ids ) {
			$mapped_ids = wp_list_pluck( $user_ids, 'id' );

			$roles = apply_filters( 'bmlm_modify_roles', array( 'administrator', 'bmlm_sponsor' ) );
			$args  = array(
				'role__in'    => $roles,
				'order'       => 'DESC',
				'orderby'     => 'ID',
				'count_total' => false,
				'include'     => $mapped_ids,
				'fields'      => array( 'ID', 'user_login', 'display_name', 'user_email', 'user_registered' ),
			);

			$user_query = new \WP_User_Query( $args );
			$users      = $user_query->get_results();

			$users = $this->network_user->bmlm_validate_network_users( $users, $mapped_ids, $this );

			return $users;
		}

		/**
		 * Inject sponsor parent child relationship in custom table for hierarchy/commission management
		 *
		 * @param string $parent_sponsor_code parent sponsor Code.
		 * @param int    $user_id current user id.
		 * @param int    $nrow current user network row.
		 *
		 * @return \WP_Error
		 */
		public function bmlm_inject_sponsor_dependency( $parent_sponsor_code, $user_id, $nrow ) {
			$wpdb_obj        = $this->wpdb;
			$relations       = array();
			$sponsor_user_id = $this->bmlm_get_sponsor_user_id( $parent_sponsor_code );
			$relations       = $this->bmlm_get_sponsor_parent_child_relation( $sponsor_user_id );

			$values = array(
				'child'  => $user_id,
				'parent' => $sponsor_user_id,
			);

			if ( ! empty( $relations ) ) {
				array_walk_recursive(
					$relations,
					function ( &$v, $k ) use ( &$user_id ) {
						if ( 'child' === $k ) {
							$v = $user_id;
						}
					},
					$user_id
				);
			}
			array_push( $relations, $values );
			$imploded_array = array();

			foreach ( $relations as $row ) {
				$imploded_array[] = '(' . intval( $row['child'] ) . ', ' . intval( $row['parent'] ) . ', ' . intval( $nrow ) . ')';
			}
			$query = "INSERT INTO {$wpdb_obj->prefix}bmlm_gtree_nodes (child, parent, nrow) VALUES " . implode( ', ', $imploded_array );
			$wpdb_obj->query( $query );
			return true;
		}

		/**
		 * Get sponsor parent user ids and levels.
		 *
		 * @param array $args arguments.
		 * @return array
		 */
		public function bmlm_get_sponsor_parents( $args ) {
			$wpdb_obj = $this->wpdb;
			$sponsors = array();
			$user_id  = empty( $args['user_id'] ) ? 0 : intval( $args['user_id'] );

			if ( ! empty( $user_id ) ) {
				$query    = $wpdb_obj->prepare( "SELECT umeta.meta_value as level, gtree.parent as user_id FROM {$wpdb_obj->prefix}bmlm_gtree_nodes as gtree JOIN {$wpdb_obj->base_prefix}usermeta  as umeta ON gtree.parent = umeta.user_id WHERE gtree.child=%d AND umeta.meta_key=%s ORDER BY gtree.parent ASC", intval( $user_id ), 'bmlm_tree_level' );
				$sponsors = $wpdb_obj->get_results( $query, ARRAY_A );
			}

			return $sponsors;
		}

		/**
		 * Get sponsor status html.
		 *
		 * @param int $sponsor_id Sponsor Id.
		 *
		 * @return string
		 */
		public function bmlm_get_status_html( $sponsor_id = 0 ) {
			$status = empty( $sponsor_id ) ? 0 : $this->bmlm_get_sponsor_status( $sponsor_id );

			$status_html = '<mark class="bmlm-status bmlm-status-pending tips"><span>' . esc_html__( 'Pending', 'binary-mlm' ) . '</span></mark>';

			if ( ! empty( $status ) ) {
				$status_html = '<mark class="bmlm-status bmlm-status-completed tips"><span>' . esc_html__( 'Approved', 'binary-mlm' ) . '</span></mark>';
			}

			return apply_filters( 'bmlm_modify_sponsor_status_html', $status_html, $status );
		}

		/**
		 * Get all emails for existing network users.
		 *
		 * @return array
		 */
		public function bmlm_get_existing_network_user_emails() {
			$wpdb_obj = $this->wpdb;
			$emails   = array();

			$users_data = $wpdb_obj->get_results( "SELECT `user_data` FROM {$wpdb_obj->prefix}bmlm_network_users", ARRAY_A );

			foreach ( is_iterable( $users_data ) ? $users_data : array() as $data ) {
				$user_data = empty( $data['user_data'] ) ? 0 : $data['user_data'];

				$result = maybe_unserialize( $user_data );

				if ( empty( $result['user_email'] ) ) {
					continue;
				}

				$emails[] = $result['user_email'];
			}

			return $emails;
		}

		/**
		 * Get referral code.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return string
		 */
		public function bmlm_get_referral_code( $sponsor_id ) {
			if ( get_userdata( $sponsor_id ) instanceof \WP_User ) {
				return get_user_meta( $sponsor_id, 'bmlm_refferal_id', true );
			}
			$sponsor_id = (int) $sponsor_id;
			return $this->network_user->bmlm_get_network_user_referral_code( $sponsor_id );
		}

		/**
		 * Get Tree level.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return string
		 */
		public function bmlm_get_sponsor_tree_level( $sponsor_id ) {
			$tree_level = 0;

			if ( get_userdata( $sponsor_id ) instanceof \WP_User ) {
				$tree_level = get_user_meta( $sponsor_id, 'bmlm_tree_level', true );
			}

			if ( empty( $tree_level ) ) {
				$tree_level = $this->network_user->bmlm_get_network_user_tree_level( $sponsor_id );
			}

			return apply_filters( 'bmlm_sponsor_level', $tree_level, $sponsor_id );
		}

		/**
		 * Get Network id.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return string
		 */
		public function bmlm_get_network_id( $sponsor_id ) {
			if ( get_userdata( $sponsor_id ) instanceof \WP_User ) {
				return get_user_meta( $sponsor_id, 'bmlm_network_id', true );
			}

			return $this->network_user->bmlm_get_network_user_id( $sponsor_id );
		}

		/**
		 * Get Network id.
		 *
		 * @param int $sponsor_id Sponsor id.
		 *
		 * @return string
		 */
		public function bmlm_get_sponsor_status( $sponsor_id ) {
			$status = 0;

			if ( intval( $sponsor_id ) > 0 ) {
				$status  = get_user_meta( $sponsor_id, '_approved', true );
				$sponsor = get_user_by( 'ID', $sponsor_id );
				if ( $sponsor instanceof \WP_User && in_array( 'administrator', $sponsor->roles, true ) ) {
					$status = 1;
				} elseif ( empty( $status ) ) {
					$this->network_user = empty( $this->network_user ) ? BMLM_Network_Users::get_instance( $sponsor_id ) : $this->network_user;

					$status = $this->network_user->bmlm_get_network_user_status( $sponsor_id );
				}
			}
			return is_bool( $status ) ? wc_bool_to_string( $status ) : $status;
		}
	}
}
