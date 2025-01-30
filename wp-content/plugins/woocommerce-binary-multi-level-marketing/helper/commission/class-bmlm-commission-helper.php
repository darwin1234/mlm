<?php
/**
 * Commission helper
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Helper\Commission;

use WCBMLMARKETING\Helper\Badges\BMLM_Badges;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'BMLM_Commission_Helper' ) ) {
	/**
	 * Commission helper class
	 */
	class BMLM_Commission_Helper {
		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Commission
		 *
		 * @var object
		 */
		protected $commission;

		/**
		 * Levelup Commission Amount
		 *
		 * @var object
		 */
		public $levelup_commission;

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Construct
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb               = $wpdb;
			$this->levelup_commission = get_option( 'bmlm_levelup_commission_amount', true );
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}

			return static::$instance;
		}

		/**
		 * Get commission rules.
		 *
		 * @return array
		 */
		public function bmlm_get_commission_rules() {
			$joining_commission = array();
			$sale_commission    = array();
			$levelup_commission = array();

			$membership    = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$membership_id = empty( $membership->ID ) ? 0 : intval( $membership->ID );
			$product       = wc_get_product( $membership_id );

			$admin_sale_commission       = get_option( 'bmlm_sales_commission_admin', true );
			$bmlm_sales_commission_other = get_option( 'bmlm_sales_commission_other', true );

			$bmlm_joining_amount_settings_enable = get_option( 'bmlm_joining_amount_settings_enable', false );
			$bmlm_levelup_amount_settings_enable = get_option( 'bmlm_levelup_amount_settings_enable', false );

			if ( ! empty( $bmlm_joining_amount_settings_enable ) ) {
				$bmlm_joining_commission             = get_option( 'bmlm_joining_commission_admin' );
				$bmlm_joining_commission_other       = get_option( 'bmlm_joining_commission_other' );
				$bmlm_joining_commission_alot_amount = $product->get_price();

				$joining_commission = array(
					'admin'   => wc_format_decimal( $bmlm_joining_commission ),
					'other'   => $bmlm_joining_commission_other,
					'initial' => wc_format_decimal( $bmlm_joining_commission_alot_amount ),
				);
			}

			if ( ! empty( $bmlm_levelup_amount_settings_enable ) ) {
				$bmlm_levelup_commission = get_option( 'bmlm_levelup_commission' );
				$levelup_commission      = $bmlm_levelup_commission;
			}

			$sale_commission = array(
				'admin' => wc_format_decimal( $admin_sale_commission ),
				'other' => $bmlm_sales_commission_other,
			);

			$this->commission = apply_filters(
				'bmlm_modify_commission_rules',
				array(
					'sale'    => $sale_commission,
					'joining' => $joining_commission,
					'levelup' => $levelup_commission,

				)
			);
			return $this->commission;
		}

		/**
		 * Calculate commission.
		 *
		 * @param array $sponsors sponsors.
		 *
		 * @param array $rules Commission rules.
		 *
		 * @param array $args Cart args.
		 *
		 * @return array
		 */
		public function bmlm_calculate_commission( $sponsors, $rules, $args ) {
			$commission       = array();
			$admin_commission = 0;

			if ( isset( $rules['admin'] ) && ! empty( $args['price'] ) ) {
				$admin_commission_percent = $rules['admin'];
				$amount                   = $args['price'];
				$admin_commission         = $this->bmlm_evaluate_percentage_amount( $admin_commission_percent, $amount );

				$sponsor_commission_amount = wc_format_decimal( $amount ) - wc_format_decimal( $admin_commission );

				$commission[] = array(
					'id'         => 'admin',
					'commission' => $admin_commission,
					'level'      => 0,
				);

				$levels       = wp_list_pluck( $sponsors, 'level' );
				$levels_count = array_count_values( $levels );

				$other_commission_percent = $rules['other'];

				foreach ( $sponsors as $sponsor ) {
					$level   = $sponsor['level'];
					$user_id = $sponsor['user_id'];

					if ( ! empty( $other_commission_percent ) ) {
						foreach ( $other_commission_percent as $other ) {
							if ( intval( $other['level'] ) === intval( $level ) && ! empty( $other['rate'] ) ) {
								$count           = $levels_count[ $level ];
								$user_commission = $this->bmlm_evaluate_percentage_amount( $other['rate'] / $count, $sponsor_commission_amount );
								$commission[]    = array(
									'id'         => $user_id,
									'commission' => $user_commission,
									'level'      => $level,
								);
							}
						}
					}
				}
			}

			return apply_filters( 'bmlm_modify_calculated_commission', $commission, $sponsors, $rules, $args );
		}

		/**
		 * Calculate level up commission
		 *
		 * @param array $sponsors sponsors.
		 *
		 * @param array $rules commission rules.
		 *
		 * @param array $args cart args.
		 *
		 * @return array
		 */
		public function bmlm_calculate_levelup_commission( $sponsors, $rules, $args ) {
			$commission = array();
			if ( ! empty( $args['price'] ) ) {
				$amount       = wc_format_decimal( $args['price'] );
				$levels       = wp_list_pluck( $sponsors, 'level' );
				$levels_count = array_count_values( $levels );

				foreach ( $sponsors as $sponsor ) {
					$level   = $sponsor['level'];
					$user_id = $sponsor['user_id'];
					if ( ! empty( $rules ) ) {
						foreach ( $rules as $row ) {
							if ( intval( $row['level'] ) === intval( $level ) && ! empty( $row['rate'] ) ) {
								$count           = $levels_count[ $level ];
								$user_commission = $this->bmlm_evaluate_percentage_amount( $row['rate'] / $count, $amount );
								$commission[]    = array(
									'id'         => $user_id,
									'commission' => $user_commission,
									'level'      => $level,
								);
							}
						}
					}
				}
			}

			return apply_filters( 'bmlm_modify_levelup_commission', $commission, $sponsors, $rules, $args );
		}

		/**
		 * Calculate Bonus commission
		 *
		 * @param array $sponsors sponsors.
		 * @param array $rules commission rules.
		 *
		 * @return array
		 */
		public function bmlm_calculate_bonus_commission( $sponsors, $rules ) {
			$commission = array();
			$helper     = new BMLM_Badges();

			if ( ! empty( $sponsors ) ) {
				foreach ( $sponsors as $sponsor ) {
					$sponsor_gross_business = wc_format_decimal( $sponsor->gross_business );

					$suitable_rules = array_filter(
						$rules,
						function ( $rule ) use ( $sponsor_gross_business ) {
							return wc_format_decimal( $rule['max_business'] ) <= $sponsor_gross_business;
						}
					);

					if ( empty( $suitable_rules ) ) {
						bmlm_wc_log( 'bmlm_calculate_bonus_commission- no suitable rule for sponsor with commission: ' . $sponsor_gross_business );
						continue;
					}

					$closest = array_reduce(
						$suitable_rules,
						function ( $carry, $item ) use ( $sponsor_gross_business ) {
							return ( abs( wc_format_decimal( $item['max_business'] ) - $sponsor_gross_business ) < abs( wc_format_decimal( $carry['max_business'] ) - $sponsor_gross_business ) ) ? $item : $carry;
						},
						reset( $suitable_rules )
					);
					if ( ! empty( $closest ) ) {
						$user_id    = $sponsor->user_id;
						$user_badge = get_user_meta( $user_id, 'bmlm_badge', true );
						if ( empty( $user_badge ) || ( ! empty( $user_badge ) && $user_badge !== $closest['id'] ) ) {
							// Add badge to user meta field.
							update_user_meta( $user_id, 'bmlm_badge', $closest['id'] );
							$helper->bmlm_map_sponsor_badge( $user_id, $closest['id'] );
							$user_commission = $closest['bonus_amt'];
							$commission[]    = array(
								'id'         => $user_id,
								'commission' => $user_commission,
							);
						}
					}
				}
			}

			return apply_filters( 'bmlm_modify_bonus_commission', $commission, $sponsors, $rules );
		}

		/**
		 * Evaluate percentage commission.
		 *
		 * @param int    $commission_percent Commission percent.
		 * @param string $amount Amount.
		 *
		 * @return int
		 */
		public function bmlm_evaluate_percentage_amount( $commission_percent, $amount ) {
			// Ensure numeric values
			$commission_percent = floatval($commission_percent);
			$amount = floatval($amount);
		
			// Perform calculation
			$commission = ( $commission_percent / 100 ) * $amount;
		
			return apply_filters( 'bmlm_modify_commission_evaluated', $commission );
		}
		

		/**
		 * Format commission.
		 *
		 * @param array  $commission Commission data.
		 * @param string $type Commission type.
		 * @param array  $args Sponsor args.
		 *
		 * @return array
		 */
		public function bmlm_format_commission( $commission, $type, $args ) {
			global $bmlm;
			$user_id       = isset( $args['user_id'] ) ? wc_clean( $args['user_id'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$data          = array();
			$user          = empty( $user_id ) ? array() : get_userdata( $user_id );
			$admin_user_id = $bmlm->bmlm_get_first_admin_user_id();

			foreach ( $commission as $value ) {
				$id = $value['id'];

				if ( 'bonus' === $type ) {
					$message = esc_html__( 'Bonus commission received', 'binary-mlm' );
				} else {
					/* translators: %1$s: user email */
					$message = sprintf( esc_html__( 'Commission received from %1$s for level %2$s', 'binary-mlm' ), $user->user_email, $value['level'] );
					if ( 'admin' === $id ) {
						$id = $admin_user_id;
						/* translators: %1$s: user email */
						$message = sprintf( esc_html__( 'Company Commission received from %1$s', 'binary-mlm' ), $user->user_email );
					}
				}

				$data[] = array(
					'user_id'     => $id,
					'type'        => $type,
					'description' => $message,
					'commission'  => $value['commission'],
					'date'        => current_time( 'Y-m-d H:i:s' ),
					'paid'        => false,
				);
			}
			return apply_filters( 'bmlm_formatted_commission', $data );
		}

		/**
		 * Get All commission list.
		 *
		 * @param array $args Arguments.
		 *
		 * @return int
		 */
		public function bmlm_get_all_commission( $args ) {
			$wpdb_obj = $this->wpdb;
			$where    = '';
			$orderby  = ! empty( $args['orderby'] ) ? $args['orderby'] : 'id';
			$order    = ! empty( $args['order'] ) ? $args['order'] : 'desc';
			$user_id  = ! empty( $args['user_id'] ) ? intval( $args['user_id'] ) : '';
			$from     = ! empty( $args['from'] ) ? $args['from'] : '';
			$from     = ! empty( $from ) ? gmdate( 'Y-m-d H:i:s', strtotime( $from ) ) : '';
			$to       = ! empty( $args['to'] ) ? $args['to'] : '';
			$to       = ! empty( $to ) ? gmdate( 'Y-m-d H:i:s', strtotime( $to ) ) : '';
			$date     = gmdate( 'Y-m-d H:i:s' );

			if ( ! empty( $args['type'] ) ) {
				$where .= $wpdb_obj->prepare( ' AND type=%s', $args['type'] );
			}

			if ( ! empty( $user_id ) ) {
				$where .= $wpdb_obj->prepare( ' AND user_id=%d', $user_id );
			}

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$where .= $wpdb_obj->prepare( ' AND date BETWEEN %s AND %s', $from, $to );
			} elseif ( ! empty( $from ) ) {
				$where .= $wpdb_obj->prepare( ' AND date BETWEEN %s AND %s', $from, $date );
			} elseif ( ! empty( $to ) ) {
				$where .= $wpdb_obj->prepare( ' AND date BETWEEN %s AND %s', $date, $to );
			}

			$order_by    = " ORDER BY $orderby $order";
			$limit       = $wpdb_obj->prepare( ' LIMIT %d OFFSET %d', esc_attr( $args['limit'] ), esc_attr( $args['start'] ) );
			$sql         = "SELECT * FROM {$wpdb_obj->prefix}bmlm_commission WHERE 1=1 $where $order_by $limit";
			$commissions = $wpdb_obj->get_results( $sql, ARRAY_A );

			return apply_filters( 'bmlm_modify_commissions_record', $commissions, $args );
		}

		/**
		 * Get All commissions count function.
		 *
		 * @param array $args Arguments.
		 *
		 * @return int
		 */
		public function bmlm_get_all_commission_count( $args ) {
			$wpdb_obj = $this->wpdb;
			$where    = '';
			$user_id  = empty( $args['user_id'] ) ? 0 : intval( $args['user_id'] );
			$from     = empty( $args['from'] ) ? '' : $args['from'];
			$from     = empty( $from ) ? '' : gmdate( 'Y-m-d H:i:s', strtotime( $from ) );
			$to       = empty( $args['to'] ) ? '' : $args['to'];
			$to       = empty( $to ) ? '' : gmdate( 'Y-m-d H:i:s', strtotime( $to ) );
			$date     = gmdate( 'Y-m-d H:i:s' );

			if ( ! empty( $args['type'] ) ) {
				$where .= $wpdb_obj->prepare( ' AND type=%s', $args['type'] );
			}

			if ( ! empty( $user_id ) ) {
				$where .= $wpdb_obj->prepare( ' AND user_id=%d', $user_id );
			}

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$where .= $wpdb_obj->prepare( ' AND date BETWEEN %s AND %s', $from, $to );
			} elseif ( ! empty( $from ) ) {
				$where .= $wpdb_obj->prepare( ' AND date BETWEEN %s AND %s', $from, $date );
			} elseif ( ! empty( $to ) ) {
				$where .= $wpdb_obj->prepare( ' AND date BETWEEN %s AND %s', $date, $to );
			}
			$sql   = "SELECT COUNT(*) FROM {$wpdb_obj->prefix}bmlm_commission WHERE 1=1 $where";
			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'bmlm_modify_total_commissions_count', $total );
		}

		/**
		 * Get commission.
		 *
		 * @param array $id commission id.
		 *
		 * @return array $commission
		 */
		public function bmlm_get_commission( $id ) {
			$wpdb_obj   = $this->wpdb;
			$sql        = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}bmlm_commission WHERE id=%d AND paid=%d", $id, 0 );
			$commission = $wpdb_obj->get_row( $sql, ARRAY_A );

			return apply_filters( 'bmlm_sponsor_commission', $commission, $id );
		}

		/**
		 * Get commission users
		 *
		 * @param array $ids Commission ids.
		 *
		 * @return array $users
		 */
		public function bmlm_get_commission_users( $ids ) {
			$wpdb_obj   = $this->wpdb;
			$sql        = "SELECT user_id FROM {$wpdb_obj->prefix}bmlm_commission WHERE paid=0 AND id in (" . implode( ',', array_map( 'absint', $ids ) ) . ')';
			$commission = $wpdb_obj->get_results( $sql, ARRAY_A );

			return apply_filters( 'bmlm_sponsor_commission', $commission, $ids );
		}

		/**
		 * Get commission types.
		 *
		 * @return array $commission_types
		 */
		public function bmlm_get_commission_types() {
			$wpdb_obj         = $this->wpdb;
			$sql              = "SELECT DISTINCT(type) FROM {$wpdb_obj->prefix}bmlm_commission";
			$commission_types = $wpdb_obj->get_results( $sql, ARRAY_A );
			return apply_filters( 'bmlm_commission_types', $commission_types );
		}

		/**
		 * Insert Commission data.
		 *
		 * @param array $data Commission data.
		 *
		 * @return bool
		 */
		public function bmlm_add_commission( $data ) {
			$wpdb_obj = $this->wpdb;

			foreach ( $data as $commission ) {
				if ( ! empty( $commission['commission'] ) ) {
					$wpdb_obj->insert(
						$wpdb_obj->prefix . 'bmlm_commission',
						array(
							'user_id'     => $commission['user_id'],
							'type'        => $commission['type'],
							'description' => $commission['description'],
							'commission'  => $commission['commission'],
							'date'        => $commission['date'],
							'paid'        => $commission['paid'],
						),
						array(
							'%d',
							'%s',
							'%s',
							'%f',
							'%s',
							'%d',
						)
					);
				}
			}

			return apply_filters( 'bmlm_insert_commission_data', true, $data );
		}

		/**
		 * Delete commission function
		 *
		 * @param int $commission_id  id.
		 *
		 * @return int|bool
		 */
		public function bmlm_delete_commission( $commission_id ) {
			$wpdb_obj = $this->wpdb;

			return $wpdb_obj->delete(
				$wpdb_obj->prefix . 'bmlm_commission',
				array(
					'id' => $commission_id,
				),
				array( '%d' )
			);
		}

		/**
		 * Update commission function.
		 *
		 * @param int $commission_id Commission id.
		 *
		 * @return int|bool
		 */
		public function bmlm_update_commission_status( $commission_id ) {
			$wpdb_obj = $this->wpdb;
			return $wpdb_obj->update(
				$wpdb_obj->prefix . 'bmlm_commission',
				array(
					'paid' => 1,
				),
				array(
					'id' => $commission_id,
				),
				array( '%d' ),
				array( '%d' )
			);
		}

		/**
		 * Get sponsor gross business
		 *
		 * @param int $args Sponsor arguments.
		 *
		 * @return double $gross_business.
		 */
		public function bmlm_sponsor_get_gross_business( $args ) {
			$wpdb_obj        = $this->wpdb;
			$sponsor_user_id = $args['user_id'];
			$where           = '';
			$type            = ! empty( $args['type'] ) ? $args['type'] : '';
			$current         = ! empty( $args['current'] ) ? $args['current'] : '';
			$paid            = ! empty( $args['paid'] ) ? 1 : 0;

			if ( ! empty( $type ) ) {
				$where .= $wpdb_obj->prepare( ' AND type = %s', $type );
			}
			if ( ! empty( $sponsor_user_id ) ) {
				$where .= $wpdb_obj->prepare( ' AND user_id = %d', $sponsor_user_id );
			}
			if ( ! empty( $current ) ) {
				$where .= ' AND date >= ( LAST_DAY( NOW() ) + INTERVAL 1 DAY - INTERVAL 1 MONTH )
				AND date < ( LAST_DAY( NOW() ) + INTERVAL 1 DAY )';
			}
			if ( ! empty( $args['paid'] ) ) {
				$where .= $wpdb_obj->prepare( ' AND paid = %d', $paid );
			}
			$query          = "SELECT SUM(commission) FROM {$wpdb_obj->prefix}bmlm_commission WHERE 1=1 $where";
			$gross_business = $wpdb_obj->get_var( $query );

			return apply_filters( 'bmlm_sponsor_gross_business', wc_format_decimal( $gross_business ), $sponsor_user_id );
		}

		/**
		 * Get sponsors gross business
		 *
		 * @param array $users sponsors array.
		 *
		 * @return array $gross_business.
		 */
		public function bmlm_sponsors_get_gross_business( $users ) {
			$wpdb_obj       = $this->wpdb;
			$query          = "SELECT SUM(commission) as gross_business, user_id FROM {$wpdb_obj->prefix}bmlm_commission WHERE user_id in (" . implode( ',', array_map( 'absint', $users ) ) . ') GROUP BY user_id';
			$gross_business = $wpdb_obj->get_results( $query );
			return apply_filters( 'bmlm_sponsors_gross_business', $gross_business, $users );
		}

		/**
		 * Get Commission type html.
		 *
		 * @param string $type Commission type.
		 *
		 * @return string
		 */
		public function bmlm_get_commission_type_html( $type ) {
			$html = ' <mark class="bmlm-status bmlm-status-' . $type . ' tips"><span> ' . ucfirst( $type ) . ' </span></mark>';

			return apply_filters( 'bmlm_modify_commission_type_html', $html, $type );
		}
	}
}
