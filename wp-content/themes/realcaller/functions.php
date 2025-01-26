<?php

/**
 * Include Theme Customizer.
 *
 * @since v1.0
 */
$theme_customizer = __DIR__ . '/inc/customizer.php';
if ( is_readable( $theme_customizer ) ) {
	require_once $theme_customizer;
}
// Custom Nav Walker: wp_bootstrap_navwalker().
$custom_walker = __DIR__ . '/inc/wp-bootstrap-navwalker.php';
if ( is_readable( $custom_walker ) ) {
	require_once $custom_walker;
}

$custom_walker_footer = __DIR__ . '/inc/wp-bootstrap-navwalker-footer.php';
if ( is_readable( $custom_walker_footer ) ) {
	require_once $custom_walker_footer;
}



class dsMLM {

	public function __construct()
	{


		add_action( 'after_setup_theme', array($this,'realcaller_setup_theme' ));

		add_action( 'enqueue_block_assets', array($this,'realcaller_load_editor_styles'));

		remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
		
		remove_action( 'enqueue_block_editor_assets', 'gutenberg_enqueue_block_editor_assets_block_directory' );

		add_filter( 'user_contactmethods', array($this, 'realcaller_add_user_fields' ));
	
		add_filter( 'comments_open', array($this,'realcaller_filter_media_comment_status' ), 10, 2 );

		add_filter( 'edit_post_link', array($this,'realcaller_custom_edit_post_link' ));

		add_filter( 'edit_comment_link', array($this, 'realcaller_custom_edit_comment_link'));

		add_filter( 'embed_oembed_html', array($this,'realcaller_oembed_filter'), 10 );		

		add_action( 'widgets_init', array($this,'realcaller_widgets_init' ));

		add_filter( 'next_posts_link_attributes', array($this,'posts_link_attributes' ) );
		
		add_filter( 'previous_posts_link_attributes', array($this,'posts_link_attributes') );

		add_filter( 'the_password_form', array($this,'realcaller_password_form'));

		add_filter( 'comment_form_defaults', array($this,'realcaller_custom_commentform' ));

		add_action( 'wp_enqueue_scripts', array($this, 'realcaller_scripts_loader'));

		add_action('woocommerce_before_cart', array($this,'restrict_cart_to_single_specific_product'));

		register_nav_menus(['main-menu'   => 'Main Navigation Menu','footer-menu' => 'Footer Menu',]);

		add_action('woocommerce_checkout_process', array($this,'check_cart_contents_before_checkout_specific_product'));

		add_filter('nav_menu_css_class', array($this, 'add_nav_item_class'), 10, 3);

		add_action( 'woocommerce_register_form_start', array($this, 'bbloomer_add_name_woo_account_registration'));

		add_action( 'after_setup_theme', array($this, 'register_navwalker'));
		
		add_filter( 'woocommerce_account_menu_items', array($this, 'ak_remove_my_account_links' ));

		add_action('init', array($this, 'register_as_client'));

		//add_action('init', array($this,'register_dealer' ));

		if ( ! function_exists( 'realcaller_article_posted_on' ) ) {
			/**
			 * "Theme posted on" pattern.
			 *
			 * @since v1.0
			 */
			function realcaller_article_posted_on() {
				printf(
					wp_kses_post( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author-meta vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'realcaller' ) ),
					esc_url( get_the_permalink() ),
					esc_attr( get_the_date() . ' - ' . get_the_time() ),
					esc_attr( get_the_date( 'c' ) ),
					esc_html( get_the_date() . ' - ' . get_the_time() ),
					esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
					sprintf( esc_attr__( 'View all posts by %s', 'realcaller' ), get_the_author() ),
					get_the_author()
				);
			}
		}

		// Redirect to checkout if the specific product is the only one in the cart
		function redirect_to_checkout_if_single_specific_product() {
			// Define the product ID you want to restrict
			$specific_product_id = 11; // Change this to your desired product ID

			if (is_cart() && WC()->cart->get_cart_contents_count() === 1) {
				$cart_product_ids = array();
				foreach (WC()->cart->get_cart() as $cart_item) {
					$cart_product_ids[] = $cart_item['product_id'];
				}

				if (in_array($specific_product_id, $cart_product_ids)) {
					wp_redirect(wc_get_checkout_url());
					exit;
				}
			}
		}
		
		add_filter( 'woocommerce_registration_redirect', array($this, 'custom_redirection_after_registration'), 10, 1 );
	
		add_filter('woocommerce_checkout_fields', array($this, 'addBootstrapToCheckoutFields' ));

		add_filter('woocommerce_checkout_fields', array($this,  'custom_reorder_checkout_fields'));

		add_filter( 'woocommerce_checkout_fields', array($this,'custom_remove_woocommerce_checkout_fields'));

		// Hook to add product to the cart after WooCommerce initializes
		add_action('wp_loaded',array($this, 'empty_the_cart'));


	}

	public function empty_the_cart(){
		$user_id = get_current_user_id();

		if ($user_id && get_transient('add_product_to_cart_for_user_' . $user_id)) {
				WC()->cart->empty_cart();
				$product_id = 76;
				$quantity = 1;
				WC()->cart->add_to_cart($product_id, $quantity);
				// Clear the transient
				delete_transient('add_product_to_cart_for_user_' . $user_id);
		}
	}
	

	public function custom_remove_woocommerce_checkout_fields( $fields ) 
	{
		// Remove billing fields
		unset($fields['billing']['billing_company']);
		unset($fields['billing']['billing_postcode']);
		unset($fields['billing']['billing_company']);
		unset($fields['billing']['billing_city']);
		unset($fields['billing']['billing_state']);
		unset($fields['billing']['billing_country']);
		unset($fields['billing']['billing_address_1']);
		unset($fields['billing']['billing_address_2']);

		
		//Remove Shipping fields
		unset($fields['shipping']['shipping_first_name']);
		unset($fields['shipping']['shipping_last_name']);
		unset($fields['shipping']['shipping_address_2']);
		
		return $fields;
	}

	public function custom_reorder_checkout_fields($fields) {
		$fields['billing']['billing_email']['priority'] = 30;
		$fields['shipping']['shipping_address_1']['priority'] = 20;
		$fields['shipping']['shipping_address_2']['priority'] = 20;
		return $fields;
	}

	public function addBootstrapToCheckoutFields($fields) {
		foreach ($fields as &$fieldset) {
			foreach ($fieldset as &$field) {
				// if you want to add the form-group class around the label and the input
				$field['class'][] = 'form-group'; 
	
				// add form-control to the actual input
				$field['input_class'][] = 'form-control';
			}
		}
		return $fields;
	}

	public function custom_redirection_after_registration( $redirection_url ){

		$redirection_url = get_home_url() . "/checkout"; // Home page
	
		return $redirection_url; // Always return something
	}
	

	private function generate_random_string($length = 10) {
		// Define characters to choose from (letters, numbers, and special characters)
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=<>?';
		
		// Initialize the random string
		$random_string = '';
		
		// Loop to generate the random string of specified length
		for ($i = 0; $i < $length; $i++) {
			$random_string .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $random_string;
	}

	public function register_dealer() {

		if(!is_user_logged_in())
		{
			if (!is_user_logged_in()) {
				if (isset($_POST['register_dealer'])) {
					// Sanitize user input
					$first_name = sanitize_text_field($_POST['first_name']);
					$last_name = sanitize_text_field($_POST['last_name']);
					$email_address = sanitize_email($_POST['email_address']);
					$password = sanitize_text_field($_POST['password']);
					
					// Validate email
					if (!is_email($email_address)) {
						wp_die('Invalid email address.'); // You can replace this with a user-friendly error.
					}
					
					// Check if the email already exists
					if (email_exists($email_address)) {
						wp_die('Email already exists. Please use a different email.'); // Customize error message.
					}
					
					// Create user
					$user_id = wp_create_user($email_address, $password, $email_address);
			
					if (is_wp_error($user_id)) {
						// Handle errors
						wp_die($user_id->get_error_message());
					}
			
					// Update user meta
					wp_update_user([
						'ID' => $user_id,
						'first_name' => $first_name,
						'last_name' => $last_name,
					]);
			
					$bmlm_sponsor_id = $this->generate_random_string(10);
			
					add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);
			
					// Optionally, assign a role (e.g., subscriber, dealer, etc.)
					$user = new WP_User($user_id);
					$user->set_role('bmlm_sponsor'); // Replace with a custom role if applicable.
					
					// Product ID to be ordered
					$product_id = 76; // Replace with the actual product ID
					$quantity = 1;     // Specify the quantity
					
					// Ensure the product exists
					$product = wc_get_product($product_id);
					if (!$product) {
						wc_add_notice(__('Invalid product.', 'woocommerce'), 'error');
						return;
					}
				
					if (!$user_id) {
						wc_add_notice(__('You must be logged in to place an order.', 'woocommerce'), 'error');
						return;
					}
				
					// Create a new order
					$order = wc_create_order();
				
					// Add the product to the order
					$order->add_product($product, $quantity);
				
					// Set the customer ID
					$order->set_customer_id($user_id);
				
					// Add billing and shipping information (replace with actual user data)
					$user_data = get_userdata($user_id);
					
					$address = [
						'first_name' => $user_data->first_name,
						'last_name'  => $user_data->last_name,
						'email'      => $user_data->user_email,
						'phone'      => get_user_meta($user_id, 'billing_phone', true),
						'address_1'  => get_user_meta($user_id, 'billing_address_1', true),
						'address_2'  => get_user_meta($user_id, 'billing_address_2', true),
						'city'       => get_user_meta($user_id, 'billing_city', true),
						'state'      => get_user_meta($user_id, 'billing_state', true),
						'postcode'   => get_user_meta($user_id, 'billing_postcode', true),
						'country'    => get_user_meta($user_id, 'billing_country', true),
					];
				
					$order->set_address($address, 'billing');
					$order->set_address($address, 'shipping');
				
					// Calculate totals
					$order->calculate_totals();
			
					// Automatically set order status to completed
					$order->set_status('completed');
					$order->save();
				
					// Auto-login the user
					wp_clear_auth_cookie();
					wp_set_current_user($user_id);
					wp_set_auth_cookie($user_id);

					// Redirect user after successful order creation
					wp_safe_redirect(site_url() . "/my-account");
					exit;
				}
			}
				
		}
	
	}

	public function register_as_client()
	{
		if (!is_user_logged_in()) {
			if (isset($_POST['register_client'])) {

				$first_name = sanitize_text_field($_POST['first_name']);
				$last_name = sanitize_text_field($_POST['last_name']);
				$email_address = sanitize_email($_POST['email']);
				$password = sanitize_text_field($_POST['password']);

				// Validate email
				if (!is_email($email_address)) {
					wp_die('Invalid email address.');
				}

				// Check if the email already exists
				if (email_exists($email_address)) {
					wp_die('Email already exists. Please use a different email.');
				}

				// Create user
				$user_id = wp_create_user($email_address, $password, $email_address);

				if (is_wp_error($user_id)) {
					// Handle errors
					wp_die($user_id->get_error_message());
				}

				// Update user meta
				wp_update_user([
					'ID' => $user_id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'billing_first_name' => $first_name,
					'billing_last_name' => $last_name,
				]);

				$bmlm_sponsor_id = $this->generate_random_string(10);

				add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);

				// Assign role
				$user = new WP_User($user_id);
				$user->set_role('bmlm_sponsor');

				// Auto-login the user
				wp_clear_auth_cookie();
				wp_set_current_user($user_id);
				wp_set_auth_cookie($user_id);

				// Add product to cart and redirect
				if (class_exists('WooCommerce')) {
					// Set a flag to add the product to the cart
					set_transient('add_product_to_cart_for_user_' . $user_id, true, 10);

					// Redirect to the invoice page
					$redirect_url = site_url('/sponsor/invoice/');
					wp_safe_redirect($redirect_url);
					exit; // Ensure the script stops here
				} else {
					wp_die('WooCommerce is not active. Please enable WooCommerce.');
				}
			}
		}
	}

	public function ak_remove_my_account_links( $menu_links )
	{
		unset( $menu_links[ 'dashboard' ] ); // Remove Dashboard
		return $menu_links;    
	}

	public function register_navwalker(){
		require_once get_template_directory() . '/includes/class-wp-bootstrap-navwalker.php';
	}
	

	public function bbloomer_add_name_woo_account_registration() {
		?>
			<div class="form-row form-row-first">
				<label for="reg_billing_first_name"><span class="required">*</span><?php _e( 'First name', 'woocommerce' ); ?></label>
				<input type="text" class="form-control input-text" name="first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
			</div>
			
			<div class="form-row form-row-last">
				<label for="reg_billing_last_name"><span class="required">*</span><?php _e( 'Last name', 'woocommerce' ); ?></label>
				<input type="text" class="form-control input-text" name="last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
			</div>
			
			<input type="hidden" name="register_client" value="1">
		
			<div class="clear"></div>
	  
		<?php
	}

	public function add_nav_item_class($classes, $item, $args) {
		// Add 'nav-item' class to each <li> element
		$classes[] = 'nav-item';
		return $classes;
	}

	public function check_cart_contents_before_checkout_specific_product() {
		// Define the product ID you want to restrict
		$specific_product_id = 11; // Change this to your desired product ID
	
		if (WC()->cart->get_cart_contents_count() > 1) {
			$cart_product_ids = array();
			foreach (WC()->cart->get_cart() as $cart_item) {
				$cart_product_ids[] = $cart_item['product_id'];
			}
	
			// Check if there is any product other than the specific product in the cart
			if (count(array_unique($cart_product_ids)) > 1 || !in_array($specific_product_id, $cart_product_ids)) {
				wc_add_notice( __('You can only proceed to checkout with the specified product in your cart. Please remove other products.'), 'error' );
				wp_safe_redirect(wc_get_cart_url());
				exit;
			}
		}
	}

	public function restrict_cart_to_single_specific_product() {
		// Define the product ID you want to restrict
		$specific_product_id = 11; // Change this to your desired product ID
	
		if (is_cart()) {
			$cart_item_count = WC()->cart->get_cart_contents_count();
			$cart_product_ids = array();
	
			// Loop through the cart items and get the product IDs
			foreach (WC()->cart->get_cart() as $cart_item) {
				$cart_product_ids[] = $cart_item['product_id'];
			}
	
			// Check if there is more than one product or a different product is in the cart
			if (count(array_unique($cart_product_ids)) > 1 || !in_array($specific_product_id, $cart_product_ids)) {
				wc_add_notice( __('You can only have the specified product in your cart (Product ID: ' . $specific_product_id . '). Please remove other products.'), 'error' );
				
				// Remove all products that are not the specified product
				foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
					if ($cart_item['product_id'] !== $specific_product_id) {
						WC()->cart->remove_cart_item($cart_item_key);
					}
				}
			}
		}
	}

	public function realcaller_scripts_loader() {
		$theme_version = wp_get_theme()->get( 'Version' );
	
		// 1. Styles.
		wp_enqueue_style( 'style', get_theme_file_uri( 'style.css' ), array(), $theme_version, 'all' );
		wp_enqueue_style( 'main', get_theme_file_uri( 'build/main.css' ), array(), $theme_version, 'all' ); // main.scss: Compiled Framework source + custom styles.
	
		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl', get_theme_file_uri( 'build/rtl.css' ), array(), $theme_version, 'all' );
		}
	
		// 2. Scripts.
		wp_enqueue_script( 'mainjs', get_theme_file_uri( 'build/main.js' ), array(), $theme_version, true );
	
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	public function realcaller_custom_commentform( $args = array(), $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$commenter     = wp_get_current_commenter();
		$user          = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';

		$args = wp_parse_args( $args );

		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true' required" : '' );
		$consent  = ( empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"' );
		$fields   = array(
			'author'  => '<div class="form-floating mb-3">
							<input type="text" id="author" name="author" class="form-control" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_html__( 'Name', 'realcaller' ) . ( $req ? '*' : '' ) . '"' . $aria_req . ' />
							<label for="author">' . esc_html__( 'Name', 'realcaller' ) . ( $req ? '*' : '' ) . '</label>
						</div>',
			'email'   => '<div class="form-floating mb-3">
							<input type="email" id="email" name="email" class="form-control" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_html__( 'Email', 'realcaller' ) . ( $req ? '*' : '' ) . '"' . $aria_req . ' />
							<label for="email">' . esc_html__( 'Email', 'realcaller' ) . ( $req ? '*' : '' ) . '</label>
						</div>',
			'url'     => '',
			'cookies' => '<p class="form-check mb-3 comment-form-cookies-consent">
							<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" class="form-check-input" type="checkbox" value="yes"' . $consent . ' />
							<label class="form-check-label" for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'realcaller' ) . '</label>
						</p>',
		);

		$defaults = array(
			'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
			'comment_field'        => '<div class="form-floating mb-3">
											<textarea id="comment" name="comment" class="form-control" aria-required="true" required placeholder="' . esc_attr__( 'Comment', 'realcaller' ) . ( $req ? '*' : '' ) . '"></textarea>
											<label for="comment">' . esc_html__( 'Comment', 'realcaller' ) . '</label>
										</div>',
			/** This filter is documented in wp-includes/link-template.php */
			'must_log_in'          => '<p class="must-log-in">' . sprintf( wp_kses_post( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'realcaller' ) ), wp_login_url( esc_url( get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
			/** This filter is documented in wp-includes/link-template.php */
			'logged_in_as'         => '<p class="logged-in-as">' . sprintf( wp_kses_post( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'realcaller' ) ), get_edit_user_link(), $user->display_name, wp_logout_url( apply_filters( 'the_permalink', esc_url( get_the_permalink( get_the_ID() ) ) ) ) ) . '</p>',
			'comment_notes_before' => '<p class="small comment-notes">' . esc_html__( 'Your Email address will not be published.', 'realcaller' ) . '</p>',
			'comment_notes_after'  => '',
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'class_submit'         => 'btn btn-primary',
			'name_submit'          => 'submit',
			'title_reply'          => '',
			'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'realcaller' ),
			'cancel_reply_link'    => esc_html__( 'Cancel reply', 'realcaller' ),
			'label_submit'         => esc_html__( 'Post Comment', 'realcaller' ),
			'submit_button'        => '<input type="submit" id="%2$s" name="%1$s" class="%3$s" value="%4$s" />',
			'submit_field'         => '<div class="form-submit">%1$s %2$s</div>',
			'format'               => 'html5',
		);

		return $defaults;
	}

	public function posts_link_attributes() {
		return 'class="btn btn-secondary btn-lg"';
	}

	public function realcaller_password_form() {
		global $post;
		$label = 'pwbox-' . ( empty( $post->ID ) ? wp_rand() : $post->ID );
	
		$output                  = '<div class="row">';
			$output             .= '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">';
			$output             .= '<h4 class="col-md-12 alert alert-warning">' . esc_html__( 'This content is password protected. To view it please enter your password below.', 'realcaller' ) . '</h4>';
				$output         .= '<div class="col-md-6">';
					$output     .= '<div class="input-group">';
						$output .= '<input type="password" name="post_password" id="' . esc_attr( $label ) . '" placeholder="' . esc_attr__( 'Password', 'realcaller' ) . '" class="form-control" />';
						$output .= '<div class="input-group-append"><input type="submit" name="submit" class="btn btn-primary" value="' . esc_attr__( 'Submit', 'realcaller' ) . '" /></div>';
					$output     .= '</div><!-- /.input-group -->';
				$output         .= '</div><!-- /.col -->';
			$output             .= '</form>';
		$output                 .= '</div><!-- /.row -->';
	
		return $output;
	}

	public function realcaller_widgets_init() {
		// Area 1.
		register_sidebar(
			array(
				'name'          => 'Primary Widget Area (Sidebar)',
				'id'            => 'primary_widget_area',
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	
		// Area 2.
		register_sidebar(
			array(
				'name'          => 'Secondary Widget Area (Header Navigation)',
				'id'            => 'secondary_widget_area',
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	
		// Area 3.
		register_sidebar(
			array(
				'name'          => 'Third Widget Area (Footer)',
				'id'            => 'third_widget_area',
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}

	public function realcaller_oembed_filter( $html ) {
		return '<div class="ratio ratio-16x9">' . $html . '</div>';
	}

	public function realcaller_filter_media_comment_status( $open, $post_id = null ) {
		$media_post = get_post( $post_id );
	
		if ( 'attachment' === $media_post->post_type ) {
			return false;
		}
	
		return $open;
	}

	public function realcaller_add_user_fields( $fields ) {
		// Add new fields.
		$fields['facebook_profile'] = 'Facebook URL';
		$fields['twitter_profile']  = 'Twitter URL';
		$fields['linkedin_profile'] = 'LinkedIn URL';
		$fields['xing_profile']     = 'Xing URL';
		$fields['github_profile']   = 'GitHub URL';

		return $fields;
	}
	
	public function realcaller_setup_theme() {
		// Make theme available for translation: Translations can be filed in the /languages/ directory.
		load_theme_textdomain( 'realcaller', __DIR__ . '/languages' );

		/**
		 * Set the content width based on the theme's design and stylesheet.
		 *
		 * @since v1.0
		 */
		global $content_width;
		if ( ! isset( $content_width ) ) {
			$content_width = 800;
		}

		// Theme Support.
		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
				'navigation-widgets',
			)
		);

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );
		// Add support for full and wide alignment.
		add_theme_support( 'align-wide' );
		// Add support for Editor Styles.
		add_theme_support( 'editor-styles' );
		// Enqueue Editor Styles.
		add_editor_style( 'style-editor.css' );

		// Default attachment display settings.
		update_option( 'image_default_align', 'none' );
		update_option( 'image_default_link_type', 'none' );
		update_option( 'image_default_size', 'large' );

		// Custom CSS styles of WorPress gallery.
		add_filter( 'use_default_gallery_style', '__return_false' );
	
	}
	
	public function realcaller_load_editor_styles() {
		if ( is_admin() ) {
			wp_enqueue_style( 'editor-style', get_theme_file_uri( 'style-editor.css' ) );
		}
	}

	public function realcaller_custom_edit_post_link( $link ) {
		return str_replace( 'class="post-edit-link"', 'class="post-edit-link badge bg-secondary"', $link );
	}

	public function realcaller_custom_edit_comment_link( $link ) {
		return str_replace( 'class="comment-edit-link"', 'class="comment-edit-link badge bg-secondary"', $link );
	}

	
}

new dsMLM;


if ( ! function_exists( 'wp_body_open' ) ) {
	/**
	 * Fire the wp_body_open action.
	 *
	 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
	 *
	 * @since v2.2
	 *
	 * @return void
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}



/**
 * Test if a page is a blog page.
 * if ( is_blog() ) { ... }
 *
 * @since v1.0
 *
 * @global WP_Post $post Global post object.
 *
 * @return bool
 */
function is_blog() {
	global $post;
	$posttype = get_post_type( $post );

	return ( ( is_archive() || is_author() || is_category() || is_home() || is_single() || ( is_tag() && ( 'post' === $posttype ) ) ) ? true : false );
}




if ( ! function_exists( 'realcaller_content_nav' ) ) {
	/**
	 * Display a navigation to next/previous pages when applicable.
	 *
	 * @since v1.0
	 *
	 * @param string $nav_id Navigation ID.
	 */
	function realcaller_content_nav( $nav_id ) {
		global $wp_query;

		if ( $wp_query->max_num_pages > 1 ) {
			?>
			<div id="<?php echo esc_attr( $nav_id ); ?>" class="d-flex mb-4 justify-content-between">
				<div><?php next_posts_link( '<span aria-hidden="true">&larr;</span> ' . esc_html__( 'Older posts', 'realcaller' ) ); ?></div>
				<div><?php previous_posts_link( esc_html__( 'Newer posts', 'realcaller' ) . ' <span aria-hidden="true">&rarr;</span>' ); ?></div>
			</div><!-- /.d-flex -->
			<?php
		} else {
			echo '<div class="clearfix"></div>';
		}
	}

}





if ( ! function_exists( 'realcaller_comment' ) ) {
	/**
	 * Style Reply link.
	 *
	 * @since v1.0
	 *
	 * @param string $link Link output.
	 *
	 * @return string
	 */
	function realcaller_replace_reply_link_class( $link ) {
		return str_replace( "class='comment-reply-link", "class='comment-reply-link btn btn-outline-secondary", $link );
	}
	add_filter( 'comment_reply_link', 'realcaller_replace_reply_link_class' );

	/**
	 * Template for comments and pingbacks:
	 * add function to comments.php ... wp_list_comments( array( 'callback' => 'realcaller_comment' ) );
	 *
	 * @since v1.0
	 *
	 * @param object $comment Comment object.
	 * @param array  $args    Comment args.
	 * @param int    $depth   Comment depth.
	 */
	function realcaller_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback':
			case 'trackback':
				?>
		<li class="post pingback">
			<p>
				<?php
					esc_html_e( 'Pingback:', 'realcaller' );
					comment_author_link();
					edit_comment_link( esc_html__( 'Edit', 'realcaller' ), '<span class="edit-link">', '</span>' );
				?>
			</p>
				<?php
				break;
			default:
				?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php
							$avatar_size = ( '0' !== $comment->comment_parent ? 68 : 136 );
							echo get_avatar( $comment, $avatar_size );

							/* Translators: 1: Comment author, 2: Date and time */
							printf(
								wp_kses_post( __( '%1$s, %2$s', 'realcaller' ) ),
								sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
								sprintf(
									'<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									/* Translators: 1: Date, 2: Time */
									sprintf( esc_html__( '%1$s ago', 'realcaller' ), human_time_diff( (int) get_comment_time( 'U' ), current_time( 'timestamp' ) ) )
								)
							);

							edit_comment_link( esc_html__( 'Edit', 'realcaller' ), '<span class="edit-link">', '</span>' );
						?>
					</div><!-- .comment-author .vcard -->

					<?php if ( '0' === $comment->comment_approved ) { ?>
						<em class="comment-awaiting-moderation">
							<?php esc_html_e( 'Your comment is awaiting moderation.', 'realcaller' ); ?>
						</em>
						<br />
					<?php } ?>
				</footer>

				<div class="comment-content"><?php comment_text(); ?></div>

				<div class="reply">
					<?php
						comment_reply_link(
							array_merge(
								$args,
								array(
									'reply_text' => esc_html__( 'Reply', 'realcaller' ) . ' <span>&darr;</span>',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth'],
								)
							)
						);
					?>
				</div><!-- /.reply -->
			</article><!-- /#comment-## -->
				<?php
				break;
		endswitch;
	}

}