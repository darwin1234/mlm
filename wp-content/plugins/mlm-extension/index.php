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
        $order_data = [
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'products' => isset($_POST['product']) ? array_map('sanitize_text_field', $_POST['product']) : [],
            'quantities' => isset($_POST['quantity']) ? array_map('intval', $_POST['quantity']) : [],
            'order_date' => current_time('mysql'),
            'order_status' => 'pending'
        ];

        // Validate required fields
        if (empty($order_data['customer_name']) || empty($order_data['customer_email']) || empty($order_data['products'])) {
            wp_send_json_error('Please fill all required fields');
        }

        // Create order and get order ID
        $order_id = $this->create_order($order_data);
        
        if (!$order_id) {
            wp_send_json_error('Could not create order');
        }

        // Generate invoice PDF
        $invoice_url = $this->generate_invoice_pdf($order_id, $order_data);
        
        // Send emails with invoice
        $email_sent = $this->send_order_email_with_invoice($order_id, $order_data, $invoice_url);
        
        // Prepare response
        $response = [
            'success' => true,
            'data' => [
                'message' => 'Order processed successfully',
                'order_id' => $order_id,
                'invoice_url' => $invoice_url,
                'redirect_url' => add_query_arg('order_id', $order_id, get_permalink(get_page_by_path('order-confirmation')))
            ]
        ];
        
        wp_send_json($response);
    }

    public function create_order($order_data) {
        // Create order post
        $order_id = wp_insert_post([
            'post_title' => 'Order #' . time() . ' - ' . $order_data['customer_name'],
            'post_type' => 'shop_order',
            'post_status' => 'publish',
            'meta_input' => [
                '_customer_name' => $order_data['customer_name'],
                '_customer_email' => $order_data['customer_email'],
                '_order_date' => $order_data['order_date'],
                '_order_status' => $order_data['order_status'],
                '_order_items' => serialize($this->get_order_items($order_data))
            ]
        ]);
        
        return $order_id ?: false;
    }

    function get_order_items($order_data) {
        $items = [];
        
        foreach ($order_data['products'] as $index => $product_id) {
            $quantity = $order_data['quantities'][$index] ?? 1;
            $price = $this->get_product_price($product_id);
            
            $items[] = [
                'product_id' => $product_id,
                'product_name' => $this->get_product_name($product_id),
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $price * $quantity
            ];
        }
        
        return $items;
    }

    public function get_product_price($product_id) {
        // In a real implementation, fetch from database
        return $product_id === '76' ? 1000 : 0;
    }

    public function get_product_name($product_id) {
        // In a real implementation, fetch from database
        return $product_id === '76' ? 'RealCallerAI' : 'Unknown Product';
    }

    public function generate_invoice_pdf($order_id, $order_data) {
        // This would use a PDF library like TCPDF or Dompdf
        // For now, we'll create a simple HTML invoice page
        
        $invoice_content = build_invoice_html($order_id, $order_data);
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $order_id . '.html';
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        file_put_contents($filepath, $invoice_content);
        
        return $upload_dir['url'] . '/' . $filename;
    }

    public function build_invoice_html($order_id, $order_data) {
        ob_start(); ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice #<?php echo $order_id; ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .invoice-header { margin-bottom: 20px; }
                .invoice-details { margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; font-size: 1.2em; }
            </style>
        </head>
        <body>
            <div class="invoice-header">
                <h1>INVOICE #<?php echo $order_id; ?></h1>
                <p>Date: <?php echo date('F j, Y', strtotime($order_data['order_date'])); ?></p>
            </div>
            
            <div class="invoice-details">
                <h3>Bill To:</h3>
                <p><?php echo $order_data['customer_name']; ?><br>
                <?php echo $order_data['customer_email']; ?></p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $order_total = 0;
                    $items = maybe_unserialize(get_post_meta($order_id, '_order_items', true));
                    
                    foreach ($items as $item): 
                        $order_total += $item['subtotal'];
                    ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                <p>Total: $<?php echo number_format($order_total, 2); ?></p>
            </div>
            
            <p>Thank you for your business!</p>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public function send_order_email_with_invoice($order_id, $order_data, $invoice_url) {
        $to = $order_data['customer_email'];
        $subject = 'Your Invoice #' . $order_id;
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        $message = build_email_template($order_id, $order_data, $invoice_url);
        
        return wp_mail($to, $subject, $message, $headers);
    }

    public function build_email_template($order_id, $order_data, $invoice_url) {
        ob_start(); ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 600px; margin: 0 auto; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button { display: inline-block; padding: 10px 20px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 4px; }
                .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Thank You for Your Order!</h1>
            </div>
            
            <div class="content">
                <p>Dear <?php echo $order_data['customer_name']; ?>,</p>
                
                <p>We've received your order (#<?php echo $order_id; ?>) and it's being processed.</p>
                
                <h3>Order Summary</h3>
                
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order_data['order_date'])); ?></p>
                
                <p>You can view and download your invoice using the button below:</p>
                
                <p><a href="<?php echo $invoice_url; ?>" class="button">Download Invoice</a></p>
                
                <p>If you have any questions about your order, please reply to this email.</p>
            </div>
            
            <div class="footer">
                <p>© <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

}

// Initialize the plugin class
new RealCallerAiExtension;