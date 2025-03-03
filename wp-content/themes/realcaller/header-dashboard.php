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
    <link href="<?php echo get_template_directory_uri(); ?>/assets/css/style.css?v=<?php echo strtotime("now"); ?><?php echo rand(0,199999);?>" rel="stylesheet">

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
    <style>
	    .woocommerce-MyAccount-navigation-link--customer-user{
	        width:200px;
	    }
	    .woocommerce-MyAccount-navigation-link--customer-email{
	         width:200px;
	    } 
      .dsfooter .fluid-container {
        margin-top:20px;
      }
      .dsfooter .fluid-container p.text-right{
        text-align:right;
      }
      .dslogout{
        position:fixed;
        left:30px;
        bottom:10px;
        font-size:12px;
        width:100%;
      }
      .dslogout p{
        line-height:20px;
        padding:0;
        margin:0;
        font-weight:normal;
      }
      .dslogout .box {
        position:relative;
        width:240px;
        padding:15px;
        padding-top:10px;
      }
      .dslogout  .box span.woocommerce-MyAccount-navigation-link--MarketingCRMLink{
        width: 25px;
        height: 25px;
        padding: 0;
        margin: 0;
        margin-top: -67px;
        margin-right: -14px;
        float: right;
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
