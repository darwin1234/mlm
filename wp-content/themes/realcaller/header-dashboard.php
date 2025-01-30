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
      .content {
            width: 100%;
            margin: auto;
            margin-bottom: 60px;
            overflow: auto;
      }
    </style>
	<?php wp_head();?>
  </head>
  <?php 
    $slug = basename(get_permalink());
  ?>
  <script>
    jQuery(document).ready(function(){
        jQuery(".woocommerce-MyAccount-navigation-link--sponsormarketing-crm-link a").attr("href", 'https://app.marketingdpt.co/');
    });
  </script>
<body class="<?php echo $slug; ?>">
<main>
  <div id="mainWrapper" class="container-fluid py-4">
    <main id="main" class="container"<?php if ( isset( $navbar_position ) && 'fixed_top' === $navbar_position ) : echo ' style="padding-top: 100px;"'; elseif ( isset( $navbar_position ) && 'fixed_bottom' === $navbar_position ) : echo ' style="padding-bottom: 100px;"'; endif; ?>>
      <?php
        // If Single or Archive (Category, Tag, Author or a Date based page).
        if ( is_single() || is_archive() ) :
      ?>
        <div class="row">
          <div class="col-md-8 col-sm-12">
      <?php
        endif;
      ?>
