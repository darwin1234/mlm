<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function() {

    $ghlconnectpro_locationId = get_option( 'ghlconnectpro_locationId' );
    $is_access_token_valid = get_transient('is_access_token_valid');

    if ( ! empty( $ghlconnectpro_locationId ) && ! $is_access_token_valid ) {
        
        // renew the access token
        ghlconnectpro_get_new_access_token();
    }

});

function ghlconnectpro_get_new_access_token()
{
	$key = 'is_access_token_valid';
    $expiry = 59  * 60 * 24; // almost 1 day

	$ghlconnectpro_client_id 		= get_option( 'ghlconnectpro_client_id' );
	$ghlconnectpro_client_secret 	= get_option( 'ghlconnectpro_client_secret' );
	$refreshToken 			= get_option( 'ghlconnectpro_refresh_token' );
	
	$endpoint = GHLCONNECTPRO_GET_TOKEN_API;
	$body = array(
		'client_id' 	=> $ghlconnectpro_client_id,
		'client_secret' => $ghlconnectpro_client_secret,
		'grant_type' 	=> 'refresh_token',
		'refresh_token' => $refreshToken
	);

	$request_args = array(
		'body' 		=> $body,
		'headers' 	=> array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		),
	);

	$response = wp_remote_post( $endpoint, $request_args );
	$http_code = wp_remote_retrieve_response_code( $response );

	if ( 200 === $http_code ) {

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		$new_ghlconnectpro_access_token = $body->access_token;
		$new_ghlconnectpro_refresh_token = $body->refresh_token;

		update_option( 'ghlconnectpro_access_token', $new_ghlconnectpro_access_token );
		update_option( 'ghlconnectpro_refresh_token', $new_ghlconnectpro_refresh_token );

	
		set_transient( $key, true, $expiry );
	}

	return null;
}