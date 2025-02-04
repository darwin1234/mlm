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
   
        add_shortcode('ds_registration_form', array($this, 'ds_registration_form'));
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

    public function ds_registration_form(){
        ?>
        <form id="registration_client_form" style="display:block!important;">
        <div class="container">
            <div id="sponsorFrm" class="row">
                <div class="col-md-8 m-auto block">
                        <img  class="w-60 m-auto" src="<?php echo  bloginfo('template_url');?>/assets/images/logo.png">
                        <h2 class="text-center">Dealer's Registration</h2>
                        <div class="row">
                            <div class="col">
                                <label>First Name</label>
                                <input type="text" class="form-control" placeholder="First name">
                            </div>
                            <div class="col">
                                 <label>Last Name</label>
                                <input type="text" class="form-control" placeholder="Last name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Company Name</label>
                                <input type="text" class="form-control" placeholder="First name">
                            </div>
                            <div class="col">
                                <label>Business Name</label>
                                <input type="text" class="form-control" placeholder="Last name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Phone Number</label>
                                <input type="text" class="form-control" placeholder="First name">
                            </div>
                            <div class="col">
                                <label>Address</label>
                                <input type="text" class="form-control" placeholder="Last name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>City</label>
                                <input type="text" class="form-control" placeholder="First name">
                            </div>
                            <div class="col">
                                <label>State</label>
                                <input type="text" class="form-control" placeholder="Last name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Postal Code</label>
                                <input type="text" class="form-control" placeholder="First name">
                            </div>
                            <div class="col">
                                <label>Country</label>
                                <input type="text" class="form-control" placeholder="Last name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Email Address</label> 
                                <input type="email" class="form-control">   
                                <label>Password</label> 
                                <input type="password" class="form-control woocommerce-Input woocommerce-Input--text input--text">   
                            </div>
                        </div>
                        <div class="woocommerce-form-row form-row">
                            <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit">Register Now</button>
                        </div>

                </div>
            </div>
        </div>
    </form>
        <?php 
    }

}

// Initialize the plugin class
new Custom_Rewrite_Rule();