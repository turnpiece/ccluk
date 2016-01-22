<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php 	
        		extract(cosmica_get_theme_var());
        		$site_url       	=   esc_url(get_site_url());
        		$blog_title 		=   esc_html(get_bloginfo('name'));
        		$blog_description 	=   esc_html(get_bloginfo('description'));
        		$logo_url			=	esc_url(COSMICA_URI.'/images/logo.png');
        ?>	
	<?php wp_head(); ?>		
	</head>
<body <?php body_class(); ?>>
<div class="wrapper">		
<?php if ( esc_url(get_header_image()) != '') {?>
<div class="cdns-header-img">
	<div class="cdns-header-content"> </div>
	<img class="img-responsive" src="<?php echo esc_url(get_header_image()); ?>" height="<?php echo esc_attr(get_custom_header()->height); ?>" width="<?php echo esc_attr(get_custom_header()->width); ?>" alt="" />
</div>
<?php } ?>
	<header id="header" class="header-type-three">
			<div id="top-bar">
				<div class="container clearfix">
					<div id="top-bar-phone" class="topbar-item  left-icon contact-phone-left">
						<a class="contact-phone icon-before" href="callto:<?php echo esc_attr($contact_phone) ?>"> <?php echo esc_html($contact_phone); ?> </a> 
					</div>
					<div id="top-bar-email" class="topbar-item left-icon contact-email-left">
						<a class="contact-email icon-before" href="mailto:<?php echo esc_attr($contact_email); ?>"> <?php echo esc_html($contact_email); ?> </a>
					</div>
					<div id="top-bar-socials" class="topbar-item social-icon-right">
						<?php cosmica_social_links();  ?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>			
		
		<div class="middle-bar">
			<div class="container">
				<aside id="site-logo-text-container" class="site-logo col-lg-2 col-md-3 col-sm-5 col-xs-5">
					<?php if (!empty(esc_url($logo_url)) && intval($cosmica_show_logo)): ?>
						<div id="site-logo">
							<a href="<?php echo esc_url($site_url); ?>" rel="home" title="<?php echo esc_attr($blog_title); ?>"><img itemprop="logo" src="<?php echo esc_url($logo_url); ?>" /> </a>
						</div>
					<?php else: ?>
					<div id="site-logo-text">
						<?php if ( is_front_page() && is_home() ) : ?>
						<h1 id="site-title" itemprop="headline"><a href="<?php echo esc_url($site_url); ?>" rel="home"><?php echo esc_html($blog_title); ?></a></h1>
						<?php else: ?>
						<h2 id="site-title" itemprop="headline"><a href="<?php echo esc_url($site_url) ?>" rel="home"><?php echo esc_html($blog_title); ?></a></h2>
						<?php endif; ?>
							<p id="site-description" itemprop="description"><?php echo esc_html($blog_description); ?></p>
					</div>
					<?php endif; ?>
				</aside>
				
				<section class="hc-right col-lg-10 col-md-9 col-sm-7 col-xs-7" id="hc-right">
					<div class="search-cart-container">
						<div role="button" id="search-button" href="#" class="search-button fa fa-search">
						</div>
						<div class="bottom-search-form-container">
								<div id="search-form-incont" class="search-form-incont animated lightSpeedOut">
								<?php get_search_form(); ?>
								</div>
						</div>
					</div>

					<div class="nav-container" id="primary-nav-container">
						<?php  wp_nav_menu(array('theme_location'=>'primary_menu', 'container'=> 'nav', 'container_class' => 'primary-nav big-navbar', 'container_id' => 'primary-nav',  'menu_id'=>'primary-menu','menu_class' => 'nav navbar-nav', 'fallback_cb' => false)); ?>
					</div>
					
					<div class="clearfix"></div>
				</section>
				
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="clearfix"></div>
	</header>

	<div id="main">