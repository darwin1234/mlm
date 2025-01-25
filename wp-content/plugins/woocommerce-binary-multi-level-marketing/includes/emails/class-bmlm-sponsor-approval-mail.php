<?php
/**
 * BMLM Sponsor Approve Email Class
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes\Emails;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Sponsor_Approval_Mail' ) ) {
	/**
	 * Sponsor Approval Email.
	 */
	class BMLM_Sponsor_Approval_Mail extends \WC_Email {
		/**
		 * Footer.
		 *
		 * @var string
		 */
		public $footer;

		/**
		 * Constructor of the class.
		 *
		 * BMLM_Sponsor_Approval_Mail constructor.
		 */
		public function __construct() {
			$this->id             = 'bmlm_sponsor_approval';
			$this->title          = esc_html__( 'Sponsor Approval', 'binary-mlm' );
			$this->description    = esc_html__( 'Sponsor Approval mail trigger when any Sponsor will approve', 'binary-mlm' );
			$this->heading        = esc_html__( 'Sponsor Approval Mail', 'binary-mlm' );
			$this->subject        = '[' . get_option( 'blogname' ) . ']' . esc_html__( 'Sponsor Account Approved', 'binary-mlm' );
			$this->template_html  = 'emails/bmlm-sponsor-approval.php';
			$this->template_plain = 'emails/plain/bmlm-sponsor-approval.php';
			$this->footer         = esc_html__( 'Thanks for choosing Binary MLM.', 'binary-mlm' );
			$this->template_base  = BMLM_PLUGIN_FILE . 'woocommerce/templates/';

			// Call parent constructor.
			parent::__construct();
			add_action( 'bmlm_sponsor_approval_mail_notification', array( $this, 'trigger' ), 10, 2 );

			// Other settings.
			$this->recipient = get_option( 'admin_email' );
		}

		/**
		 * Trigger.
		 *
		 * @param string $email Email.
		 * @param array  $data Data.
		 */
		public function trigger( $email, $data ) {
			if ( ! empty( $email ) && ! empty( $data ) ) {
				$this->data      = $data;
				$this->recipient = $email;
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading'      => $this->get_heading(),
					'admin_email'        => get_option( 'admin_email' ),
					'data'               => $this->data,
					'blogname'           => $this->get_blogname(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
					'additional_content' => $this->get_additional_content(),
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'email_heading'      => $this->get_heading(),
					'admin_email'        => get_option( 'admin_email' ),
					'data'               => $this->data,
					'blogname'           => $this->get_blogname(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
					'additional_content' => $this->get_additional_content(),
				),
				'',
				$this->template_base
			);
		}
	}
}
