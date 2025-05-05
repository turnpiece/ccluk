<?php

/**
 * The template for displaying Category pages.
 *
 * Used to display archive-type pages for posts in a category.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCL UK Theme 1.0.0
 */
get_header();
?>

<div class="home-inner-wrap listing category">

	<div id="primary" class="site-content">

		<div id="content" role="main">

			<?php if (have_posts()) : ?>
				<header class="archive-header dir-header">
					<h1 class="archive-title"><?php echo single_cat_title('', false) ?></h1>

					<?php if (category_description()) : // Show an optional category description 
					?>
						<div class="archive-meta"><?php echo category_description(); ?></div>
					<?php endif; ?>
				</header><!-- .archive-header -->

				<?php
				/* Start the Loop */
				while (have_posts()) : the_post(); ?>

					<div class="article-outher">

						<?php get_template_part('template-parts/content', 'author'); ?>

						<div class="content-wrap">
							<?php get_template_part('template-parts/content', get_post_format()); ?>
						</div>

					</div>

				<?php endwhile; ?>

				<div class="pagination-below">
					<?php ccluk_pagination(); ?>
				</div>

			<?php else : ?>
				<?php get_template_part('template-parts/content', 'none'); ?>
			<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->
</div>

<?php get_footer();
