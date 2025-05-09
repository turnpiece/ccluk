<?php

/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package OneSocial Theme
 */
get_header();

?>

<div class="home-inner-wrap listing archive">

	<div id="primary" class="site-content">

		<div id="content" role="main">

			<?php if (have_posts()) : ?>

				<header class="archive-header dir-header">
					<?php

					if (is_post_type_archive())
						echo '<h1 class="page-title">' . post_type_archive_title('', false) . '</h1>';
					elseif (is_author())
						echo '<span class="author-avatar">' . get_avatar(get_the_author_meta('ID'), 70, '', get_the_author()) . '</span><h1 class="page-title">' . get_the_author() . '</h1>';
					else
						the_archive_title('<h1 class="page-title">', '</h1>');

					the_archive_description('<div class="taxonomy-description">', '</div>');
					?>
				</header><!-- .archive-header -->

				<?php
				/* Start the Loop */
				while (have_posts()) : the_post(); ?>

					<div class="article-outher">
						<?php get_template_part('template-parts/content'); ?>
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

<?php
get_footer();
