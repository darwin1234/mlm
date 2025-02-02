<?php
if ( ! defined( 'ABSPATH' ) ) exit;
add_action('init', function() {

    if ( isset( $_GET['code'] ) ) {
        $code = sanitize_text_field( $_GET['code'] );
        $ghlconnectpro_client_id           = get_option( 'ghlconnectpro_client_id' );
        $ghlconnectpro_client_secret       = get_option( 'ghlconnectpro_client_secret' );
        
        $result = ghlconnectpro_get_first_auth_code($code, $ghlconnectpro_client_id, $ghlconnectpro_client_secret);
        
        $ghlconnectpro_access_token = $result->access_token;
        $ghlconnectpro_refresh_token = $result->refresh_token;
        $ghlconnectpro_locationId = $result->locationId;
        // Save data
        update_option( 'ghlconnectpro_access_token', $ghlconnectpro_access_token );
        update_option( 'ghlconnectpro_refresh_token', $ghlconnectpro_refresh_token );
        update_option( 'ghlconnectpro_locationId', $ghlconnectpro_locationId );
        update_option( 'ghlconnectpro_location_connected', 1 );

        // delete old transient (if exists any)
        delete_transient('ghlconnectpro_location_tags');
        delete_transient('ghlconnectpro_location_workflow');

        wp_redirect( admin_url( 'admin.php?page=ib-ghlconnectpro-settings' ) );
        exit();
    }
});

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

function ghlconnectpro_get_first_auth_code($code, $client_id, $client_secret){

    $endpoint = GHLCONNECTPRO_GET_TOKEN_API;
    $body = array(
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'grant_type'    => 'authorization_code',
        'code'          => $code
    );

    $request_args = array(
        'body'      => $body,
        'headers'   => array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
    );

    $response = wp_remote_post( $endpoint, $request_args );
    $http_code = wp_remote_retrieve_response_code( $response );

    if ( 200 === $http_code ) {

        $body = json_decode( wp_remote_retrieve_body( $response ) );
        return $body;
    }    
}