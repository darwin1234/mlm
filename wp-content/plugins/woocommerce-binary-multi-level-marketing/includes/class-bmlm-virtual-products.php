<?php
/**
 * Virtual product create controller.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Virtual_Products' ) ) {
	/**
	 * BMLM Virtual Products
	 */
	class BMLM_Virtual_Products {
		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'bmlm_create_custom_product' ) );
		}

		/**
		 * Create Custom Virtual Products
		 *
		 * @return void
		 */
		public function bmlm_create_custom_product() {

			$membership_args = array(
				'post_author' => get_current_user_ID(),
				'post_status' => 'publish',
				'post_title'  => 'MLM Membership',
				'post_type'   => 'product',
			);

			$membership           = get_page_by_path( 'mlm-membership', OBJECT, 'product' );
			$membership_image_url = BMLM_PLUGIN_URL . 'assets/images/membership.jpg';

			if ( empty( $membership->post_title ) ) {
				$post_id = $this->bmlm_create_product( $membership_args, $membership_image_url );
				update_post_meta( $post_id, 'membership_virtual_product', 'yes' );
			}
		}

		/**
		 * Create Product
		 *
		 * @param array  $post product arguments.
		 *
		 * @param string $image_url product image.
		 *
		 * @return int
		 */
		public function bmlm_create_product( $post, $image_url ) {
			global $wpdb;
			$wpdb_obj = $wpdb;
			$post_id  = wp_insert_post( $post );
			wp_set_object_terms( $post_id, 'simple', 'product_type' );

			$meta = array(
				'_regular_price'         => '100',
				'_visibility'            => 'hidden',
				'_sku'                   => '',
				'_price'                 => '100',
				'_manage_stock'          => 'no',
				'_stock_status'          => 'instock',
				'total_sales'            => '0',
				'_downloadable'          => 'no',
				'_virtual'               => 'yes',
				'_purchase_note'         => '',
				'_featured'              => 'no',
				'_weight'                => '',
				'_length'                => '',
				'_width'                 => '',
				'_height'                => '',
				'_product_attributes'    => '',
				'_sale_price'            => '',
				'_sale_price_dates_from' => '',
				'_sale_price_dates_to'   => '',
				'_sold_individually'     => 'yes',
				'_backorders'            => 'no',
				'_stock'                 => '',
				'_upsell_ids'            => '',
				'_crosssell_ids'         => '',
				'_product_image_gallery' => '',
				'_tax_status'            => 'none',
			);

			foreach ( $meta as $meta_key => $value ) {
				update_post_meta( $post_id, $meta_key, $value );
			}

			$term_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT term_id FROM {$wpdb_obj->prefix}terms WHERE slug=%s OR slug=%s", 'exclude-from-catalog', 'exclude-from-search' ), ARRAY_A );

			foreach ( $term_ids as $value ) {
				$count = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT count(object_id) FROM {$wpdb_obj->prefix}term_relationships WHERE object_id=%d AND term_taxonomy_id=%d && term_order=%d", $post_id, (int) $value['term_id'], 0 ) );
				if ( empty( $count ) ) {
					$query = $wpdb_obj->prepare( "INSERT INTO {$wpdb_obj->prefix}term_relationships( object_id, term_taxonomy_id, term_order ) VALUES( %d, %d, %d )", $post_id, (int) $value['term_id'], 0 );
					$wpdb_obj->query( $query );
				}
			}

			$uploaddir = wp_upload_dir();
			$filename  = basename( $image_url );
			$filetype  = wp_check_filetype( basename( $filename ), null );

			if ( ! file_exists( $uploaddir['path'] . "/$filename" ) ) {
				file_put_contents( $uploaddir['path'] . '/' . $filename, file_get_contents( $image_url ) );
			}

			$uploadfile     = $uploaddir['path'] . "/$filename";
			$parent_post_id = $post_id;

			$attachment_file = array(
				'guid'           => $uploaddir['path'] . '/' . $filename,
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);
			$attach_id       = wp_insert_attachment( $attachment_file, $uploadfile, $parent_post_id );
			update_post_meta( $post_id, '_thumbnail_id', $attach_id );
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaddir['path'] . '/' . $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			return $post_id;
		}
	}
}
