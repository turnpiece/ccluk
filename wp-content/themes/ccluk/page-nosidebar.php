<?php

/**
 *
 * Template Name: No Sidebar
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 *
 */
get_header();
?>

<div id="primary" class="site-content default-page">

	<div id="content" role="main">

		<?php while (have_posts()) : the_post(); ?>
			<?php get_template_part('template-parts/content', 'page'); ?>
		<?php endwhile; // end of the loop.  
		?>

	</div>
</div>

<?php

get_footer();
