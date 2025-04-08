<?php
/**
 * Plugin Name: RealCallerAi Extension
 * Description: MLM Extension
 * Version: 1.0
 * Author: Darwin Sese
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define("DSPATH", plugin_dir_path(__FILE__));
define("pluginsurl", plugins_url() . '/mlm-extension');

require_once(DSPATH . 'includes/registration.php');   
require_once(DSPATH . 'admin/admin.php');   

class RealCallerAiExtension {

    
    private $ghl_endpoint = "https://rest.gohighlevel.com";

	private $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55X2lkIjoiZGNCY0g4enRsRE8zcWZ3QmV2M3oiLCJ2ZXJzaW9uIjoxLCJpYXQiOjE3NDE1OTUwNzUwMDMsInN1YiI6IkhKR1djblpzbUprN3FBdjI2bG9YIn0.OXlDtQunS4CjjshhGFvYxBP4c8mZwdoIutMAuzyQYcY';

	private $bmlm_gtree_nodes;

	private $planEndpoint = "https://api.stripe.com/v1/plans";

	private $subscriptionEndpoint = "https://api.stripe.com/v1/subscriptions";

	private $stripe_api_key = "sk_test_t6DTE0aLHvnhI3dZcqyQLEwl";

    
	const COMMISSION_RATE = 0.10; // Define at the top of the class

	const COMMISSION_RATE_TWO_PERCENT = 0.02; // Define at the top of the class

    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );
        add_action('init', array( $this, 'mlm_rewrite_rule' ) );
        add_filter('query_vars', array( $this, 'dealers_link_var' ) );
        add_filter('query_vars', array( $this, 'client_link_var' ) );
        add_action('template_include', array( $this, 'registration_form' ) );
        add_action('init', array(new mlmregistration, 'ProcessRegistration'));
        add_action('init', array(new mlmregistration ,'register_dealer_no_tree'));
        add_action( 'admin_menu', array(new MLMExtensionAdminMenu,'mlm_admin_control_menu'), 60);
        add_action('woocommerce_order_status_completed', array(new mlmregistration, 'processGHLAccount'));
        add_action('woocommerce_order_status_completed', array($this, 'process_client_order'),10,3);
        
        add_action( 'add_meta_boxes', array(new MLMExtensionAdminMenu,'stripe_product_ids'));
        add_action( 'save_post',  array(new MLMExtensionAdminMenu,'save_product_id'), 10, 3 );
         // Register AJAX handler
        add_action('wp_ajax_process_order_form', array($this,'process_order_form_callback'));
        add_action('wp_ajax_nopriv_process_order_form', array($this,'process_order_form_callback'));

        add_action('wp_enqueue_scripts', array($this,'enqueue_bootstrap_js'));

        add_action('ds_woocommerce_products', array($this, 'woocommerce_products'));


        add_action('ds_invoice_form', array($this, 'ds_invoice_form'));


        add_action('wp_ajax_get_product_details', array($this,'get_product_details_callback'));
        add_action('wp_ajax_nopriv_get_product_details', array($this,'get_product_details_callback'));
     
    }
    
    
    public function get_product_details_callback() {
        if (!isset($_POST['product_id'])) {
            wp_send_json_error('Product ID missing');
        }
    
        $product_id = intval($_POST['product_id']);
        $product = wc_get_product($product_id);
    
        if (!$product) {
            wp_send_json_error('Product not found');
        }
    
        wp_send_json_success([
            'name' => $product->get_name(),
            'description' => $product->get_short_description(),
            'price' => $product->get_price(),
            'price_html' => $product->get_price_html(),
            'type' => $product->get_type()
        ]);
    }

            // In your theme's functions.php or plugin file
    public function enqueue_bootstrap_js() {
        // Enqueue Bootstrap JS
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array(), '5.3.0', true);
    }
    
    public function mlm_rewrite_rule() {
        add_rewrite_rule(
            '^dealer/([^/]+)/?$',
            'index.php?dealers-form=$matches[1]', // The URL is passed to the 'dealers-form' query var
            'top'
        );

        add_rewrite_rule(
            '^client/([^/]+)/?$',
            'index.php?clients-form=$matches[1]', // The URL is passed to the 'clients-form' query var
            'top'
        );
    }
   
    public function dealers_link_var( $vars ) {
        $vars[] = 'dealers-form'; // Register the Dealers var
        return $vars;
    }

  
    public function client_link_var( $vars ) {
        $vars[] = 'clients-form'; // Register the Dealers var
        return $vars;
    }


    public function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    public function registration_form( $template ) {

        if ( get_query_var( 'dealers-form' ) ) {
            // Load custom page template for dealers
            $new_template = plugin_dir_path( __FILE__ ) . 'templates/dealers.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        if ( get_query_var( 'clients-form' ) ) {
            // Load custom page template for dealers
            $new_template = plugin_dir_path( __FILE__ ) . 'templates/clients.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }
        return $template;
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

    public function process_client_order($order_id) {
        global $wpdb;
        if(get_post_meta($order_id, 'dealer_user_id', true))
        {
            try {
                // Validate order ID
                if (!is_numeric($order_id) || $order_id <= 0) {
                    throw new InvalidArgumentException("Invalid order ID");
                }
        
                // Get the WooCommerce order object
                $order = wc_get_order($order_id);
                if (!$order) {
                    throw new RuntimeException("Order not found");
                }
        
                // Get parent dealer ID
                $parent_id = get_post_meta($order_id, 'dealer_user_id', true);
                
                if (empty($parent_id)) {
                    throw new RuntimeException("No dealer user ID found for order");
                }
        
                // Get customer data from the order
                $customer_data = [
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'email' => $order->get_billing_email(),
                    'phone' => $order->get_billing_phone(),
                    'address' => $order->get_billing_address_1(),
                    'address_2' => $order->get_billing_address_2(),
                    'city' => $order->get_billing_city(),
                    'state' => $order->get_billing_state(),
                    'postal_code' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country(),
                    'business_name' => $order->get_billing_company() ?: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'company_name' => $order->get_billing_company() ?: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                ];
        
                // Insert into ds_clients table
                $client_inserted = $wpdb->insert($wpdb->prefix . 'ds_clients', [
                    'child' => 0,
                    'parent' => $parent_id,
                    'order_id' => $order_id,
                ]);
        
                if (!$client_inserted) {
                    throw new RuntimeException("Failed to insert client record");
                }
        
                // Insert initial commission for the parent
                $commission_inserted = $wpdb->insert($wpdb->prefix . 'bmlm_commission', [
                    'user_id' => $parent_id,
                    'type' => 'joining',
                    'description' => 'Commission for client ' . $customer_data['first_name'] . ' ' . $customer_data['last_name'],
                    'commission' => $order->get_total() * self::COMMISSION_RATE,
                    'date' => current_time('mysql'),
                    'paid' => 'unpaid'
                ]);
        
                if (!$commission_inserted) {
                    throw new RuntimeException("Failed to insert commission record");
                }
        
                $this->processDealerCommissions($parent_id, $order);
                
                // Create business sub-account via API
                $business_data = [
                    "businessName" => sanitize_text_field($customer_data['business_name']),
                    "companyName" => sanitize_text_field($customer_data['company_name']),
                    "email" => sanitize_email($customer_data['email']),
                    "phone" => sanitize_text_field($customer_data['phone']),
                    "address" => sanitize_text_field($customer_data['address']),
                    "city" => sanitize_text_field($customer_data['city']),
                    "state" => sanitize_text_field($customer_data['state']),
                    "postalCode" => sanitize_text_field($customer_data['postal_code']),
                    "country" => sanitize_text_field($customer_data['country'])
                ];
        
                $response = $this->send_api_request("/v1/locations/", $business_data);
                
                if (!$response || empty($response['id'])) {
                    throw new RuntimeException("Failed to create sub-account via API");
                }
        
                $location_id = $response['id'];
                // Create admin user via API
                $user_data = [
                    "locationIds" => [$location_id], // Consider making this configurable
                    "firstName" => sanitize_text_field($customer_data['first_name']),
                    "lastName" => sanitize_text_field($customer_data['last_name']),
                    "email" => sanitize_email($customer_data['email']),
                    "type" => "account",
                    "role" => "user",
                    "permissions" => [
                        "campaignsEnabled" => true,
                        "contactsEnabled" => true,
                        "workflowsEnabled" => true,
                        "triggersEnabled" => true,
                        "funnelsEnabled" => true,
                        "opportunitiesEnabled" => true,
                        "dashboardStatsEnabled" => true,
                        "bulkRequestsEnabled" => true,
                        "appointmentsEnabled" => true,
                        "reviewsEnabled" => true,
                        "onlineListingsEnabled" => true,
                        "phoneCallEnabled" => true,
                        "conversationsEnabled" => true,
                        "assignedDataOnly" => false,
                        "settingsEnabled" => true,
                        "tagsEnabled" => true,
                        "leadValueEnabled" => true,
                        "marketingEnabled" => true
                    ]
                ];
                
                $this->save_data_for_cron($user_data,$location_id,$customer_data['email']);

                /*$user_response = $this->send_api_request("/v1/users/", $user_data);
                
                if (!$user_response) {
                    throw new RuntimeException("Failed to create admin user via API");
                }
        
                error_log("Success: Order $order_id processed successfully. Sub-Account and Admin User Created.");*/

                return true;
        
            } catch (Exception $e) {
                error_log("Error processing order $order_id: " . $e->getMessage());
                return false;
            }
        }
     
    }

    public function save_data_for_cron($user_data,$location_id,$email) {
        global $wpdb;

        $wpdb->insert($wpdb->prefix . "ghl_locations",[
            'location_id' => $location_id,
            'user_meta' => json_encode($user_data),
            'user_email' => $email
        ],[
            '%s',
            '%s',
            '%s'
        ]);

    }

    private function processDealerCommissions($child, $order, $level = 1, $downline_limit = 5) {
		
		global $wpdb;
	
		// Base case: Stop recursion if the level exceeds the downline limit
		if ($level > $downline_limit) {
			return;
		}
	
		// Fetch dealers for the current child
		$dealers = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bmlm_gtree_nodes 
			WHERE child = %d 
			AND (limit_commissions < %d OR limit_commissions IS NULL)",
			$child,
			$downline_limit
		));
		
		// If no dealers are found, stop recursion
		if (empty($dealers)) {
			return;
		}
	
		// Process each dealer
		foreach ($dealers as $dealer) {
			// Insert a commission if the count is less than the downline limit
			$result = $wpdb->insert($wpdb->prefix . 'bmlm_commission', [
					'user_id' => $dealer->parent,
					'type' => 'joining',
					'description' => '',
					'commission' => $order->get_total() * self::COMMISSION_RATE_TWO_PERCENT,
					'date' => current_time('mysql'),
					'paid' => 'unpaid'
			]);

			$this->updateLimitColumn($dealer->parent,$child);
			if (!$result) {
				error_log("Failed to insert commission for dealer ID: {$dealer->parent}");
			}
			// Recursively process the next level (parent of the current dealer)
			$this->processDealerCommissions($dealer->parent, $order, $level + 1, $downline_limit);
		}

	}

    public function process_order_form_callback() {
        try {
            // Verify nonce
            if (!isset($_POST['order_form_nonce'])) {
                throw new Exception(__('Security token missing', 'your-text-domain'));
            }
            
            if (!wp_verify_nonce($_POST['order_form_nonce'], 'order_form_action')) {
                throw new Exception(__('Security check failed', 'your-text-domain'));
            }
    
            // Check if WooCommerce is active
            if (!function_exists('wc_create_order')) {
                throw new Exception(__('WooCommerce is not active', 'your-text-domain'));
            }
    
            // Sanitize and validate required fields
            $required_fields = [
                'customer_first_name' => sanitize_text_field($_POST['customer_first_name'] ?? ''),
                'customer_last_name'  => sanitize_text_field($_POST['customer_last_name'] ?? ''),
                'customer_email'      => sanitize_email($_POST['customer_email'] ?? ''),
                'customer_business'   => sanitize_text_field($_POST['customer_business'] ?? ''),
                'customer_address'    => sanitize_text_field($_POST['customer_address'] ?? ''),
                'customer_city'       => sanitize_text_field($_POST['customer_city'] ?? ''),
                'customer_zip'        => sanitize_text_field($_POST['customer_zip'] ?? ''),
                'customer_country'    => sanitize_text_field($_POST['customer_country'] ?? ''),
                'customer_state'      => sanitize_text_field($_POST['customer_state'] ?? '')
            ];
    
            // Check for empty required fields
            foreach ($required_fields as $field => $value) {
                if (empty($value) && $field !== 'customer_address2') {
                    throw new Exception(sprintf(__('%s is a required field', 'your-text-domain'), ucfirst(str_replace('customer_', '', $field))));
                }
            }
    
            // Validate email format
            if (!is_email($required_fields['customer_email'])) {
                throw new Exception(__('Please enter a valid email address', 'your-text-domain'));
            }
    
            // Validate country and state
            $countries = WC()->countries->get_countries();
            if (!isset($countries[$required_fields['customer_country']])) {
                throw new Exception(__('Please select a valid country', 'your-text-domain'));
            }
    
            $states = WC()->countries->get_states($required_fields['customer_country']);
            if (!empty($required_fields['customer_state']) && !empty($states) && !isset($states[$required_fields['customer_state']])) {
                throw new Exception(__('Please select a valid state/province', 'your-text-domain'));
            }
    
            // Create order
            $order = wc_create_order();
            if (is_wp_error($order)) {
                throw new Exception(__('Failed to create order: ', 'your-text-domain') . $order->get_error_message());
            }
    
            // Add product to order
            $product_id = $_POST['product'] ?? 76; // Your fixed product ID
            $quantity = 1;    // Fixed quantity
            $product = wc_get_product($product_id);
            
            if (!$product) {
                throw new Exception(__('Product not found', 'your-text-domain'));
            }
            
            $order->add_product($product, $quantity);
    
            // Set customer details
            $order->set_customer_id(0); // 0 for guests
            $order->set_billing_first_name($required_fields['customer_first_name']);
            $order->set_billing_last_name($required_fields['customer_last_name']);
            $order->set_billing_company($required_fields['customer_business']);
            $order->set_billing_email($required_fields['customer_email']);
    
            // Set complete address information
            $order->set_billing_address_1($required_fields['customer_address']);
            $order->set_billing_address_2(sanitize_text_field($_POST['customer_address2'] ?? ''));
            $order->set_billing_city($required_fields['customer_city']);
            $order->set_billing_postcode($required_fields['customer_zip']);
            $order->set_billing_country($required_fields['customer_country']);
            $order->set_billing_state($required_fields['customer_state']);
    
            // Copy billing to shipping
            $order->set_shipping_first_name($required_fields['customer_first_name']);
            $order->set_shipping_last_name($required_fields['customer_last_name']);
            $order->set_shipping_company($required_fields['customer_business']);
            $order->set_shipping_address_1($required_fields['customer_address']);
            $order->set_shipping_address_2(sanitize_text_field($_POST['customer_address2'] ?? ''));
            $order->set_shipping_city($required_fields['customer_city']);
            $order->set_shipping_postcode($required_fields['customer_zip']);
            $order->set_shipping_country($required_fields['customer_country']);
            $order->set_shipping_state($required_fields['customer_state']);
    
            // Set payment method
            $order->set_payment_method('bacs');
            $order->set_payment_method_title(__('Invoice Payment', 'your-text-domain'));
    
            // Calculate and save
            $order->calculate_totals();
            $order->save();
    
            // Set order status
            $order->update_status('pending', __('Awaiting invoice payment', 'your-text-domain'));
    
            // Send invoice email
            $email_sent = false;
            $mailer = WC()->mailer();
            
            if (isset($mailer->emails['WC_Email_Customer_Invoice'])) {
                $email = $mailer->emails['WC_Email_Customer_Invoice'];
                $email->trigger($order->get_id(), $order, true);
                $email_sent = true;
            }
    
            // Fallback email if WC email fails
            if (!$email_sent) {
                $to = $required_fields['customer_email'];
                $subject = sprintf(__('Invoice #%s from %s', 'your-text-domain'), $order->get_id(), get_bloginfo('name'));
                
                $message = sprintf(
                    '<h2>%s</h2>
                    <p>%s %s,</p>
                    <p>%s #%s %s.</p>
                    <p>%s: %s</p>
                    <p>%s: %s</p>
                    <p>%s</p>
                    <p><a href="%s">%s</a></p>
                    <p>%s</p>',
                    __('New Invoice', 'your-text-domain'),
                    __('Hello', 'your-text-domain'),
                    $required_fields['customer_first_name'] . ' ' . $required_fields['customer_last_name'],
                    __('Your invoice', 'your-text-domain'),
                    $order->get_id(),
                    __('has been created', 'your-text-domain'),
                    __('Product', 'your-text-domain'),
                    $product->get_name(),
                    __('Total Amount', 'your-text-domain'),
                    $order->get_formatted_order_total(),
                    __('Please make payment at your earliest convenience.', 'your-text-domain'),
                    $order->get_checkout_payment_url(),
                    __('Pay Now', 'your-text-domain'),
                    __('Thank you for your business!', 'your-text-domain')
                );
    
                $headers = ['Content-Type: text/html; charset=UTF-8'];
                $email_sent = wp_mail($to, $subject, $message, $headers);
            }
    
            // Store dealer information
            $dealer_user_id = get_current_user_id();
            if ($dealer_user_id) {
                update_post_meta($order->get_id(), 'dealer_user_id', $dealer_user_id);
            }
    
            // Return success response
            wp_send_json_success([
                'message' => __('Invoice created successfully.', 'your-text-domain') . 
                             ($email_sent ? __(' Email notification sent.', 'your-text-domain') : __(' Email notification could not be sent.', 'your-text-domain')),
                'order_id' => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'order_total' => $order->get_formatted_order_total(),
                'email_sent' => $email_sent
            ]);
    
        } catch (Exception $e) {
            error_log('Invoice processing error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => 'order_processing_error'
            ]);
        }
    }

    public function woocommerce_products()
    {
       
        if (!file_exists(plugin_dir_path(__FILE__) . 'templates/products.php')) {
            return;
        }
        load_template(plugin_dir_path(__FILE__) . 'templates/products.php');
    }

    public function ds_invoice_form()
    {
        if (!file_exists(plugin_dir_path(__FILE__) . 'templates/invoice.php')) {
            return;
        }
        load_template(plugin_dir_path(__FILE__) . 'templates/invoice.php');
    }
    
        

}

// Initialize the plugin class
new RealCallerAiExtension;