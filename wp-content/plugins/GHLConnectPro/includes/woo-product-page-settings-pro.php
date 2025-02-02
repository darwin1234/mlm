<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Register the Tab inside woocommerce.
if ( ! function_exists( 'ghlconnectpro_product_data_tab' ) ) {
    
    function ghlconnectpro_product_data_tab( $tabs ) {
        $tabs['ghlconnectpro-tab'] = array(
            'label'     => __( 'GHL Connect Pro', 'ghl-connect-pro' ),
            'target'    => 'ghlconnectpro-tab',
            'class'     => array(),
        );
        return $tabs;
    }
    add_filter( 'woocommerce_product_data_tabs', 'ghlconnectpro_product_data_tab' );
}


if ( ! function_exists( 'ghlconnectpro_single_product_settings_fields' ) ) {
    
    function ghlconnectpro_single_product_settings_fields() {
        
        global $post;
        $post_id = $post->ID;
        $reload_url = esc_url( admin_url( sanitize_text_field( basename( $_SERVER['REQUEST_URI']))));

        if( ! strpos( $reload_url, 'ghl_reload=1' ) ) {
            $reload_url .= '&ghl_reload=1';
        }
        ?>

<div id='ghlconnectpro-tab' class='panel woocommerce_options_panel'>
    <div class='options_group'>
        <div class="ghlconnectpro-tab-field">
            <label>Add tags on GHL account after successful purchase</label>
            <select name="ghlconnectpro_location_tags[]" id="ghlconnectpro-tag-box" multiple="multiple">
                <?php
                        echo ghlconnectpro_get_location_tag_options( $post_id);
                    ?>
            </select>
        </div>
        <div class="ghlconnectpro-tab-field">
            <label>Add workflow on GHL account after successful purchase</label>

            <select name="ghlconnectpro_location_workflow[]" id="ghlconnectpro-wokflow-box" multiple="multiple">
                <?php
                        
                        echo ghlconnectpro_get_location_workflow_options($post_id);
                        ?>
            </select>
        </div>

        <div>
            <a class="ghl_connect_reload button" href="<?php echo esc_url($reload_url); ?>">Reload Data</a>
            <p class="description">Before select the above field click the "Reload Data".</p>
        </div>

    </div>
</div><?php
    }
    add_action('woocommerce_product_data_panels', 'ghlconnectpro_single_product_settings_fields');
}

// Save data 
if (!function_exists('ghlconnectpro_woocom_save_data')) {

    function ghlconnectpro_woocom_save_data($post_id) {

        // Check if the current user has permission to save data
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check nonce
        if (isset($_POST['ghlconnectpro_nonce']) && wp_verify_nonce(sanitize_text_field( wp_unslash ($_POST['ghlconnectpro_nonce']) ) , 'ghlconnectpro_nonce_action')) {

            $ghlconnectpro_location_tags = isset($_POST['ghlconnectpro_location_tags']) ? ghlconnectpro_recursive_sanitize_array($_POST['ghlconnectpro_location_tags']) : array();
            $ghlconnectpro_location_workflow = isset($_POST['ghlconnectpro_location_workflow']) ? ghlconnectpro_recursive_sanitize_array($_POST['ghlconnectpro_location_workflow']) : array();

            // Additional checks or processing as needed

            // Update post meta
            update_post_meta($post_id, 'ghlconnectpro_location_tags', $ghlconnectpro_location_tags);
            update_post_meta($post_id, 'ghlconnectpro_location_workflow', $ghlconnectpro_location_workflow);

            //for Grouped/bundle products.
            $product_id = $post_id; // Replace with your product ID
            // Get the product object
            $product = wc_get_product($product_id);
            // Check if the product is a grouped product
            if ($product && $product->is_type('grouped')) {
                // Get the children IDs of the grouped product
                $children_ids = $product->get_children();
                foreach ($children_ids as $child_id) {
                    update_post_meta($child_id, 'ghlconnectpro_location_tags', $ghlconnectpro_location_tags);
                    update_post_meta($child_id, 'ghlconnectpro_location_workflow', $ghlconnectpro_location_workflow);
                }
             
            }       
        }
    }

    // Add actions for saving data
    add_action('woocommerce_process_product_meta_simple', 'ghlconnectpro_woocom_save_data');
    add_action('woocommerce_process_product_meta_variable', 'ghlconnectpro_woocom_save_data');
    add_action( 'woocommerce_process_product_meta_subscription', 'ghlconnectpro_woocom_save_data' );
   
    add_action('woocommerce_process_product_meta_grouped', 'ghlconnectpro_woocom_save_data');
    add_action('woocommerce_process_product_meta_external', 'ghlconnectpro_woocom_save_data');
    add_action('woocommerce_process_product_meta_variable-subscription', 'ghlconnectpro_woocom_save_data'); // For variable subscription products
    //add for BodyGraph Chart Report.
    add_action('woocommerce_process_product_meta_report', 'ghlconnectpro_woocom_save_data');
    
    // // Add nonce field to the WooCommerce product form
    add_action('woocommerce_product_options_general_product_data', 'ghlconnectpro_add_nonce_field');

    function ghlconnectpro_add_nonce_field() {
        // Output nonce field
        wp_nonce_field('ghlconnectpro_nonce_action', 'ghlconnectpro_nonce');
    }
}




// for tags
if (!function_exists('ghlconnectpro_get_location_tag_options')) {
    function ghlconnectpro_get_location_tag_options($post_id)
    {
        $tags = ghlconnectpro_get_location_tags();
        $options    = "";
        $ghlconnectpro_location_tags = get_post_meta( $post_id, 'ghlconnectpro_location_tags', true );

        $ghlconnectpro_location_tags = ( !empty($ghlconnectpro_location_tags) ) ? $ghlconnectpro_location_tags :  [];

        foreach ($tags as $tag ) {
            $tag_name = $tag->name;
            $selected = "";

            if ( in_array( $tag_name, $ghlconnectpro_location_tags )) {
                $selected = "selected";
            }

            $options .= "<option value='{$tag_name}' {$selected}>";
            $options .= $tag_name;
            $options .= "</option>";
        }

        return $options;
        
    }
}

//  for workflows
if (!function_exists('ghlconnectpro_get_location_workflow_options')) {
    function ghlconnectpro_get_location_workflow_options($post_id)
    {
        $workflows = ghlconnectpro_get_location_workflows();
        $options    = "";
        $ghlconnectpro_location_workflow = get_post_meta( $post_id, 'ghlconnectpro_location_workflow', true );

        $ghlconnectpro_location_workflow = ( !empty($ghlconnectpro_location_workflow) ) ? $ghlconnectpro_location_workflow :  [];

        foreach ($workflows as $workflow ) {
            $workflow_id        = $workflow->id;
            $workflow_name      = $workflow->name;
            $workflow_status    = $workflow->status;
            $selected           = "";
            $disabled           = "";

            if ( in_array( $workflow_id, $ghlconnectpro_location_workflow )) {
                $selected = "selected";
            }

            if ( 'draft' == $workflow_status ) {
                $disabled = "disabled";
            }

            $options .= "<option value='{$workflow_id}' {$selected} {$disabled}>";
            $options .= $workflow_name;
            $options .= "</option>";
        }

        return $options;

    }
}


// Sanitize Array
function ghlconnectpro_recursive_sanitize_array( $array ) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = recursive_sanitize_text_field( $value );
        }
        else {
            $value = sanitize_text_field( $value );
        }
    }

    return $array;
}

// reload Data so that if it exist that will be deleted.
if ( isset( $_GET['ghl_reload'] ) && absint($_GET['ghl_reload']) == 1 ) {
    $key_tags       = 'ghlconnectpro_location_tags';
    $key_workflow   = 'ghlconnectpro_location_workflow';
    //delete the previous data if any.
    delete_transient($key_tags);
    delete_transient($key_workflow);
}