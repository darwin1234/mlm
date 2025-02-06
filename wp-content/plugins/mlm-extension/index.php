<?php
/**
 * Plugin Name: RealCallerAi Extension
 * Description: MLM Extension
 * Version: 1.0
 * Author: Darwin Sese
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define("DSPATH", plugin_dir_path(__FILE__));
define("pluginsurl", plugins_url() . '/mlm-extension');

require_once(DSPATH . 'includes/registration.php');   

class RealCallerAiExtension {

    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );
        add_action('init', array( $this, 'mlm_rewrite_rule' ) );
        add_filter('query_vars', array( $this, 'dealers_link_var' ) );
        add_filter('query_vars', array( $this, 'client_link_var' ) );
        add_action('template_include', array( $this, 'registration_form' ) );
        add_action('init', array(new mlmregistration, 'ProcessRegistration'));
        add_action('init', array(new mlmregistration ,'register_dealer_no_tree'));
        
        add_action('woocommerce_order_status_completed', array(new mlmregistration, 'processGHLAccount' ), 10, 3);
      
    }
    
    public function mlm_rewrite_rule() {
        add_rewrite_rule(
            '^dealer/([^/]+)/?$',
            'index.php?dealers-form=$matches[1]', // The URL is passed to the 'dealers-form' query var
            'top'
        );

        add_rewrite_rule(
            '^client/([^/]+)/?$',
            'index.php?clients-form=$matches[1]', // The URL is passed to the 'clients-form' query var
            'top'
        );
    }
   
    public function dealers_link_var( $vars ) {
        $vars[] = 'dealers-form'; // Register the Dealers var
        return $vars;
    }

  
    public function client_link_var( $vars ) {
        $vars[] = 'clients-form'; // Register the Dealers var
        return $vars;
    }


    public function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    public function registration_form( $template ) {

        if ( get_query_var( 'dealers-form' ) ) {
            // Load custom page template for dealers
            $new_template = plugin_dir_path( __FILE__ ) . 'templates/dealers.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        if ( get_query_var( 'clients-form' ) ) {
            // Load custom page template for dealers
            $new_template = plugin_dir_path( __FILE__ ) . 'templates/clients.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }
        return $template;
    }

}

// Initialize the plugin class
new RealCallerAiExtension;