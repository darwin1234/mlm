<?php 

require __DIR__ . '/wp-blog-header.php';

/*global $wpdb;
$parent_id =  176;

$dealers = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}bmlm_gtree_nodes WHERE parent = %d ORDER BY ID DESC LIMIT 1",
    $parent_id
));

if (empty($dealers)) {
    echo "No dealers found";
} else {
    var_dump($dealers); // Output the dealer data if found
}*/


//var_dump($dealers[0]->nrow);

	// Prepare API data for business creation
 $business_data = [
        "businessName" => 'ABCTESTINGINC55235',
        "companyName" => 'ABCTESTINGINC55235',
        "email" => 'darwinsesetestingclient55235@test.com',
        "phone" => '09123123123',
        "address" => '1255 Mabini Street',
        "city" => 'San Fernando',
        "state" => 'La Union',
        "postalCode" =>'25000',
        "country" => 'PH'
 ];

 // Send API request to create sub-account
$response = send_api_request("/v1/locations/", $business_data);
    if (!$response || empty($response['id'])) {
       // error_log("Failed to create sub-account for order $order_id.");
        return;
    }

    $location_id = $response['id'];

    $user_data = [
        "locationIds" => [$location_id, 'VxgP7Rj68WYNIXhMQsb5'],
        "firstName" => "Darwin",
        "lastName" => "TestingClient",
        "email" =>  "darwinsesetestingclient55235@test.com",
        "password" =>"donmock123",
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

    // Send API request to create admin user
    $user_response = send_api_request("/v1/users/", $user_data);
    if ($user_response) {
       echo "SUCCESS!";
    } else {
       echo "ERROR";
    }

function send_api_request($endpoint, $data)
{
    $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55X2lkIjoiZGNCY0g4enRsRE8zcWZ3QmV2M3oiLCJ2ZXJzaW9uIjoxLCJpYXQiOjE3NDE1OTUwNzUwMDMsInN1YiI6IkhKR1djblpzbUprN3FBdjI2bG9YIn0.OXlDtQunS4CjjshhGFvYxBP4c8mZwdoIutMAuzyQYcY';
		$response = wp_remote_post('https://rest.gohighlevel.com' . $endpoint, [
			'method' => 'POST',
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
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