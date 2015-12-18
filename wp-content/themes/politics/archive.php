<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package politics
 */

get_header();
?>

<?php if ( have_posts() ) : ?>

	<div class="row">
		<div class="large-12 columns">

			<header class="page-header">
				<?php
					the_archive_title( '<h1 class="page-header-title">', '</h1>' );
					the_archive_description( '<h2 class="page-header-subtitle"><div class="taxonomy-description">', '</div></h2>' );
				?>
			</header><!-- .page-header -->

		</div><!-- .large-12 .blog_page_header -->
	</div><!-- .row -->

	<div class="row">

		<div class="large-8 columns">

		<div id="primary" class="content-area">
			<main id="main" class="site-main traditional-wrap" role="main">

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'template-parts/content', 'content' ); ?>

				<?php endwhile; ?>

				<?php the_posts_navigation(); ?>

			<?php else : ?>

				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		</div><!-- .large-8 -->

		<div class="large-3 offset-1 columns inner-sidebar">

			<?php get_sidebar(); ?>

		</div><!-- .large-3 .offset-1 -->

	</div><!-- .row -->

<?php get_footer(); ?>
