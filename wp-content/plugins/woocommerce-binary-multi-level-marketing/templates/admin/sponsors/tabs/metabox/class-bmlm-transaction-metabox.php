<?php
/**
 * Sponsor transaction meta box Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox;

use WCBMLMARKETING\Helper\Transaction\BMLM_Transaction;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Transaction_Metabox' ) ) {
	/**
	 * BMLM Transaction Metabox
	 */
	class BMLM_Transaction_Metabox {
		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Sponsor id.
		 *
		 * @var int Sponsor id.
		 */
		protected $sponsor_id;

		/**
		 * Construct
		 *
		 * @param int $sponsor_id Sponsor ID.
		 */
		public function __construct( $sponsor_id ) {
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
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			$obj        = BMLM_Transaction::get_instance();
			$sponsor_id = empty( $this->sponsor_id ) ? 0 : intval( $this->sponsor_id );

			if ( $sponsor_id > 0 ) {
				$transactions = $obj->bmlm_get_customer_transaction( $sponsor_id );
				?>
			<div id="transaction-notes" class="postbox ">
				<div class="postbox-header">
					<h2 class=" ui-sortable-handle"><?php esc_html_e( 'Latest Transactions', 'binary-mlm' ); ?></h2>
				</div>
				<div class="inside">
					<ul class="transaction_notes bmlm_transaction_notes">
						<?php
						if ( ! empty( $transactions ) ) :
							foreach ( $transactions as $transaction ) :
								$user = get_userdata( $transaction['sender'] );
								?>
								<li rel="<?php echo esc_attr( $transaction['id'] ); ?>" class="note">
									<div class="note_content">
										<p><?php echo esc_html( $transaction['note'] ); ?></p>
										<b><?php esc_html_e( 'Amount -', 'binary-mlm' ); ?> <?php echo wp_kses_post( wc_price( $transaction['amount'] ) ); ?></b>
									</div>
									<p class="meta">
										<abbr class="exact-date" title="">
											<?php echo esc_attr( $transaction['date'] ); ?>
										</abbr>
										&nbsp;&nbsp;<?php esc_html_e( 'by', 'binary-mlm' ); ?> <?php echo esc_attr( ucfirst( $user->display_name ) ); ?>
									</p>
								</li>
								<?php
							endforeach;
						else :
							?>
							<li rel="" class="note">
								<?php esc_html_e( 'No transactions generated yet', 'binary-mlm' ); ?>
							</li>
							<?php
						endif;
						?>
					</ul>
				</div>
			</div>
				<?php
			}
		}
	}
}
