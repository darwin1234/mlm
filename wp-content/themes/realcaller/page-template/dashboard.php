<?php 
/*
    Template Name: Dashboard
*/
get_header('dashboard');
?>



<div id="post-<?php the_ID(); ?>" <?php post_class( 'content' ); ?>>
	<?php the_content();?>
</div>


<?php 
get_footer('login');
?>