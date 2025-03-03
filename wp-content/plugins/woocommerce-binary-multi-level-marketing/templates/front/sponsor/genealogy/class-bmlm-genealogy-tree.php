<?php
/**
 * Sponsor Genealogy Tree Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Genealogy;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Genealogy_Tree' ) ) {
	/**
	 * BMLM Genealogy Tree Class
	 */
	class BMLM_Genealogy_Tree {
		/**
		 * Construct
		 */
		public function __construct() {
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			?>
             <?php do_action( 'bmlm_wc_account_menu' ); ?>
             <div class="woocommerce-account woocommerce">
                <div class="woocommerce-MyAccount-content bmlm-account-content">
                    <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                  
                                </div>
                                <div class="col-md-6">
                                    <div class="search-container">
                                       
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="row bmlm-front-action-container mb-3">
                        <!-- Fullscreen Button -->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <button class="bmlm-btn-action btn btn-primary btn-block" onclick="params.funcs.toggleFullScreen()">
                                <?php esc_html_e( 'Fullscreen', 'binary-mlm' ); ?>
                                <span class='icon'>
                                    <i class="dashicons dashicons-editor-expand" aria-hidden="true"></i>
                                </span>
                            </button>
                        </div>

                        <!-- Expand All Button -->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <button class="bmlm-btn-action btn btn-primary btn-block" onclick="params.funcs.expandAll()">
                                <?php esc_html_e( 'Expand All', 'binary-mlm' ); ?>
                                <span class='icon'>
                                    <i class="dashicons dashicons-remove" aria-hidden="true"></i>
                                </span>
                            </button>
                        </div>

                        <!-- Collapse All Button -->
                        <div class="col-md-4">
                            <button class="bmlm-btn-action btn btn-primary btn-block" onclick="params.funcs.collapsAll()">
                                <?php esc_html_e( 'Collapse All', 'binary-mlm' ); ?>
                                <span class='icon'>
                                    <i class="dashicons dashicons-insert" aria-hidden="true"></i>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="wrap bmlm-wrapper">
                        <div id="bmlm-full-container">
                            <div class="bmlm-gtree" id="bmlmTree"></div>
                        </div>
                    </div>

                    <!-- User Search Box -->
                    <div class="bmlm-user-search-box">
                        <div class="input-box">
                            <div class="bmlm-close-button-wrapper">
                                <i onclick="params.funcs.closeSearchBox()" class="dashicons dashicons-dismiss" aria-hidden="true"></i>
                            </div>
                            <div class="input-wrapper">
                                <input type="text" class="form-control search-input" placeholder="<?php esc_attr_e( 'Search', 'binary-mlm' ); ?>" />
                                <div class="input-bottom-placeholder"><?php esc_html_e( 'By Name, Email', 'binary-mlm' ); ?></div>
                            </div>
                        </div>

                        <!-- Result Box -->
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
