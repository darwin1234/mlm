<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>RealCaller.ai</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/jumbotron/">

    <!-- Bootstrap core CSS -->
    <link href="<?php echo bloginfo('template_url');?>/assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo bloginfo('template_url');?>/assets/css/style.css" rel="stylesheet">
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
	<?php wp_head();?>
  </head>
  <?php 
    $slug = basename(get_permalink());
  ?>
  <body class="<?php echo $slug; ?>">
  <div class="top">
    <p class="text-center text-white">Pharetra pulvinar sed velit suscipit lobortis. Posuere vitae lorem mauris nulla massa urna eu. Porta interdum aliquam vivamus.</p>
  </div>
<main>
  <div id="mainWrapper" class=" container-fluid py-4">
    <header class="container ">
      <!-- Bootstrap Nav -->
      <nav class="navbar navbar-expand-lg navbar-light">
        <a href="/" class="d-flex align-items-center text-dark text-decoration-none">
            <img id="logo" src="<?php echo bloginfo('template_url');?>/assets/images/logo.png">
        </a>
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
        
            <?php 
              wp_nav_menu( array(
                'theme_location'  => 'main-menu',
                'depth'           => 2, // 1 = no dropdowns, 2 = with dropdowns.
                'container'       => 'div',
                'container_class' => 'collapse navbar-collapse',
                'container_id'    => 'navbarNav',
                'menu_class'      => 'navbar-nav ms-auto',
                'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
                'walker'          => new WP_Bootstrap_Navwalker(),
            ) );
            
            ?>
            
         
        </div>
      </nav>
    </header>
	<main id="main" class=" <?php echo is_front_page() ? 'bghome' : ''; ?>  container"<?php if ( isset( $navbar_position ) && 'fixed_top' === $navbar_position ) : echo ' style="padding-top: 100px;"'; elseif ( isset( $navbar_position ) && 'fixed_bottom' === $navbar_position ) : echo ' style="padding-bottom: 100px;"'; endif; ?>>
		<?php
			// If Single or Archive (Category, Tag, Author or a Date based page).
			if ( is_single() || is_archive() ) :
		?>
			<div class="row">
				<div class="col-md-8 col-sm-12">
		<?php
			endif;
		?>
