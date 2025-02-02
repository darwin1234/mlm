<?php
 if ( ! defined( 'ABSPATH' ) ) exit;
function ghlconnectpro_connect_to_ghl_based_on_order( $order_id, $old_status, $new_status ){

    $order = wc_get_order($order_id);//fetch the order id.
    
    //check for downloadable products.
    $flag=0;

    if ($order) {
        // Loop through each item in the order
        foreach ($order->get_items() as $item_id => $item) {
            // Get the product object
            $product = $item->get_product();
            
            // Check if the product is downloadable
            if ($product && $product->is_downloadable()) {
                    $flag=1;
            }
        }
    }
   
    //get trigger option for physical products.
    $ghlconnectpro_order_status = get_option('ghlconnectpro_order_status', 'wc-processing');
    
    //get trigger option for downlodable products.
    $ghlconnectpro_order_status_downloadable = get_option('ghlconnectpro_order_status_downloadable', 'wc-processing');
    
    //check for downloadable or not.
    if($flag==1){
        $set_new_status = str_ireplace( "wc-", "", $ghlconnectpro_order_status_downloadable );
    
        if ( $set_new_status != $new_status ) {
            return;
        }
    }
    else{
        $set_new_status = str_ireplace( "wc-", "", $ghlconnectpro_order_status );
    
        if ( $set_new_status != $new_status ) {
            return;
        }
    }
    
    
    //fetch the location 
    $locationId = get_option( 'ghlconnectpro_locationId' );
    
    //make a contact data and send it to the connected location.
    $contact_data = [
        "locationId"    => $locationId,
        "firstName"     => $order->get_billing_first_name(),
        "lastName"      => $order->get_billing_last_name(),
        "email"         => $order->get_billing_email(),
        "phone"         => $order->get_billing_phone()      
    ];
    $invoice_data=get_option('ghlconnectpro_invoice_check');
    $contactId = ghlconnectpro_get_location_contact_id($contact_data);
    //do here to send invoice data.
    if($invoice_data==='yes'){
        create_ghlpro_invoices($order,$contactId);
    }
    

    // Get and Loop Over Order Items
    foreach ( $order->get_items() as $item_id => $item ) {
        
        $product_id = $item->get_product_id();
        $ghlconnectpro_location_tags = get_post_meta( $product_id, 'ghlconnectpro_location_tags' );
        $addTags = !empty($ghlconnectpro_location_tags) ? ['tags' => $ghlconnectpro_location_tags[0]] : ['tags' => ''];
        $globTags = ['tags' => get_option('ghlconnectpro_globTags')];
        $emptyTags = ['tags' => ''];

        // Retrieve order status and checkbox setting
        $order_status = $new_status;
        $orderTags = get_option('ghlconnectpro_order_check'); // "yes" or "no" value from the checkbox

        // Determine which tags to use
        $result_tags = !empty($addTags['tags']) ? $addTags : (!empty($globTags['tags']) ? $globTags : $emptyTags);

        // Check if result_tags['tags'] is empty and apply the order status based on checkbox
        if (empty($result_tags['tags'])) {
            $result_tags['tags'] = ($orderTags === 'yes') ? [$order_status] : [];
        } else {
            // Ensure tags is an array and filter out any empty strings
            $result_tags['tags'] = array_filter((array)$result_tags['tags']);
            
            // Append $order_status only if checkbox value is "yes"
            if ($orderTags === 'yes') {
                $result_tags['tags'][] = $order_status;
            }
        }

        // Add tags to GHL
        ghlconnectpro_location_add_contact_tags($contactId, $result_tags);
        
        
        
        //workflow part.
        $ghlconnectpro_location_workflow = get_post_meta( $product_id, 'ghlconnectpro_location_workflow' );

        if ( !empty($ghlconnectpro_location_workflow) ) {
            $ghlconnectpro_location_workflow = $ghlconnectpro_location_workflow[0];
            
            foreach ( $ghlconnectpro_location_workflow as $workflow_id ){
                ghlconnectpro_location_add_contact_to_workflow( $contactId, $workflow_id );
            }
        }

    }

}
add_action( 'woocommerce_order_status_changed', 'ghlconnectpro_connect_to_ghl_based_on_order', 10, 3 );