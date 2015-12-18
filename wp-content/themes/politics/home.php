<?php
/**
 * The template file displays the blog page.
 * Yes, I know it would be better named 'blog.php' but
 * the name is designated by WordPress template hierarchy
 * so ¯\_(ツ)_/¯
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package politics
 */

get_header();

$politics_blog_style = get_theme_mod('politics_blog_style', customizer_library_get_default( 'politics_blog_style' ) ); ?>

<div class="row">
	<div class="large-12 columns">
		<div class="page-header">

		<?php
			// Get the blog page title
			$politics_blog_title = get_theme_mod('politics_blog_title', customizer_library_get_default( 'politics_blog_title' ) );
			if ( $politics_blog_title) { ?>

				<h1 class="page-header-title">
					<?php echo wp_kses( $politics_blog_title, array('strong' => array(), 'a' => array('href') ) ); ?>
				</h1><!-- .page-header-title -->

			<?php } // end politics_blog_title ?>

		<?php
			// Get the blog page subtitle
			$politics_blog_subtitle = get_theme_mod('politics_blog_subtitle', customizer_library_get_default( 'politics_blog_subtitle' ) );
			if ( $politics_blog_subtitle ) { ?>

				<h2 class="page-header-subtitle">
					<?php echo wp_kses( $politics_blog_subtitle, array('strong' => array(), 'a' => array('href') ) ); ?>
				</h2><!-- .page-header-subtitle -->

			<?php } // end politics_blog_subtitle ?>

		</div><!-- .blog_page_header -->
	</div><!-- .large-12 .blog_page_header -->
</div><!-- .row -->

<div class="row">

	<div class="<?php if ( $politics_blog_style == "masonry" ) { echo "large-12"; } else { echo "large-8"; } ?>  columns">

		<div id="primary" class="content-area clearfix">
			<main id="main" class="site-main load <?php if ( $politics_blog_style == "masonry" ) { echo "masonry-wrap"; } else { echo "traditional-wrap"; } ?>" role="main">

					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'template-parts/content', $politics_blog_style ); ?>

					<?php endwhile; // end loop ?>

					<?php
            $args = array(
                    'prev_text' => __( 'Earlier Posts', 'politics' ),
                    'next_text' => __( 'Next Posts', 'politics' )
            );
						the_posts_navigation( $args );
					?>

					<?php else : ?>

						<?php get_template_part( 'template-parts/content', 'none' ); ?>

					<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

	</div><!-- <?php if ( $politics_blog_style == "masonry" ) { echo ".large-12"; } else { echo ".large-8"; } ?> -->

<?php if ( $politics_blog_style == "traditional" ) { ?>
	<div class="large-3 offset-1 columns inner-sidebar">

		<?php get_sidebar(); ?>

	</div><!-- .large-3 .offset-1 -->
<?php } // end politics_blog_style check ?>

</div><!-- .row -->

<?php get_footer(); ?>
