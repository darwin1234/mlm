<?php 

require __DIR__ . '/wp-blog-header.php';

global $wpdb;
$parent_id =  176;

$dealers = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}bmlm_gtree_nodes WHERE parent = %d ORDER BY ID DESC LIMIT 1",
    $parent_id
));

if (empty($dealers)) {
    echo "No dealers found";
} else {
    var_dump($dealers); // Output the dealer data if found
}


//var_dump($dealers[0]->nrow);