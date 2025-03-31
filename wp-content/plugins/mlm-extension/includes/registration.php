<?php 


class mlmregistration 
{
    private $ghl_endpoint = "https://rest.gohighlevel.com";

	private $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55X2lkIjoiZGNCY0g4enRsRE8zcWZ3QmV2M3oiLCJ2ZXJzaW9uIjoxLCJpYXQiOjE3NDE1OTUwNzUwMDMsInN1YiI6IkhKR1djblpzbUprN3FBdjI2bG9YIn0.OXlDtQunS4CjjshhGFvYxBP4c8mZwdoIutMAuzyQYcY';

	private $bmlm_gtree_nodes;

	private $planEndpoint = "https://api.stripe.com/v1/plans";

	private $subscriptionEndpoint = "https://api.stripe.com/v1/subscriptions";

	private $stripe_api_key = "sk_test_t6DTE0aLHvnhI3dZcqyQLEwl";

	private $ds_clients;

	const COMMISSION_RATE = 0.10; // Define at the top of the class

	const COMMISSION_RATE_TWO_PERCENT = 0.02; // Define at the top of the class
    
	public function ProcessRegistration(){
        //global $_POST;
        if(isset($_POST['submit'])){
           
            if(isset($_POST['email_address']) && isset($_POST['password']))
            {

                $account_type ="";

				$parent_id = 0;

                $data = [];
                
                if(isset($_POST['dealer_form_xxxx'])){$account_type ="ds_dealer";}
                
                if(isset($_POST['client_form_xxxx'])){$account_type ="ds_client";}   
                
                $email_address = sanitize_email($_POST['email_address']);
                
                $password = sanitize_text_field($_POST['password']);
                
                $user_id = wp_create_user($email_address, $password,$email_address);

                add_user_meta($user_id , 'account_type', $account_type);
                
                if(isset($_POST['first_name'])){
                    $first_name = sanitize_text_field($_POST['first_name']);
                    update_user_meta($user_id, 'first_name',$first_name);
                    $data['first_name'] = $first_name;
                }   

                if(isset($_POST['last_name'])){
                    $last_name = sanitize_text_field($_POST['last_name']);
                    update_user_meta($user_id, 'last_name',$last_name);
                    $data['last_name'] = $last_name;
                }

                if(isset($_POST['ds_company_name'])){
                    $company_name = sanitize_text_field($_POST['ds_company_name']);
                    add_user_meta($user_id, 'ds_company_name',$last_name);
                    $data['ds_company_name'] = $company_name;
                }

                if(isset($_POST['ds_business_name'])){
                    $business_name = sanitize_text_field($_POST['ds_business_name']);
                    add_user_meta($user_id, 'ds_business_name',$last_name);
                    $data['ds_business_name'] = $business_name;
                }
                
                if(isset($_POST['ds_phone'])){
                    $phone = sanitize_text_field($_POST['ds_phone']);
                    add_user_meta($user_id, 'ds_phone',$phone);
                    $data['ds_phone'] = $phone;
                }
                
                if(isset($_POST['ds_address'])){
                    $address = sanitize_text_field($_POST['ds_address']);
                    add_user_meta($user_id, 'ds_address',$address);
                    $data['ds_address'] = $address;
                }

                if(isset($_POST['city'])){
                    $city = sanitize_text_field($_POST['ds_city']);
                    add_user_meta($user_id, 'ds_city',$city);
                    $data['ds_city'] = $city;
                }

                if(isset($_POST['ds_state'])){
                    $state = sanitize_text_field($_POST['ds_state']);
                    add_user_meta($user_id, 'ds_state',$address);
                    $data['ds_state'] = $state;
                }

                if(isset($_POST['ds_postal_code'])){
                    $postal_code = sanitize_text_field($_POST['ds_postal_code']);
                    add_user_meta($user_id, 'ds_postal_code',$address);
                    $data['ds_postal_code'] = $postal_code;
                }

                if(isset($_POST['ds_country'])){
                    $country = sanitize_text_field($_POST['ds_country']);
                    add_user_meta($user_id, 'ds_country',$country);
                    $data['ds_country'] = $country;
                }
				
				if(isset($_POST['password']))
				{
					$password = sanitize_text_field($_POST['password']);
                    add_user_meta($user_id, 'ds_password',$password);
                    $data['ds_password'] = $password;
				}

				if(isset($_POST['parent_id'])){
					$parent_id =  sanitize_text_field($_POST['parent_id']);
					add_user_meta($user_id, 'ds_parent_id',$parent_id);
				}
              
                $bmlm_sponsor_id = $this->generate_random_string(10);
                add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);

                if(isset($_POST['bmlm_refferal_id'])){
                    $bmlm_refferal_id = $_POST['bmlm_refferal_id'];
                    add_user_meta($user_id, 'bmlm_refferal_id', $bmlm_refferal_id);
                }
                // Assign role
				$user = new WP_User($user_id);
				$user->set_role('bmlm_sponsor');

                // Auto-login the user
				wp_clear_auth_cookie();
				wp_set_current_user($user_id);
				wp_set_auth_cookie($user_id);
                
				// Add product to cart and redirect
				if (class_exists('WooCommerce')) 
                {
					$this->createNodes($parent_id, $user_id,$account_type);
                    $this->add_to_cart($user_id, $account_type, $data);
				} else {
					wp_die('WooCommerce is not active. Please enable WooCommerce.');
				}
            }

        }
	}

	private function createNodes($parent, $child, $account_type)
	{
		global $wpdb;
		
		// Ensure account_type is 'ds_dealer' before proceeding
		if ($account_type === "ds_dealer") {

			// Fetch the dealer data only if needed
			$dealer = $this->checkTreeIfNotEmpty($parent);
			
			// If the dealer data is not empty (i.e., the parent has a child already)
			if ($dealer) {
				// Insert a new node with the dealer's nrow incremented
				$wpdb->insert(
					$wpdb->prefix . 'bmlm_gtree_nodes',
					array(
						'child' => $child,
						'parent' => $parent,
						'nrow' => $dealer[0]->nrow + 1
					)
				);
			} else {
				// Insert a new node with nrow set to 1 if the parent has no children yet
				$wpdb->insert(
					$wpdb->prefix . 'bmlm_gtree_nodes',
					array(
						'child' => $child,
						'parent' => $parent,
						'nrow' => 1
					)
				);
			}
		}
	}

	public function checkTreeIfNotEmpty($parent_id)
	{
		global $wpdb;

		// Query to check if the parent has any children
		$dealers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}bmlm_gtree_nodes WHERE parent = %d ORDER BY ID DESC LIMIT 1",
				$parent_id
			)
		);

		// If no dealers found, return false instead of 0 for better readability and handling
		if (empty($dealers)) {
			return false;
		} else {
			// Return the dealer data if found
			return $dealers;
		}
	}

    public function add_to_cart($user_id, $membership_type,$address){
		
        if($membership_type==="ds_dealer")
		{
			$dealer_product_id = get_option('mlm_dealer_product');
			$quantity = 1;
			// Ensure the product exists
			$product = wc_get_product($dealer_product_id);
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

			$order->set_address($address, 'billing');
			$order->set_address($address, 'shipping');
			

            // Calculate totals and complete order
            $order->calculate_totals();
            $order->set_status('completed');
            $order->save();    
            wp_safe_redirect(site_url() . '/sponsor/dashboard/');
			exit;
        }

        if($membership_type==="ds_client"){
			$quantity = 1;
			$client_product_id = get_option('mlm_client_product');
			
			WC()->cart->empty_cart();	
			// Add product to the cart
			$added = WC()->cart->add_to_cart($client_product_id, $quantity);

            $redirect_url = wc_get_checkout_url();
            wp_safe_redirect($redirect_url);
            exit; // Ensure the script stops here
        }

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


	public function updateLimitColumn($parent, $child)
	{
		global $wpdb;
	
		// Prepare the SQL query to update the 'limit_commissions' column, using COALESCE to handle NULL
		$query = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}bmlm_gtree_nodes SET limit_commissions = COALESCE(limit_commissions, 0) + 1 WHERE parent = %d AND child = %d", 
			$parent,
			$child
		);
	
		// Print the actual query for debugging
		//echo $query;
	
		// Execute the query
		$result = $wpdb->query($query);
	
		// Debugging output (for development)
		//var_dump($result);
	
		if ($result === false) {
			///echo "Error: " . $wpdb->last_error; // Display the last error from wpdb
			return false; // Indicate failure
		}
	
		if ($result === 0) {
			return false;
		} 

		return true; // Indicate success
	}
	
	
	public function processGHLAccount($order_id) {
		global $wpdb;
	
		// Fetch the order object
		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Order $order_id not found.");
			return;
		}
	
		// Get the customer ID from the order
		$customer_id = $order->get_user_id();
		if (!$customer_id) {
			error_log("Order $order_id has no associated customer.");
			return;
		}
	
		// Retrieve user meta data
		$user_meta = [
			'first_name' => get_user_meta($customer_id, 'first_name', true),
			'last_name' => get_user_meta($customer_id, 'last_name', true),
			'email' => get_user_meta($customer_id, 'billing_email', true),
			'ds_business_name' => get_user_meta($customer_id, 'ds_business_name', true),
			'ds_company_name' => get_user_meta($customer_id, 'ds_company_name', true),
			'ds_phone' => get_user_meta($customer_id, 'ds_phone', true),
			'address' => get_user_meta($customer_id, 'ds_address', true),
			'city' => get_user_meta($customer_id, 'ds_city', true),
			'state' => get_user_meta($customer_id, 'ds_state', true),
			'postal_code' => get_user_meta($customer_id, 'ds_postal_code', true),
			'country' => get_user_meta($customer_id, 'ds_country', true),
			'password' => get_user_meta($customer_id, 'ds_password', true),
			'account_type' => get_user_meta($customer_id, 'account_type', true),
			'parent_id' => get_user_meta($customer_id, 'ds_parent_id', true)
		];
	
		// Check if the account type is "ds_client"
		if ($user_meta['account_type'] === "ds_client") {
			// Insert into ds_clients table
			$wpdb->insert($wpdb->prefix . 'ds_clients', [
				'child' => $customer_id,
				'parent' => $user_meta['parent_id'],
				'order_id' => $order_id
			]);
	
			// Insert initial commission for the parent
			$wpdb->insert($wpdb->prefix . 'bmlm_commission', [
				'user_id' => $user_meta['parent_id'],
				'type' => 'joining',
				'description' => '',
				'commission' => $order->get_total() * self::COMMISSION_RATE,
				'date' => current_time('mysql'),
				'paid' => 'unpaid'
			]);
	
			// Process dealer commissions recursively
			$this->processDealerCommissions($user_meta['parent_id'], $order);
	
			// Update order meta with user data
			update_post_meta($order_id, '_billing_phone', $user_meta['ds_phone']);
			update_post_meta($order_id, '_billing_address_1', $user_meta['address']);
			update_post_meta($order_id, '_billing_city', $user_meta['city']);
	
			update_post_meta($order_id, '_shipping_phone', $user_meta['ds_phone']);
			update_post_meta($order_id, '_shipping_address_1', $user_meta['address']);
			update_post_meta($order_id, '_shipping_city', $user_meta['city']);
	
			// Add company and business name to order meta
			add_post_meta($order_id, '_company_name', $user_meta['ds_company_name']);
			add_post_meta($order_id, '_business_name', $user_meta['ds_business_name']);
		}
	
		// Prepare API data for business creation
		$business_data = [
			"businessName" => $user_meta['ds_business_name'],
			"companyName" => $user_meta['ds_company_name'],
			"email" => $user_meta['email'],
			"phone" => $user_meta['ds_phone'],
			"address" => $user_meta['address'],
			"city" => $user_meta['city'],
			"state" => $user_meta['state'],
			"postalCode" => $user_meta['postal_code'],
			"country" => $user_meta['country']
		];
	
		// Send API request to create sub-account
		$response = $this->send_api_request("/v1/locations/", $business_data);
		if (!$response || empty($response['id'])) {
			error_log("Failed to create sub-account for order $order_id.");
			return;
		}
	
		$location_id = $response['id'];
	
		// Prepare API data for admin user creation
		$user_data = [
			"locationIds" => [$location_id],
			"firstName" => $user_meta['first_name'],
			"lastName" => $user_meta['last_name'],
			"email" => $user_meta['email'],
			"password" => $user_meta['password'],
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
	
		$this->save_data_for_cron($user_data,$location_id, $user_meta['email']);
		// Send API request to create admin user
		/*$user_response = $this->send_api_request("/v1/users/", $user_data);
		if ($user_response) {
			error_log("Success: Sub-Account and Admin User Created Successfully for order $order_id.");
		} else {
			error_log("Failed: Admin user creation failed for order $order_id.");
		}*/
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

	public function register_dealer_no_tree(){
		if(isset($_POST['register_dealer_no_tree'])){
			
			$email_address = sanitize_email($_POST['email_address']);
                
			$password = sanitize_text_field($_POST['password']);
			
			$user_id = wp_create_user($email_address, $password,$email_address);

			add_user_meta($user_id , 'account_type', "ds_dealer");

			if(isset($_POST['first_name'])){
				$first_name = sanitize_text_field($_POST['first_name']);
				update_user_meta($user_id, 'first_name',$first_name);
				$data['first_name'] = $first_name;
			}   

			if(isset($_POST['last_name'])){
				$last_name = sanitize_text_field($_POST['last_name']);
				update_user_meta($user_id, 'last_name',$last_name);
				$data['last_name'] = $last_name;
			}

			if(isset($_POST['ds_company_name'])){
				$company_name = sanitize_text_field($_POST['ds_company_name']);
				add_user_meta($user_id, 'ds_company_name',$last_name);
				$data['ds_company_name'] = $company_name;
			}

			if(isset($_POST['ds_business_name'])){
				$business_name = sanitize_text_field($_POST['ds_business_name']);
				add_user_meta($user_id, 'ds_business_name',$last_name);
				$data['ds_business_name'] = $business_name;
			}
			
			if(isset($_POST['ds_phone'])){
				$phone = sanitize_text_field($_POST['ds_phone']);
				add_user_meta($user_id, 'ds_phone',$phone);
				$data['ds_phone'] = $phone;
			}
			
			 // Assign role
			 $user = new WP_User($user_id);
			 $user->set_role('bmlm_sponsor');

			 $bmlm_sponsor_id = $this->generate_random_string(10);
			 add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);
			 // Auto-login the user
			 wp_clear_auth_cookie();
			 wp_set_current_user($user_id);
			 wp_set_auth_cookie($user_id);
			 $this->add_to_cart($user_id, "ds_dealer", $data);
	
		}
	}

	public function process_stripe($order_id, $stripe_product_id)
	{
	
		// Prepare the arguments for creating the plan
	
		$planEndpointArgs = [
			"headers" => [
				"Authorization" => "Bearer " . $this->stripe_api_key,
			],
			"body" => [
				"amount" => 0,
				"interval_count" => 1,
				"interval" => "monthly",
				"product" => $createProducstripe_product_idtResult->id,
				"currency" => "cad",
				"id" => $order_id,
			],
		];
		// Call the plan creation endpoint
		$planResult = wp_remote_post($this->planEndpoint, $planEndpointArgs);

		// Decode the JSON response for the plan creation
		$planResult = json_decode($planResult["body"]);

		// Extract plan information
		$productID = $planResult->product;
		$planID = $planResult->id;

		// Prepare the arguments for creating the subscription
		$subscriptionArgs = [
			"headers" => [
				"Authorization" => "Bearer " . $this->stripe_api_key,
			],
			"body" => [
				"customer" => $customer_id,
				"items[0][plan]" => $planID,
			],
		];

		// Call the subscription creation endpoint
		$subscriptionResult = wp_remote_post($this->subscriptionEndpoint, $subscriptionArgs);

		// Decode the response from the subscription creation
		$subscriptionBody = $subscriptionResult["body"];
		$subscriptionBody = json_decode($subscriptionBody);
		$subscriptionBody = (array) $subscriptionBody;

		// Extract the subscription ID
		$subscriptionID = $subscriptionBody["id"];

	}
}