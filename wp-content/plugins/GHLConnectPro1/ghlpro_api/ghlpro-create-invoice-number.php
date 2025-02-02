<?php

if ( ! function_exists( 'ghlpro_create_invoice_number' ) ) {
    
    function ghlpro_create_invoice_number() {
		$ghlconnectpro_access_token = get_option('ghlconnectpro_access_token');
        $ghlconnectpro_locationId = get_option('ghlconnectpro_locationId');
		$endpoint = "https://services.leadconnectorhq.com/invoices/generate-invoice-number";
		$ghl_version = '2021-07-28';
        $data = array(
            'altId' 	=> $ghlconnectpro_locationId,
            'altType'   => 'location'
        );
        $request_args = array(
            'body' 		=> $data,
			'headers' => array(  
				'Authorization' => "Bearer {$ghlconnectpro_access_token}",
				'Version' => $ghl_version
			),
		);

		$response = wp_remote_get( $endpoint, $request_args );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $http_code) {
            $body = json_decode( wp_remote_retrieve_body( $response ) );
			$invoice_number = $body->invoiceNumber;
			return $invoice_number;
		}
    	
		return "";
		
    }
}