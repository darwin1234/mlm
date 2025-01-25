<?php
/**
 * Plugin Name: MLM Affiliate Link
 * Description: A custom user registration form plugin.
 * Version: 1.0
 * Author: Darwin Sese
 * License: GPL2
 */

// Ensure WordPress has loaded before running the plugin code
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Hook to add the registration form shortcode
add_shortcode( 'custom_registration_form', 'custom_registration_form_shortcode' );

// Function to display the registration form
function custom_registration_form_shortcode() {
    // Check if the form is submitted
    if ( isset( $_POST['submit_registration'] ) ) {
        custom_handle_registration();
    }

    // Output the registration form
    ob_start();
    ?>
    <div class="woocommerce-account woocommerce">
        <div class="woocommerce-MyAccount-content bmlm-account-content">
            <div class="row bmlm-front-action-container mb-3">
                <!-- Fullscreen Button -->
                <div class="col-md-4 mb-2 mb-md-0">
                    <button class="bmlm-btn-action btn btn-primary btn-block" onclick="params.funcs.toggleFullScreen()">
                        <?php esc_html_e( 'Fullscreen', 'binary-mlm' ); ?>
                        <span class='icon'>
                            <i class="dashicons dashicons-editor-expand" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>

                <!-- Expand All Button -->
                <div class="col-md-4 mb-2 mb-md-0">
                    <button class="bmlm-btn-action btn btn-primary btn-block" onclick="params.funcs.expandAll()">
                        <?php esc_html_e( 'Expand All', 'binary-mlm' ); ?>
                        <span class='icon'>
                            <i class="dashicons dashicons-remove" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>

                <!-- Collapse All Button -->
                <div class="col-md-4">
                    <button class="bmlm-btn-action btn btn-primary btn-block" onclick="params.funcs.collapsAll()">
                        <?php esc_html_e( 'Collapse All', 'binary-mlm' ); ?>
                        <span class='icon'>
                            <i class="dashicons dashicons-insert" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </div>

            <div class="wrap bmlm-wrapper">
                <div id="bmlm-full-container">
                    <div class="bmlm-gtree" id="bmlmTree"></div>
                </div>
            </div>

            <!-- User Search Box -->
            <div class="bmlm-user-search-box">
                <div class="input-box">
                    <div class="bmlm-close-button-wrapper">
                        <i onclick="params.funcs.closeSearchBox()" class="dashicons dashicons-dismiss" aria-hidden="true"></i>
                    </div>
                    <div class="input-wrapper">
                        <input type="text" class="form-control search-input" placeholder="<?php esc_attr_e( 'Search', 'binary-mlm' ); ?>" />
                        <div class="input-bottom-placeholder"><?php esc_html_e( 'By Name, Email', 'binary-mlm' ); ?></div>
                    </div>
                </div>

                <!-- Result Box -->
                <div class="result-box">
                    <div class="result-header"><?php esc_html_e( 'RESULTS ', 'binary-mlm' ); ?></div>
                    <div class="result-list">
                        <div class="buffer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

// Function to handle form submission
function custom_handle_registration() {
    if ( isset( $_POST['username'] ) && isset( $_POST['email'] ) && isset( $_POST['password'] ) && isset( $_POST['confirm_password'] ) ) {
        
        $username = sanitize_text_field( $_POST['username'] );
        $email = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate the password confirmation
        if ( $password !== $confirm_password ) {
            echo '<p style="color: red;">Passwords do not match!</p>';
            return;
        }

        // Validate email
        if ( ! is_email( $email ) ) {
            echo '<p style="color: red;">Please enter a valid email address.</p>';
            return;
        }

        // Check if the username or email already exists
        if ( username_exists( $username ) ) {
            echo '<p style="color: red;">Username already exists. Please choose a different one.</p>';
            return;
        }

        if ( email_exists( $email ) ) {
            echo '<p style="color: red;">Email already registered. Please choose a different one.</p>';
            return;
        }

        // Create the user
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            echo '<p style="color: red;">There was an error creating the account. Please try again.</p>';
        } else {
            // Automatically log in the new user
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );
            echo '<p style="color: green;">Registration successful! You are now logged in.</p>';
        }
    }
}
?>
