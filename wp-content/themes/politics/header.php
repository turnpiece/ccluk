<?php
/**
 * The header for our theme.
 *
 * @package politics
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php echo esc_attr( get_bloginfo( 'pingback_url' ) ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="hfeed site">

<header class="mini-header-wrap" role="banner">
	<div class="mini-header">
		<div class="row ">

			<div class="large-3 columns">

			<?php if ( has_nav_menu( 'social' ) ) : ?>
				<nav class="social-navigation" role="navigation">
					<?php
						// Social links navigation menu.
						wp_nav_menu( array(
							'theme_location' => 'social',
							'depth'          => 1,
							'link_before'    => '<span class="screen-reader-text">',
							'link_after'     => '</span>',
						) );
					?>
				</nav><!-- .social-navigation -->
			<?php endif; ?>

			</div><!-- .large-3 -->

			<div class="large-9 columns header-contact">

				<?php if ( get_theme_mod( 'politics-header-phone') !== '' ) { ?>
				<div class="header-phone">

					<?php $politics_header_phone = get_theme_mod( 'politics-header-phone', customizer_library_get_default( 'politics-header-phone' ) ); ?>

					<a href="tel:+<?php echo esc_attr( $politics_header_phone ); ?>">
						<i class="fa fa-phone"></i>
						<span><?php echo esc_attr( $politics_header_phone ); ?>
					</a>

				</div><!-- .header-phone -->
				<?php } ?>

				<?php if ( get_theme_mod( 'politics-header-address') !== '' ) { ?>
				<div class="header-address">

					<?php $politics_header_address = get_theme_mod( 'politics-header-address', customizer_library_get_default( 'politics-header-address' ) ); ?>

					<a target="_blank" href="http://maps.google.com/?q=<?php echo esc_attr( $politics_header_address ); ?>">
						<i class="fa fa-map-marker fa-lg"></i>
						<span><?php echo esc_attr( $politics_header_address ); ?>
					</a>

				</div><!-- .header-address -->
				<?php } ?>

			</div><!-- .large-9 -->

		</div><!-- .row -->
	</div><!-- .mini-header -->
</header><!-- .mini-header-wrap -->

<header id="masthead" class="site-header" role="banner">

	<div class="row">
		<div class="large-12 columns">

		<div class="site-branding">

		<?php
			$politics_logo = get_theme_mod( 'politics-logo', customizer_library_get_default( 'politics-logo' ) );
			if ( $politics_logo ) { ?>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img src="<?php echo esc_url( $politics_logo ) ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>

		<?php } else { ?>

			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'name' ) ); ?></a></h1>

			<p class="site-description"><?php echo esc_attr( get_bloginfo( 'description' ) ); ?></p>

		<?php } ?>

		</div><!-- .site-branding -->

		<nav id="site-navigation" role="navigation" aria-label="<?php _e( 'Primary Menu', 'politics' ); ?>">

        <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' 	=> 'primary',
                    'container'      => false,
                    'menu_class'     => 'main-navigation',
                    'walker'         => new Politics_Aria_Walker_Nav_Menu(),
                    'items_wrap'     => '<ul id="%1$s" class="%2$s" role="menubar">%3$s</ul>',
                    )
                );
            }
        ?>

		</nav><!-- #site-navigation -->

		</div><!-- .large-12 -->
	</div><!-- .row -->

	<?php if ( !is_page_template( 'front-page-template.php' ) ) { ?>
		<hr>
	<?php } ?>

	</header><!-- #masthead -->

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'politics' ); ?></a>

	<?php if ( is_page_template( 'front-page-template.php' ) ) { ?>

		<div class="home_hero">

			<div class="home-header-bg color-overlay clearfix">

			  <div class="hero-widgets-wrap">

			    <div class="row">

			      <div class="large-8 large-centered columns">

			        <?php if ( is_active_sidebar( 'home-hero' ) ) { ?>

			          <?php dynamic_sidebar( 'home-hero' ); ?>

			        <?php } ?>

			      </div><!-- .large-8 -->

			    </div><!-- .row -->

			  </div><!-- .hero-widgets-wrap -->

			</div><!-- .header-bg -->

		</div><!-- .color-overlay -->

	<?php } ?>

<div id="content" class="site-content" role="main">
