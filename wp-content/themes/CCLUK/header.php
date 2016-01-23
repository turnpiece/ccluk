<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?>> <!--<![endif]-->
<head>

	<!-- Basic Page Needs
  ================================================== -->
	<meta charset="utf-8">
	<title>
		   <?php
		      if (function_exists('is_tag') && is_tag()) {
		         single_tag_title("Tag Archive for &quot;"); echo '&quot; - '; }
                         elseif ( is_front_page()) {
		         wp_title(''); echo ' Home - '; }
                         
                        
		      elseif (is_archive()) {
		         wp_title(''); echo ' Archive - '; }
		      elseif (is_search()) {
		         echo 'Search for &quot;'.wp_specialchars($s).'&quot; - '; }
		      elseif (!(is_404()) && (is_single()) || (is_page())) {
		         wp_title(''); echo ' - '; }
		      elseif (is_404()) {
		         echo 'Not Found - '; }
		      if (is_home()) {
		         bloginfo('name'); echo ' - '; bloginfo('description'); }
		      else {
		          bloginfo('name'); }
		      if ($paged>1) {
		         echo ' - page '. $paged; }
		   ?>
	</title>
 
        <meta name="author" content="">


	<!-- Mobile Specific Metas
  ================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
          <?php if (is_search()) { ?>
	   <meta name="robots" content="noindex, nofollow" /> 
	<?php } ?>
            

	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Favicons
	================================================== -->
	<link rel="shortcut icon" href="images/favicon.ico">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
        
        <!-- Atoms & Pingback
        ================================================== -->

        <link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
        <link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
        <link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

        <!-- Theme Hook -->
        
     <?php wp_head(); ?>
     
</head>

    <body data-spy="scroll" data-target=".subnav" data-offset="50" <?php body_class(); ?>>

            <div id="topbar">
        
        <div class="container clearfix">
          
            <div class="sixteen columns">
                
                <div class="siteLogo">
                  <a href="<?php echo home_url(); ?>">
                  <?php if (ot_get_option('logo')) : ?>
                    <img alt="<?php echo esc_attr(get_bloginfo('name')) ?>" src="<?php echo ot_get_option('logo') ?>" />
                  <?php else : ?>
                    <h1><?php bloginfo('name') ?></h1>
                  <?php endif; ?>
                  </a>
                </div>
                
                 <nav class="mainNav">

                    <?php
                    wp_nav_menu(array(
                        'container' => false,
                        'menu_class' => 'nav clearfix sf-menu sf-js-enabled sf-shadow',
                        'theme_location' => 'main_menu',
                        'echo' => true,
                        'before' => '',
                        'after' => '',
                        'link_before' => '',
                        'fallback_cb' => 'display_home2',
                        'link_after' => '',
                        'depth' => 0
                        )
                    );
                    ?>
                 
                  <?php if (ot_get_option('topbarsearch') == 'yes') : ?>
                  <div class="topBarSearch clearfix">
                    <?php get_search_form(); ?>
                  </div>
                  <?php endif; ?>

                </nav>
                
            </div>
            
        </div>
        
    </div>
     
          <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);

?>

           <?php if( $headerimg) { ?>
                                    
                  <header style="background: url(<?php echo $headerimg; ?>) top center no-repeat;">
        
        <div class="container">
            
            <div class="sixteen columns">
                
            </div>
            
        </div>
        
    </header>            
        

            <?php } else if ( is_search() ) { ?>
        
        <?php  if (ot_get_option('searchbanner') != '') { ?>
        
          <header style="background: url(<?php echo ot_get_option('searchbanner') ?>) top center no-repeat;">
        
        <div class="container">
            
            <div class="sixteen columns">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>            
        
        <?php } else { ?>
        
           <header class="noBanner">
        
        <div class="container">
            
            <div class="sixteen columns">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>  
        
                <?php } ?>
        
               <?php } else if ( is_author() ) { ?>
        
        <?php  if (ot_get_option('authorbanner') != '') { ?>
        
          <header style="background: url(<?php echo ot_get_option('authorbanner') ?>) top center no-repeat;">
        
        <div class="container">
            
            <div class="sixteen columns">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>            
        
        <?php } else { ?>
        
           <header class="noBanner">
        
        <div class="container">
            
            <div class="sixteen columns">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>  
        
                <?php } ?>
        
                 <?php } else if ( is_archive() ) { ?>
        
        <?php  if (ot_get_option('archivebanner') != '') { ?>
        
          <header style="background: url(<?php echo ot_get_option('archivebanner') ?>) top center no-repeat;">
        
        <div class="container">
            
            <div class="sixteen columns datearchive">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>            
        
        <?php } else { ?>
        
           <header class="noBanner">
        
        <div class="container">
            
            <div class="sixteen columns">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>  
        
                <?php } ?>
        

        
            <?php } else { ?>
        
          <header class="noBanner">
        
        <div class="container">
            
            <div class="sixteen columns">
                
               <a href="<?php echo home_url(); ?>"><img alt="" src="<?php echo ot_get_option('logo') ?>" /></a>
                
            </div>
            
        </div>
        
    </header>  
   
            <?php } ?>