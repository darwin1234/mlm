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


	private $ghl_endpoint = "https://rest.gohighlevel.com";

	private $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55X2lkIjoiZGNCY0g4enRsRE8zcWZ3QmV2M3oiLCJ2ZXJzaW9uIjoxLCJpYXQiOjE3MzgyMjgwNjQzNjUsInN1YiI6IlROQ05yaTZEc3Q2OVRzZGVxbjU5In0.I6e3Wqej7p5zeXCS3aPFlogvDXR41aT5ciKPLEY6Cc8';

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

		add_action('init', array($this,'register_dealer' ));
		
		add_action('init', array($this,'register_client_1' ));
		
		add_filter( 'woocommerce_registration_redirect', array($this, 'custom_redirection_after_registration'), 10, 1 );
	
		add_filter('woocommerce_checkout_fields', array($this, 'addBootstrapToCheckoutFields' ));

		add_filter('woocommerce_checkout_fields', array($this,  'custom_reorder_checkout_fields'));

		add_filter( 'woocommerce_checkout_fields', array($this,'custom_remove_woocommerce_checkout_fields'));

		// Hook to add product to the cart after WooCommerce initializes
		add_action('wp_loaded',array($this, 'empty_the_cart'));

		// Add Admin Menu
		add_action('admin_menu', array($this,'ghl_plugin_menu'));

		add_action('woocommerce_order_status_completed', array($this,'ds_order_completed' ), 10, 1);
		

	}

	/**
	 * Helper function to send API requests
	 */
	private function send_api_request($endpoint, $data)
	{
		$response = wp_remote_post($this->ghl_endpoint . $endpoint, [
			'method' => 'POST',
			'headers' => [
				'Authorization' => 'Bearer ' . $this->api_key,
				'Content-Type' => 'application/json'
			],
			'body' => json_encode($data),
			'data_format' => 'body'
		]);

		if (is_wp_error($response)) {
			error_log("API ERROR: " . $response->get_error_message());
			return false;
		}

    	return json_decode(wp_remote_retrieve_body($response), true);
	}

	public function ds_order_completed($order_id)
	{
		$order = wc_get_order($order_id);
		$customer_id = $order->get_user_id();

		if (!$customer_id) {
			error_log("Order $order_id has no associated customer.");
			return;
		}

		// Retrieve user meta
		$first_name = get_user_meta($customer_id, 'first_name', true);
		$last_name = get_user_meta($customer_id, 'last_name', true);
		$email = get_user_meta($customer_id, 'billing_email', true);
		$ds_business_name = get_user_meta($customer_id, 'ds_business_name', true);
		$ds_company_name = get_user_meta($customer_id, 'ds_company_name', true);
		$ds_phone =  get_user_meta($customer_id, 'ds_phone', true);
		$address = get_user_meta($customer_id, 'ds_address', true);
		$city  =  get_user_meta($customer_id, 'ds_city', true);
		$state  =  get_user_meta($customer_id, 'ds_state', true);
		$postal_code  = get_user_meta($customer_id, 'ds_postal_code', true);
		$country =   get_user_meta($customer_id, 'ds_country', true);	
		$password =  get_user_meta($customer_id, 'ds_password', true);
		
		// Prepare API data
		$business_data = [
			"businessName" => $ds_business_name,
			"companyName" => $ds_company_name,
			"email" => $email,
			"phone" => $sanitized_input['phone'],
			"address" => $sanitized_input['address'],
			"city" => $sanitized_input['city'],
			"state" => $sanitized_input['state'],
			"postalCode" => $sanitized_input['postalCode'],
			"country" => $sanitized_input['country']
		];

		
		// Send request to create sub-account
		$response = $this->send_api_request("/v1/locations/", $business_data);

		if (!$response || empty($response['id'])) {
			error_log("Failed to create sub-account for order $order_id.");
			return;
		}

		$location_id = $response['id'];

		// Prepare admin user data
		$user_data = [
			"locationIds" => $location_id,//['VxgP7Rj68WYNIXhMQsb5'],
			"firstName" => $first_name,
			"lastName" => $last_name,
			"email" => $email,
			"password" => $password,
			"type" => "account",
    		"role" =>"user",
			"permissions" => [
				"campaignsEnabled" => true,
				"campaignsReadOnly"=> false,
				"contactsEnabled"=> true,
				"workflowsEnabled"=> true,
				"triggersEnabled"=> true,
				"funnelsEnabled" =>  true,
				"websitesEnabled" => false,
				"opportunitiesEnabled"=> true,
				"dashboardStatsEnabled"=> true,
				"bulkRequestsEnabled"=>true,
				"appointmentsEnabled"=> true,
				"reviewsEnabled"=> true,
				"onlineListingsEnabled"=> true,
				"phoneCallEnabled"=> true,
				"conversationsEnabled"=> true,
				"assignedDataOnly"=> false,
				"adwordsReportingEnabled"=> false,
				"membershipEnabled"=> false,
				"facebookAdsReportingEnabled"=> false,
				"attributionsReportingEnabled"=> false,
				"settingsEnabled"=> true,
				"tagsEnabled"=> true,
				"leadValueEnabled"=> true,
				"marketingEnabled"=> true
			]
		];
	
		// Send request to create admin user
		$user_response = $this->send_api_request("/v1/users/", $user_data);


		if ($user_response) {
			error_log("Success: Sub-Account and Admin User Created Successfully!");
		} else {
			error_log("Failed: Admin user creation failed for order $order_id.");
		}
	}

	public function ghl_plugin_menu() {
		add_menu_page('GHL Sub-Account Creator', 'GHL Creator', 'manage_options', 'ghl_creator', array($this, 'ghl_plugin_page'));
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

		if (!is_user_logged_in()) {
			if (isset($_POST['register_dealer'])) {
				// Sanitize user input
				$first_name = sanitize_text_field($_POST['first_name']);
				$last_name = sanitize_text_field($_POST['last_name']);
				$email_address = sanitize_email($_POST['email_address']);
				$password = sanitize_text_field($_POST['password']);
				$confirm_password = sanitize_text_field($_POST['confirm_password']); // Password confirmation
	
				// Validate email
				if (!is_email($email_address)) {
					wp_die('Invalid email address.'); 
				}
	
				// Check if the email already exists
				if (email_exists($email_address)) {
					wc_add_notice('Email already exists. Please use a different email.', 'error');
				}
	
				// Check if passwords match
				if ($password !== $confirm_password) {
					wp_die('Passwords do not match. Please try again.');
				}
	
				// Create user
				$user_id = wp_create_user($email_address, $password, $email_address);
	
				if (is_wp_error($user_id)) {
					wp_die($user_id->get_error_message());
				}
	
				// Update user meta
				wp_update_user([
					'ID'         => $user_id,
					'first_name' => $first_name,
					'last_name'  => $last_name,
				]);
	
				$bmlm_sponsor_id = $this->generate_random_string(10);
				add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);
	
				// Assign a role
				$user = new WP_User($user_id);
				$user->set_role('bmlm_sponsor');
	
				// Product ID to be ordered
				$product_id = 76; 
				$quantity = 1;
	
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
				
				// Create a new WooCommerce order
				$order = wc_create_order();
				$order->add_product($product, $quantity);
				$order->set_customer_id($user_id);
	
				// Get user data
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
	
				// Calculate totals and complete order
				$order->calculate_totals();
				$order->set_status('completed');
				$order->save();
	
				// Auto-login the user
				wp_clear_auth_cookie();
				wp_set_current_user($user_id);
				wp_set_auth_cookie($user_id);
	
				// Redirect after successful order creation
				wp_safe_redirect(site_url() . "/my-account");
				exit;
			}
		}
	}

	public function register_client_1(){
		
		if (!is_user_logged_in()) {
			if (isset($_POST['register_client_1'])) {

				$first_name = sanitize_text_field($_POST['first_name']);
				$last_name = sanitize_text_field($_POST['last_name']);
				$email_address = sanitize_email($_POST['email']);
				$password = sanitize_text_field($_POST['password']);
				$company_name =  sanitize_text_field($_POST['company_name']);
				$business_name =  sanitize_text_field($_POST['business_name']);
				$company_name =  sanitize_text_field($_POST['company_name']);
				$phone =  sanitize_text_field($_POST['phone']);
				$address =  sanitize_text_field($_POST['address']);
				$city  =  sanitize_text_field($_POST['city']);
				$state  =  sanitize_text_field($_POST['state']);
				$postal_code  =  sanitize_text_field($_POST['postal_code']);
				$country =  sanitize_text_field($_POST['country']);
				
				$ds_password= $password;

				// Validate email
				if (!is_email($email_address)) {
					wp_die('Invalid email address.');
				}

				// Check if the email already exists
				if (email_exists($email_address)) {
					wc_add_notice('Email already exists.', 'error');
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

				add_user_meta($user_id, 'account_type', 'ds_client');

				$bmlm_sponsor_id = $this->generate_random_string(10);

				add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);
				
				add_user_meta($user_id, 'ds_business_name', $business_name);

				add_user_meta($user_id, 'ds_company_name', $company_name);

				add_user_meta($user_id, 'ds_phone', $phone);
				
				add_user_meta($user_id, 'ds_address', $address);
				
				add_user_meta($user_id, 'ds_city', $city);
				
				add_user_meta($user_id, 'ds_state', $state);
				
				add_user_meta($user_id, 'ds_postal_code', $postal_code);
				
				add_user_meta($user_id, 'ds_country', $country);
				
				add_user_meta($user_id, 'ds_password', $ds_password);
				
				
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
					// Redirect user to WooCommerce Checkout Page
					$redirect_url = wc_get_checkout_url();
					wp_safe_redirect($redirect_url);
					exit; // Ensure the script stops here
				} else {
					wp_die('WooCommerce is not active. Please enable WooCommerce.');
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
				$business_name =  sanitize_text_field($_POST['business_name']);
				$company_name =  sanitize_text_field($_POST['company_name']);
				$phone =  sanitize_text_field($_POST['phone']);
				$address =  sanitize_text_field($_POST['address']);
				$city  =  sanitize_text_field($_POST['city']);
				$state  =  sanitize_text_field($_POST['state']);
				$postal_code  =  sanitize_text_field($_POST['postal_code']);
				$country =  sanitize_text_field($_POST['country']);

				$ds_password = $password;

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
				
				add_user_meta($user_id, 'ds_business_name', $business_name);

				add_user_meta($user_id, 'ds_company_name', $company_name);

				add_user_meta($user_id, 'ds_phone', $phone);
				
				add_user_meta($user_id, 'ds_address', $address);
				
				add_user_meta($user_id, 'ds_city', $city);
				
				add_user_meta($user_id, 'ds_state', $state);
				
				add_user_meta($user_id, 'ds_postal_code', $postal_code);
				
				add_user_meta($user_id, 'ds_country', $country);

				add_user_meta($user_id, 'ds_password', $ds_password);

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

					// Redirect user to WooCommerce Checkout Page
					$redirect_url = wc_get_checkout_url();
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
		unset( $menu_links[ 'orders' ] );
		$user_id = get_current_user_id();
		if ($user_id){
			$account_type = get_user_meta($user_id, 'account_type', true);
			if($account_type =="ds_client"){
				set( $menu_links[ 'orders' ] );
			}
		}
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
			<div class="form-row form-row-first">
				<label for="reg_company_name"><span class="required">*</span><?php _e( 'Company Name', 'woocommerce' ); ?></label>
				<input type="text" class="form-control" id="company_name" name="company_name" placeholder="" aria-describedby="email address">
            </div>
			<div class="form-row form-row-last">
				<label for="reg_business_name"><span class="required">*</span><?php _e( 'Business Name', 'woocommerce' ); ?></label>
                <input type="text" class="form-control" id="business_name" name="business_name" placeholder="" aria-describedby="email address">
            </div>
			<div class="form-row form-row-first">
				<label for="reg_phone_number"><span class="required">*</span><?php _e( 'Phone Number', 'woocommerce' ); ?></label>
                <input type="text" class="form-control" id="phone_number" name="phone" placeholder="" aria-describedby="Phone Number">
            </div>
			<div class="form-row form-row-last">
				<label for="reg_address"><span class="required">*</span><?php _e( 'Address', 'woocommerce' ); ?></label>
                <input type="text" class="form-control" id="address" name="address" placeholder="" aria-describedby="">
            </div>
			<div class="form-row form-row-first">
				<label for="reg_address"><span class="required">*</span><?php _e( 'City', 'woocommerce' ); ?></label>
                <input type="text" class="form-control" id="city" name="city" placeholder="" aria-describedby="City">
            </div>
            <div class="form-row form-row-last">
				<label for="reg_state"><span class="required">*</span><?php _e( 'State', 'woocommerce' ); ?></label>
                <input type="text" class="form-control" id="state" name="state" placeholder="" aria-describedby="State">
            </div>
			<div class="form-row form-row-first">
				<label for="reg_postal_code"><span class="required">*</span><?php _e( 'Postal code', 'woocommerce' ); ?></label>
                <input type="text" class="form-control" id="postalCode" name="postal_code" placeholder="" aria-describedby="postal_code">
            </div>
            <div class="form-row form-row-last">
				<label for="reg_country"><span class="required">*</span><?php _e( 'Country', 'woocommerce' ); ?></label>
				<select class="form-select form-control" autocomplete="country" id="country" name="country">
					<option value="">Select Country</option>
					<option value="AF">Afghanistan</option>
					<option value="AX">Åland Islands</option>
					<option value="AL">Albania</option>
					<option value="DZ">Algeria</option>
					<option value="AS">American Samoa</option>
					<option value="AD">Andorra</option>
					<option value="AO">Angola</option>
					<option value="AI">Anguilla</option>
					<option value="AQ">Antarctica</option>
					<option value="AG">Antigua and Barbuda</option>
					<option value="AR">Argentina</option>
					<option value="AM">Armenia</option>
					<option value="AW">Aruba</option>
					<option value="AU">Australia</option>
					<option value="AT">Austria</option>
					<option value="AZ">Azerbaijan</option>
					<option value="BS">Bahamas</option>
					<option value="BH">Bahrain</option>
					<option value="BD">Bangladesh</option>
					<option value="BB">Barbados</option>
					<option value="BY">Belarus</option>
					<option value="BE">Belgium</option>
					<option value="BZ">Belize</option>
					<option value="BJ">Benin</option>
					<option value="BM">Bermuda</option>
					<option value="BT">Bhutan</option>
					<option value="BO">Bolivia (Plurinational State of)</option>
					<option value="BA">Bosnia and Herzegovina</option>
					<option value="BW">Botswana</option>
					<option value="BV">Bouvet Island</option>
					<option value="BR">Brazil</option>
					<option value="IO">British Indian Ocean Territory</option>
					<option value="BN">Brunei Darussalam</option>
					<option value="BG">Bulgaria</option>
					<option value="BF">Burkina Faso</option>
					<option value="BI">Burundi</option>
					<option value="CV">Cabo Verde</option>
					<option value="KH">Cambodia</option>
					<option value="CM">Cameroon</option>
					<option value="CA">Canada</option>
					<option value="BQ">Caribbean Netherlands</option>
					<option value="KY">Cayman Islands</option>
					<option value="CF">Central African Republic</option>
					<option value="TD">Chad</option>
					<option value="CL">Chile</option>
					<option value="CN">China</option>
					<option value="CX">Christmas Island</option>
					<option value="CC">Cocos (Keeling) Islands</option>
					<option value="CO">Colombia</option>
					<option value="KM">Comoros</option>
					<option value="CG">Congo</option>
					<option value="CD">Congo, Democratic Republic of the</option>
					<option value="CK">Cook Islands</option>
					<option value="CR">Costa Rica</option>
					<option value="HR">Croatia</option>
					<option value="CU">Cuba</option>
					<option value="CW">Curaçao</option>
					<option value="CY">Cyprus</option>
					<option value="CZ">Czech Republic</option>
					<option value="CI">Côte d'Ivoire</option>
					<option value="DK">Denmark</option>
					<option value="DJ">Djibouti</option>
					<option value="DM">Dominica</option>
					<option value="DO">Dominican Republic</option>
					<option value="EC">Ecuador</option>
					<option value="EG">Egypt</option>
					<option value="SV">El Salvador</option>
					<option value="GQ">Equatorial Guinea</option>
					<option value="ER">Eritrea</option>
					<option value="EE">Estonia</option>
					<option value="SZ">Eswatini (Swaziland)</option>
					<option value="ET">Ethiopia</option>
					<option value="FK">Falkland Islands (Malvinas)</option>
					<option value="FO">Faroe Islands</option>
					<option value="FJ">Fiji</option>
					<option value="FI">Finland</option>
					<option value="FR">France</option>
					<option value="GF">French Guiana</option>
					<option value="PF">French Polynesia</option>
					<option value="TF">French Southern Territories</option>
					<option value="GA">Gabon</option>
					<option value="GM">Gambia</option>
					<option value="GE">Georgia</option>
					<option value="DE">Germany</option>
					<option value="GH">Ghana</option>
					<option value="GI">Gibraltar</option>
					<option value="GR">Greece</option>
					<option value="GL">Greenland</option>
					<option value="GD">Grenada</option>
					<option value="GP">Guadeloupe</option>
					<option value="GU">Guam</option>
					<option value="GT">Guatemala</option>
					<option value="GG">Guernsey</option>
					<option value="GN">Guinea</option>
					<option value="GW">Guinea-Bissau</option>
					<option value="GY">Guyana</option>
					<option value="HT">Haiti</option>
					<option value="HM">Heard Island and Mcdonald Islands</option>
					<option value="HN">Honduras</option>
					<option value="HK">Hong Kong</option>
					<option value="HU">Hungary</option>
					<option value="IS">Iceland</option>
					<option value="IN">India</option>
					<option value="ID">Indonesia</option>
					<option value="IR">Iran</option>
					<option value="IQ">Iraq</option>
					<option value="IE">Ireland</option>
					<option value="IM">Isle of Man</option>
					<option value="IL">Israel</option>
					<option value="IT">Italy</option>
					<option value="JM">Jamaica</option>
					<option value="JP">Japan</option>
					<option value="JE">Jersey</option>
					<option value="JO">Jordan</option>
					<option value="KZ">Kazakhstan</option>
					<option value="KE">Kenya</option>
					<option value="KI">Kiribati</option>
					<option value="KP">Korea, North</option>
					<option value="KR">Korea, South</option>
					<option value="XK">Kosovo</option>
					<option value="KW">Kuwait</option>
					<option value="KG">Kyrgyzstan</option>
					<option value="LA">Lao People's Democratic Republic</option>
					<option value="LV">Latvia</option>
					<option value="LB">Lebanon</option>
					<option value="LS">Lesotho</option>
					<option value="LR">Liberia</option>
					<option value="LY">Libya</option>
					<option value="LI">Liechtenstein</option>
					<option value="LT">Lithuania</option>
					<option value="LU">Luxembourg</option>
					<option value="MO">Macao</option>
					<option value="MK">Macedonia North</option>
					<option value="MG">Madagascar</option>
					<option value="MW">Malawi</option>
					<option value="MY">Malaysia</option>
					<option value="MV">Maldives</option>
					<option value="ML">Mali</option>
					<option value="MT">Malta</option>
					<option value="MH">Marshall Islands</option>
					<option value="MQ">Martinique</option>
					<option value="MR">Mauritania</option>
					<option value="MU">Mauritius</option>
					<option value="YT">Mayotte</option>
					<option value="MX">Mexico</option>
					<option value="FM">Micronesia</option>
					<option value="MD">Moldova</option>
					<option value="MC">Monaco</option>
					<option value="MN">Mongolia</option>
					<option value="ME">Montenegro</option>
					<option value="MS">Montserrat</option>
					<option value="MA">Morocco</option>
					<option value="MZ">Mozambique</option>
					<option value="MM">Myanmar (Burma)</option>
					<option value="NA">Namibia</option>
					<option value="NR">Nauru</option>
					<option value="NP">Nepal</option>
					<option value="NL">Netherlands</option>
					<option value="AN">Netherlands Antilles</option>
					<option value="NC">New Caledonia</option>
					<option value="NZ">New Zealand</option>
					<option value="NI">Nicaragua</option>
					<option value="NE">Niger</option>
					<option value="NG">Nigeria</option>
					<option value="NU">Niue</option>
					<option value="NF">Norfolk Island</option>
					<option value="MP">Northern Mariana Islands</option>
					<option value="NO">Norway</option>
					<option value="OM">Oman</option>
					<option value="PK">Pakistan</option>
					<option value="PW">Palau</option>
					<option value="PS">Palestine</option>
					<option value="PA">Panama</option>
					<option value="PG">Papua New Guinea</option>
					<option value="PY">Paraguay</option>
					<option value="PE">Peru</option>
					<option value="PH">Philippines</option>
					<option value="PN">Pitcairn Islands</option>
					<option value="PL">Poland</option>
					<option value="PT">Portugal</option>
					<option value="PR">Puerto Rico</option>
					<option value="QA">Qatar</option>
					<option value="RE">Reunion</option>
					<option value="RO">Romania</option>
					<option value="RU">Russian Federation</option>
					<option value="RW">Rwanda</option>
					<option value="BL">Saint Barthelemy</option>
					<option value="SH">Saint Helena</option>
					<option value="KN">Saint Kitts and Nevis</option>
					<option value="LC">Saint Lucia</option>
					<option value="MF">Saint Martin</option>
					<option value="PM">Saint Pierre and Miquelon</option>
					<option value="VC">Saint Vincent and the Grenadines</option>
					<option value="WS">Samoa</option>
					<option value="SM">San Marino</option>
					<option value="ST">Sao Tome and Principe</option>
					<option value="SA">Saudi Arabia</option>
					<option value="SN">Senegal</option>
					<option value="RS">Serbia</option>
					<option value="CS">Serbia and Montenegro</option>
					<option value="SC">Seychelles</option>
					<option value="SL">Sierra Leone</option>
					<option value="SG">Singapore</option>
					<option value="SX">Sint Maarten</option>
					<option value="SK">Slovakia</option>
					<option value="SI">Slovenia</option>
					<option value="SB">Solomon Islands</option>
					<option value="SO">Somalia</option>
					<option value="ZA">South Africa</option>
					<option value="GS">South Georgia and the South Sandwich Islands</option>
					<option value="SS">South Sudan</option>
					<option value="ES">Spain</option>
					<option value="LK">Sri Lanka</option>
					<option value="SD">Sudan</option>
					<option value="SR">Suriname</option>
					<option value="SJ">Svalbard and Jan Mayen</option>
					<option value="SE">Sweden</option>
					<option value="CH">Switzerland</option>
					<option value="SY">Syria</option>
					<option value="TW">Taiwan</option>
					<option value="TJ">Tajikistan</option>
					<option value="TZ">Tanzania</option>
					<option value="TH">Thailand</option>
					<option value="TL">Timor-Leste</option>
					<option value="TG">Togo</option>
					<option value="TK">Tokelau</option>
					<option value="TO">Tonga</option>
					<option value="TT">Trinidad and Tobago</option>
					<option value="TN">Tunisia</option>
					<option value="TR">Turkey (Türkiye)</option>
					<option value="TM">Turkmenistan</option>
					<option value="TC">Turks and Caicos Islands</option>
					<option value="TV">Tuvalu</option>
					<option value="UM">U.S. Outlying Islands</option>
					<option value="UG">Uganda</option>
					<option value="UA">Ukraine</option>
					<option value="AE">United Arab Emirates</option>
					<option value="GB">United Kingdom</option>
					<option value="US">United States</option>
					<option value="UY">Uruguay</option>
					<option value="UZ">Uzbekistan</option>
					<option value="VU">Vanuatu</option>
					<option value="VA">Vatican City Holy See</option>
					<option value="VE">Venezuela</option>
					<option value="VN">Vietnam</option>
					<option value="VG">Virgin Islands, British</option>
					<option value="VI">Virgin Islands, U.S</option>
					<option value="WF">Wallis and Futuna</option>
					<option value="EH">Western Sahara</option>
					<option value="YE">Yemen</option>
					<option value="ZM">Zambia</option>
					<option value="ZW">Zimbabwe</option>
				</select>
				<!-- total - 252 -->

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