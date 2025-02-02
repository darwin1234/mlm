<?php
	 if ( ! defined( 'ABSPATH' ) ) exit;
	if ( isset( $_GET['connection_status'] ) && sanitize_text_field($_GET['connection_status']) === 'success' ) {
		$ghlconnectpro_access_token 	= sanitize_text_field( $_GET['acctn'] );
		$ghlconnectpro_refresh_token 	= sanitize_text_field( $_GET['reftn'] );
		$ghlconnectpro_locationId 	    = sanitize_text_field( $_GET['locid'] );
		$ghlconnectpro_client_id 		= sanitize_text_field( $_GET['cntid'] );
		$ghlconnectpro_client_secret 	= sanitize_text_field( $_GET['cntst'] );
        
		// Save data
	    update_option( 'ghlconnectpro_access_token', $ghlconnectpro_access_token );
	    update_option( 'ghlconnectpro_refresh_token', $ghlconnectpro_refresh_token );
	    update_option( 'ghlconnectpro_locationId', $ghlconnectpro_locationId );
	    update_option( 'ghlconnectpro_client_id', $ghlconnectpro_client_id );
	    update_option( 'ghlconnectpro_client_secret', $ghlconnectpro_client_secret );
	    update_option( 'ghlconnectpro_location_connected', 1 );
		update_option( 'ghlconnectpro_loc_name', ghlconnectpro_location_name($ghlconnectpro_locationId)->name);
	    //  (delete if any old transient  exists )
	    delete_transient('ghlconnectpro_location_tags');
	    delete_transient('ghlconnectpro_location_wokflow');

	    wp_redirect('admin.php?page=ib-ghlconnectpro');
	}
    
	$ghlconnectpro_location_connected	= get_option( 'ghlconnectpro_location_connected', GHLCONNECTPRO_LOCATION_CONNECTED );
	$ghlconnectpro_client_id 			= get_option( 'ghlconnectpro_client_id' );
	$ghlconnectpro_client_secret 		= get_option( 'ghlconnectpro_client_secret' );
	$ghlconnectpro_locationId 		    = get_option( 'ghlconnectpro_locationId' );
	$ghlconnectpro_locationName		    = get_option( 'ghlconnectpro_loc_name' );
	$redirect_page 				    = get_site_url(null, '/wp-admin/admin.php?page=ib-ghlconnectpro');
	$redirect_uri 				    = get_site_url();
	$client_id_and_secret 		    = '';

	$auth_end_point = GHLCONNECTPRO_AUTH_END_POINT;
	$scopes = "workflows.readonly contacts.readonly contacts.write campaigns.readonly conversations/message.readonly conversations/message.write forms.readonly locations.readonly locations/customValues.readonly locations/customValues.write locations/customFields.readonly locations/customFields.write opportunities.readonly opportunities.write users.readonly links.readonly links.write surveys.readonly users.write locations/tasks.readonly locations/tasks.write locations/tags.readonly locations/tags.write locations/templates.readonly calendars.write calendars/groups.readonly calendars/groups.write forms.write medias.readonly medias.write";

    $connect_url = GHLCONNECTPRO_AUTH_URL . "?get_code=1&redirect_page={$redirect_page}";

	// if ( ! empty( $ghlconnectpro_client_id ) && ! str_contains( $ghlconnectpro_client_id, 'lq4sb5tt' ) ) {
		
	// 	$connect_url = $auth_end_point . "?response_type=code&redirect_uri={$redirect_uri}&client_id={$ghlconnectpro_client_id}&scope={$scopes}";
	// }
	
	
?>

<div id="ib-ghlconnectpro">
    <h1> <?php esc_html_e('Connect With Your GHL Subaccount', 'ghl-connect-pro'); ?> </h1>
    <hr />
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">
                    <label> <?php esc_html_e('Connect GHL Subaccount Location', 'ghl-connect-pro'); ?> </label>
                </th>
                <td>
                    <?php if ($ghlconnectpro_location_connected) { ?>
                    <div class="connected-location">
                        <button class="button button-connected" disabled>Connected</button>
                        <!-- Show success message after connection -->
                        <?php if (isset($_GET['connected']) && sanitize_text_field($_GET['connected']) === 'true') { ?>
                        <p class="success-message">You have successfully connected to Subaccount Location ID:
                            <?php echo esc_html($ghlconnectpro_locationId); ?></p>
                        <?php } ?>
                        <p class="description">To connect another subaccount location, click below:</p>
                        <a class="ghl_connect button" href="<?php echo esc_url($connect_url); ?>">Connect Another
                            Subaccount</a>
                    </div>
                    <?php } else { ?>
                    <div class="not-connected-location">
                        <p class="description">You're not connected to any subaccount location yet.</p>
                        <a class="ghl_connect button" href="<?php echo esc_url($connect_url); ?>">Connect GHL
                            Subaccount</a>
                    </div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Your Connected GHL Subaccount Details', 'ghl-connect-pro'); ?></label>
                </th>
                <td>
                    <?php if ($ghlconnectpro_location_connected) { ?>
                    <p class="description">Location ID: <?php echo esc_html($ghlconnectpro_locationId); ?></p>
                    <p class="description">Location Name: <?php echo esc_html($ghlconnectpro_locationName); ?></p>
                    <?php } else { ?>
                    <p class="description">You are not connected yet. Please connect by clicking the above button</p>
                    <?php } ?>
                </td>
            </tr>

        </tbody>
    </table>


</div>