<?php

if ( ! function_exists( 'ghlconnectpro_get_contactsList' ) ) {
    
    function ghlconnectpro_get_contactsList() {

    	$key = 'ghlconnectpro_contactsList';
    	$expiry = 60  * 60 * 24; // 1 day

    	$contactsList = get_transient($key);

    	// if ( !empty( $contactsList ) ) {
    	// 	//delete_transient($key);
    	// 	return $contactsList;
    	// }

		$ghlconnectpro_locationId = get_option('ghlconnectpro_locationId');
		$ghlconnectpro_access_token = get_option('ghlconnectpro_access_token');
		$endpoint = "https://services.leadconnectorhq.com/contacts/";
		$ghl_version = '2021-07-28';
        $body = array(
            'locationId' 	=> $ghlconnectpro_locationId,
			'limit'         => 30  
        );
        $request_args = array(
            'body' 		=> $body,
			'headers' => array(  
				'Authorization' => "Bearer {$ghlconnectpro_access_token}",
				'Content-Type' => 'application/json',
				'Version' => $ghl_version,
			),
		);

		$response = wp_remote_get( $endpoint, $request_args );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $http_code ) {

			$body = wp_remote_retrieve_body( $response );
			$contactsList = json_decode( $body )->contacts;
			set_transient( $key, $contactsList, $expiry );
			return $contactsList;

		}elseif( 401 === $http_code ){
			ghlconnectpro_get_new_access_token();
		}
    }
}