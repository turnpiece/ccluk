<?php

/**
 * The template used for displaying front page content
 *
 * @package WordPress
 * @subpackage CCLUK Theme
 * @since CCLUK Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
</article>