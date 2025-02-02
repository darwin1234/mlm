<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.ibsofts.com
 * @since             2.0.2
 * @package           GHLCONNECTPRO
 *
 * @wordpress-plugin
 * Plugin Name:       GHL Connect for WooCommerce Pro
 * Plugin URI:        https://www.ibsofts.com/plugins/ghl-connect-pro
 * Description:       This plugin will connect the popular CRM goHighlevel(Go High Level) to the most popular content management software WordPress.
 * Version:           2.0.2
 * Author:            iB Softs
 * Author URI:        https://www.ibsofts.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ghl-connect-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 2.0.2 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GHLCONNECTPRO_VERSION', '2.0.2' );
define( 'GHLCONNECTPRO_PLUGIN_BASENAME', plugin_basename( __DIR__ ) );
define( 'GHLCONNECTPRO_LOCATION_CONNECTED', false );
define( 'GHLCONNECTPRO_PATH', plugin_basename( __FILE__ ));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ghl-connect-pro-activator.php
 */
if ( ! function_exists( 'ghlconnectpro_activate' ) ) {
	function ghlconnectpro_activate() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ghl-connect-pro-activator.php';
		GHLCONNECTPRO_Activator::activate();
	}
	register_activation_hook( __FILE__, 'ghlconnectpro_activate' );
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ghl-connect-pro-deactivator.php
 */
if ( ! function_exists( 'ghlconnectpro_deactivate' ) ) {
	function ghlconnectpro_deactivate() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ghl-connect-pro-deactivator.php';
		GHLCONNECTPRO_Deactivator::deactivate();
	}
	register_deactivation_hook( __FILE__, 'ghlconnectpro_deactivate' );
}

/* Check If woocommerce Is Active */
function ghlconnectpro_woocommerce() {   
    
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        add_action('admin_notices', 'ghlconnectpro_woo_notice');
        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }      
}
add_action('admin_init', 'ghlconnectpro_woocommerce');



/**
 * Display an error message when parent plugin is missing
 */
function ghlconnectpro_woo_notice()
{
?>
<div class="notice notice-error">
    <p>
        <strong>Error:</strong>
        <em>GHL Connect for WooCommerce</em> plugin won't execute
        because the required Woocommerce plugin is not active. Install Woocommerce.
    </p>
</div>
<?php
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ghl-connect-pro.php';
/**
 * Inclusion of ghl-connect-pro-definitions.php
 */
require_once plugin_dir_path( __FILE__ ) . 'ghl-connect-pro-definitions.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.2
 */
if ( ! function_exists( 'ghlconnectpro_run' ) ) {
	function ghlconnectpro_run() {

		$plugin = new GHLCONNECTPRO();
		$plugin->run();

	}
	ghlconnectpro_run();
}


//cron job// Add custom cron schedule for every 10 seconds
add_filter('cron_schedules', function($schedules) {
    $schedules['ten_seconds'] = array(
        'interval' => 10, // 10 seconds interval
        'display' => __('Every 10 seconds')
    );
    return $schedules;
});

// Hook the cron event to sync contacts in the background
add_action('ghl_sync_contacts_event', 'ghl_sync_contacts_in_background');


function ghl_sync_contacts_in_background() {
    $users = get_option('ghl_user_sync_list', array());

    if (empty($users)) {
		update_option('sync_complete', 'yes');
        wp_clear_scheduled_hook('ghl_sync_contacts_event');
        return;
    }

    $total_users = get_option('ghl_total_users'); // Total number of users to sync
    $synced_users = get_option('ghl_synced_users', 0); // Already synced users count

    // Process users in chunks of 100
    $chunk = array_splice($users, 0, 100); // Get the first 100 users
    update_option('ghl_user_sync_list', $users); // Update the remaining list

    foreach ($chunk as $user) {
        $user_info = get_userdata($user->ID);
        $locationId = get_option('ghlconnectpro_locationId');

        $contact_data = array(
            "locationId" => $locationId,
            "firstName" => $user_info->first_name,
            "lastName" => $user_info->last_name,
            "email" => $user_info->user_email,
            "phone" => $user_info->billing_phone,
            "tags" => "WP-User"
        );

        ghlconnectpro_get_location_contact_data($contact_data);
    }

    // Update progress
    $synced_users += count($chunk);
    update_option('ghl_synced_users', $synced_users);

    // If no users remain, clear the event
    if (empty($users)) {
		update_option('sync_complete', 'yes');
        wp_clear_scheduled_hook('ghl_sync_contacts_event');
    }
}