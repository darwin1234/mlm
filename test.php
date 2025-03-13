<?php 

require __DIR__ . '/wp-load.php';

global $wpdb;
$downline_limit = 5;
$parent =249;
$order = 1000;

function processDealerCommissions($child, $order, $level = 1, $downline_limit = 5) 
{
		
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
                'commission' => 1000 * 0.02,
                'date' => current_time('mysql'),
                'paid' => 'unpaid'
        ]);

        updateLimitColumn($dealer->parent,$child);
        if (!$result) {
            echo "Unsuccessfull";
        }
        // Recursively process the next level (parent of the current dealer)
        processDealerCommissions($dealer->parent, $order, $level + 1, $downline_limit);
    }

}
function updateLimitColumn($parent, $child)
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
	
$wpdb->insert($wpdb->prefix . 'bmlm_commission', [
    'user_id' => $parent,
    'type' => 'joining',
    'description' => '',
    'commission' => 1000 * 0.10,
    'date' => current_time('mysql'),
    'paid' => 'unpaid'
]);

// Process dealer commissions recursively
processDealerCommissions($parent, $order);