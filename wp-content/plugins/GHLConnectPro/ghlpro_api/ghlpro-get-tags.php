<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! function_exists( 'ghlconnectpro_get_location_tags' ) ) {
    
    function ghlconnectpro_get_location_tags() {

    	$key = 'ghlconnectpro_location_tags';
    	$expiry = 60  * 60 * 24; // 1 day

    	$tags = get_transient($key);

    	if ( !empty( $tags ) ) {
    		
    		return $tags;
    	}

		$ghlconnectpro_locationId = get_option( 'ghlconnectpro_locationId' );
		$ghlconnectpro_access_token = get_option( 'ghlconnectpro_access_token' );

		$endpoint = GHLCONNECTPRO_GET_TAGS_API . "{$ghlconnectpro_locationId}/tags";
		$ghl_version = GHLCONNECTPRO_GET_TAGS_VERSION;

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
			$tags = json_decode( $body )->tags;
			set_transient( $key, $tags, $expiry );
			return $tags;

		}elseif( 401 === $http_code ){
			ghlconnectpro_get_new_access_token();
		}
    }
}