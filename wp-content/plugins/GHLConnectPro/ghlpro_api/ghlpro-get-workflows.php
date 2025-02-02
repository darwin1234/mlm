<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! function_exists( 'ghlconnectpro_get_location_workflows' ) ) {
    
    function ghlconnectpro_get_location_workflows() {

    	$key = 'ghlconnectpro_location_workflow';
    	$expiry = 60  * 60 * 24; // 1 day

    	$workflows = get_transient($key);

    	if ( !empty( $workflows ) ) {    		
    		//delete_transient($key);
    		return $workflows;
    	}

		$ghlconnectpro_locationId = get_option( 'ghlconnectpro_locationId' );
		$ghlconnectpro_access_token = get_option( 'ghlconnectpro_access_token' );

		$endpoint = GHLCONNECTPRO_GET_WORKFLOWS_API . "{$ghlconnectpro_locationId}";
		$ghl_version = GHLCONNECTPRO_GET_WORKFLOWS_VERSION;

		$request_args = array(
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
			$workflows = json_decode( $body )->workflows;
			set_transient( $key, $workflows, $expiry );
			return $workflows;

		}elseif( 401 === $http_code ){

			ghlconnectpro_get_new_access_token();
			
		}
    }
}