<?php 
/*
    Template Name: Checkout Page
*/
?>
<?php  get_header('dashboard');?>

<div id="post-<?php the_ID(); ?>" <?php post_class( 'content' ); ?>>
    <div class="container">
        <div class="row">
            <div class="col-md-10 m-auto">
                 <?php the_content();?>
            </div>
        </div>
    </div>
</div>

<?php  get_footer('login');?>