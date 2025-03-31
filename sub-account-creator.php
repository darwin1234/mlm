<?php 

require __DIR__ . '/wp-load.php';

global $wpdb;

// Prepare the SQL query
$query = "SELECT * FROM {$wpdb->prefix}ghl_locations WHERE status=0";

// Execute the query and get the results as an associative array
$sub_accounts = $wpdb->get_results($query, ARRAY_A);

function send_api_request($endpoint, $data) {
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

// Iterate through the sub_accounts and send API request
foreach($sub_accounts as $account) {
    $response = send_api_request("/v1/users/", json_decode($account['user_meta'], true));

    // Check if the API request was successful
    if ($response) {
        // Update the status to 1 for this account
        $wpdb->update(
            "{$wpdb->prefix}ghl_locations",  // Table name
            ['status' => 1],                 // Data to update
            ['ID' => $account['ID']],        // Condition to identify the row
            ['%d'],                          // Format of the data
            ['%d']                           // Format for the condition
        );
    }
}
