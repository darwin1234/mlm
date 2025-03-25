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
        add_action('init', array($this,'process_order_form'));
     
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

    // Process order form
    public function process_order_form() {
        if (isset($_POST['submit_order'])) {
            // Verify nonce for security
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'order_form_nonce')) {
                wp_die('Security check failed');
            }
            
            // Sanitize data
            $customer_name = sanitize_text_field($_POST['customer_name']);
            $customer_email = sanitize_email($_POST['customer_email']);
            $products = array_map('sanitize_text_field', $_POST['product']);
            $quantities = array_map('intval', $_POST['quantity']);
            
            // Calculate total
            $total = 0;
            $order_items = [];
            for ($i = 0; $i < count($products); $i++) {
                $price = ($products[$i] === 'product1') ? 10 : 20;
                $subtotal = $price * $quantities[$i];
                $total += $subtotal;
                
                $order_items[] = [
                    'product' => $products[$i],
                    'quantity' => $quantities[$i],
                    'price' => $price,
                    'subtotal' => $subtotal
                ];
            }
            
            // Save order to database (simplified)
            $order_id = wp_insert_post([
                'post_title' => 'Order for ' . $customer_name,
                'post_type' => 'shop_order',
                'post_status' => 'publish',
                'meta_input' => [
                    '_customer_name' => $customer_name,
                    '_customer_email' => $customer_email,
                    '_order_total' => $total,
                    '_order_items' => serialize($order_items)
                ]
            ]);
            
            // Generate invoice (would require PDF library)
            // $invoice = generate_invoice($order_id);
            
            // Send email to customer
            $to = $customer_email;
            $subject = 'Your Order Invoice #' . $order_id;
            $message = "Dear $customer_name,\n\nThank you for your order!\n\n";
            $message .= "Order #: $order_id\n";
            $message .= "Total: $" . number_format($total, 2) . "\n\n";
            $message .= "Items:\n";
            
            foreach ($order_items as $item) {
                $message .= "- {$item['product']} x {$item['quantity']}: $" . number_format($item['subtotal'], 2) . "\n";
            }
            
            wp_mail($to, $subject, $message);
            
            // Show success message
            add_action('wp_footer', function() {
                echo '<div class="order-success">Thank you! Your order has been received. We have sent the invoice to your email.</div>';
            });
        }
    }

}

// Initialize the plugin class
new RealCallerAiExtension;