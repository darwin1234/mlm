<?php
/**
 * Manage Sponsor badge template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Badge;

use WCBMLMARKETING\Inc\BMLM_Errors;
use WCBMLMARKETING\Helper\Badges\BMLM_Badges;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Manage_Badge' ) ) {
	/**
	 * Add/Edit Sponsor badge class
	 */
	class BMLM_Manage_Badge extends BMLM_Errors {

		/**
		 * Sponsor Badge id
		 *
		 * @var int
		 */
		protected $badge_id;

		/**
		 * Sponsor Badge Helper Variable
		 *
		 * @var object
		 */
		protected $helper;

		/**
		 * Badge Data
		 *
		 * @var array
		 */
		protected $item = array();

		/**
		 * Class constructor
		 *
		 * @param integer $lid badge id.
		 */
		public function __construct( $lid = '' ) {
			$this->badge_id = $lid;
			$this->helper   = new BMLM_Badges();
			$this->bmlm_save_badge();
			$this->bmlm_prepare_items();
		}

		/**
		 * Prepare Items
		 *
		 * @return void
		 */
		public function bmlm_prepare_items() {
			if ( ! empty( $this->badge_id ) ) {
				$this->item = $this->helper->bmlm_get_badge( $this->badge_id );
				if ( empty( $this->item ) ) {
					$message = esc_html__( 'Invalid sponsor badge.', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
					exit();
				}
			}
		}

		/**
		 * Render the bulk edit checkbox.
		 *
		 * @return void
		 */
		public function get_template() {
			$default_data = array(
				'badge_id'     => $this->badge_id,
				'name'         => '',
				'max_business' => '',
				'bonus_amt'    => 0.00,
				'priority'     => '',
				'image'        => '',
				'status'       => 1,
			);

			$_options = array(
				'Disable',
				'Enable',
			);

			$data = wp_parse_args( $this->item, $default_data );
			extract( $data );
			$badge_image_id    = $data['image'];
			$badge_status      = $data['status'];
			$level_default_src = esc_url( wc_placeholder_img_src() );

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( empty( $this->item ) && ! empty( $posted_data ) ) {
				$data['name']         = empty( $posted_data['bmlm_badge_name'] ) ? $data['name'] : $posted_data['bmlm_badge_name'];
				$data['max_business'] = empty( $posted_data['bmlm_max_business'] ) ? $data['max_business'] : $posted_data['bmlm_max_business'];
				$data['bonus_amt']    = empty( $posted_data['bmlm_bonus_amt'] ) ? $data['bonus_amt'] : $posted_data['bmlm_bonus_amt'];
				$data['priority']     = empty( $posted_data['bmlm_priority'] ) ? $data['priority'] : $posted_data['bmlm_priority'];
				$badge_status         = empty( $posted_data['status'] ) ? $data['status'] : $posted_data['status'];
				$badge_image_id       = empty( $posted_data['bmlm_badge_image'] ) ? $data['image'] : $posted_data['bmlm_badge_image'];
			}
			?>
			<form method="POST" id="bmlm-sposnor-badge" novalidate>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="bmlm-badge-name">
									<?php esc_html_e( 'Badge name', 'binary-mlm' ); ?> <span class="bmlm-badge-error-color" > * </span>
								</label>
							</th>
							<td class="forminp forminp-text">
								<?php echo wc_help_tip( esc_html__( 'Add Badge Name Like: Gold', 'binary-mlm' ), false ); ?>
								<input type="text" class="regular-text" name="bmlm_badge_name" id="bmlm-badge-name" value="<?php echo esc_attr( $data['name'] ); ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc"><label for="bmlm-max-business"><?php esc_html_e( 'Maximum Business', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label></th>
							<td class="forminp forminp-text">
								<?php echo wc_help_tip( esc_html__( 'Add Maximum Business Amount', 'binary-mlm' ), false ); ?>
								<input class="regular-text" type="text" name="bmlm_max_business" id="bmlm-max-business" value="<?php echo esc_attr( $data['max_business'] ); ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc"><label for="bmlm-bonus-amount"><?php esc_html_e( 'Bonus Amount', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label></th>
							<td class="forminp forminp-text">
								<?php echo wc_help_tip( esc_html__( 'Add Bonus Amount', 'binary-mlm' ), false ); ?>
								<input class="regular-text" type="text" name="bmlm_bonus_amt" id="bmlm-bonus-amount" value="<?php echo esc_attr( $data['bonus_amt'] ); ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc"><label for="bmlm-priority"><?php esc_html_e( 'Priority', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label></th>
							<td class="forminp forminp-text">
								<?php echo wc_help_tip( esc_html__( 'Add Priority to which badge apply first', 'binary-mlm' ), false ); ?>
								<input type="text" class="regular-text" name="bmlm_priority" id="bmlm-priority" value="<?php echo esc_attr( $data['priority'] ); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<label for="bmlm-badge-status"><?php esc_html_e( 'Status', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label>
							</th>
							<td>
								<?php echo wc_help_tip( esc_html__( 'Select the status', 'binary-mlm' ), false ); ?>
								<select name="bmlm_badge_status" id="bmlm-badge-status" class="regular-text">
									<?php
									$selected = ! empty( $badge_status ) ? $badge_status : '';
									foreach ( $_options as $option_key => $option_value ) :
										?>
										<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_html( $option_value ); ?></option>
										<?php
										endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc"><label for="bmlm-level-image"><?php esc_html_e( 'Badge Image', 'binary-mlm' ); ?><span class="bmlm-badge-error-color" > * </span></label></th>
							<td class="forminp forminp-text">
								<?php echo wc_help_tip( esc_html__( 'Select the Badge Image', 'binary-mlm' ), false ); ?>
								<button class="button button-primary bmlm_upload_badge"><?php esc_html_e( 'Upload', 'binary-mlm' ); ?></button><br/>
								<?php $badge_image = ! empty( $badge_image_id ) ? wp_get_attachment_url( $badge_image_id ) : $level_default_src; ?>
								<div class="bmlm-image-wrapper">
									<img src="<?php echo esc_url( $badge_image ); ?>" class="bmlm-badge-img" />
								</div>
								<input type="hidden" id="bmlm-badge-image" name="bmlm_badge_image" value="<?php echo esc_attr( $badge_image_id ); ?>" />
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="bmlm_badge_id" value="<?php echo esc_attr( $data['badge_id'] ); ?>" />
				<?php
				wp_nonce_field( 'bmlm_badge_nonce_action', 'bmlm_badge_nonce' );

				$action = empty( $_GET['action'] ) ? '' : htmlspecialchars( wp_unslash( wc_clean( $_GET['action'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$submit_name = ( 'edit' === $action ) ? esc_html__( 'update', 'binary-mlm' ) : esc_html__( 'save', 'binary-mlm' );
				submit_button( ucfirst( $submit_name ), 'primary', 'bmlm_' . $submit_name . '_badge' );
				?>
			</form>
			<?php
		}

		/**
		 * Save badge function
		 *
		 * @return void
		 */
		public function bmlm_save_badge() {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ( ! empty( $posted_data['bmlm_update_badge'] ) || ! empty( $posted_data['bmlm_save_badge'] ) ) && ! empty( $posted_data['bmlm_badge_nonce'] ) && wp_verify_nonce( wp_unslash( $posted_data['bmlm_badge_nonce'] ), 'bmlm_badge_nonce_action' ) ) {
				$this->bmlm_validate( $posted_data );
			}
		}

		/**
		 * Validate badge function.
		 *
		 * @param array $data Badge form data.
		 *
		 * @return void
		 */
		public function bmlm_validate( $data ) {
			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Badge args.
			$badge_id     = ! empty( $data['bmlm_badge_id'] ) ? sanitize_text_field( $data['bmlm_badge_id'] ) : '';
			$name         = ! empty( $data['bmlm_badge_name'] ) ? sanitize_text_field( $data['bmlm_badge_name'] ) : '';
			$max_business = ! empty( $data['bmlm_max_business'] ) ? sanitize_text_field( wp_unslash( $data['bmlm_max_business'] ) ) : '';
			$bonus_amt    = ! empty( $data['bmlm_bonus_amt'] ) ? sanitize_text_field( wp_unslash( $data['bmlm_bonus_amt'] ) ) : '';
			$priority     = ! empty( $data['bmlm_priority'] ) ? sanitize_text_field( wp_unslash( $data['bmlm_priority'] ) ) : '';
			$image        = ! empty( $data['bmlm_badge_image'] ) ? sanitize_text_field( wp_unslash( $data['bmlm_badge_image'] ) ) : '';
			$status       = isset( $data['bmlm_badge_status'] ) ? intval( $data['bmlm_badge_status'] ) : '';

			if ( ! empty( $badge_id ) ) {
				$slug      = $this->helper->bmlm_generate_badge_slug( $name );
				$is_exists = $this->helper->bmlm_is_duplicate_badge( $slug, $badge_id );
				if ( $is_exists ) {
					$message = esc_html__( 'Badge name is already in use, try different name.', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
				}
			} else {
				$slug      = $this->helper->bmlm_generate_badge_slug( $name );
				$is_exists = $this->helper->bmlm_is_duplicate_badge( $slug, '' );
				if ( $is_exists ) {
					$message = esc_html__( 'Badge name is already in use, try different name.', 'binary-mlm' );
					parent::bmlm_set_error_code( 1 );
					parent::bmlm_print_notification( $message );
				}
			}

			if ( empty( $name ) ) {
				$message = esc_html__( 'Badge name is mandatory.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $name ) ) {
				$message = esc_html__( 'Enter a Valid Badge name.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}

			if ( empty( $max_business ) ) {
				$message = esc_html__( 'Max business amount is mandatory.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( ! is_numeric( $max_business ) || $max_business <= 0 ) {
				$message = esc_html__( 'Max business amount is invalid.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( empty( $bonus_amt ) ) {
				$message = esc_html__( 'Bonus amount is mandatory.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( ! is_numeric( $bonus_amt ) || $bonus_amt <= 0 ) {
				$message = esc_html__( 'Bonus amount is invalid.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( empty( $priority ) ) {
				$message = esc_html__( 'Priority is mandatory.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( ! is_numeric( $priority ) || $priority <= 0 ) {
				$message = esc_html__( 'Priority is invalid.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( empty( $image ) ) {
				$message = esc_html__( 'Badge image is mandatory.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}
			if ( 1 !== $status && 0 !== $status ) {
				$message = esc_html__( 'Badge status is invalid.', 'binary-mlm' );
				parent::bmlm_set_error_code( 1 );
				parent::bmlm_print_notification( $message );
			}

			if ( 0 === parent::bmlm_get_error_code() ) {
				if ( ! empty( $data['bmlm_save_badge'] ) ) {
					$is_done = $this->helper->bmlm_create_badge( $data );
					if ( $is_done ) {
						$message = esc_html__( 'Sponsor badge created successfully.', 'binary-mlm' );
						unset( $_POST );
					}
				} elseif ( $data['bmlm_update_badge'] ) {
					$is_done = $this->helper->bmlm_update_badge( $data );
					if ( $is_done ) {
						$message = esc_html__( 'Sponsor badge updated successfully.', 'binary-mlm' );
						unset( $_POST );
					}
				}
				parent::bmlm_set_error_code( 0 );
				parent::bmlm_print_notification( $message );
			}
		}
	}
}
