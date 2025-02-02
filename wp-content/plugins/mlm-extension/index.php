<?php
/**
 * Plugin Name: Custom Rewrite Rule and Page
 * Description: A custom plugin to create a URL rewrite rule and custom page template in an OOP style.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Custom_Rewrite_Rule {

    public function __construct() {
        // Register activation hook to flush rewrite rules on activation
        register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );

        // Add the rewrite rule and query variable
        add_action( 'init', array( $this, 'add_rewrite_rule' ) );
        add_filter( 'query_vars', array( $this, 'add_custom_query_var' ) );

        // Handle the custom page template
        add_action( 'template_include', array( $this, 'load_custom_page_template' ) );
    }

    /**
     * Add custom rewrite rule for our custom URL
     */
    public function add_rewrite_rule() {
        add_rewrite_rule(
            '^personal-information/([^/]+)/?$',
            'index.php?custom_page_param=$matches[1]', // The URL is passed to the 'custom_page_param' query var
            'top'
        );
    }

    /**
     * Register custom query variable for the custom URL
     */
    public function add_custom_query_var( $vars ) {
        $vars[] = 'custom_page_param'; // Register the custom query var
        return $vars;
    }

    /**
     * Flush rewrite rules on plugin activation
     */
    public function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    /**
     * Load the custom page template when the custom URL is accessed
     */
    public function load_custom_page_template( $template ) {
        if ( get_query_var( 'custom_page_param' ) ) {
            // Load custom page template
            $new_template = plugin_dir_path( __FILE__ ) . 'templates/user-profile.php';

            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        return $template;
    }
}

// Initialize the plugin class
new Custom_Rewrite_Rule();