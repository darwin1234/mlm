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



        function process_order_form_callback() {
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

            // Calculate total and prepare items
            $total = 0;
            $order_items = [];
            
            foreach ($products as $index => $product_id) {
                $quantity = $quantities[$index] ?? 1;
                
                // Get product price (in a real implementation, you'd fetch this from database)
                $price = 0;
                if ($product_id === '76') {
                    $price = 1000;
                }
                
                $subtotal = $price * $quantity;
                $total += $subtotal;
                
                $order_items[] = [
                    'product_id' => $product_id,
                    'product_name' => $product_id === '76' ? 'RealCallerAI' : 'Unknown Product',
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal
                ];
            }

            // Send emails
            $email_sent = $this->send_order_email($customer_name, $customer_email, $order_items, $total);
            
            if ($email_sent) {
                wp_send_json_success('Order processed successfully');
            } else {
                wp_send_json_error('Order processed but email failed to send');
            }
        }

        function send_order_email($customer_name, $customer_email, $order_items, $total) {
            // Admin email
            $admin_email = get_option('admin_email');
            $admin_subject = 'New Order: ' . $customer_name;
            
            // Customer email
            $customer_subject = 'Your Order Confirmation';
            
            // Build email content
            $message = '<h2>Order Details</h2>';
            $message .= '<p><strong>Customer:</strong> ' . $customer_name . '</p>';
            $message .= '<p><strong>Email:</strong> ' . $customer_email . '</p>';
            $message .= '<h3>Order Items</h3>';
            $message .= '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">';
            $message .= '<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>';
            
            foreach ($order_items as $item) {
                $message .= '<tr>';
                $message .= '<td>' . $item['product_name'] . '</td>';
                $message .= '<td>' . $item['quantity'] . '</td>';
                $message .= '<td>$' . number_format($item['price'], 2) . '</td>';
                $message .= '<td>$' . number_format($item['subtotal'], 2) . '</td>';
                $message .= '</tr>';
            }
            
            $message .= '</table>';
            $message .= '<h3>Total: $' . number_format($total, 2) . '</h3>';
            
            // Set HTML content type
            add_filter('wp_mail_content_type', function() { return 'text/html'; });
            
            // Send to admin
            $admin_sent = wp_mail($admin_email, $admin_subject, $message);
            
            // Send to customer
            $headers = ['From: Your Store <noreply@' . $_SERVER['HTTP_HOST'] . '>'];
            $customer_sent = wp_mail($customer_email, $customer_subject, $message, $headers);
            
            // Reset content type
            remove_filter('wp_mail_content_type', 'set_html_content_type');
            
            return $admin_sent && $customer_sent;
        }

}

// Initialize the plugin class
new RealCallerAiExtension;