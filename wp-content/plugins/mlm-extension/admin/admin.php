<?php 
class MLMExtensionAdminMenu{
	
    public function mlm_admin_control_menu() {
    
    

        add_submenu_page(
            'woocommerce',                      // Parent slug (WooCommerce menu)
            'MLM Product Settings',              // Page title
            'MLM Product Settings',              // Menu title
            'manage_options',                    // Capability
            'mlm-product-settings',              // Menu slug
            array($this, 'mlm_product'),          // Callback function
            plugins_url('/dsbooking/resources/booking-icon.png') // Icon (this won't display here but retained if needed elsewhere)
        );
    }

    public function mlm_product() {
        // Handle form submission
        if (isset($_POST['mlm_product_save'])) {
            // Verify nonce for security
            if (isset($_POST['_mlm_product_nonce']) && wp_verify_nonce($_POST['_mlm_product_nonce'], 'mlm_product_save_action')) {
                $client_product = sanitize_text_field($_POST['client_product']);
                $dealer_product = sanitize_text_field($_POST['dealer_product']);
    
                update_option('mlm_client_product', $client_product);
                update_option('mlm_dealer_product', $dealer_product);
    
                echo '<div class="updated"><p>Settings saved successfully.</p></div>';
            } else {
                echo '<div class="error"><p>Security check failed. Please try again.</p></div>';
            }
        }
    
        // Retrieve saved values
        $client_product = get_option('mlm_client_product', '');
        $dealer_product = get_option('mlm_dealer_product', '');
    
        // Get WooCommerce products
        $products = wc_get_products([
            'status' => 'publish',
            'limit'  => -1, // Get all products
            'orderby' => 'title',
            'order'   => 'ASC',
        ]);
        ?>
    
        <div class="wrap">
            <h1>MLM Product Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('mlm_product_save_action', '_mlm_product_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="client_product">Clients Product</label></th>
                        <td>
                            <select name="client_product" id="client_product">
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected($client_product, $product->get_id()); ?>>
                                        <?php echo esc_html($product->get_name()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dealer_product">Dealers Product</label></th>
                        <td>
                            <select name="dealer_product" id="dealer_product">
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected($dealer_product, $product->get_id()); ?>>
                                        <?php echo esc_html($product->get_name()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings', 'primary', 'mlm_product_save'); ?>
            </form>
        </div>
    
        <?php
    }
    

}   