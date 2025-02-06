<?php 


class mlmregistration 
{
    private $ghl_endpoint = "https://rest.gohighlevel.com";

	private $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55X2lkIjoiZGNCY0g4enRsRE8zcWZ3QmV2M3oiLCJ2ZXJzaW9uIjoxLCJpYXQiOjE3MzgyMjgwNjQzNjUsInN1YiI6IlROQ05yaTZEc3Q2OVRzZGVxbjU5In0.I6e3Wqej7p5zeXCS3aPFlogvDXR41aT5ciKPLEY6Cc8';

	private $bmlm_gtree_nodes;

	private $ds_clients;

    public function ProcessRegistration(){
        //global $_POST;
        if(isset($_POST['submit'])){
           
            if(isset($_POST['email_address']) && isset($_POST['password']))
            {

                $account_type ="";

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

				if(isset($_POST['parent_id'])){
					$parent_id =  sanitize_text_field($_POST['parent_id']);
					add_user_meta($user_id, 'ds_parent_id',$parent_id);
				}
              
                $bmlm_sponsor_id = $this->generate_random_string(10);
                add_user_meta($user_id, 'bmlm_sponsor_id', $bmlm_sponsor_id);

            
            
                if(isset($_POST['bmlm_refferal_id'])){
                    $bmlm_refferal_id = sanitize_text_field($_POST['bmlm_refferal_id']);
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

	private function createNodes($parent, $child,$account_type)
	{
		global $wpdb;
		
		if($account_type === "ds_dealer")
		{
			$wpdb->insert($wpdb->prefix .'bmlm_gtree_nodes', array(
				'child' => $child,
				'parent' => $parent,
				'nrow' => 1
			));
		}			
	}
    
    public function add_to_cart($user_id, $membership_type,$address){
        // Product ID to be ordered
		$product_id = 76; 
		$quantity = 1;
        if($membership_type==="ds_dealer")
		{
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

			$order->set_address($address, 'billing');
			$order->set_address($address, 'shipping');
			

            // Calculate totals and complete order
            $order->calculate_totals();
            $order->set_status('completed');
            $order->save();    
            wp_safe_redirect(site_url() . '/my-account');
        }

        if($membership_type==="ds_client"){
			WC()->cart->empty_cart();	
			// Add product to the cart
			$added = WC()->cart->add_to_cart($product_id, $quantity);

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

    public function processGHLAccount($order_id)
	{
		global $wpdb;

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
		$account_type =  get_user_meta($customer_id, 'account_type', true);
		$parent_id =  get_user_meta($customer_id, 'ds_parent_id', true);
		
		if($account_type === "ds_client")
		{
			$wpdb->insert($wpdb->prefix . 'ds_clients', array(
				'child' => $customer_id,
				'parent' => $parent_id,
				'order_id' => $order_id
			));
		}
		
		// Prepare API data
		/*$business_data = [
			"businessName" => $ds_business_name,
			"companyName" => $ds_company_name,
			"email" => $email,
			"phone" => $ds_phone,
			"address" => $address,
			"city" => $city,
			"state" => $state,
			"postalCode" =>$postal_code,
			"country" => $country
		];
		
		// Send request to create sub-account
		$response = $this->send_api_request("/v1/locations/", $business_data);

		if (!$response || empty($response['id'])) {
			error_log("Failed to create sub-account for order $order_id.");
			return;
		}

		$location_id = $response['id'];*/

		// Prepare admin user data
		$user_data = [
			"locationIds" =>'VxgP7Rj68WYNIXhMQsb5',
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



}