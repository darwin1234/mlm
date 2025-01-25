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
    <?php 
      // woocommerce_get_template('myaccount/form-register.php'); // Show only registration form
    ?>
</div>
<?php
get_footer();