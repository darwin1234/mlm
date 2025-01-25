<?php
/**
 * Pagination.
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Pagination' ) ) {

	/**
	 * Pagination class
	 */
	class BMLM_Pagination {

		/**
		 * Total.
		 *
		 * @var integer $total total records.
		 */
		public $total = 0;

		/**
		 * Current Page
		 *
		 * @var integer $page
		 */
		public $page = 1;

		/**
		 * Limit per page records
		 *
		 * @var integer $limit
		 */
		public $limit = 20;

		/**
		 * Number of links
		 *
		 * @var string $num_links
		 */
		public $num_links = 3;

		/**
		 * Pagination url
		 *
		 * @var string $url
		 */
		public $url = '';

		/**
		 * Less than icon
		 *
		 * @var string $text_first
		 */
		public $text_first = '|&lt;';

		/**
		 * Greater than icon
		 *
		 * @var string $text_next
		 */
		public $text_last = '&gt;|';

		/**
		 * Next text
		 *
		 * @var string $text_next
		 */
		public $text_next = '&gt;';

		/**
		 * Text previous
		 *
		 * @var string $text_prev
		 */
		public $text_prev = '&lt;';

		/**
		 * Output pagination
		 *
		 * @return string $output
		 */
		public function bmlm_render() {
			$total = $this->total;

			if ( $this->page < 1 ) {
				$page = 1;
			} else {
				$page = $this->page;
			}

			if ( ! (int) $this->limit ) {
				$limit = 10;
			} else {
				$limit = $this->limit;
			}

			$num_links = $this->num_links;
			$num_pages = ceil( $total / $limit );

			$this->url = str_replace( '%7Bpage%7D', '{page}', $this->url );

			$output  = '<nav class="woocommerce-pagination">';
			$output .= '<ul class="page-numbers">';

			if ( $page > 1 ) {
				$output .= '<li><a class="page-numbers" href="' . str_replace( array( '/page/{page}', 'page/{page}' ), '', $this->url ) . '">' . $this->text_first . '</a></li>';

				if ( 1 === $page - 1 ) {
					$output .= '<li><a class="prev page-numbers" href="' . str_replace( array( '/page/{page}', 'page/{page}' ), '', $this->url ) . '">' . $this->text_prev . '</a></li>';
				} else {
					$output .= '<li><a class="prev page-numbers" href="' . str_replace( '{page}', $page - 1, $this->url ) . '">' . $this->text_prev . '</a></li>';
				}
			}

			if ( $num_pages > 1 ) {
				if ( $num_pages <= $num_links ) {
					$start = 1;
					$end   = $num_pages;
				} else {
					$start = $page - floor( $num_links / 2 );
					$end   = $page + floor( $num_links / 2 );

					if ( $start < 1 ) {
						$end  += abs( $start ) + 1;
						$start = 1;
					}

					if ( $end > $num_pages ) {
						$start -= ( $end - $num_pages );
						$end    = $num_pages;
					}
				}

				for ( $i = $start; $i <= $end; $i++ ) {
					if ( $page === $i ) {
						$output .= '<li><span aria-current="page" class="page-numbers current">' . $i . '</span></li>';
					} else {

						$output .= '<li><a class="page-numbers" href="' . str_replace( '{page}', $i, $this->url ) . '">' . $i . '</a></li>';

						if ( 1 === $i ) {
							$output .= '<li><a class="page-numbers" href="' . str_replace( array( '/page/{page}', 'page/{page}' ), '', $this->url ) . '">' . $i . '</a></li>';
						}
					}
				}
			}

			if ( $page < $num_pages ) {
				$output .= '<li><a class="next page-numbers" href="' . str_replace( '{page}', $page + 1, $this->url ) . '">' . $this->text_next . '</a></li>';
				$output .= '<li><a class="page-numbers" href="' . str_replace( '{page}', $num_pages, $this->url ) . '">' . $this->text_last . '</a></li>';
			}

			$output .= '</ul>';
			$output .= '</nav>';

			if ( $num_pages < 1 ) {
				$output = '';
			}

			return $output;
		}
	}

}
