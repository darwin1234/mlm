<?php

/**
     * Plugin Name: Display All Users
     * Description: A simple plugin to display all registered users using a shortcode.
     * Version: 1.0
     * Author: Your Name
*/


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class mlmExtension {
    
    public function __construct()
    {

        // Register the shortcode
        add_shortcode('display_all_users', array($this,'dau_display_all_users_with_profiles' ));
        
        add_action('init', array($this,'dau_add_rewrite_rules'));

        add_filter('query_vars', array($this,'dau_add_query_vars' ));

        add_action('template_redirect', array($this, 'dau_template_redirect'));

        register_activation_hook(__FILE__, array($this,'dau_flush_rewrite_rules' ));

        register_deactivation_hook(__FILE__, array($this,'dau_remove_rewrite_rules' ));

        add_action('mlm_extension_menu',array($this,'mlm_extension_menu'));

    }

    public function mlm_extension_menu(){
        
        $links=array(
            'marketing-crm-link' => 'Marketing CRM Links',
            'social-media-kit' => 'Social Media Kit',
            'training-resources' => 'Training Resources'
        );
        $menu = "";
        foreach($links as $slug => $link){
            $menu .="<li class='woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--".$slug."'><a href='#'>".$link."</a>";
        }
        echo $menu;
    }

    // Shortcode to display all users
    public function dau_display_all_users_with_profiles() {
        // Get all users
        $users = get_users();
        if (empty($users)) {
            return '<p>No users found.</p>';
        }

        // Start output buffering
        ob_start();

        echo '<ul>';
        foreach ($users as $user) {
            $profile_url = site_url('/sponsor/?sponsor=' . $user->ID); // Generate profile URL
            echo '<li>';
            echo '<a href="' . esc_url($profile_url) . '">' . esc_html($user->display_name) . '</a>';
            echo '</li>';
        }
        echo '</ul>';

        // Return the buffered output
        return ob_get_clean();
    }

    // Register the custom rewrite rule for profile pages
    public function dau_add_rewrite_rules() {
        add_rewrite_rule('^sponsor/([0-9]+)/?$', 'index.php?user_id=$matches[1]', 'top');
    }

    // Add query variable for user ID
    public function dau_add_query_vars($vars) {
        $vars[] = 'user_id';
        return $vars;
    }

    // Template redirect for profile pages
    public function dau_template_redirect() {
        $user_id = get_query_var('user_id');
        if ($user_id) {
            // Check if user exists
            $user = get_user_by('ID', $user_id);
            if ($user) {
                // Display user profile
                include plugin_dir_path(__FILE__) . 'templates/user-profile.php';
                exit;
            } else {
                wp_die('User not found.');
            }
        }
    }
    // Flush rewrite rules on plugin activation
    public function dau_flush_rewrite_rules() {
        dau_add_rewrite_rules();
        flush_rewrite_rules();
    }

    

    // Flush rewrite rules on plugin deactivation
    public function dau_remove_rewrite_rules() {
        flush_rewrite_rules();
    }

}

new mlmExtension;













