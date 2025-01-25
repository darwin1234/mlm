<?php
/**
 * Dashboard Sponsor Ads Template.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Templates\Front\Sponsor\Ads;

use WCBMLMARKETING\Helper\Sponsor\Products\BMLM_Products;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Sponsor_Ads' ) ) {
	/**
	 * BMLM Dashboard Sponsor Ads
	 */
	class BMLM_Sponsor_Ads extends BMLM_Products {
		/**
		 * Pagination Limit.
		 *
		 * @var int
		 */
		protected $limit = 10;

		/**
		 * Products.
		 *
		 * @var array
		 */
		protected $products = array();

		/**
		 * Page.
		 *
		 * @var int
		 */
		protected $page = 1;

		/**
		 * Total Products.
		 *
		 * @var int
		 */
		protected $total;

		/**
		 * Pagination.
		 *
		 * @var array
		 */
		protected $pagination;

		/**
		 * Sponsor class object
		 *
		 * @var object
		 */
		protected $sponsor;

		/**
		 * Share text
		 *
		 * @var string
		 */
		protected $share_text;

		/**
		 * Sponsor referral id.
		 *
		 * @var string
		 */
		protected $refferal_id;

		/**
		 * Post table.
		 *
		 * @var object
		 */
		protected $post_table;

		/**
		 * Filter name.
		 *
		 * @var object
		 */
		protected $filter_name;

		/**
		 * Constructor
		 *
		 * @param object $sponsor Sponsor class object.
		 */
		public function __construct( $sponsor ) {
			global $bmlm, $wpdb;
			$this->wpdb        = $wpdb;
			$this->post_table  = $this->wpdb->prefix . 'posts';
			$this->filter_name = '';
			$this->sponsor     = $sponsor;
			$paged             = get_query_var( 'leaf' ) ? get_query_var( 'leaf' ) : $this->page;
			$this->page        = 0 === $paged || $paged <= 0 ? 1 : $paged;
			$url               = get_permalink() . 'ads';

			$this->share_text = esc_html__(
				'Shop whenever in doubt',
				'binary-mlm'
			);

			// Filter product.
			if ( ! empty( $_GET['bmlm_product_search_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['bmlm_product_search_nonce'] ) ), 'bmlm_product_search_nonce_action' ) ) {
				if ( ! empty( $_GET['bmlm_search'] ) ) {
					$this->filter_name = sanitize_text_field( wp_unslash( $_GET['bmlm_search'] ) );
				}
			}
			$filter_data = array(
				'start'       => ( $this->page - 1 ) * $this->limit,
				'limit'       => $this->limit,
				'filter_name' => $this->filter_name,
			);

			$this->refferal_id = $this->sponsor->bmlm_get_sponsor_code( get_current_user_id() );
			$this->refferal_id = $bmlm->encrypt_decrypt( trim( $this->refferal_id ), 'e' );
			$this->products    = $this->bmlm_get_sponsor_products( $filter_data );
			$this->total       = $this->bmlm_get_sponsor_product_count( $filter_data );
			$this->pagination  = $bmlm->bmlm_get_pagination( $this->total, $this->page, $this->limit, $url );
			$this->bmlm_share_template();
		}

		/**
		 * Template
		 *
		 * @return void
		 */
		public function get_template() {
			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'bmlm_wc_account_menu' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="wrap bmlm-wrapper">
						<div class="bmlm-sponsor-ads-wrapper">

							<form method="GET" id="bmlm-product-list-form">
								<div class="bmlm-table-action-wrap">
									<div class="bmlm-action-section left">
										<h3 class="title"><?php esc_html_e( 'Sponsor Ads', 'binary-mlm' ); ?></h3>
									</div>
									<div class="bmlm-action-section right bmlm-text-right">
										<input type="text" name="bmlm_search" placeholder="<?php esc_attr_e( 'Search Product', 'binary-mlm' ); ?>" value="<?php echo esc_attr( $this->filter_name ); ?>">
										<?php wp_nonce_field( 'bmlm_product_search_nonce_action', 'bmlm_product_search_nonce' ); ?>
										<input type="submit" value="<?php esc_attr_e( 'Search', 'binary-mlm' ); ?>" data-action="search"/>
									</div>
								</div>
							</form>
							<?php
							echo wp_kses_post( $this->pagination['results'] );
							echo wp_kses_post( $this->pagination['pagination'] );
							?>
							<form action="" method="post" enctype="multipart/form-data" id="bmlm-delete-product">
								<div class="bmlm-table-responsive">
									<table class="table table-bordered table-hover sponsor-ads-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Image', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Product', 'binary-mlm' ); ?></th>
											<th><?php esc_html_e( 'Share URL', 'binary-mlm' ); ?></th>
											<th style="width:20%;"><?php esc_html_e( 'Action', 'binary-mlm' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										if ( $this->products ) {
											foreach ( $this->products as $product ) {
												$product_id    = $product['ID'];
												$product_image = get_the_post_thumbnail_url( $product['ID'] );
												$product_link  = get_permalink( $product_id );
												$share_link    = add_query_arg( 'ref_id', $this->refferal_id, $product_link );
												?>
												<tr>
													<td>
														<img src="<?php echo esc_url( $product_image ); ?>" height="50" width="60" class="bmlm-img-thumbnail"/>
													</td>
													<td>
														<?php if ( strtolower( $product['post_status'] ) === 'publish' ) { ?>
															<a href="<?php echo esc_url( $product_link ); ?>"><?php echo esc_html( $product['post_title'] ); ?></a>
															<?php
														} else {
															echo esc_attr( $product['post_title'] );
														}
														?>
													</td>
													<td>
														<input type="text" value="<?php echo esc_url( $share_link ); ?>" class="bmlm-input">
														<div class="bmlm-tooltip">
															<button class="bmlm-tooltip-btn bmlm_front_share_btn" type="button">
																<span class="bmlm-tooltiptext"><?php esc_html_e( 'Copy to clipboard', 'binary-mlm' ); ?></span>
																<?php esc_html_e( 'Copy share link', 'binary-mlm' ); ?>
															</button>
														</div>
														<a href="javascript:void(0);" data-url="<?php echo esc_url( $share_link ); ?>" class="button bmlm-button bmlm-share-btn bmlm_front_share_btn">
															<i class="dashicons dashicons-share"></i>
															<?php esc_html_e( 'Share', 'binary-mlm' ); ?>
														</a>
														<div class="bmlm-share-box">
														</div>
													</td>
													<td>
														<a target="_blank" class="button" href="<?php echo esc_url( $share_link ); ?>"><?php esc_html_e( 'Open Link', 'binary-mlm' ); ?></a>
													</td>
												</tr>
												<?php
											}
										} else {
											?>
											<tr>
												<td colspan="5" class="bmlm-text-center"><?php esc_html_e( 'No Data Found', 'binary-mlm' ); ?></td>
											</tr>
											<?php
										}
										?>
									</tbody>
									</table>
								</div>
							</form>

							<?php
							echo wp_kses_post( $this->pagination['results'] );
							echo wp_kses_post( $this->pagination['pagination'] );
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Share Template
		 *
		 * @return void
		 */
		public function bmlm_share_template() {
			?>
			<script id="tmpl-bmlm-share-template" type="text/html">
				<div class="bmlm-share-brick">
					<div class="bmlm-share-section">
						<div class="bmlm-social-btns-sm">
							<a class="bmlm-social-btn bmlm-social-btn-fb" onclick="window.open( this.href, 'targetWindow', 'toolbar=0,status=0,width=620,height=500'); return false;" class="bmlm-social-btn bmlm-social-btn-fb" href="http://www.facebook.com/sharer.php?u={{{data.url}}}&description=<?php echo esc_attr( $this->share_text ); ?>">
								<?php esc_html_e( 'Facebook', 'binary-mlm' ); ?>
							</a>
							<a onclick="window.open('https://twitter.com/share?url={{{data.url}}}&hashtags=<?php echo esc_attr( get_bloginfo() ); ?>&related=<?php echo esc_attr( get_bloginfo() ); ?>&text=<?php echo esc_attr( $this->share_text ); ?>', 'sharer', 'toolbar=0,status=0,width=620,height=500')" class="bmlm-social-btn bmlm-social-btn-tw" href="javascript:void(0);">
								<?php esc_html_e( 'Twitter', 'binary-mlm' ); ?>
							</a>
							<a onclick="window.open( 'https://www.linkedin.com/shareArticle?mini=true&url={{{data.url}}}&title=<?php echo esc_attr( $this->share_text ); ?>&summary=<?php echo esc_attr( $this->share_text ); ?>&source=<?php echo esc_attr( site_url() ); ?>', 'sharer', 'toolbar=0,status=0,width=620,height=500')" class="bmlm-social-btn bmlm-social-btn-in" href="javascript:void(0);">
								<?php esc_html_e( 'LinkedIn', 'binary-mlm' ); ?>
							</a>

							<a href="mailto:?subject=<?php echo esc_attr( $this->share_text ); ?>&body={{{data.url}}}" class="bmlm-social-btn bmlm-social-btn-em">
								<?php esc_html_e( 'Send by Email', 'binary-mlm' ); ?>
							</a>
						</div>

					</div>
				</div>
			</script>

			<?php
		}
	}
}
