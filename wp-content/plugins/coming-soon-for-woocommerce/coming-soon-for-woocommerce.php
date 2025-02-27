<?php
/*
 * Plugin Name: Coming Soon Badge for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/coming-soon-for-woocommerce/
 * Description: Show coming soon badge over products
 * Version: 1.0.19
 * Author: wpcentrics
 * Author URI: https://www.wp-centrics.com
 * Text Domain: coming-soon-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 4.7
 * Tested up to: 6.7
 * WC requires at least: 3.0
 * WC tested up to: 9.6
 * Requires PHP: 7.0
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Coming Soon for WooCommerce
*/

defined( 'ABSPATH' ) || exit;

define ('COMING_SOON_WC_VERSION', '1.0.19' );
define ('COMING_SOON_WC_PATH', dirname(__FILE__) . '/' );
define ('COMING_SOON_WC_URL', plugin_dir_url( __FILE__ ) );

class Coming_Soon_WC {

	private $options = array ();
	private $elementor_el = false;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @version 1.0.14
	 *
	 */
	public function __construct() {

		$this->load_options();

		// Init stuff + comming products let  term
		add_action( 'init', array ( $this, 'init' ) );

		// Admin-side interface: styles & scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_load_styles_and_scripts' ) );

		// Admin-side: wizard & five stars
		add_action( 'wp_ajax_wc_coming_soon_wizard', array($this, 'wc_coming_soon_wizard') );

		// Admin-side interface: product post box
		add_action( 'post_submitbox_misc_actions', array( $this, 'product_arrival_fields' ) );

		// Admin side: product save
		add_action( 'woocommerce_after_' . 'product' . '_object_save', array ($this, 'product_saved'), 10, 2);

		// Admin-side interface: configuration
		add_action( 'admin_menu', array ($this, 'admin_submenu'), 80 );

		// Front: JS
		add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue_scripts' ) );

		// Inline CSS:
		add_action('wp_head', array( $this, 'echo_inline_styles'), 100);
		
		// Front: Loop
		add_action( 'woocommerce_before_shop_loop_item_title', array ( $this, 'display_coming_soon_loop_wc_open' ), 9 );
		add_action( 'woocommerce_before_shop_loop_item_title', array ( $this, 'display_coming_soon_loop_wc_close' ), 11 );
		
		// Actions on single product page
		add_action( 'woocommerce_before_template_part', array ( $this, 'wc_before_template_part' ), 10, 4 );
		add_action( 'woocommerce_after_template_part',  array ( $this, 'wc_after_template_part' ),  10, 4 );

		// Link to re-start wizard
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_plugin_action_link' ) );
		
		// Support for elementor background product image
		add_action( 'elementor/frontend/before_render', array ( $this, 'elementor_before_render' ) );
		add_filter( 'elementor/frontend/the_content',   array ( $this, 'elementor_the_content' ) );
		
		// Support for divi background product image
		add_filter( 'et_module_process_display_conditions', array ( $this, 'divi_feat_post_image_background' ), 10, 3 );
	}
	
	/**
	 * After all plugins are loaded, we will initialise everything
	 *
	 * @since 1.0.0
	 *
	 */
	function init() {

		// Register plugin text domain for translations files
		load_plugin_textdomain( 'coming-soon-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' );
		
	}
		
	/**
	 * Load options at plugin inisialitation, and maybe first install.
	 *
	 * @since 1.0.0
	 * @version 1.0.11
	 */
	public function load_options() {
		
		$should_update = false;
		
		// Set default options, for first installation
		$options = array(
			'first_version'    => COMING_SOON_WC_VERSION,
			'show_wizard'      => time() - 1, // now
			'first_install'    => time(),
			'five_stars'       => time() + (60 * 60 * 24 * 10), // ten days
			'current_version'  => '',
			
			'badge_loop_style' => 'circle-text',
			'badge_loop_text'  => __('COMING SOON', 'coming-soon-for-woocommerce'),
			'badge_loop_opts'  => array (
									'font-size'       => '14',
									'font-weight'     => '600',
									'color'           => 'FFFFFF',
									'background'      => '555555',
									'width'           => '70',
									'height'          => '70',
									'padding-top'     => '5',
									'padding-bottom'  => '5',
									'padding-left'    => '5',
									'padding-right'   => '5',
									'background-size' => 'contain',
									'align-hor'       => 'left',
									'margin-hor'      => '10',
									'align-ver'       => 'top',
									'margin-ver'      => '10',
									'border-radius'   => '5',
									'custom_css' => '',
									'custom-img'      => ''
			),

			'badge_product_style' => 'circle-text',
			'badge_product_text'  => __('COMING SOON', 'coming-soon-for-woocommerce'),
			'badge_product_opts'  => array (
									'font-size'       => '28', // double as loop
									'font-weight'     => '600',
									'color'           => 'FFFFFF',
									'background'      => '555555',
									'width'           => '140', // double as loop
									'height'          => '140', // double as loop
									'padding-top'     => '5',
									'padding-bottom'  => '5',
									'padding-left'    => '5',
									'padding-right'   => '5',
									'background-size' => 'contain',
									'align-hor'       => 'left',
									'margin-hor'      => '20', // double as loop
									'align-ver'       => 'top',
									'margin-ver'      => '20', // double as loop
									'border-radius'   => '10', // double as loop
									'custom_css' => '',
									'custom-img'      => ''
			),

		);
		
		// Load options from DB and overwrite defaults
		$opt_db = get_option( 'coming-soon-for-woocommerce', array() );
		if (is_array($opt_db)) {
			foreach ($opt_db as $key=>$value) {
				$options[$key] = $value;
			}
		}

		// First install?
		if ($options['current_version'] == '') {
			$options['current_version'] = COMING_SOON_WC_VERSION;
			$should_update = true;
		}
		
		// Plugin Update?
		if (version_compare($options['current_version'], COMING_SOON_WC_VERSION, '<') ) {
			$options['current_version'] = COMING_SOON_WC_VERSION;
			$should_update = true;
		}

		// Five stars Remind later bug (previous releases)
		if ( $options['five_stars'] > time() * 2 ) {
			$options['five_stars'] = time() + 60 * 60 * 24;
			$should_update = true;
		}
		
		$this->options = $options;
		
		if ($should_update) {
			
			$this->set_options($options);
		}
	}
	
	public function get_options() {
		
		return $this->options;
	}

	public function set_options($options) {

		update_option( 'coming-soon-for-woocommerce', $options, true );
		$this->options = $options;
	}

	/**
	 * Admin-side styles and scripts
	 *
	 * @since 1.0.0
	 *
	 */
	public function admin_load_styles_and_scripts () {

		// Only on WC settings > shipping tab we will load the admin script, for performance reasons
		//if ( 'post.php' == basename($_SERVER["SCRIPT_NAME"]) || 'post-new.php' == basename($_SERVER["SCRIPT_NAME"]) ||  isset($_GET['page'] ) && $_GET['page'] == 'coming-soon-wc-opts' ) {

			if ( isset($_GET['page'] ) && $_GET['page'] == 'coming-soon-wc-opts' ) {

				if ( ! did_action( 'wp_enqueue_media' ) ) wp_enqueue_media();

				wp_register_script( 'coming_soon_wc_admin_script', COMING_SOON_WC_URL . 'assets/js/admin-coming-soon-wc.js', array( 'jquery-core', 'wp-color-picker' ), COMING_SOON_WC_VERSION );
				wp_register_style ( 'coming_soon_wc_admin_style', COMING_SOON_WC_URL . 'assets/css/admin-coming-soon-wc.css', array('wp-color-picker'), COMING_SOON_WC_VERSION );

				wp_enqueue_script ( 'coming_soon_wc_admin_script' );
				wp_enqueue_style  ( 'coming_soon_wc_admin_style' );
			} else {
				
				// Five stars / Wizard dialogs isolated for performance
				wp_register_script( 'coming_soon_wc_admin_light_script', COMING_SOON_WC_URL . 'assets/js/admin-coming-soon-wc-light.js', array('jquery-core'), COMING_SOON_WC_VERSION );
				wp_enqueue_script ( 'coming_soon_wc_admin_light_script' );
			}

		//}
	}

	/**
	 * Check PHP version and WooCommerce
	 *
	 * @since 1.0.8
	 *
	 */
	function is_wc() {
		if ( version_compare( phpversion(), '7.0', '<') ) return false;
		if ( !function_exists('WC') || version_compare( WC()->version, '2.6.0', '<') ) return false;
		return true;
	}

	/***********************************************************
	  Admin product edition interface + save comming soon date
	 ***********************************************************/

	/**
	 * Print all the admin coming soon interface into post box
	 * Inspired on:   https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/meta-boxes.php
	 *
	 * @since 1.0.0
	 * @version 1.0.16
	 *
	 */

	function product_arrival_fields( $post ) {
						
		$post_id          = (int) $post->ID;
		$post_type        = $post->post_type;

		if ( 'product' !== $post_type ) {
			return;
		}

		$post_type_object = get_post_type_object( $post_type );
		$can_publish      = current_user_can( $post_type_object->cap->publish_posts );

		// Contributors don't get to choose the date of publish.
		if ( !$can_publish ) return; 

		$switch = false;

		if( $WC_Product = wc_get_product($post_id) )
		{			
			$arrival_date = $this->get_product_arrival_date( $WC_Product ); 
			$switch = $this->time_diff( $arrival_date ) > 0;
		}
		?>
		<div class="misc-pub-section coming-soon-wc">
			<input type="checkbox" name="coming_soon_wc_switch" value="1" <?php if ($switch) echo 'checked'; ?> >
			<?php esc_html_e('Show Coming Soon badge', 'coming-soon-for-woocommerce'); ?>
			<input type="hidden" name="coming_soon_wc_post_id" value="<?php echo esc_attr( $WC_Product->get_id() ); ?>" />
		</div>
		<?php
	}

	/**
	 * Save the coming soon time fields from product editor
	 *
	 * @since 1.0.0
	 * @version 1.0.16
	 *
	 */

	function product_saved( $WC_Product, $WC_Data_Store_WP ) {

		$product_id = $WC_Product->get_id();

		if ( !isset( $_POST['coming_soon_wc_post_id'] ) ) return;

		if ( $_POST['coming_soon_wc_post_id'] == 0 || $_POST['coming_soon_wc_post_id'] == $product_id ) {

			$switch = rest_sanitize_boolean ( isset( $_POST['coming_soon_wc_switch'] ) && $_POST['coming_soon_wc_switch'] == 1 );

			if ($switch) {
				$arrival_date = '2100-01-01 00:00:00'; // Really far future
			
			} else {
				
				$arrival_date = $WC_Product->get_date_created();
				
				if ( is_null($arrival_date) ) {
					// Can't get the date from product creation
					$arrival_date = date('Y-m-d H:i:s', time() );

				} else {

					// Let's put date product creation (it will syncronize coming soon with future products publication)
					$arrival_date = date('Y-m-d H:i:s', $arrival_date->getTimestamp() );
				}
			}

			// save arrival date
			//update_post_meta( $product_id, '_coming_soon_wc_arrival', $arrival_date );
			$WC_Product->update_meta_data( '_coming_soon_wc_arrival', $arrival_date );
			$WC_Product->save_meta_data();
		}
	}

	/**********************************
	  Admin config pane 
	 ***********************************/

	/**
	 * Add submenu link on the WooCommerce admin menu option
	 *
	 * @since 1.0.0
	 *
	 */
	function admin_submenu() {
		add_submenu_page( 'woocommerce', esc_html__('Coming Soon for WooCommerce configuration', 'coming-soon-for-woocommerce'), esc_html__('Coming Soon config', 'coming-soon-for-woocommerce'), 'manage_options', 'coming-soon-wc-opts', array ($this, 'admin_pane'), 20 );
	}
	
	/**
	 * Require the admin pane printing and his functionalitiy
	 *
	 * @since 1.0.0
	 *
	 */
	function admin_pane() {
		require( COMING_SOON_WC_PATH . 'inc/admin-pane.php');
	}
	
	/**
	* Add link on the plugin list, to re-start the wizard
	*
	*/
	public static function add_plugin_action_link( $links ){
	
		$start_link = array(
			'<a href="'. admin_url( 'admin.php?page=coming-soon-wc-opts' )
			 .'" style="color: #a16696; font-weight: bold;">'. esc_html__( 'Configure', 'coming-soon-for-woocommerce') .'</a>',
		);
	
		return array_merge( $start_link, $links );
	}	

	/**
	 * Ajax wizard / five stars, from AJAX call.
	 *
	 * @since 1.0.8
	 */
	function wc_coming_soon_wizard() {

		$what  = isset($_GET['ajax'])  ? sanitize_key ( $_GET['ajax'] )  : '';
		$key   = isset($_GET['key'])   ? sanitize_key ( $_GET['key'] )   : '';
		$when  = isset($_GET['param']) ? sanitize_key ( $_GET['param'] ) : '';
				
		// Dimiss wizard / five stars (here key is not used) 
		if ( !in_array($what, array('wizard', 'five-stars'), true ) || !in_array($when, array('now', 'later', 'off'), true ) ) {
			echo '0';
			exit();
		}
		
		$this->update_wizard_opts($what, $when, true);
	}

	/**
	 * Ajax or URL
	 * @since 1.0.8
	 * @version 1.0.11
	 *
	 * @param $what: wizard | five-stars
	 * @param $when: now | later | off
	 * @param $ajax: boolean
	 */
	function update_wizard_opts($what, $when, $ajax = false) {

		$options = $this->options;

		// We should show now / later / hide wizard forever?
		if ($what == 'wizard') {
		
			// Request 5 stars now can irritate
			$five_stars_time = time() + 60*60*24;
		
			if ($when=='now') $options['show_wizard'] = time() -1; // Now
		
			if ($when=='off') $options['show_wizard'] = time() * 2; // Hide forever

			if ($when=='later') {
				$options['show_wizard'] = time() + 60*60*24*7; // a week
				$five_stars_time = time() + 60*60*24*8; // 8 days
			}
			
			if ( $options['five_stars'] < $five_stars_time) $options['five_stars'] = $five_stars_time;
		
			$this->set_options($options);

		// We should show later / hide five stars forever? (failed AJAX)
		} elseif ($what == 'five-stars') {
		
			if ($when=='off')   $options['five_stars'] = time() * 2; // Hide forever
			if ($when=='later') $options['five_stars'] = time() + 60*60*24*7; // a week
		
			$this->set_options($options);
		}
		
		if ($ajax) {
			echo '1';
			exit();
		}
	}


	/**********************************
	  Front
	 ***********************************/

	/**
	 * Front side JS
	 *
	 * @since 1.0.0
	 *
	 */
	function front_enqueue_scripts () {

		wp_register_script( 'coming_soon_wc_script', COMING_SOON_WC_URL . 'assets/js/coming-soon-wc.js', array('jquery-core' ), COMING_SOON_WC_VERSION );
		wp_enqueue_script  ( 'coming_soon_wc_script' );
	}

	/**
	 * Inline CSS
	 *
	 * @since 1.0.0
	 *
	 */
	function echo_inline_styles () {
		
		$inline_css = $this->get_inline_scripts('loop') . $this->get_inline_scripts('product');
		if ($inline_css != '') echo '<style>'.$inline_css.'</style>';
	}

	/**
	 * Dual purpose inline CSS generator: loop and product styles
	 *
	 * @since 1.0.0
	 * @version 1.0.12
	 */
	function get_inline_scripts ($purpose = 'loop') {

		$bg_url= '';

		$inline_css  = "
.coming_soon_wc_".$purpose."_wrapper {
	position:relative;
}
.elementor_col_coming_soon_wrapper {
	position:relative;
}
.elementor_col_coming_soon_wrapper .coming_soon_wc_".$purpose."_wrapper {
	position:static;
}
";

		$opts         = $this->get_options();
		$badge_opts   = $opts['badge_'.$purpose.'_opts'];
		$badge_style  = $opts['badge_'.$purpose.'_style'];
		
		if ( substr($badge_style, 0, 6) == 'image-') {

			$n_image      = intval(substr($badge_style, 6));
			$badge_style  = substr($badge_style, 0, 5);

			$bg_url       = COMING_SOON_WC_URL . 'assets/img/coming_soon_'.$n_image.'.png';
		}

		$properties_px = array ( 'font-size', 'width', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right' );

		if ( $badge_style != 'circle-text' ) $properties_px[] = 'border-radius';
		if ( $badge_style != 'line-text' )   $properties_px[] = 'height';
		
		switch ($badge_style) {

			case 'off':
				break;

			case 'square-text':
			case 'line-text':
			case 'circle-text':

				if ( $badge_opts['custom_css'] == '1' ) {
					break;
				}	

				$inline_css  .= "
.coming_soon_wc_".$purpose."_wrapper img {
	position: static;
}
.coming_soon_wc_".$purpose."_wrapper .coming_soon_text {
	position:absolute;
	z-index: 1;
	display: flex;
	justify-content: center;
	align-items: center;
	text-align: center;
	box-sizing: border-box;
	line-height:1.1em;
	overflow: hidden;
";

				foreach ( $properties_px as $field ) {
					
					$inline_css .= "\r\n	" . esc_html($field) . ": " . esc_html($badge_opts[$field]) . "px;";
				}

				$inline_css .= "\r\n	font-weight: " . esc_html($badge_opts['font-weight']) . ";";
				$inline_css .= "\r\n	color: #" . esc_html($badge_opts['color']) . ";";
				$inline_css .= "\r\n	background: #" . esc_html($badge_opts['background']) . ";";

				if ( $opts['badge_'.$purpose.'_style'] == 'circle-text' ) {

					$inline_css .= "\r\n	border-radius: 50%;";
				}

				// Margin are the value of selected position
				$align_hor = $badge_opts['align-hor'];

				if ($align_hor == 'center') {
					$inline_css .= "\r\n	left: 50%; margin-left: -" . esc_html( floor( $badge_opts['width'] / 2 ) ) . "px;";
				} else {
					$inline_css .= "\r\n	" . esc_html($align_hor) . ': ' . esc_html($badge_opts['margin-hor']) . "px;";
				}
			
				$align_ver = $badge_opts['align-ver'];
				if ($align_ver == 'middle') {
					
					if ( $badge_style == 'line-text') {
						// Unknown height element must be vertically centered later through JavaScript
						$inline_css .= "\r\n	top: 50%;  margin-top: -15px";
					} else {
						$inline_css .= "\r\n	top: 50%; margin-top: -" . esc_html(floor( $badge_opts['height'] / 2 ) ) . "px;";
					}
				} else {
					$inline_css .= "\r\n	" . esc_html($align_ver) . ": " . esc_html($badge_opts['margin-ver']) . "px;";
				}
				$inline_css .= "\r\n}";
			
				break;

			case 'custom-image':

				if ( $image = wp_get_attachment_image_src( intval ( $badge_opts['custom-img'] ), 'woocommerce_thumbnail' ) ) {
					$bg_url = $image[0];
				}

			case 'image':

				$bg_size = $badge_opts['background-size'];
				if ( intval($bg_size) != 0 ) $bg_size .= '%';

				// Background position: middle is not allowed, also center for vertical
				$align_ver_bg = $badge_opts['align-ver'];
				if ( $align_ver_bg == 'middle' ) $align_ver_bg = 'center';

				$align_hor = $badge_opts['align-hor'];

				$inline_css  = "
.coming_soon_wc_".$purpose."_wrapper img {
	position: static;
}
.coming_soon_wc_".$purpose."_wrapper .coming_soon_img {
	position: absolute;
	z-index: 1;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
	background: url(" . esc_html($bg_url) . ") no-repeat " . esc_html($align_hor) . " " . esc_html($align_ver_bg) . ";
	background-size: " . esc_html($bg_size) . ";
}
";

				break;
		}

		return $inline_css;
	}

	/**
	 * Open coming soon in the products of the loop
	 *
	 * @since 1.0.0
	 * @version 1.0.14
	 *
	 */	
	function display_coming_soon_loop_wc_open() {
		
		echo $this->get_open_badge('loop');
	}

	/**
	 * Close coming soon in the products of the loop
	 *
	 * @since 1.0.0
	 * @version 1.0.14
	 *
	 */	
	function display_coming_soon_loop_wc_close() {

		echo $this->get_close_badge('loop');
	}

	/**
	 * Before get template part. Can debug for loged admin.
	 *
	 * @since 1.0.0
	 * @version 1.0.14
	 *
	 */	
	function wc_before_template_part ( $template_name, $template_path, $located, $args ) {

		if ( isset($_GET['coming-soon-wc']) && $_GET['coming-soon-wc'] == 'show-placeholders' && current_user_can(manage_options) ) {
			echo '<!-- CSW debug, open: ' . $template_name . '-->';
		}
				
		if ($template_name == 'single-product/product-image.php') {
			
			echo $this->get_open_badge('product');
			echo $this->get_close_badge('product');
		}
		
	}
		
	/**
	 * After get template part. Can debug for loged admin.
	 *
	 * @since 1.0.0
	 *
	 */	
	function wc_after_template_part ( $template_name, $template_path, $located, $args ) {

		if ( isset($_GET['coming-soon-wc']) && $_GET['coming-soon-wc'] == 'show-placeholders' && current_user_can(manage_options) ) {
			echo '<!-- CSW debug, closing: ' . $template_name . '-->';
		}
	}

	/**
	 * Open badge, double purpose: loop and product
	 *
	 * @since 1.0.14
	 *
	 */	
	function get_open_badge ( $purpose = 'loop' ) {
		
		global $product;
		
		if ( !$product || !$product instanceof WC_Product) 
			return '';

		$badge_style = $this->options['badge_'.$purpose.'_style'];
		
		if ( $badge_style == 'off' ) 
			return '';
		
		$code_html = '';

		if ( substr($badge_style, 0, 6) == 'image-') {
			//$n_image = intval(substr($badge_style, 6));
			$badge_style = substr($badge_style, 0, 5);
		}
		if ( $this->is_product_not_arrived ($product) ) {

			$extra_css = array();
			
			if ( $badge_style == 'line-text' && $this->options['badge_'.$purpose.'_opts']['align-ver'] == 'middle' ) $extra_css[] = 'coming-soon-wc-js-middle';

			$code_html .= '<div class="coming_soon_wc_'.$purpose.'_wrapper ' . implode(' ', $extra_css) . '">';

			switch ($badge_style) {

				case 'circle-text':
				case 'square-text':
				case 'line-text':
									
					$code_html .= '<span class="coming_soon_text">' . esc_html( $this->options['badge_'.$purpose.'_text'] ) . '</span>';
					break;

				case 'image':
				case 'custom-image':

					$code_html .= '<span class="coming_soon_img"></span>';
					break;
			}
		}
		return $code_html;
	}

	/**
	 * Close badge, double purpose: loop and product
	 *
	 * @since 1.0.14
	 *
	 */	
	function get_close_badge ( $purpose = 'loop' ) {

		global $product;
		if ( !$product || !$product instanceof WC_Product) return '';
				
		if ( $this->options['badge_'.$purpose.'_style'] == 'off' ) return '';

		if ( $this->is_product_not_arrived ($product) ) {
			return '</div>';
		}
	}


	/**********************************
	  Auxiliary functions
	 ***********************************/

	/**
	 * Check if product is pending to arrival (coming soon must be shown)
	 *
	 * @since 1.0.0
	 * @version 1.0.16
	 *
	 */
	function is_product_not_arrived ( $product ) {
		
		$arrival_date = $this->get_product_arrival_date ( $product );

		$time_diff = $this->time_diff ($arrival_date);
	 
		return $time_diff > 0;
	}

	/**
	 * get product arrival
	 *
	 * @since 1.0.0
	 * @version 1.0.16
	 *
	 */
	function get_product_arrival_date ( $WC_Product ) {
		
		// $date = get_post_meta( $product_id, '_coming_soon_wc_arrival', true );
		$date = $WC_Product->get_meta('_coming_soon_wc_arrival', true);

		if ($date == '') $date = '0000-00-00 00:00:00';

		return $date;
	}

	/**
	 * get product arrival
	 *
	 * @since 1.0.0
	 * @version 1.0.3
	 *
	 */
	function time_diff ( $arrival_date, $date_now = false) {
				
		if (!$date_now) $date_now = date('Y-m-d H:i:s', time() );

		$arrival_date = strtotime($arrival_date);
		$date_now     = strtotime($date_now);

		return $arrival_date - $date_now;
	}


	/**********************************
	  3rd Party integrations
	 ***********************************/

	/* Support for background product image on Elementor */
	
	/**
	 * Catch Elementor element with product image background
	 *
	 * @since 1.0.12
	 *
	 */
	public function elementor_before_render( $element ) {
		
		if ( $this->elementor_el != false ) return;
		
		$settings = $element->get_settings();
		if ( !is_array( $settings ) || !isset( $settings['__dynamic__'] ) ) return;

		$dynamic = $settings['__dynamic__'];
		if ( !is_array( $dynamic ) ) return;
		
		if ( isset( $dynamic['background_image'] ) ) {
		
			$img = $dynamic['background_image'];
			
		} elseif ( isset( $dynamic['image'] ) ) {
			
			$img = $dynamic['image'];
			
		} else {
			return;
		}
		
		if ( strpos( $img, 'woocommerce-product-image-tag' ) === false 
			&& strpos( $img, 'post-featured-image' ) === false ) return;
			
		$this->elementor_el = $element->get_id();

		$element->add_render_attribute(
			'_wrapper',
			[
				'class' => 'elementor_col_coming_soon_wrapper',
			]
		);
	}

	/**
	 * Place Badge inside catched element (if any)
	 *
	 * @since 1.0.12
	 * @version 1.0.14
	 *
	 */
	public function elementor_the_content( $content ) {
		
		if ( !$this->elementor_el ) return $content;
		$pos = strpos( $content, 'data-id="'.$this->elementor_el.'"' );
		if ( $pos === false ) return $content;

		$maybe_badge = $this->get_open_badge('product') . $this->get_close_badge('product');

		$insert_point  = strpos( $content, '<', $pos + 1); // skip first open tag

		if ( $maybe_badge != '' && $insert_point !== false)
			$content = substr( $content, 0, $insert_point ) . $maybe_badge . substr( $content, $insert_point );
		
		$this->elementor_el = false; 
		
		return $content;

	}

	/**
	 * Place Badge inside Divi with featured post image background
	 *
	 * @since 1.0.14
	 *
	 */

	function divi_feat_post_image_background( $output, $render_method, $element ) {

		// When inside the builder the $output is an array so we can return early. Also only on product single page.
		if ( is_array( $output ) || !is_singular( 'product' ) ) {
			return $output;
		}

		// We need the get unprocessed module atrributes.
		$attrs_unprocessed = $element->get_attrs_unprocessed();

		// Get the background_image attribute value if it exists.
		$background_image = et_()->array_get( $attrs_unprocessed, 'background_image' );

		// Process the background_image attriburte value.
		$background_image_parsed = et_builder_parse_dynamic_content( $background_image );

		// Determine if background_image is of post_featured_image type.
		$is_dynamic_featured_image = 'post_featured_image' === $background_image_parsed->get_content();

		if ( $is_dynamic_featured_image )
		{
			$maybe_badge = $this->get_open_badge('product') . $this->get_close_badge('product');

			$insert_point  = strpos( $output, '<', 1); // skip first open tag
			
			if ( $maybe_badge != '' && $insert_point !== false)
				$output = substr( $output, 0, $insert_point ) . $maybe_badge . substr( $output, $insert_point );
		}
		return $output;
	}
	/*
	function divi_feat_post_image_background( $output, $render_method, $element ) {

		if ( is_singular('product') && $element && isset($element->props) )
		{
			if ( isset($element->props['_dynamic_attributes']) && $element->props['_dynamic_attributes'] == 'background_image' )
			{
				$maybe_badge = $this->get_open_badge('product') . $this->get_close_badge('product');

				$insert_point  = strpos( $output, '<', 1); // skip first open tag
				
				if ( $maybe_badge != '' && $insert_point !== false)
					$output = substr( $output, 0, $insert_point ) . $maybe_badge . substr( $output, $insert_point );
			}
		}
		
		return $output;
	}
	*/
}
global $Coming_Soon_WC;
$Coming_Soon_WC = new Coming_Soon_WC();

// Declare WooCommerce HPOS compatibility
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

