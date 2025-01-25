<?php
/**
 * Sponsor Manifesto Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Sponsors\Tabs;

use WCBMLMARKETING\Helper\Badges\BMLM_Badges;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Account_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Badge_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Current_Month_Business_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_General_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Lifetime_Business_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Transaction_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Wallet_Metabox;
use WCBMLMARKETING\Templates\Admin\Sponsors\Tabs\Metabox\BMLM_Badge_History_Metabox;
use WCBMLMARKETING\Helper\Sponsor\BMLM_Sponsor;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Sponsor_Manifesto' ) ) {
	/**
	 * Sponsor Manifesto
	 */
	class BMLM_Sponsor_Manifesto {
		/**
		 * Sponsor id.
		 *
		 * @var int Sponsor id.
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
		 * @param integer $sponsor_id Sponsor id.
		 */
		public function __construct( $sponsor_id ) {
			$this->sponsor_id = $sponsor_id;
		}

		/**
		 * Ensures only one instance of the class can be loaded.
		 *
		 * @param integer $sponsor_id Sponsor id.
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
			$badge       = array();
			$badge_image = '';

			$sponsor_id = empty( $this->sponsor_id ) ? 0 : intval( $this->sponsor_id );

			if ( $sponsor_id > 0 ) {
				$sponsor_obj = BMLM_Sponsor::get_instance( $sponsor_id );

				$badge_id = $sponsor_obj->bmlm_get_sponsor_badge( $sponsor_id );
				$badges   = $sponsor_obj->bmlm_get_sponsor_badge_list( $sponsor_id );

				if ( ! empty( $badge_id ) ) {
					$badge_obj = new BMLM_Badges();
					$badge     = $badge_obj->bmlm_get_badge( $badge_id );
					if ( ! empty( $badge ) ) {
						$badge_image = wp_get_attachment_image_src( $badge['image'] );
					}
				}
				?>

			<div id="poststuff" class="bmlm-wrapper">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php
								$tr_obj = BMLM_Transaction_Metabox::get_instance( $sponsor_id );
								$tr_obj->get_template();
							?>
						</div>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<p><?php esc_html_e( 'Detail information about sponsor.', 'binary-mlm' ); ?></p>
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="bmlm-general" class="postbox">
								<div class="postbox-header">
									<h2 class=" ui-sortable-handle"><?php esc_html_e( 'General Information', 'binary-mlm' ); ?></h2>

								</div>
								<div class="inside bmlm-inside">
									<div class="table-warpper right">
										<?php
										$tr_obj = BMLM_General_Metabox::get_instance( $sponsor_id );
										$tr_obj->get_template();
										?>
									</div>
									<div class="table-warpper right">
										<?php
										$ac_obj = BMLM_Account_Metabox::get_instance( $sponsor_id );
										$ac_obj->get_template();
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="postbox-container-3" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="bmlm-business" class="postbox">
								<div class="postbox-header">
									<h2 class=" ui-sortable-handle"><?php esc_html_e( 'Business Status', 'binary-mlm' ); ?></h2>

								</div>
								<div class="inside bmlm-inside">
									<div class="table-warpper right">
										<?php
										$tr_obj = BMLM_Lifetime_Business_Metabox::get_instance( $sponsor_id );
										$tr_obj->get_template();
										?>
									</div>
									<div class="table-warpper right">
										<?php
										$tr_obj = BMLM_Current_Month_Business_Metabox::get_instance( $sponsor_id );
										$tr_obj->get_template();
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="postbox-container-4" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="bmlm-wallet" class="postbox">
								<div class="postbox-header">
									<h2 class=" ui-sortable-handle"><?php esc_html_e( 'Balance', 'binary-mlm' ); ?></h2>

								</div>
								<div class="inside bmlm-inside">
									<div class="bmlm-badge-wrapper">
										<?php
										if ( ! empty( $badge_image ) ) {
											echo '<img src="' . esc_url( $badge_image[0] ) . '" height="' . esc_attr( $badge_image[1] ) . '" width="' . esc_attr( $badge_image[2] ) . '" />';
										}
										?>
									</div>
									<div>
										<div class="table-warpper right">
											<?php
											$wallet_obj = BMLM_Wallet_Metabox::get_instance( $sponsor_id );
											$wallet_obj->get_template();
											?>
										</div>
										<div class="table-warpper right">
											<?php
											$badge_obj = BMLM_Badge_Metabox::get_instance( $badge );
											$badge_obj->get_template();
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="postbox-container-5" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="bmlm-badges" class="postbox">
								<div class="postbox-header">
									<h2 class=" ui-sortable-handle"><?php esc_html_e( 'Sponsor Badges', 'binary-mlm' ); ?></h2>

								</div>
								<div class="inside bmlm-inside">
									<div class="table-warpper full">
										<?php
										$bhistory = BMLM_Badge_History_Metabox::get_instance( $badges );
										$bhistory->get_template();
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

				<?php
			}
		}
	}
}
