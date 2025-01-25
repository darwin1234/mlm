<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
get_header();

// Get the user ID from the query variable
$user_id = get_query_var('user_id');

// Get user data
$user = get_user_by('ID', $user_id);
?>
<div class="container mt-5">
    <?php if ($user): ?>
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4"><?php echo esc_html($user->display_name); ?>'s Profile</h1>
                <p><strong>Username:</strong> <?php echo esc_html($user->user_login); ?></p>
                <p><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></p>
                <p><strong>Role:</strong> <?php echo esc_html(implode(', ', $user->roles)); ?></p>
                <p><strong>Registered:</strong> <?php echo esc_html($user->user_registered); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-5 mb-4">Available Products</h2>
            </div>
        </div>
        <div class="row">
            <?php
            // Query to get all WooCommerce products
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => -1, // Retrieve all products
                'post_status'    => 'publish',
            ];
            $products = new WP_Query($args);

            if ($products->have_posts()):
                while ($products->have_posts()): $products->the_post();
                    global $product; ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (has_post_thumbnail()): ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', ['class' => 'card-img-top']); ?>
                                </a>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                        <?php the_title(); ?>
                                    </a>
                                </h5>
                                <p class="card-text">
                                    <?php echo wc_price($product->get_price()); // Display the price ?>
                                </p>
                            </div>
                            <div class="card-footer text-center">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Product</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
                wp_reset_postdata();
            else: ?>
                <div class="col-md-12">
                    <p class="text-muted">No products found.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-12">
                <p class="text-danger">User not found.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();