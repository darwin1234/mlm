<?php
/**
 * BMLM emails class.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

use WCBMLMARKETING\Includes\Emails;
use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Emails' ) ) {
	/**
	 * Email handler class
	 */
	class BMLM_Emails {

		/**
		 * Transaction class object.
		 *
		 * @var object  $helper Transaction class object.
		 */
		protected $helper;

		/**
		 * Email handler constructor
		 */
		public function __construct() {

			$this->helper = BMLM_Transaction::get_instance();

			add_filter( 'woocommerce_email_classes', array( $this, 'bmlm_add_new_email_notification' ), 10, 1 );
			add_filter( 'woocommerce_email_actions', array( $this, 'bmlm_add_woocommerce_email_actions' ) );
			// Emails prepare actions & filters.
			add_action( 'bmlm_approve_sponsor_account', array( $this, 'bmlm_prepare_sponsor_approval_mail' ), 10, 1 );
			add_filter( 'bmlm_insert_commission_data', array( $this, 'bmlm_prepare_commission_mail' ), 10, 2 );
			add_filter( 'bmlm_create_sponsor_transaction', array( $this, 'bmlm_prepare_transaction_mail' ), 10, 2 );
		}

		/**
		 * Added email notification in WooCommerce email classes.
		 *
		 * @param array $email Emails.
		 *
		 * @return array
		 */
		public function bmlm_add_new_email_notification( $email ) {
			$email['WC_Email_SponsorApprovalMail']  = new Emails\BMLM_Sponsor_Approval_Mail();
			$email['WC_Email_CommissionMail']       = new Emails\BMLM_Commission_Mail();
			$email['WC_Email_TransactionMail']      = new Emails\BMLM_Transaction_Mail();
			$email['WC_Email_BadgeAchievementMail'] = new Emails\BMLM_Badge_Achievement_Mail();

			return $email;
		}

		/**
		 * Added email actions in WooCommerce email actions.
		 *
		 * @param array $actions Actions.
		 *
		 * @return array $actions
		 */
		public function bmlm_add_woocommerce_email_actions( $actions ) {

			$actions[] = 'bmlm_sponsor_approval_mail';
			$actions[] = 'bmlm_commission_mail';
			$actions[] = 'bmlm_transaction_mail';
			$actions[] = 'bmlm_badge_achievement_mail';

			return $actions;
		}

		/**
		 * Prepare sponsor approval email
		 *
		 * @param int $user_id User id.
		 *
		 * @return void
		 */
		public function bmlm_prepare_sponsor_approval_mail( $user_id ) {
			$user = get_user_by( 'ID', $user_id );
			if ( ! empty( $user ) ) {
				$email = $user->data->user_email;
				$data  = array(
					'email'       => $user->data->user_email,
					'name'        => $user->data->user_nicename,
					'referral_id' => get_user_meta( $user_id, 'bmlm_refferal_id', true ),
					'sponsor_id'  => get_user_meta( $user_id, 'bmlm_sponsor_id', true ),
					'level'       => get_user_meta( $user_id, 'bmlm_tree_level', true ),
				);
				do_action( 'bmlm_sponsor_approval_mail', $email, apply_filters( 'bmlm_sponsor_approval_mail_data', $data ) );
			}
		}

		/**
		 * Prepare commission email.
		 *
		 * @param bool  $status Status.
		 * @param array $insert_data Insert data.
		 *
		 * @return bool
		 */
		public function bmlm_prepare_commission_mail( $status, $insert_data ) {
			global $wpdb;
			$wpdb_obj    = $wpdb;
			$insert_data = reset( $insert_data );
			if ( ! empty( $insert_data ) && isset( $insert_data['user_id'] ) ) {
				$user  = get_user_by( 'ID', $insert_data['user_id'] );
				$email = $user->data->user_email;
				$data  = array(
					'email'       => $user->data->user_email,
					'name'        => $user->data->user_nicename,
					'type'        => $insert_data['type'],
					'description' => $insert_data['description'],
					'commission'  => $insert_data['commission'],
					'date'        => $insert_data['date'],
				);
				switch ( $data['type'] ) {
					case 'joining':
						$data['commission_name'] = esc_html__( 'JOIN COMMISSION', 'binary-mlm' );
						do_action( 'bmlm_commission_mail', $email, apply_filters( 'bmlm_' . $data['type'] . '_commission_mail_data', $data ) );
						break;
					case 'sale':
						$data['commission_name'] = esc_html__( 'SALE COMMISSION', 'binary-mlm' );
						do_action( 'bmlm_commission_mail', $email, apply_filters( 'bmlm_' . $data['type'] . '_commission_mail_data', $data ) );
						break;
					case 'levelup':
						$data['commission_name'] = esc_html__( 'LEVEL UP COMMISSION', 'binary-mlm' );
						do_action( 'bmlm_commission_mail', $email, apply_filters( 'bmlm_' . $data['type'] . '_commission_mail_data', $data ) );
						break;
					case 'bonus':
						$badge_id = get_user_meta( $user->data->ID, 'bmlm_badge', true );

						$query = "SELECT name as badge FROM {$wpdb_obj->prefix}bmlm_sponsor_badge WHERE id=$badge_id";

						$badge_name = $wpdb_obj->get_results( $query );

						$data['user_badge'] = $badge_name[0]->badge;

						do_action( 'bmlm_badge_achievement_mail', $email, apply_filters( 'bmlm_' . $data['type'] . '_commission_mail_data', $data ) );
						break;

					default:
						// code...
						break;
				}
			}
			return $status;
		}

		/**
		 * Prepare transaction email.
		 *
		 * @param int   $insert_id Insert id.
		 * @param array $data Data.
		 *
		 * @return int
		 */
		public function bmlm_prepare_transaction_mail( $insert_id, $data ) {
			$user                = get_user_by( 'ID', $data['customer'] );
			$email               = $user->data->user_email;
			$data['email']       = $user->data->user_email;
			$data['name']        = $user->data->user_nicename;
			$data['wallet_bal']  = (float) get_user_meta( $data['customer'], 'wkwc_wallet_amount', true );
			$data['transaction'] = $this->helper->bmlm_get_transaction_by_id( $insert_id );

			do_action( 'bmlm_transaction_mail', $email, apply_filters( 'bmlm_transaction_mail_data', $data ) );

			return $insert_id;
		}
	}
}
