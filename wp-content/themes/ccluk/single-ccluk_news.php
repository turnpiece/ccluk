<?php

/**
 * The Template for displaying all news posts.
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCL UK Theme 1.0.0
 */
get_header();
?>

<div id="primary" class="site-content">
	<div id="content" role="main">

		<?php while (have_posts()) : the_post(); ?>

			<?php get_template_part('template-parts/content', get_post_format()); ?>

		<?php endwhile; // end of the loop. 
		?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php
get_footer();
