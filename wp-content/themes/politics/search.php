<?php
/**
 * The template for displaying search results pages.
 *
 * @package politics
 */

get_header();
?>

<?php if ( have_posts() ) : ?>

	<div class="row">
		<div class="large-12 columns">

			<header class="page-header">
				<h1 class="page-header-title"><?php printf( esc_html__( 'Search Results for: %s', 'politics' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</header><!-- .page-header -->

		</div><!-- .large-12 .blog_page_header -->
	</div><!-- .row -->

<div class="row">

	<div class="large-8 columns">

	<section id="primary" class="content-area">
		<main id="main" class="site-main traditional-wrap" role="main">

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part( 'template-parts/content', 'search' );
			?>

			<?php endwhile; ?>

			<?php the_posts_navigation(); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content', 'none' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

</div><!-- .large-8 -->

	<div class="large-3 offset-1 columns inner-sidebar">

		<?php get_sidebar(); ?>

	</div><!-- .large-3 .offset-1 -->

</div><!-- .row -->

<?php get_footer(); ?>
