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
require_once(DSPATH . 'admin/admin.php');   

class RealCallerAiExtension {

    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );
        add_action('init', array( $this, 'mlm_rewrite_rule' ) );
        add_filter('query_vars', array( $this, 'dealers_link_var' ) );
        add_filter('query_vars', array( $this, 'client_link_var' ) );
        add_action('template_include', array( $this, 'registration_form' ) );
        add_action('init', array(new mlmregistration, 'ProcessRegistration'));
        add_action('init', array(new mlmregistration ,'register_dealer_no_tree'));
        add_action( 'admin_menu', array(new MLMExtensionAdminMenu,'mlm_admin_control_menu'), 60);
        add_action('woocommerce_order_status_completed', array(new mlmregistration, 'processGHLAccount'));
        add_action( 'add_meta_boxes', array(new MLMExtensionAdminMenu,'stripe_product_ids'));
        add_action( 'save_post',  array(new MLMExtensionAdminMenu,'save_product_id'), 10, 3 );
        add_shortcode('ds_invoice_form' , array($this, 'ds_invoice_form'));
         // Register AJAX handler
        add_action('wp_ajax_process_order_form', array($this,'process_order_form_callback'));
        add_action('wp_ajax_nopriv_process_order_form', array($this,'process_order_form_callback'));
     
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

    public function ds_invoice_form(){
          // Load custom page template for dealers
          $new_template = plugin_dir_path( __FILE__ ) . 'templates/invoice.php';
          if ( file_exists( $new_template ) ) {
              return $new_template;
          }
    }

    public function process_order_form_callback() {
        // Verify nonce
        if (!isset($_POST['order_form_nonce']) || !wp_verify_nonce($_POST['order_form_nonce'], 'order_form_action')) {
            wp_send_json_error('Security check failed');
        }
    
        // Sanitize data
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $products = isset($_POST['product']) ? array_map('sanitize_text_field', $_POST['product']) : [];
        $quantities = isset($_POST['quantity']) ? array_map('intval', $_POST['quantity']) : [];
    
        // Validate required fields
        if (empty($customer_name) || empty($customer_email) || empty($products)) {
            wp_send_json_error('Please fill all required fields');
        }
    
        // Create WooCommerce order
        $order = wc_create_order();
        
        // Add products to order
        foreach ($products as $index => $product_id) {
            $quantity = $quantities[$index] ?? 1;
            $product = wc_get_product($product_id);
            
            if ($product) {
                $order->add_product($product, $quantity);
            }
        }
    
        // Set customer details
        $order->set_customer_first_name($customer_name);
        $order->set_billing_email($customer_email);
        $order->set_billing_first_name($customer_name);
        $order->calculate_totals();
        $order->save();
    
        // Generate checkout URL
        $checkout_url = wc_get_checkout_url();
        
        // Empty current cart and add products
        WC()->cart->empty_cart();
        foreach ($products as $index => $product_id) {
            $quantity = $quantities[$index] ?? 1;
            WC()->cart->add_to_cart($product_id, $quantity);
        }
    
        // Send email notification
        $email_sent = $this->send_order_email($customer_name, $customer_email, $order->get_id(), $checkout_url);
        
        if ($email_sent) {
            wp_send_json_success([
                'message' => 'Order processed successfully',
                'redirect_url' => $checkout_url
            ]);
        } else {
            wp_send_json_error('Order created but email failed to send');
        }
    }
    
    public function send_order_email($customer_name, $customer_email, $order_id, $checkout_url) {
        // Get order object
        $order = wc_get_order($order_id);
        
        // Build email content
        $message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $message .= '<h2 style="color: #333;">Order #' . $order_id . '</h2>';
        $message .= '<p><strong>Customer:</strong> ' . $customer_name . '</p>';
        $message .= '<p><strong>Email:</strong> ' . $customer_email . '</p>';
        
        $message .= '<h3 style="color: #333; margin-top: 20px;">Order Items</h3>';
        $message .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $message .= '<tr style="background-color: #f5f5f5;">';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product</th>';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Quantity</th>';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Price</th>';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Subtotal</th>';
        $message .= '</tr>';
        
        foreach ($order->get_items() as $item) {
            $message .= '<tr>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . $item->get_name() . '</td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . $item->get_quantity() . '</td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . wc_price($item->get_subtotal() / $item->get_quantity()) . '</td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . wc_price($item->get_subtotal()) . '</td>';
            $message .= '</tr>';
        }
        
        $message .= '</table>';
        $message .= '<h3 style="color: #333;">Total: ' . wc_price($order->get_total()) . '</h3>';
        
        // Add checkout button
        $message .= '<div style="margin: 30px 0; text-align: center;">';
        $message .= '<a href="' . esc_url($checkout_url) . '" style="';
        $message .= 'display: inline-block; padding: 12px 24px; background-color: #0073aa; ';
        $message .= 'color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">';
        $message .= 'Proceed to Checkout</a>';
        $message .= '</div>';
        
        $message .= '<p>If you have any questions, please reply to this email.</p>';
        $message .= '</div>';
    
        // Set HTML content type
        add_filter('wp_mail_content_type', function() { return 'text/html'; });
        
        // Send to admin
        $admin_email = get_option('admin_email');
        $admin_subject = 'New Order #' . $order_id . ': ' . $customer_name;
        $admin_sent = wp_mail($admin_email, $admin_subject, $message);
        
        // Send to customer
        $customer_subject = 'Your Order #' . $order_id . ' Confirmation';
        $headers = ['From: ' . get_bloginfo('name') . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>'];
        $customer_sent = wp_mail($customer_email, $customer_subject, $message, $headers);
        
        // Reset content type
        remove_filter('wp_mail_content_type', 'set_html_content_type');
        
        return $admin_sent && $customer_sent;
    }
}

// Initialize the plugin class
new RealCallerAiExtension;