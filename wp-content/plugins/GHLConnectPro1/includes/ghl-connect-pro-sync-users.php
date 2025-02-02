<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['contact_register_btn'])) {
        //when sync button is clicked
        update_option('sync_click', 'yes');
        update_option('sync_complete', 'no');
        
        // Initialize sync progress
        update_option('ghl_synced_users', 0); // Reset synced count
        $users = get_users();
        update_option('ghl_total_users', count($users)); // Save total user count
        update_option('ghl_user_sync_list', $users); // Save users for cron job

        // Schedule cron event
        if (!wp_next_scheduled('ghl_sync_contacts_event')) {
            wp_schedule_event(time(), 'ten_seconds', 'ghl_sync_contacts_event');
        }
    }
}


?>
<form method="post" class="form-table">
    <?php 
    $sync_click=get_option('sync_click');
    $sync_complete=get_option('sync_complete');
    
    $register_data = get_option('ghlconnectpro_contact_register_choice');
    $ghlconnectpro_location_connected = get_option('ghlconnectpro_location_connected', GHLCONNECTPRO_LOCATION_CONNECTED);
    
    // Get sync progress
    $total_users = get_option('ghl_total_users', 0);
    $synced_users = get_option('ghl_synced_users', 0);
    ?>

    <table>
        <tbody>
            <tr>
                <th scope="row">
                    <label>Add All Users to GHL?</label>
                </th>
                <td>
                    <?php if ($sync_complete === "yes") { ?>
                    <button class="ghl_connectpro_sync button" type="submit" name="contact_register_btn">Sync
                        Again</button>
                    <?php } else { ?>
                    <?php if ($ghlconnectpro_location_connected) { ?>
                    <button class="ghl_connectpro_sync button" type="submit" name="contact_register_btn">Sync
                        Users</button>
                    <?php } else { ?>
                    <button class="ghl_connectpro_sync button" type="submit" name="contact_register_btn" disabled>Sync
                        Users</button>
                    <p class="syncp">First Connect Your GHL Subaccount.</p>
                    <?php } ?>
                    <?php } ?>

                    <!-- Display sync progress -->
                    <p class="description-ghl"></p>

                </td>
            </tr>
        </tbody>
    </table>
</form>