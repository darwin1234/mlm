<?php
function create_ghlpro_invoices($order, $contactId) {
    $locationId = get_option('ghlconnectpro_locationId');
    $ghlconnectpro_access_token = get_option('ghlconnectpro_access_token');
    $locationName = get_option('ghlconnectpro_loc_name');
    $ghl_invoice_number = ghlpro_create_invoice_number();

    if ($order) {
        $order_date = $order->get_date_created();
        $formatted_order_date = $order_date ? $order_date->date('Y-m-d') : '2024-01-01';
        $discount_amount = intval($order->get_discount_total());
    }

    $item_array = array();

    if ($order) {
        $items = $order->get_items();
        $product_currency = $order->get_currency();

        foreach ($items as $item_id => $item) {
            $product_name = $item->get_name();
            $product_price = $item->get_subtotal() / $item->get_quantity(); // Get unit price
            $product_quantity = $item->get_quantity();

            $item_array[] = array(
                "name"     => $product_name,
                "currency" => $product_currency,
                "amount"   => $product_price, // Use unit price
                "qty"      => $product_quantity,
            );
        }
    }

    $body_data = [
        'altId' => $locationId,
        'altType' => 'location',
        'name' => $order->get_billing_first_name() . ' Invoice',
        'businessDetails' => [
            'name' => $locationName
        ],
        'currency' => $product_currency,
        'items' => array_map(function ($item) {
            return [
                'name' => $item['name'],
                'currency' => $item['currency'],
                'amount' => $item['amount'],
                'qty' => $item['qty']
            ];
        }, $item_array),
        'discount' => [
            'value' => $discount_amount,
            'type' => 'percentage'
        ],
        'title' => 'INVOICE',
        'contactDetails' => [
            'id' => $contactId,
            'name' => $order->get_billing_first_name(),
            'phoneNo' => $order->get_billing_phone(),
            'email' => $order->get_billing_email(),
            'address' => [
                'addressLine1' => $order->get_billing_address_1(),
                'addressLine2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'countryCode' => $order->get_billing_country(),
                'postalCode' => $order->get_billing_postcode()
            ]
        ],
        'invoiceNumber' => $ghl_invoice_number,
        'issueDate' => $formatted_order_date,
        'liveMode' => true
    ];

    $body_data['postfields'] = json_encode($body_data);

    $endpoint = "https://services.leadconnectorhq.com/invoices/";
    $ghl_version = '2021-07-28';

    $request_args = [
        'body'    => $body_data['postfields'],
        'headers' => [
            'Authorization' => "Bearer {$ghlconnectpro_access_token}",
            'Version'       => $ghl_version,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
    ];

    $response = wp_remote_post($endpoint, $request_args);
    $http_code = wp_remote_retrieve_response_code($response);
    if (200 === $http_code || 201 === $http_code) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
    } else {
        echo "Error: HTTP Code $http_code";
    }
}
?>