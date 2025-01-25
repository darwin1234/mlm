<?php
/**
 * Sponsor Genealogy Tree Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Admin\Genealogy;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Genealogy_Tree' ) ) {
	/**
	 * BMLM Dasboard Genealogy Tree
	 */
	class BMLM_Genealogy_Tree {
		/**
		 * Construct
		 */
		public function __construct() {}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			?>
			<div class="wrap">
				<div id="bmlm-full-container">
					<div class="bmlm-gtree" id="bmlmTree"></div>
				</div>
				<div class="bmlm-search-tree" >
					<div class="bmlm-action-container">
						<button class="button button-primary bmlm-btn-action btn-fullscreen wkmlm-button-primary" onclick="params.funcs.toggleFullScreen()">
						<?php
						esc_html_e( 'Fullscreen', 'binary-mlm' );
						?>
						<span class='icon'/><i class="dashicons dashicons-editor-expand" aria-hidden="true"></i></span></button>
						<button class="button button-primary bmlm-btn-action btn-search" onclick="params.funcs.search()">
							<?php esc_html_e( 'Search', 'binary-mlm' ); ?>
							<span class='icon' />
								<i class="dashicons dashicons-search" aria-hidden="true"></i>
							</span>
						</button>
						<button class="button button-primary bmlm-btn-action btn-search" onclick="params.funcs.expandAll()">
							<?php esc_html_e( 'Expand All', 'binary-mlm' ); ?>
							<span class='icon' />
								<i class="dashicons dashicons-remove" aria-hidden="true"></i>
							</span>
						</button>
						<button class="button button-primary bmlm-btn-action btn-search" onclick="params.funcs.collapsAll()">
							<?php esc_html_e( 'Collapse All', 'binary-mlm' ); ?>
							<span class='icon' />
								<i class="dashicons dashicons-insert" aria-hidden="true"></i>
							</span>
						</button>
					</div>

					<div class="bmlm-user-search-box">
						<div class="input-box">
							<div class="bmlm-close-button-wrapper"><i onclick="params.funcs.closeSearchBox()" class="dashicons dashicons-dismiss" aria-hidden="true"></i></div>
							<div class="input-wrapper">
								<input type="text" class="search-input" placeholder="<?php esc_attr_e( 'Search', 'binary-mlm' ); ?>" />
								<div class="input-bottom-placeholder"><?php esc_html_e( 'By Name, Email', 'binary-mlm' ); ?></div>
							</div>
							<div>
							</div>
						</div>
						<div class="result-box">
							<div class="result-header"><?php esc_html_e( 'RESULTS ', 'binary-mlm' ); ?></div>
							<div class="result-list">
								<div class="buffer"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
