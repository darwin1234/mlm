<?php
if (! defined('ABSPATH')) exit;
function ghlconnectpro_connect_to_ghl_based_on_order($order_id, $old_status, $new_status)
{

    $order = wc_get_order($order_id); //fetch the order id.

    //check for downloadable products.
    $flag = 0;

    if ($order) {
        // Loop through each item in the order
        foreach ($order->get_items() as $item_id => $item) {
            // Get the product object
            $product = $item->get_product();

            // Check if the product is downloadable
            if ($product && $product->is_downloadable()) {
                $flag = 1;
            }
        }
    }

    //get trigger option for physical products.
    $ghlconnectpro_order_status = get_option('ghlconnectpro_order_status', 'wc-processing');

    //get trigger option for downlodable products.
    $ghlconnectpro_order_status_downloadable = get_option('ghlconnectpro_order_status_downloadable', 'wc-processing');

    //check for downloadable or not.
    if ($flag == 1) {
        $set_new_status = str_ireplace("wc-", "", $ghlconnectpro_order_status_downloadable);

        if ($set_new_status != $new_status) {
            return;
        }
    } else {
        $set_new_status = str_ireplace("wc-", "", $ghlconnectpro_order_status);

        if ($set_new_status != $new_status) {
            return;
        }
    }


    //fetch the location 
    $locationId = get_option('ghlconnectpro_locationId');

    //make a contact data and send it to the connected location.
    $contact_data = [
        "locationId"    => $locationId,
        "firstName"     => $order->get_billing_first_name(),
        "lastName"      => $order->get_billing_last_name(),
        "email"         => $order->get_billing_email(),
        "phone"         => $order->get_billing_phone()
    ];
    $invoice_data = get_option('ghlconnectpro_invoice_check');
    $contactId = ghlconnectpro_get_location_contact_id($contact_data);
    //do here to send invoice data.
    if ($invoice_data === 'yes') {
        create_ghlpro_invoices($order, $contactId);
    }

    // Loop over order items
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $variation_id = $item->get_variation_id();

        // Get tags for the product
        $ghlconnectpro_location_tags = get_post_meta($product_id, 'ghlconnectpro_location_tags', true);
        $addTags = !empty($ghlconnectpro_location_tags) ? ['tags' => $ghlconnectpro_location_tags] : ['tags' => ''];
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
            $result_tags['tags'] = array_filter((array) $result_tags['tags']);

            // Append $order_status only if checkbox value is "yes"
            if ($orderTags === 'yes') {
                $result_tags['tags'][] = $order_status;
            }
        }

        // Check for variable product and get variation-specific tags
        $product = wc_get_product($product_id);
        $variation_specific_tags = '';
        $send_only_variation_tags = '';

        if ($product && $product->is_type('variable') && $variation_id) {
            // Fetch variation-specific tags
            $variation_specific_tags = get_post_meta($variation_id, '_variation_specific_tags', true);

            // Fetch checkbox value
            $send_only_variation_tags = get_post_meta($variation_id, '_send_only_variation_tags', true);
        }

        // Determine the final tags to send
        if ($send_only_variation_tags === 'yes') {
            // If checkbox is "yes," send only variation-specific tags
            $final_variation = array_filter(array_map('trim', explode(',', $variation_specific_tags)));
            $final_tags = ['tags' => $final_variation];
        } else {
            // Merge all other tags including variation-specific tags
            $final_tags = $result_tags;

            // Append variation-specific tags to the final tags if available
            if (! empty($variation_specific_tags)) {
                $final_variation = array_filter(array_map('trim', explode(',', $variation_specific_tags)));
                $final_tags['tags'] = array_merge($final_tags['tags'], $final_variation);
                $final_tags['tags'] = array_unique($final_tags['tags']); // Ensure unique tags
            }
        }

        // Send the tags to GHL CRM
        ghlconnectpro_location_add_contact_tags($contactId, $final_tags);




        //workflow part.
        $ghlconnectpro_location_workflow = get_post_meta($product_id, 'ghlconnectpro_location_workflow');

        if (!empty($ghlconnectpro_location_workflow)) {
            $ghlconnectpro_location_workflow = $ghlconnectpro_location_workflow[0];

            foreach ($ghlconnectpro_location_workflow as $workflow_id) {
                ghlconnectpro_location_add_contact_to_workflow($contactId, $workflow_id);
            }
        }
    }
}
add_action('woocommerce_order_status_changed', 'ghlconnectpro_connect_to_ghl_based_on_order', 10, 3);

// Add custom fields to variation settings
add_action('woocommerce_product_after_variable_attributes', 'add_custom_fields_to_variations', 10, 3);
function add_custom_fields_to_variations($loop, $variation_data, $variation)
{
    // Custom field for 'Variation Specific Tags'
    woocommerce_wp_textarea_input(
        array(
            'id'            => "variation_specific_tags_{$loop}",
            'name'          => "variation_specific_tags[{$variation->ID}]",
            'label'         => __('Variation Specific Tags', 'ghl-connect-pro'),
            'description'   => __('Enter specific tags for this variation comma seperated.', 'ghl-connect-pro'),
            'value'         => get_post_meta($variation->ID, '_variation_specific_tags', true),
            'desc_tip'      => true,
        )
    );

    // Checkbox field for 'Send only the Variation Specific Tags?'
    woocommerce_wp_checkbox(
        array(
            'id'            => "send_only_variation_tags_{$loop}",
            'name'          => "send_only_variation_tags[{$variation->ID}]",
            'label'         => __('Send only the Variation Specific Tags?', 'ghl-connect-pro'),
            'description'   => __('Check this box to send only the variation-specific tags.', 'ghl-connect-pro'),
            'value'         => get_post_meta($variation->ID, '_send_only_variation_tags', true),
            'desc_tip'      => true,
        )
    );
}

// Save custom field values
add_action('woocommerce_save_product_variation', 'save_custom_fields_for_variations', 10, 2);
function save_custom_fields_for_variations($variation_id, $i)
{
    // Save 'Variation Specific Tags'
    if (isset($_POST['variation_specific_tags'][$variation_id])) {
        $tags = sanitize_textarea_field($_POST['variation_specific_tags'][$variation_id]);
        update_post_meta($variation_id, '_variation_specific_tags', $tags);
    }

    // Save 'Send only the Variation Specific Tags?' checkbox
    $send_only_tags = isset($_POST['send_only_variation_tags'][$variation_id]) ? 'yes' : 'no';
    update_post_meta($variation_id, '_send_only_variation_tags', $send_only_tags);
}

// Add custom fields to variation data in the frontend
add_filter('woocommerce_available_variation', 'add_custom_fields_to_frontend');
function add_custom_fields_to_frontend($variation_data)
{
    // Add 'Variation Specific Tags' to variation data
    $variation_data['variation_specific_tags'] = get_post_meta($variation_data['variation_id'], '_variation_specific_tags', true);

    // Add 'Send only the Variation Specific Tags?' checkbox value to variation data
    $variation_data['send_only_variation_tags'] = get_post_meta($variation_data['variation_id'], '_send_only_variation_tags', true);

    return $variation_data;
}