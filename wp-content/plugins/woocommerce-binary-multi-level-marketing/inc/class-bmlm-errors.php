<?php
/**
 * This file handles Errors
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BMLM_Errors' ) ) {
	/**
	 * Set error codes
	 */
	class BMLM_Errors {
		/**
		 * Error code
		 *
		 * @var int
		 */
		public $error_code = 0;

		/**
		 * Class constructor
		 *
		 * @param integer $error_code Error code.
		 */
		public function __construct( $error_code = 0 ) {
			$this->error_code = $error_code;
		}

		/**
		 * Set error code
		 *
		 * @param integer $code Error code.
		 * @return void
		 */
		public function bmlm_set_error_code( $code ) {

			if ( ! empty( $code ) ) {
				$this->error_code = $code;
			}
		}
		/**
		 * Get error code
		 *
		 * @return integer
		 */
		public function bmlm_get_error_code() {
			return $this->error_code;
		}

		/**
		 * Print notification.
		 *
		 * @param string $message Error message.
		 * @return void
		 */
		public function bmlm_print_notification( $message ) {
			if ( is_admin() ) {
				if ( 0 === intval( $this->error_code ) ) {
					?>
					<div class='notice notice-success is-dismissible'>
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					<?php
				} elseif ( 1 === intval( $this->error_code ) ) {
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					<?php
				}
			}
			if ( ! is_admin() ) {
				if ( 0 === intval( $this->error_code ) ) {
					wc_print_notice( $message, 'success' );
				} elseif ( 1 === intval( $this->error_code ) ) {
					wc_print_notice( $message, 'error' );
				}
			}
		}
	}
}
