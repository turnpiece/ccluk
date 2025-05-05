<?php

/**
 * The template used for displaying page content in page.php
 *
 * @package WordPress
 * @subpackage CCLUK Theme
 * @since CCLUK Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php if (has_post_thumbnail()) : ?>
			<?php the_post_thumbnail('ccluk-medium') ?>
		<?php endif; ?>
	</header>

	<div class="entry-content">
		<?php the_content(); ?>
	</div>

</article>