<?php
/**
 * Query variables
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

namespace WCBMLMARKETING\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'BMLM_Query_Vars' ) ) {

	/**
	 * Query functions class
	 */
	class BMLM_Query_Vars {

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			add_filter( 'query_vars', array( $this, 'bmlm_insert_custom_query_variables' ) );
			add_filter( 'rewrite_rules_array', array( $this, 'bmlm_insert_custom_rules' ) );
		}

		/**
		 * Insert Custom Query Variables
		 *
		 * @param array $vars Query Variables.
		 * @return $vars
		 */
		public function bmlm_insert_custom_query_variables( $vars ) {
			$new_vars = array( 'main_page', 'action', 'leaf' );

			array_push( $vars, ...$new_vars );

			return $vars;
		}

		/**
		 * Insert custom query rules
		 *
		 * @param array $rules Rules.
		 * @return $rules
		 */
		public function bmlm_insert_custom_rules( $rules ) {
			global $bmlm;
			$page_name = $bmlm->sponsor_page_slug;
			$new_rules = array(
				$page_name . '/([-a-z]+)/transaction/([-a-z0-9]+)?' => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&action=transaction&leaf=$matches[2]',
				$page_name . '/([-a-z]+)/page/([0-9]+)?' => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]&action=page&leaf=$matches[2]',
				$page_name . '/(.+)/?'                   => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]',
			);

			$rules = array_merge( $new_rules, $rules );
			return $rules;
		}
	}
}
