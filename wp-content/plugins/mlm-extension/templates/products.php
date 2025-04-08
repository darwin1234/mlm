<?php 

 // Define the WooCommerce product category
 $category_slug = 'realcaller'; // Replace with your desired category slug
    
 // Set up the WP_Query to fetch products in that category
 $args = array(
     'post_type' => 'product', // We are querying products
     'posts_per_page' => 12,   // Limit the number of products
     'tax_query' => array(
         array(
             'taxonomy' => 'product_cat',  // We are filtering by WooCommerce product categories
             'field'    => 'slug',         // Using the category slug
             'terms'    => $category_slug, // The category slug to filter by
             'operator' => 'IN',           // Filter by this category
         ),
     ),
     'orderby' => 'date', // Order products by date
     'order'   => 'DESC', // Descending order
 );

 // The Query
 $loop = new WP_Query($args);

 // Check if any products were found
 if ($loop->have_posts()) :
 ?>
     <div class="container">
         <h2 class="text-center mb-4">Featured Products in RealCaller</h2>
         <div class="row">
             <?php while ($loop->have_posts()) : $loop->the_post(); 
                 global $product; 
                 $user_id = get_current_user_id();
                 $p_url = site_url('/client/clients-form/?sponsor=' . $user_id . '&product='. $product->id); 
             ?>
             <div class="col-md-3 col-sm-6 mb-4">
                 <div class="card shadow-sm h-100">
                     <a href="<?php the_permalink(); ?>" class="d-block">
                         <img src="<?php echo wp_get_attachment_url($product->get_image_id()); ?>" class="card-img-top" alt="<?php the_title(); ?>" style="object-fit:cover; width:120px; display:block; margin:auto; border-bottom: 1px solid #ddd;">
                     </a>
                     <div class="card-body d-flex flex-column">
                         <h5 class="card-title" style="font-size:14px; font-weight: 600;"><?php the_title(); ?></h5>
                         <p class="card-text" style="font-size:14px; color:#777;"><?php echo wp_trim_words($product->get_short_description(), 15); ?></p>
                         <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
                         
                         <!-- Sponsor ID Form -->
                         <div class="mt-auto">
                             <label for="sponsor-id" class="small text-muted hidden"><?php esc_html_e( 'Sponsor ID', 'binary-mlm' ); ?></label>
                             <input type="hidden" id="sponsor-id-<?php the_ID(); ?>" value="<?php echo esc_url( $p_url ); ?>" class="bmlm-input form-control" readonly>
                             <button class="btn btn-primary w-100 copy-btn" style="width:100%; font-size:14px;" type="button" data-target="#sponsor-id-<?php the_ID(); ?>">
                                     <span class="bmlm-tooltiptext"><?php esc_html_e( 'Copy to clipboard', 'binary-mlm' ); ?></span>
                             </button>
                             
                         </div>
                     </div>
                 </div>
             </div>
             <?php endwhile; ?>
         </div>
     </div>

     <script>
         // Pure JavaScript clipboard copy functionality
         document.addEventListener('DOMContentLoaded', function() {
             const copyButtons = document.querySelectorAll('.copy-btn');
             
             copyButtons.forEach(button => {
                 button.addEventListener('click', function() {
                     const targetId = this.getAttribute('data-target');
                     const targetElement = document.querySelector(targetId);
                     
                     if (targetElement) {
                         // Create a temporary input element
                         const tempInput = document.createElement('input');
                         tempInput.value = targetElement.value;
                         document.body.appendChild(tempInput);
                         
                         // Select and copy the text
                         tempInput.select();
                         tempInput.setSelectionRange(0, 99999); // For mobile devices
                         
                         try {
                             const successful = document.execCommand('copy');
                             if (successful) {
                                 alert('Affiliate link copied to clipboard!');
                             } else {
                                 throw new Error('Copy command failed');
                             }
                         } catch (err) {
                             alert('Failed to copy the affiliate link. Please try again.');
                         }
                         
                         // Remove the temporary input
                         document.body.removeChild(tempInput);
                     }
                 });
             });
         });
     </script>

 <?php
 else :
     echo '<p class="text-center">No products found in this category.</p>';
 endif;

 // Reset Post Data
 wp_reset_postdata();