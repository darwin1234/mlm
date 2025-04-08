<?php 
/**
 * Invoice Form Template - Product Selection + Order Form (Two-Step Process)
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} 
?>
<div class="ds-invoice-wrapper">
    <?php 
    // Define the WooCommerce product category
    $category_slug = 'realcaller'; // Replace with your desired category slug
        
    // Set up the WP_Query to fetch products in that category
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
                'operator' => 'IN',
            ),
        ),
        'orderby' => 'date',
        'order'   => 'DESC',
    );

    // The Query
    $loop = new WP_Query($args);
    ?>

    <!-- Step 1: Product Selection -->
    <div class="product-selection-step" id="step1">
        <div class="container">
            <h2 class="text-center mb-4">Select Your Product</h2>
            <div class="row">
                <?php if ($loop->have_posts()) : 
                    while ($loop->have_posts()) : $loop->the_post(); 
                        global $product; 
                        $product_id = $product->get_id();
                ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card product-card shadow-sm h-100" data-product-id="<?php echo $product_id; ?>">
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <?php echo $product->get_image('small', ['class' => 'img-fluid', 'style' => 'max-height: 150px; width:100px; margin:auto; display:block;']); ?>
                            </div>
                            <h5 class="card-title" style="font-size:12px;"><?php the_title(); ?></h5>
                            <p class="card-text text-muted small"><?php echo wp_trim_words($product->get_short_description(), 15); ?></p>
                            <div class="mt-auto">
                                <p class="h5 text-primary mb-3" style="font-size:14px;"><?php echo $product->get_price_html(); ?></p>
                                <button type="button" class="btn btn-primary w-100 select-product-btn" style="font-size:14px;">
                                    Select Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">No products found in this category.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Step 2: Order Form (Initially Hidden) -->
    <div class="order-form-step" id="step2" style="display: none;">
        <form id="order-form" method="post" class="container mt-4">
            <?php wp_nonce_field('order_form_action', 'order_form_nonce'); ?>
            <input type="hidden" name="action" value="process_order_form">
            <input type="hidden" id="selected_product_id" name="product" value="">
            <input type="hidden" name="quantity" value="1">
            
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="card-body">
                    <!-- Product Summary -->
                    <div class="product-summary bg-light p-4 rounded-3 mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="text-primary mb-2" id="selected-product-name">
                                    <i class="fas fa-box me-2"></i> 
                                </h4>
                                <p class="text-muted mb-0" id="selected-product-description"></p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-inline-block">
                                    <button type="button" class="btn btn-primary btn-sm btn-outline-secondary back-to-products text-white">
                                        <i class="fas fa-arrow-left me-1"></i> Change Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress mb-4" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="section-card mb-5">
                        <h4 class="section-title">
                            <span class="icon-circle bg-primary text-white me-2">
                                <i class="fas fa-user"></i>
                            </span>
                            Customer Information
                        </h4>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="customer_first_name" name="customer_first_name" required placeholder="John">
                                    <label for="customer_first_name">First Name*</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="customer_last_name" name="customer_last_name" required placeholder="Doe">
                                    <label for="customer_last_name">Last Name*</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="customer_email" name="customer_email" required placeholder="john@example.com">
                                    <label for="customer_email">Email*</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="customer_business" name="customer_business" required placeholder="Your Company Inc.">
                                    <label for="customer_business">Business Name*</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <div class="section-card mb-5">
                        <h4 class="section-title">
                            <span class="icon-circle bg-primary text-white me-2">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            Address Information
                        </h4>
                        <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="customer_address" name="customer_address" required placeholder="123 Main St">
                                <label for="customer_address">Street Address*</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="customer_address2" name="customer_address2" placeholder="Apt 4B">
                                <label for="customer_address2">Apt/Suite (optional)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="customer_city" name="customer_city" required placeholder="New York">
                                <label for="customer_city">City*</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="customer_zip" name="customer_zip" required placeholder="10001">
                                <label for="customer_zip">ZIP/Postal Code*</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="customer_country" name="customer_country" required>
                                    <option value="">Select Country</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="AU">Australia</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="JP">Japan</option>
                                </select>
                                <label for="customer_country">Country*</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="customer_state" name="customer_state" required>
                                <option value="">Select State</option>
                                <!-- US States -->
                                <option class="state-option" value="AL" data-country="US">Alabama</option>
                                <option class="state-option" value="AK" data-country="US">Alaska</option>
                                <option class="state-option" value="AZ" data-country="US">Arizona</option>
                                <option class="state-option" value="AR" data-country="US">Arkansas</option>
                                <option class="state-option" value="CA" data-country="US">California</option>
                                <option class="state-option" value="CO" data-country="US">Colorado</option>
                                <option class="state-option" value="CT" data-country="US">Connecticut</option>
                                <option class="state-option" value="DE" data-country="US">Delaware</option>
                                <option class="state-option" value="FL" data-country="US">Florida</option>
                                <option class="state-option" value="GA" data-country="US">Georgia</option>
                                <option class="state-option" value="HI" data-country="US">Hawaii</option>
                                <option class="state-option" value="ID" data-country="US">Idaho</option>
                                <option class="state-option" value="IL" data-country="US">Illinois</option>
                                <option class="state-option" value="IN" data-country="US">Indiana</option>
                                <option class="state-option" value="IA" data-country="US">Iowa</option>
                                <option class="state-option" value="KS" data-country="US">Kansas</option>
                                <option class="state-option" value="KY" data-country="US">Kentucky</option>
                                <option class="state-option" value="LA" data-country="US">Louisiana</option>
                                <option class="state-option" value="ME" data-country="US">Maine</option>
                                <option class="state-option" value="MD" data-country="US">Maryland</option>
                                <option class="state-option" value="MA" data-country="US">Massachusetts</option>
                                <option class="state-option" value="MI" data-country="US">Michigan</option>
                                <option class="state-option" value="MN" data-country="US">Minnesota</option>
                                <option class="state-option" value="MS" data-country="US">Mississippi</option>
                                <option class="state-option" value="MO" data-country="US">Missouri</option>
                                <option class="state-option" value="MT" data-country="US">Montana</option>
                                <option class="state-option" value="NE" data-country="US">Nebraska</option>
                                <option class="state-option" value="NV" data-country="US">Nevada</option>
                                <option class="state-option" value="NH" data-country="US">New Hampshire</option>
                                <option class="state-option" value="NJ" data-country="US">New Jersey</option>
                                <option class="state-option" value="NM" data-country="US">New Mexico</option>
                                <option class="state-option" value="NY" data-country="US">New York</option>
                                <option class="state-option" value="NC" data-country="US">North Carolina</option>
                                <option class="state-option" value="ND" data-country="US">North Dakota</option>
                                <option class="state-option" value="OH" data-country="US">Ohio</option>
                                <option class="state-option" value="OK" data-country="US">Oklahoma</option>
                                <option class="state-option" value="OR" data-country="US">Oregon</option>
                                <option class="state-option" value="PA" data-country="US">Pennsylvania</option>
                                <option class="state-option" value="RI" data-country="US">Rhode Island</option>
                                <option class="state-option" value="SC" data-country="US">South Carolina</option>
                                <option class="state-option" value="SD" data-country="US">South Dakota</option>
                                <option class="state-option" value="TN" data-country="US">Tennessee</option>
                                <option class="state-option" value="TX" data-country="US">Texas</option>
                                <option class="state-option" value="UT" data-country="US">Utah</option>
                                <option class="state-option" value="VT" data-country="US">Vermont</option>
                                <option class="state-option" value="VA" data-country="US">Virginia</option>
                                <option class="state-option" value="WA" data-country="US">Washington</option>
                                <option class="state-option" value="WV" data-country="US">West Virginia</option>
                                <option class="state-option" value="WI" data-country="US">Wisconsin</option>
                                <option class="state-option" value="WY" data-country="US">Wyoming</option>
                                
                                <!-- Canadian Provinces -->
                                <option class="state-option" value="AB" data-country="CA">Alberta</option>
                                <option class="state-option" value="BC" data-country="CA">British Columbia</option>
                                <option class="state-option" value="MB" data-country="CA">Manitoba</option>
                                <option class="state-option" value="NB" data-country="CA">New Brunswick</option>
                                <option class="state-option" value="NL" data-country="CA">Newfoundland and Labrador</option>
                                <option class="state-option" value="NT" data-country="CA">Northwest Territories</option>
                                <option class="state-option" value="NS" data-country="CA">Nova Scotia</option>
                                <option class="state-option" value="NU" data-country="CA">Nunavut</option>
                                <option class="state-option" value="ON" data-country="CA">Ontario</option>
                                <option class="state-option" value="PE" data-country="CA">Prince Edward Island</option>
                                <option class="state-option" value="QC" data-country="CA">Quebec</option>
                                <option class="state-option" value="SK" data-country="CA">Saskatchewan</option>
                                <option class="state-option" value="YT" data-country="CA">Yukon</option>
                                
                                <!-- UK Regions -->
                                <option class="state-option" value="ENG" data-country="GB">England</option>
                                <option class="state-option" value="SCT" data-country="GB">Scotland</option>
                                <option class="state-option" value="WLS" data-country="GB">Wales</option>
                                <option class="state-option" value="NIR" data-country="GB">Northern Ireland</option>
                                
                                <!-- Australian States -->
                                <option class="state-option" value="ACT" data-country="AU">Australian Capital Territory</option>
                                <option class="state-option" value="NSW" data-country="AU">New South Wales</option>
                                <option class="state-option" value="NT" data-country="AU">Northern Territory</option>
                                <option class="state-option" value="QLD" data-country="AU">Queensland</option>
                                <option class="state-option" value="SA" data-country="AU">South Australia</option>
                                <option class="state-option" value="TAS" data-country="AU">Tasmania</option>
                                <option class="state-option" value="VIC" data-country="AU">Victoria</option>
                                <option class="state-option" value="WA" data-country="AU">Western Australia</option>
                                </select>
                                <label for="customer_state">State/Province*</label>
                            </div>
                        </div>
                    </div>
                    </div>
                    
                    <!-- Payment Summary -->
                    <div class="payment-summary bg-light p-4 rounded-3 mb-4">
                        <h5 class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Subtotal:</span>
                            <span id="order-subtotal">$0.00</span>
                        </h5>
                        <div class="d-flex justify-content-between align-items-center mb-2" style="display:none!important;">
                            <span class="text-muted">Tax:</span>
                            <span id="order-tax">$0.00</span>
                        </div>
                        <hr class="my-2">
                        <h4 class="d-flex justify-content-between align-items-center mb-0">
                            <span class="fw-bold">Total:</span>
                            <span class="text-success fw-bold" id="order-total">$0.00</span>
                        </h4>
                    </div>
                </div>
                
                <div class="card-footer bg-light pt-4 pb-3">
                    <div class="d-grid">
                        <button id="submit_order" type="submit" name="submit_order" class="btn btn-primary btn-lg py-3">
                            <i class="fas fa-paper-plane me-2"></i> Place Order & Send Invoice
                        </button>
                    </div>
                    <div class="security-note text-center mt-3">
                        <p class="text-muted small mb-1">
                            <i class="fas fa-lock me-1"></i> Your information is secure and will not be shared
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.ds-invoice-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}

.product-selection-step .product-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.product-selection-step .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-color: #4d90fe;
}

.section-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
}

.icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.back-to-products {
    transition: all 0.3s;
}

.back-to-products:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .product-selection-step .col-md-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .product-selection-step .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize ajaxurl if not defined
    if (typeof ajaxurl === 'undefined') {
        ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
    }

    // Product selection handler
    $('.select-product-btn').on('click', function() {
        var productCard = $(this).closest('.product-card');
        var productId = productCard.data('product-id');
        // Get product details via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_product_details',
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log(response.data.price);
                    // Update order form with product details
                    $('#selected_product_id').val(productId);
                    $('#selected-product-name').html('<i class="fas fa-box me-2"></i>' + response.data.name);
                    $('#selected-product-description').text(response.data.description);
                    $('#selected-product-price').text(response.data.price_html);
                    
                    // Update payment summary
                    updatePaymentSummary(response.data.price);
                    
                    // Switch to step 2
                    $('#step1').hide();
                    $('#step2').show();
                    
                    // Scroll to top
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching product details:', error);
            }
        });
    });

    // Back to products button
    $('.back-to-products').on('click', function() {
        $('#step2').hide();
        $('#step1').show();
    });

    // Function to update payment summary
    function updatePaymentSummary(price) {
        var tax = 0; // You can add tax calculation here if needed
        var total = price + tax;
        
        $('#order-subtotal').text('$' + price);
        $('#order-tax').text('$' + tax);
        $('#order-total').text('$' + total);
    }

    // Country-State dynamic relationship
    $('#customer_country').change(function() {
        var country = $(this).val();
        $('#customer_state option').addClass('d-none');
        if (country) {
            $('#customer_state option[data-country="' + country + '"]').removeClass('d-none');
            $('#customer_state option[value=""]').removeClass('d-none');
        } else {
            $('#customer_state option').removeClass('d-none');
        }
        $('#customer_state').val('');
    }).trigger('change');

    // Form submission
    $('#order-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate product is selected
        if (!$('#selected_product_id').val()) {
            alert('Please select a product first');
            return false;
        }
        
        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...')
                .addClass('disabled');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Success handling
                    alert("Invoice Successfully Sent");
                    // You can redirect or reset the form here
                } else {
                    alert('Error: ' + (response.data || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            },
            complete: function() {
                submitBtn.prop('disabled', false)
                        .html(originalText)
                        .removeClass('disabled');
            }
        });
    });
});
</script>